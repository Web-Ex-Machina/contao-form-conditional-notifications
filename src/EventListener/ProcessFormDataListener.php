<?php

declare(strict_types=1);

namespace WEM\FormConditionalNotificationsBundle\EventListener;

use Contao\Form;
use Contao\FormFieldModel;
use Contao\System;
use Exception;
use WEM\FormConditionalNotificationsBundle\Model\Field as FieldModel;
use WEM\FormConditionalNotificationsBundle\Model\Notification as NotificationModel;

class ProcessFormDataListener
{
	public function __invoke(array $arrData, array $arrForm, ?array $arrFiles, array $arrLabels, Form $form): void
	{
		try {
			// Check if we have an overide set up
			if(0 === NotificationModel::countBy('pid', $form->id))
				return;

			// Then, check, in the sorting/priority order, if there is a match in the overides
			$objRows = NotificationModel::findBy('pid', $form->id, ["order"=>"sorting ASC"]);
			while($objRows->next()){
				// Get all the conditions
				$objConditions = FieldModel::findBy('pid', $objRows->id, ["order"=>"sorting ASC"]);
				$blnSendNotification = true;

				// If there is no conditions, we consider this as the default one and we must use it.
				if($objConditions && 0 < $objConditions->count()){
					// For each condition saved, check if the field sent match
					while($objConditions->next()){
						// Get the field model
						$objField = FormFieldModel::findByPk($objConditions->field);

						// You shall not be checked !
						if(!$objField)
							continue;

						// Depending on the field type, there is maybe a special treatment
						switch($objField->type){
							case "select":
							case "radio":
							case "checkbox":
								$arrConditionValues = deserialize($objConditions->value);

								foreach($arrConditionValues as $strValue){
									$arrConditionValue = explode("**", $strValue);

									// If we want a group, we must check if the selected value is in the group
									if($arrConditionValue[0] == "group"){
										// First, find the correspondant key in the field options and use them to extract the possible options
										$intStartKey = 0;
										$arrOptions = [];
										foreach(deserialize($objField->options) as $intKey => $arrOption){
											// Start point of our available options
											if(array_key_exists('group', $arrOption) && $arrOption['group'] && $arrOption['value'] == $arrConditionValue[1])
												$intStartKey = $intKey;
											// End point of our available options
											else if($intStartKey > 0 && array_key_exists('group', $arrOption) && $arrOption['group'])
												break;
											// Add option to available ones
											else if($intStartKey > 0)
												$arrOptions[] = $arrOption['value'];
										}
									}
									// Else, it's an option, and we add the plain value
									else{
										$arrOptions[] = $arrConditionValue[1];
									}
								}

								// Then check if the value is in the available ones
								if(!empty($arrOptions) && !in_array($arrData[$objField->name], $arrOptions)){
									$blnSendNotification = false;
									break(2); // I want to break threeeeeee (it was three before... sad...)
								}

							break;
							default:
								// Break the while loop if something is wrong
								if($arrData[$objField->name] != $objConditions->value){
									$blnSendNotification = false;
									break(2);
								}
						}
					}
				}

				// If all the conditions are satisfying, send the notification and break the loop
				if($blnSendNotification){
					// Get the notification
					$objNotification = Notification::findByPk($objRows->nc_notification);

					// Generate the tokens
					$arrTokens = $this->generateTokens(
		                (array) $arrData,
		                (array) $arrForm,
		                (array) $arrFiles,
		                (array) $arrLabels,
		                $objNotification->flatten_delimiter ?: ','
		            );

		            // Merge with the override ones
		            $arrOverrideTokens = deserialize($objRows->tokens);

		            if(is_array($arrOverrideTokens) && !empty($arrOverrideTokens)){
		            	// Fallback if the saved keys
		            	foreach($arrOverrideTokens as $arrToken){
		            		if("form_" != substr($arrToken["key"], 0, 5))
		            			$arrTokens["form_".$arrToken["key"]] = $arrToken['value'];
		            		else
		            			$arrTokens[$arrToken["key"]] = $arrToken['value'];
		            	}

		            	// And merge with the default one
		            	$arrTokens = array_merge($arrTokens, $arrOverrideTokens);
		            }

					// And send the notification with the language wanted
					if($objNotification->send($arrTokens, $objRows->language ?: $GLOBALS['TL_LANGUAGE']))
						System::log(sprintf($GLOBALS['TL_LANG']['WEM']['FCN']['notificationSent'], $objNotification->id, $objRows->id), __METHOD__, "WEM_FCN");

			         // And break the loop
			        break;
				}
			}
		}
		catch(Exception $e){
			System::log(vsprintf($GLOBALS['TL_LANG']['WEM']['FCN']['exceptionThrown'], [$e->getMessage(), $e->getTrace()]), __METHOD__, "WEM_FCN");
		}
	}

	/**
     * Generate the tokens
     *
     * @param array $arrData
     * @param array $arrForm
     * @param array $arrFiles
     * @param array $arrLabels
     * @param string $delimiter
     *
     * @return array
     */
    public function generateTokens(array $arrData, array $arrForm, array $arrFiles, array $arrLabels, $delimiter)
    {
        $arrTokens = array();
        $arrTokens['raw_data'] = '';

        foreach ($arrData as $k => $v) {
            $this->flatten($v, 'form_'.$k, $arrTokens, $delimiter);
            $arrTokens['formlabel_'.$k] = isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k);
            $arrTokens['raw_data'] .= (isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . "\n";
        }

        foreach ($arrForm as $k => $v) {
            $this->flatten($v, 'formconfig_'.$k, $arrTokens, $delimiter);
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        // Upload fields
        foreach ($arrFiles as $fieldName => $file) {
            $arrTokens['form_' . $fieldName] = Form::getFileUploadPathForToken($file);
        }

        return $arrTokens;
    }

    /**
     * Flatten input data, Simple Tokens can't handle arrays
     *
     * @param mixed  $varValue
     * @param string $strKey
     * @param array  $arrData
     * @param string $strPattern
     */
    public function flatten($varValue, $strKey, array &$arrData, $strPattern = ', ')
    {
        if (is_object($varValue)) {
            return;
        } elseif (!is_array($varValue)) {
            $arrData[$strKey] = $varValue;
            return;
        }

        $blnAssoc = array_is_assoc($varValue);
        $arrValues = array();

        foreach ($varValue as $k => $v) {
            if ($blnAssoc || is_array($v)) {
                $this->flatten($v, $strKey.'_'.$k, $arrData);
            } else {
                $arrData[$strKey.'_'.$v] = '1';
                $arrValues[]             = $v;
            }
        }

        $arrData[$strKey] = implode($strPattern, $arrValues);
    }
}