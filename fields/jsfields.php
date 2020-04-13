<?php
/**
 * @package    JShopping - DaData suggestions
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - webmasterskaya.xyz
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('list');

require_once(JPATH_SITE . "/components/com_jshopping/lib/factory.php");
require_once(JPATH_ADMINISTRATOR . '/components/com_jshopping/functions.php');

class JFormFieldJsfields extends JFormFieldList
{

	public $type = 'jsfields';

	protected $fieldfilter;
	protected $groupfilter;

	public function __construct($form = null)
	{
		$lang    = Factory::getLanguage();
		$langtag = $lang->getTag();
		$langpatch = JPATH_ROOT . '/administrator/components/com_jshopping/lang/';
		if (file_exists($langpatch . 'override/' . $langtag . '.php'))
		{
			require_once($langpatch . 'override/' . $langtag . '.php');
		}
		if (file_exists($langpatch . $langtag . '.php'))
		{
			require_once($langpatch . $langtag . '.php');
		}
		else
		{
			require_once($langpatch . 'en-GB.php');
		}

		parent::__construct($form);
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'fieldfilter':
			case 'groupfilter':
				return $this->$name;
		}

		return parent::__get($name);
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'fieldfilter':
			case 'groupfilter':
				$value       = (string) $value;
				$this->$name = trim($value);
				break;

			default:
				parent::__set($name, $value);
		}
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->fieldfilter = (string) $this->element['fieldfilter'];
			$this->groupfilter = (string) $this->element['groupfilter'];
		}

		return $return;
	}

	protected function getOptions()
	{
		$jshopConfig = JSFactory::getConfig();
		require_once $jshopConfig->path . 'lib/default_config.php';
		$fields = $jshopConfig->getListFieldsRegister();

		$fieldFilterParts = explode('|', $this->fieldfilter);

		$fieldFilter = [];
		foreach ($fieldFilterParts as $fieldFilterPart)
		{
			$filterParts = explode(':', $fieldFilterPart);

			if (!in_array($filterParts[0], ['display', 'require']))
			{
				continue;
			}

			if (count($filterParts) === 1)
			{
				$fieldFilter[$filterParts[0]] = 1;
			}
			else
			{
				$fieldFilter[$filterParts[0]] = $filterParts[1];
			}
		}

		$groupFilter = [];
		if (!empty(trim($this->groupfilter)))
		{
			$groupFilter = explode('|', $this->groupfilter);
		}

		$options[] = HTMLHelper::_('select.option', '', '');
		if ($fields)
		{
			foreach ($fields as $groupName => $groupItems)
			{
				if (!empty($groupFilter))
				{
					if (!in_array($groupName, $groupFilter))
					{
						continue;
					}
				}

				switch ($groupName)
				{
					case 'register':
						$groupTitle = constant('_JSHOP_REGISTER');
						break;
					case 'address':
						$groupTitle = constant('_JSHOP_CHECKOUT_ADDRESS');
						break;
					case  'editaccount':
						$groupTitle = constant('_JSHOP_EDIT_ACCOUNT');
						break;
					default:
						$groupTitle = '---';
						break;
				}

				$options[] = JHtmlSelect::optgroup($groupTitle);

				foreach ($groupItems as $name => $params)
				{
					$match = true;
					if (!empty($fieldFilter))
					{

						foreach ($fieldFilter as $p => $v)
						{
							if (isset($params[$p]) && $params[$p] !== $v)
							{
								$match = false;
							}
						}
					}
					if (!$match)
					{
						continue;
					}

					if (substr($name, 0, 2) == "d_")
					{
						continue;
					}

					$options[] = JHtmlSelect::option($name, constant('_JSHOP_FIELD_' . strtoupper($name)));
				}
				$options[] = JHtmlSelect::optgroup($groupTitle);
			}
		}

		return $options;
	}
}