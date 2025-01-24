<?php

/**
 * Add ctable to tl_form
 */
$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = 'tl_wem_form_conditional_notification';

/**
 * Add operations to tl_form
 */
$GLOBALS['TL_DCA']['tl_form']['list']['operations']['wem_conditional_notifications'] = array
(
	'href' => 'table=tl_wem_form_conditional_notification',
	'icon' => 'bundles/wemformconditionalnotifications/backend/icon_notifications_16.gif'
);