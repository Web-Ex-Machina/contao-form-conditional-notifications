<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use WEM\FormConditionalNotificationsBundle\DataContainer\Field;

/**
 * Table tl_wem_form_conditional_notification_field
 */
$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'enableVersioning'            => true,
		'ptable'                      => 'tl_wem_form_conditional_notification',
		'onload_callback'			  => array
		(
			array(Field::class, 'adjustPalette')
		),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid' => 'index',
				'field' => 'index'
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
			'panelLayout'             => 'filter;search,limit',
			'headerFields'            => array('nc_notification', 'language'),
			'child_record_callback'   => array(Field::class, 'listItems'),
			'disableGrouping'		  => true
		),
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{general_legend},field,value',
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
			'foreignKey'              => 'tl_wem_form_conditional_notification.id',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
		),
		'sorting' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),

		'field' => array
		(
			'inputType'               => 'select',
			'options_callback'        => array(Field::class, 'getFormFields'),
			'foreignKey'              => 'tl_form_field.label',
			'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'value' => array
		(
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "text NULL"
		),
	)
);