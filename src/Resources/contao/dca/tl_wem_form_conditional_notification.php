<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use WEM\FormConditionalNotificationsBundle\DataContainer\Notification;

/**
 * Table tl_wem_form_conditional_notification
 */
$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'enableVersioning'            => true,
		'ptable'                      => 'tl_form',
		'ctable'					  => array('tl_wem_form_conditional_notification_field'),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => DataContainer::MODE_PARENT,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter;sorting,limit',
			'headerFields'            => array('title', 'tstamp', 'formID', 'storeValues', 'sendViaEmail', 'recipient', 'subject'),
			'child_record_callback'   => array(Notification::class, 'listItems')
		),
		'global_operations' => [
			'all',
		],
		'operations' => [
			'edit',
			'copy',
			'delete',
			'show',
			'fields' => [
				'href' => 'table=tl_wem_form_conditional_notification_field',
				'icon' => 'editor.gif'
			],
		],
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{conditions_legend},fields;{notification_legend},nc_notification,language,tokens',
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'foreignKey'              => 'tl_form.title',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'sorting' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),

		'nc_notification' => array
		(
			'exclude'                   => true,
			'inputType'                 => 'select',
			'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50 wizard'),
			'wizard' => array
			(
				array(Notification::class, 'editNotification')
			),
			'sql'                       => "int(10) unsigned NOT NULL default '0'"
		),
		'language' => array
        (
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => static fn () => System::getContainer()->get('contao.intl.locales')->getLocales(),
            'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(5) NOT NULL default ''"
        ),
        'tokens' => array
        (
            'exclude'                 => true,
            'inputType'               => 'keyValueWizard',
            'eval'                    => array('tl_class'=>'clr'),
            'sql'                     => "blob NULL"
        ),
		
		'fields' => array
		(
		    'inputType'             => 'dcaWizard',
		    'foreignTable'          => 'tl_wem_form_conditional_notification_field',
		    'foreignField'          => 'pid',
		    'params'                  => array
		    (
		        'do'                  => 'form',
		    ),
		    'eval'                  => array
		    (
		        'fields' => array('field', 'value'),
		        'orderField' => 'sorting',
		        'showOperations' => true,
		        'operations' => array('edit', 'delete'),
		        'tl_class'=>'clr',
		    ),
		),
	)
);