<?php

use WEM\FormConditionalNotificationsBundle\DataContainer\Notification;

/**
 * Table tl_wem_form_conditional_notification
 */
$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
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
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter;sorting,limit',
			'headerFields'            => array('title', 'tstamp', 'formID', 'storeValues', 'sendViaEmail', 'recipient', 'subject'),
			'child_record_callback'   => array(Notification::class, 'listItems')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'fields' => array
			(
				'href'                => 'table=tl_wem_form_conditional_notification_field',
				'icon'                => 'editor.gif'
			),
		)
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
			'options_callback'          => array('NotificationCenter\tl_form', 'getNotificationChoices'),
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
            'options'                 => System::getLanguages(),
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

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */
class tl_wem_form_conditional_notification extends Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct(){
		parent::__construct();

		$this->import('BackendUser', 'User');
	}
}