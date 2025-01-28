<?php

declare(strict_types=1);

namespace WEM\FormConditionalNotificationsBundle\DataContainer;

use Contao\DataContainer;
use Contao\FormFieldModel;
use Contao\Image;
use Contao\System;
use NotificationCenter\Model\Notification as NotificationCore;
use WEM\FormConditionalNotificationsBundle\Model\Field as FieldModel;
use WEM\FormConditionalNotificationsBundle\Model\Notification as NotificationModel;

class Notification
{
    public function listItems(array $row): string
    {
        $pattern = '%s | %s<br /><ul style="padding-left:15px">%s</ul>';
        $arrLanguages = System::getLanguages();
        $arrCountries = System::getCountries();

        $objNotification = NotificationCore::findByPk($row['nc_notification']);
        $args[] = $objNotification->title;
        $args[] = $arrLanguages[$row['language']] ?: $GLOBALS['TL_LANG']['WEM']['FCN']['neutral'];

        // Get the fields
        $objFields = FieldModel::findBy('pid', $row['id'], ["order"=>"sorting ASC"]);

        if(!$objFields || 0 == $objFields->count()){
            $args[] = '<li>'.$GLOBALS['TL_LANG']['WEM']['FCN']['defaultConfig'].'</li>';
        }
        else{
            $strList = '';
            while($objFields->next()){
                $objField = FormFieldModel::findByPk($objFields->field);
                switch($objField->type){
                    case 'select':
                    case 'checkbox':
                    case 'radio':
                        $arrValues = deserialize($objFields->value);
                        $strList .= '<li>- '.$objField->label.' = '.implode(', ', $arrValues).'</li>';
                    break;
                    case 'countryselect':
                        $strList .= '<li>- '.$objField->label.' = '.$arrCountries[$objFields->value].'</li>';
                    break;
                    default:
                        $strList .= '<li>- '.$objField->label.' = '.$objFields->value.'</li>';
                }
                
            }
            $args[] = $strList;
        }


        return vsprintf($pattern, $args);
    }

    /**
     * Return the edit notification wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function editNotification(DataContainer $dc): string
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=nc_notifications&amp;table=tl_nc_message&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(specialchars($GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['editNotification'][1]), $dc->value) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['editNotification'][1], $dc->value))) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['editNotification'][0], 'style="vertical-align:top"') . '</a>';
    }
}
