<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_form',
        'ctable' => ['tl_wem_form_conditional_notification_field'],
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'panelLayout' => 'filter;limit',
            'headerFields' => ['title', 'tstamp', 'formID', 'storeValues', 'nc_notification'],
        ],
        'global_operations' => ['all'],
        'operations' => ['edit', 'copy', 'cut', 'delete', 'show'],
    ],

    // Palettes
    'palettes' => [
        'default' => '{conditions_legend},fields;{notification_legend},nc_notification,language,tokens',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_form.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => [
                'type' => 'belongsTo',
                'load' => 'lazy',
            ],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'fields' => [
            'inputType' => 'dcaWizard',
            'foreignTable' => 'tl_wem_form_conditional_notification_field',
            'foreignField' => 'pid',
            'params' => [
                'do' => 'form',
            ],
            'eval' => [
                'fields' => ['field', 'value'],
                'orderField' => 'sorting',
                'showOperations' => true,
                'operations' => ['edit', 'delete'],
                'tl_class' => 'clr',
            ],
        ],
        'nc_notification' => [
            'exclude' => true,
            'inputType' => 'select',
            'eval' => [
                'includeBlankOption' => true,
                'chosen' => true,
                'mandatory' => true,
                'tl_class' => 'w50 wizard',
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'language' => [
            'exclude' => true,
            'inputType' => 'select',
            'eval' => [
                'includeBlankOption' => true,
                'chosen' => true,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'tokens' => [
            'exclude' => true,
            'inputType' => 'keyValueWizard',
            'eval' => [
                'tl_class' => 'clr',
            ],
            'sql' => 'blob NULL',
        ],
    ],
];
