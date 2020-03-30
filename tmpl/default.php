<?php
/**
 * @package    JShopping - DaData suggestions
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - webmasterskaya.xyz
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

defined('_JEXEC') or die;

/** @var string $input_layout */

if (file_exists(dirname(__FILE__) . '/' . pathinfo(__FILE__, PATHINFO_FILENAME) . '_' . $input_layout . '.php'))
{
	include dirname(__FILE__) . '/' . pathinfo(__FILE__, PATHINFO_FILENAME) . '_' . $input_layout . '.php';
}
else
{
	if (file_exists(dirname(__FILE__) . '/default_' . $input_layout . '.php'))
	{
		include dirname(__FILE__) . '/default_' . $input_layout . '.php';
	}
	else
	{
		include JPATH_PLUGINS . '/jshopping/dadata_suggestions/tmpl/default_' . $input_layout . '.php';
	}
}
