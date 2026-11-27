<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

namespace WEM\FormConditionalNotificationsBundle\EventListener;

use Codefog\HasteBundle\FileUploadNormalizer;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Form;
use Contao\StringUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Config\FormConfig;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\FormConfigStamp;
use WEM\FormConditionalNotificationsBundle\Condition\ConditionMatcher;
use WEM\FormConditionalNotificationsBundle\Model\Notification;

/**
 * Sends the first conditional notification (in sorting order) whose conditions match the submitted data.
 */
#[AsHook('processFormData')]
class ProcessFormDataListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ConditionMatcher $conditionMatcher,
        private readonly NotificationCenter $notificationCenter,
        private readonly FileUploadNormalizer $fileUploadNormalizer,
        private readonly ConfigLoader $configLoader,
        #[Autowire(service: 'monolog.logger.contao.general')]
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed>      $submittedData
     * @param array<string, mixed>      $formData
     * @param array<string, mixed>|null $files
     * @param array<string, mixed>      $labels
     */
    public function __invoke(array $submittedData, array $formData, array|null $files, array $labels, Form $form): void
    {
        $notifications = $this->framework->getAdapter(Notification::class)->findBy('pid', (int) $form->id, [
            'order' => 'sorting ASC',
        ]);

        if ($notifications === null) {
            return;
        }

        foreach ($notifications as $notification) {
            if (! $this->conditionMatcher->matches($notification, $submittedData)) {
                continue;
            }

            try {
                $this->sendNotification($notification, $submittedData, $formData, $files ?? [], $labels, $form);

                $this->logger->info(\sprintf('The notification ID %s has been sent from the conditional notification ID %s', $notification->nc_notification, $notification->id));
            } catch (\Throwable $e) {
                $this->logger->error(\sprintf('Unable to send the conditional notification ID %s: %s', $notification->id, $e->getMessage()), [
                    'exception' => $e,
                ]);
            }

            return;
        }
    }

    /**
     * Tokens are built the same way as the Notification Center does for the form generator,
     * then overridden by the ones configured on the conditional notification.
     *
     * @param array<string, mixed> $submittedData
     * @param array<string, mixed> $formData
     * @param array<string, mixed> $files
     * @param array<string, mixed> $labels
     */
    private function sendNotification(Notification $notification, array $submittedData, array $formData, array $files, array $labels, Form $form): void
    {
        $tokens = [];
        $rawData = [];
        $rawDataFilled = [];
        $bulkyItemVouchers = [];

        foreach ($submittedData as $k => $v) {
            // Skip the tokens that are not implodeable
            if (\is_array($v)) {
                foreach ($v as $vv) {
                    if (! \is_scalar($vv)) {
                        continue 2;
                    }
                }
            }

            $label = isset($labels[$k]) && \is_string($labels[$k]) ? StringUtil::decodeEntities($labels[$k]) : ucfirst((string) $k);
            $line = $label . ': ' . (\is_array($v) ? implode(', ', $v) : $v);

            $tokens['formlabel_' . $k] = $label;
            $tokens['form_' . $k] = $v;
            $rawData[] = $line;

            if (\is_array($v) || (string) $v !== '') {
                $rawDataFilled[] = $line;
            }
        }

        foreach ($formData as $k => $v) {
            $tokens['formconfig_' . $k] = \is_string($v) ? StringUtil::decodeEntities($v) : $v;
        }

        $tokens['raw_data'] = implode("\n", $rawData);
        $tokens['raw_data_filled'] = implode("\n", $rawDataFilled);
        $tokens['html_data'] = implode('<br>', $rawData);
        $tokens['html_data_filled'] = implode('<br>', $rawDataFilled);

        foreach ($this->fileUploadNormalizer->normalize($files) as $k => $normalizedFiles) {
            $paths = [];
            $vouchers = [];

            foreach ($normalizedFiles as $file) {
                $fileItem = \is_resource($file['stream'])
                    ? FileItem::fromStream($file['stream'], $file['name'], $file['type'], $file['size'])
                    : FileItem::fromPath($file['tmp_name'], $file['name'], $file['type'], $file['size']);

                $vouchers[] = $this->notificationCenter->getBulkyItemStorage()->store($fileItem);
                $paths[] = $file['tmp_name'];
            }

            $tokens['form_' . $k] = implode(',', $vouchers);
            $tokens['file_' . $k] = implode(',', $paths);
            $bulkyItemVouchers = [...$bulkyItemVouchers, ...$vouchers];
        }

        foreach (StringUtil::deserialize($notification->tokens, true) as $token) {
            $key = (string) ($token['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $tokens[str_starts_with($key, 'form_') ? $key : 'form_' . $key] = $token['value'] ?? '';
        }

        // Make sure we don't pass any objects as tokens
        $tokens = array_filter($tokens, static fn (mixed $v): bool => ! \is_object($v));

        $notificationId = (int) $notification->nc_notification;
        $stamps = $this->notificationCenter->createBasicStampsForNotification($notificationId, $tokens, $notification->language ?: null);

        if ($bulkyItemVouchers !== []) {
            $stamps = $stamps->with(new BulkyItemsStamp($bulkyItemVouchers));
        }

        $formConfig = $this->configLoader->loadForm((int) $form->id);

        if ($formConfig instanceof FormConfig) {
            $stamps = $stamps->with(new FormConfigStamp($formConfig));
        }

        $this->notificationCenter->sendNotificationWithStamps($notificationId, $stamps);
    }
}
