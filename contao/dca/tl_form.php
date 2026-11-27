<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = 'tl_wem_form_conditional_notification';

$GLOBALS['TL_DCA']['tl_form']['list']['operations']['wem_conditional_notifications'] = [
    'href' => 'table=tl_wem_form_conditional_notification',
    'icon' => 'bundles/wemformconditionalnotifications/fcn.svg',
];
