<?php

declare(strict_types=1);

namespace WEM\FormConditionalNotificationsBundle\DataContainer;

use Contao\DataContainer;
use Contao\FormModel;
use Contao\FormFieldModel;
use Contao\Image;
use Contao\System;
use WEM\FormConditionalNotificationsBundle\Model\Field as FieldModel;
use WEM\FormConditionalNotificationsBundle\Model\Notification as NotificationModel;

class Field extends \tl_wem_form_conditional_notification_field
{
    public function __construct()
    {
        parent::__construct();
    }

    public function adjustPalette($objDc)
    {
        $objField = FieldModel::findByPk($objDc->id);
        $objFormField = FormFieldModel::findByPk($objField->field);
        $objForm = FormModel::findByPk($objFormField->pid);

        if($objFormField->type == "select" || $objFormField->type == "radio" || $objFormField->type == "checkbox"){
            $GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['inputType'] = 'select';
            
            $arrOptions = [];
            
            /** @var \FormFieldModel $objField */
            $strClass = $GLOBALS['TL_FFL'][$objFormField->type];
            $objWidget = new $strClass($objFormField->row());

            // HOOK: load form field callback
            if (isset($GLOBALS['TL_HOOKS']['loadFormField']) && is_array($GLOBALS['TL_HOOKS']['loadFormField'])){
                foreach ($GLOBALS['TL_HOOKS']['loadFormField'] as $callback){
                    $this->import($callback[0]);
                    $objWidget = $this->{$callback[0]}->{$callback[1]}($objWidget, "", $objForm->row(), $this);
                }
            }

            foreach($objWidget->options as $arrOption){
                if($arrOption['group']){
                    $strOptionValuePrefix = "group**";
                    $strOptionType = "GROUP - ";
                }
                else{
                    $strOptionValuePrefix = "option**";
                    $strOptionType = "OPTION - ";
                }

                $arrOptions[$strOptionValuePrefix.$arrOption['value']] = $strOptionType . $arrOption['label'];
            }
            $GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['options'] = $arrOptions;
            $GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['eval']['chosen'] = true;
            $GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['eval']['multiple'] = true;
        }

        if($objFormField->type == "countryselect"){
            $GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['inputType'] = 'select';
            $GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['options'] = System::getCountries();
            $GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['eval']['chosen'] = true;
        }
    }

    /**
     * Return form fields
     *
     * @return array
     */
    public function getFormFields($objDc): array
    {
        $objFormSubmissionField = $this->Database->prepare("SELECT pid FROM tl_wem_form_conditional_notification_field WHERE id = ?")->execute($objDc->id);
        $objFormSubmission = $this->Database->prepare("SELECT pid FROM tl_wem_form_conditional_notification WHERE id = ?")->execute($objFormSubmissionField->pid);
        $objFormFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid = ?")->execute($objFormSubmission->pid);
        $arrFields = array();
        while ($objFormFields->next()) {
            $arrFields[$objFormFields->id] = $objFormFields->label;
        }
        return $arrFields;
    }

    public function listItems($row): string
    {
        $arrCountries = System::getCountries();
        $objField = FormFieldModel::findByPk($row['field']);
        $strValue = '';
        switch($objField->type){
            case 'select':
            case 'checkbox':
            case 'radio':
                $arrValues = deserialize($row['value']);
                if(!empty($arrValues))
                    $strValue = implode(', ', $arrValues);
            break;
            case 'countryselect':
                $strValue = $arrCountries[$row['value']];
            break;
            default:
                $strValue = $row['value'];
        }
        return sprintf('%s | %s', $objField->label, $strValue);
    }
}