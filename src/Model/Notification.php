<?php

namespace WEM\FormConditionalNotificationsBundle\Model;

use WEM\UtilsBundle\Model\Model as CoreModel;

class Notification extends CoreModel
{
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected static $strTable = 'tl_wem_form_conditional_notification';

	/**
     * Default order column.
     *
     * @var string
     */
    protected static $strOrderColumn = 'sorting';
}