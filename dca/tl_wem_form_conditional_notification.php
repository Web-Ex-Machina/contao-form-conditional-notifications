<?php

/**
 * Form Conditional Notifications Extension for Contao Open Source CMS
 *
 * Copyright (c) 2018 Web ex Machina
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */

/**
 * Load tl_form language file
 */
System::loadLanguageFile('tl_form');
System::loadLanguageFile('tl_nc_language');

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
			'child_record_callback'   => array('tl_wem_form_conditional_notification', 'listItems')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['copy'],
				'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'fields' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['fields'],
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
			'label'                     => &$GLOBALS['TL_LANG']['tl_form']['nc_notification'],
			'exclude'                   => true,
			'inputType'                 => 'select',
			'options_callback'          => array('NotificationCenter\tl_form', 'getNotificationChoices'),
			'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50 wizard'),
			'wizard' => array
			(
				array('tl_wem_form_conditional_notification', 'editNotification')
			),
			'sql'                       => "int(10) unsigned NOT NULL default '0'"
		),
		'language' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_nc_language']['language'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => \System::getLanguages(),
            'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(5) NOT NULL default ''"
        ),
        'tokens' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['tokens'],
            'exclude'                 => true,
            'inputType'               => 'keyValueWizard',
            'eval'                    => array('tl_class'=>'clr'),
            'sql'                     => "blob NULL"
        ),
		
		'fields' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['fields'],
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

	public function listItems($row){
		$pattern = '%s | %s<br /><ul style="padding-left:15px">%s</ul>';
		$arrLanguages = \System::getLanguages();
		$arrCountries = \System::getCountries();

		$objNotification = \NotificationCenter\Model\Notification::findByPk($row['nc_notification']);
		$args[] = $objNotification->title;
		$args[] = $arrLanguages[$row['language']] ?: $GLOBALS['TL_LANG']['WEM']['FCN']['neutral'];

		// Get the fields
		$objFields = WEM\FCN\Model\Field::findBy('pid', $row['id'], ["order"=>"sorting ASC"]);

		if(!$objFields || 0 == $objFields->count()){
			$args[] = '<li>'.$GLOBALS['TL_LANG']['WEM']['FCN']['defaultConfig'].'</li>';
		}
		else{
			$strList = '';
			while($objFields->next()){
				$objField = \FormFieldModel::findByPk($objFields->field);
				switch($objField->type){
					case 'select':
					case 'checkbox':
					case 'radio':
						$arrValues = deserialize($objFields->value);
						$strList .= '<li>- '.$objField->label.' = '.implode(', ', $arrValues).'</li>';
					break;
					case 'countryselect':
						$strList .= '<li>- '.$objField->label.' = '.$arrCountries[$objFields->value].'</li>';
					break;
					default:
						$strList .= '<li>- '.$objField->label.' = '.$objFields->value.'</li>';
				}
				
			}
			$args[] = $strList;
		}


		return vsprintf($pattern, $args);
	}

	/**
	 * Return the edit notification wizard
	 *
	 * @param DataContainer $dc
	 *
	 * @return string
	 */
	public function editNotification(DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=nc_notifications&amp;table=tl_nc_message&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(specialchars($GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['editNotification'][1]), $dc->value) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['editNotification'][1], $dc->value))) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_wem_form_conditional_notification']['editNotification'][0], 'style="vertical-align:top"') . '</a>';
	}
}