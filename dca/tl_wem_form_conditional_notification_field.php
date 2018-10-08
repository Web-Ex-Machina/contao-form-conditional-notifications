<?php

/**
 * Form Conditional Notifications Extension for Contao Open Source CMS
 *
 * Copyright (c) 2018 Web ex Machina
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */

/**
 * Table tl_wem_form_conditional_notification_field
 */
$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'ptable'                      => 'tl_wem_form_conditional_notification',
		'onload_callback'			  => array
		(
			array('tl_wem_form_conditional_notification_field', 'adjustPalette')
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
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter;search,limit',
			'headerFields'            => array('nc_notification', 'language'),
			'child_record_callback'   => array('tl_wem_form_conditional_notification_field', 'listItems'),
			'disableGrouping'		  => true
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
				'label'               => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification_field']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification_field']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification_field']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification_field']['field'],
			'inputType'               => 'select',
			'options_callback'        => array('tl_wem_form_conditional_notification_field', 'getFormFields'),
			'foreignKey'              => 'tl_form_field.label',
			'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'value' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_wem_form_conditional_notification_field']['value'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "text NULL"
		),
	)
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */
class tl_wem_form_conditional_notification_field extends Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	public function adjustPalette($objDc){
		$objField = WEM\FCN\Model\Field::findByPk($objDc->id);
		$objFormField = \FormFieldModel::findByPk($objField->field);
		$objForm = \FormModel::findByPk($objFormField->pid);

		if($objFormField->type == "select" || $objFormField->type == "radio" || $objFormField->type == "checkbox"){
			$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['inputType'] = 'select';
			
			$arrOptions = [];
			
			/** @var \FormFieldModel $objField */
			$strClass = $GLOBALS['TL_FFL'][$objFormField->type];
			$objWidget = new $strClass($objFormField->row());

			// HOOK: load form field callback
			if (isset($GLOBALS['TL_HOOKS']['loadFormField']) && is_array($GLOBALS['TL_HOOKS']['loadFormField'])){
				foreach ($GLOBALS['TL_HOOKS']['loadFormField'] as $callback){
					$this->import($callback[0]);
					$objWidget = $this->{$callback[0]}->{$callback[1]}($objWidget, "", $objForm->row(), $this);
				}
			}

			foreach($objWidget->options as $arrOption){
				if($arrOption['group']){
					$strOptionValuePrefix = "group**";
					$strOptionType = "GROUP - ";
				}
				else{
					$strOptionValuePrefix = "option**";
					$strOptionType = "OPTION - ";
				}

				$arrOptions[$strOptionValuePrefix.$arrOption['value']] = $strOptionType . $arrOption['label'];
			}
			$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['options'] = $arrOptions;
			$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['eval']['chosen'] = true;
			$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['eval']['multiple'] = true;
		}

		if($objFormField->type == "countryselect"){
			$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['inputType'] = 'select';
			$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['options'] = \System::getCountries();
			$GLOBALS['TL_DCA']['tl_wem_form_conditional_notification_field']['fields']['value']['eval']['chosen'] = true;
		}
	}

	/**
	 * Return form fields
	 *
	 * @return array
	 */
	public function getFormFields($objDc)
	{
		$objFormSubmissionField = $this->Database->prepare("SELECT pid FROM tl_wem_form_conditional_notification_field WHERE id = ?")->execute($objDc->id);
		$objFormSubmission = $this->Database->prepare("SELECT pid FROM tl_wem_form_conditional_notification WHERE id = ?")->execute($objFormSubmissionField->pid);
		$objFormFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid = ?")->execute($objFormSubmission->pid);
		$arrFields = array();
		while($objFormFields->next()){
			$arrFields[$objFormFields->id] = $objFormFields->label;
		}
		return $arrFields;
	}

	public function listItems($row){
		$arrCountries = \System::getCountries();
		$objField = \FormFieldModel::findByPk($row['field']);
		$strValue = '';
		switch($objField->type){
			case 'select':
			case 'checkbox':
			case 'radio':
				$arrValues = deserialize($row['value']);
				if(!empty($arrValues))
					$strValue = implode(', ', $arrValues);
			break;
			case 'countryselect':
				$strValue = $arrCountries[$row['value']];
			break;
			default:
				$strValue = $row['value'];
		}
		return sprintf('%s | %s', $objField->label, $strValue);
	}
}