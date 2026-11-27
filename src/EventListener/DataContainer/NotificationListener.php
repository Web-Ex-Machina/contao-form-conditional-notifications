<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

namespace WEM\FormConditionalNotificationsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Intl\Locales;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\NotificationType\FormGeneratorNotificationType;
use WEM\FormConditionalNotificationsBundle\Condition\ConditionFormatter;
use WEM\FormConditionalNotificationsBundle\Model\Field;

class NotificationListener
{
    private const string TABLE = 'tl_wem_form_conditional_notification';

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly NotificationCenter $notificationCenter,
        private readonly ConditionFormatter $conditionFormatter,
        private readonly Locales $locales,
        private readonly Connection $connection,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<int, string>
     */
    #[AsCallback(table: self::TABLE, target: 'fields.nc_notification.options')]
    public function getNotificationOptions(): array
    {
        return $this->notificationCenter->getNotificationsForNotificationType(FormGeneratorNotificationType::NAME);
    }

    /**
     * @return array<string, string>
     */
    #[AsCallback(table: self::TABLE, target: 'fields.language.options')]
    public function getLanguageOptions(): array
    {
        return $this->locales->getLocales();
    }

    #[AsCallback(table: self::TABLE, target: 'fields.nc_notification.wizard')]
    public function editNotificationWizard(DataContainer $dc): string
    {
        if ((int) $dc->value < 1) {
            return '';
        }

        $title = $this->translator->trans(self::TABLE . '.editNotification.1', [$dc->value], 'contao_' . self::TABLE);
        $href = $this->urlGenerator->generate('contao_backend', [
            'do' => 'nc_notifications',
            'table' => 'tl_nc_message',
            'id' => $dc->value,
            'popup' => '1',
            'nb' => '1',
        ]);

        return \sprintf(
            ' <a href="%s" title="%s" onclick="Backend.openModalIframe({\'title\':\'%s\',\'url\':this.href});return false">%s</a>',
            StringUtil::specialcharsUrl($href),
            StringUtil::specialchars($title),
            StringUtil::specialchars(str_replace("'", "\\'", $title)),
            $this->framework->getAdapter(Image::class)->getHtml('edit.svg', $title),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    #[AsCallback(table: self::TABLE, target: 'list.sorting.child_record')]
    public function listNotification(array $row): string
    {
        $notificationTitle = $this->connection->fetchOne('SELECT title FROM tl_nc_notification WHERE id = ?', [(int) $row['nc_notification']]);
        $language = $row['language']
            ? $this->locales->getLocales()[$row['language']] ?? $row['language']
            : $this->translator->trans('WEM.FCN.neutral', [], 'contao_default');

        $items = [];
        $conditions = $this->framework->getAdapter(Field::class)->findBy('pid', (int) $row['id'], [
            'order' => 'sorting ASC',
        ]);

        foreach ($conditions ?? [] as $condition) {
            if ($condition->value === null || null === $formatted = $this->conditionFormatter->format($condition->row())) {
                continue;
            }

            $items[] = \sprintf('<li>%s = %s</li>', StringUtil::specialchars($formatted['label']), StringUtil::specialchars($formatted['value']));
        }

        if ($items === []) {
            $items[] = \sprintf('<li>%s</li>', $this->translator->trans('WEM.FCN.defaultConfig', [], 'contao_default'));
        }

        return \sprintf(
            '%s | %s<ul style="padding-left:15px;margin-top:5px;list-style:disc">%s</ul>',
            StringUtil::specialchars($notificationTitle !== false ? (string) $notificationTitle : '-'),
            StringUtil::specialchars($language),
            implode('', $items),
        );
    }
}
