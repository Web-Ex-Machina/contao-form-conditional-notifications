<?php

namespace WEM\FormConditionalNotificationsBundle\Model;

use WEM\UtilsBundle\Model\Model as CoreModel;

class Field extends CoreModel
{
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected static $strTable = 'tl_wem_form_conditional_notification_field';

	/**
     * Default order column.
     *
     * @var string
     */
    protected static $strOrderColumn = 'sorting';
}