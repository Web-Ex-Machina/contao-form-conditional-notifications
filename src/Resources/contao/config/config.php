<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_wem_form_conditional_notification';
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_wem_form_conditional_notification_field';

/**
 * Models
 */
$GLOBALS['TL_MODELS'][\WEM\FormConditionalNotificationsBundle\Model\Field::getTable()] = \WEM\FormConditionalNotificationsBundle\Model\Field::class;
$GLOBALS['TL_MODELS'][\WEM\FormConditionalNotificationsBundle\Model\Notification::getTable()] = \WEM\FormConditionalNotificationsBundle\Model\Notification::class;