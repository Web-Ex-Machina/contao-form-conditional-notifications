<?php

/**
 * Form Conditional Notifications Extension for Contao Open Source CMS
 *
 * Copyright (c) 2018 Web ex Machina
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */

/**
 * Add ctable to tl_form
 */
$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = 'tl_wem_form_conditional_notification';

/**
 * Add operations to tl_form
 */
$GLOBALS['TL_DCA']['tl_form']['list']['operations']['wem_conditional_notifications'] = array
(
	'label'               => &$GLOBALS['TL_LANG']['tl_form']['wem_conditional_notifications'],
	'href'                => 'table=tl_wem_form_conditional_notification',
	'icon'                => 'system/modules/wem-contao-form-conditional-notifications/assets/backend/icon_notifications_16.gif'
);