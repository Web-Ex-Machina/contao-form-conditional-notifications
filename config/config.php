<?php

/**
 * Form Conditional Notifications Extension for Contao Open Source CMS
 *
 * Copyright (c) 2018 Web ex Machina
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */


/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_wem_form_conditional_notification';
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_wem_form_conditional_notification_field';

/**
 * Models
 */
$GLOBALS['TL_MODELS'][\WEM\FCN\Model\Notification::getTable()] = 'WEM\FCN\Model\Notification';
$GLOBALS['TL_MODELS'][\WEM\FCN\Model\Field::getTable()] = 'WEM\FCN\Model\Field';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['processFormData'][] = array('WEM\FCN\Hooks', 'processFormData');