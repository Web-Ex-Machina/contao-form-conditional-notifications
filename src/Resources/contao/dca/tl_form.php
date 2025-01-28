<?php

declare(strict_types=1);

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
	'icon' => 'bundles/wemformconditionalnotifications/backend/fcn.svg'
);