<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

namespace WEM\FormConditionalNotificationsBundle\Model;

use Contao\Model;

/**
 * @property int         $id
 * @property int         $pid
 * @property int         $sorting
 * @property int         $tstamp
 * @property int         $field
 * @property string|null $value
 */
class Field extends Model
{
    protected static $strTable = 'tl_wem_form_conditional_notification_field';
}
