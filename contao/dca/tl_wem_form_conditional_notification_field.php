<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_wem_form_conditional_notification',
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'field' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['nc_notification', 'language'],
            'disableGrouping' => true,
        ],
        'global_operations' => ['all'],
        'operations' => ['edit', 'delete', 'show'],
    ],

    // Palettes
    'palettes' => [
        'default' => '{general_legend},field,value',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_wem_form_conditional_notification.id',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => [
                'type' => 'belongsTo',
                'load' => 'lazy',
            ],
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'field' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_form_field.label',
            'eval' => [
                'mandatory' => true,
                'submitOnChange' => true,
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50',
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'value' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => 'text NULL',
        ],
    ],
];
