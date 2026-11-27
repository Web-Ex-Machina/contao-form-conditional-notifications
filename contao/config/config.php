<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

use WEM\FormConditionalNotificationsBundle\Model\Field;
use WEM\FormConditionalNotificationsBundle\Model\Notification;

/*
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_wem_form_conditional_notification';
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_wem_form_conditional_notification_field';

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_wem_form_conditional_notification'] = Notification::class;
$GLOBALS['TL_MODELS']['tl_wem_form_conditional_notification_field'] = Field::class;
