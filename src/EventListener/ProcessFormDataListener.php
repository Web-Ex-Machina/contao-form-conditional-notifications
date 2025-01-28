<?php

declare(strict_types=1);

namespace WEM\FormConditionalNotificationsBundle\EventListener;

use Codefog\HasteBundle\FileUploadNormalizer;
use Contao\Form;
use Contao\FormFieldModel;
use Contao\StringUtil;
use Contao\System;
use Exception;
use Psr\Log\LoggerInterface;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\FormConfigStamp;
use WEM\FormConditionalNotificationsBundle\Model\Field as FieldModel;
use WEM\FormConditionalNotificationsBundle\Model\Notification as NotificationModel;

class ProcessFormDataListener
{
	public function __construct(
        private readonly NotificationCenter $notificationCenter,
        private readonly FileUploadNormalizer $fileUploadNormalizer,
        private readonly ConfigLoader $configLoader,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

	public function __invoke(array $arrData, array $arrForm, ?array $arrFiles, array $arrLabels, Form $form): void
	{
		try {
			// Check if we have an overide set up
			if (0 === NotificationModel::countBy('pid', $form->id)) {
				return;
			}

			// Then, check, in the sorting/priority order, if there is a match in the overides
			$objRows = NotificationModel::findBy('pid', $form->id, ["order"=>"sorting ASC"]);
			
			while ($objRows->next()) {
				// Get all the conditions
				$objConditions = FieldModel::findBy('pid', $objRows->id, ["order"=>"sorting ASC"]);
				$blnSendNotification = true;

				// If there is no conditions, we consider this as the default one and we must use it.
				if ($objConditions && 0 < $objConditions->count()) {
					// For each condition saved, check if the field sent match
					while ($objConditions->next()) {
						// Get the field model
						$objField = FormFieldModel::findByPk($objConditions->field);

						// You shall not be checked !
						if (!$objField) {
							continue;
						}

						// Depending on the field type, there is maybe a special treatment
						switch ($objField->type) {
							case "select":
							case "radio":
							case "checkbox":
								$arrConditionValues = unserialize($objConditions->value);

								foreach ($arrConditionValues as $strValue) {
									$arrConditionValue = explode("**", $strValue);

									// If we want a group, we must check if the selected value is in the group
									if ($arrConditionValue[0] == "group"){
										// First, find the correspondant key in the field options and use them to extract the possible options
										$intStartKey = 0;
										$arrOptions = [];
										foreach (unserialize($objField->options) as $intKey => $arrOption) {
											// Start point of our available options
											if (array_key_exists('group', $arrOption) && $arrOption['group'] && $arrOption['value'] == $arrConditionValue[1]) {
												$intStartKey = $intKey;
											}
											// End point of our available options
											else if ($intStartKey > 0 && array_key_exists('group', $arrOption) && $arrOption['group']) {
												break;
											}
											// Add option to available ones
											else if($intStartKey > 0) {
												$arrOptions[] = $arrOption['value'];
											}
										}
									}
									// Else, it's an option, and we add the plain value
									else {
										$arrOptions[] = $arrConditionValue[1];
									}
								}

								// Then check if the value is in the available ones
								if (!empty($arrOptions) && !in_array($arrData[$objField->name], $arrOptions)) {
									$blnSendNotification = false;
									break(2); // I want to break threeeeeee (it was three before... sad...)
								}

							break;
							default:
								// Break the while loop if something is wrong
								if ($arrData[$objField->name] != $objConditions->value) {
									$blnSendNotification = false;
									break(2);
								}
						}
					}
				}

				// If all the conditions are satisfying, send the notification and break the loop
				if ($blnSendNotification) {
					$this->sendNotification(
						$arrData,
						$arrForm,
						$arrFiles,
						$arrLabels,
						$form,
						$objRows->row()
					);

					$this->logger?->info(sprintf($GLOBALS['TL_LANG']['WEM']['FCN']['notificationSent'], $objNotification->id, $objRows->id));
			        return;
				}
			}
		}
		catch (Exception $e) {
			$this->logger?->error(vsprintf($GLOBALS['TL_LANG']['WEM']['FCN']['exceptionThrown'], [$e->getMessage(), $e->getTrace()]));
		}
	}

	/**
     * @param array<string, mixed>      $submittedData
     * @param array<string, mixed>      $formData
     * @param array<string, mixed>|null $files
     * @param array<string, mixed>      $labels
     */
    public function sendNotification(array $submittedData, array $formData, array|null $files, array $labels, Form $form, array $arrNotif): void
    {
        $tokens = [];
        $rawData = [];
        $rawDataFilled = [];
        $bulkyItemVouchers = [];
        $files = !\is_array($files) ? [] : $files; // In Contao 4.13, $files can be null

        foreach ($submittedData as $k => $v) {
            // Skip the tokens that are not implodeable
            if (\is_array($v)) {
                foreach ($v as $vv) {
                    if (!\is_scalar($vv)) {
                        continue 2;
                    }
                }
            }

            $label = isset($labels[$k]) && \is_string($labels[$k]) ? StringUtil::decodeEntities($labels[$k]) : ucfirst($k);

            $tokens['formlabel_'.$k] = $label;
            $tokens['form_'.$k] = $v;

            $rawData[] = $label.': '.(\is_array($v) ? implode(', ', $v) : $v);

            if (\is_array($v) || ('' !== (string) $v)) {
                $rawDataFilled[] = $label.': '.(\is_array($v) ? implode(', ', $v) : $v);
            }
        }

        foreach ($formData as $k => $v) {
            $tokens['formconfig_'.$k] = \is_string($v) ? StringUtil::decodeEntities($v) : $v;
        }

        $tokens['raw_data'] = implode("\n", $rawData);
        $tokens['raw_data_filled'] = implode("\n", $rawDataFilled);

        foreach ($this->fileUploadNormalizer->normalize($files) as $k => $files) {
            $vouchers = [];

            foreach ($files as $file) {
                $fileItem = \is_resource($file['stream']) ?
                    FileItem::fromStream($file['stream'], $file['name'], $file['type'], $file['size']) :
                    FileItem::fromPath($file['tmp_name'], $file['name'], $file['type'], $file['size']);

                $vouchers[] = $this->notificationCenter->getBulkyItemStorage()->store($fileItem);
            }

            $tokens['form_'.$k] = implode(',', $vouchers);
            $bulkyItemVouchers = array_merge($bulkyItemVouchers, $vouchers);
        }

        // Merge with the override ones
        $arrOverrideTokens = unserialize($arrNotif['tokens']);

        if(is_array($arrOverrideTokens) && !empty($arrOverrideTokens)){
        	// Fallback if the saved keys
        	foreach($arrOverrideTokens as $arrToken){
        		if("form_" != substr($arrToken["key"], 0, 5))
        			$tokens["form_".$arrToken["key"]] = $arrToken['value'];
        		else
        			$tokens[$arrToken["key"]] = $arrToken['value'];
        	}
        }

        // Make sure we don't pass any objects as tokens
        $tokens = array_filter($tokens, static fn ($v) => !\is_object($v));

        $stamps = $this->notificationCenter->createBasicStampsForNotification(
            (int) $arrNotif['nc_notification'],
            $tokens,
        );

        if (0 !== \count($bulkyItemVouchers)) {
            $stamps = $stamps->with(new BulkyItemsStamp($bulkyItemVouchers));
        }

        $formConfig = $this->configLoader->loadForm((int) $form->id);

        if (null !== $formConfig) {
            $stamps = $stamps->with(new FormConfigStamp($formConfig));
        }

        $this->notificationCenter->sendNotificationWithStamps((int) $arrNotif['nc_notification'], $stamps);
    }
}