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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

class plgJshoppingDadata_suggestions extends CMSPlugin
{
	protected $autoloadLanguage = true;
	/**
	 * @var JDocument
	 */
	protected $doc;

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		if (Factory::getApplication()->isClient('site'))
		{
			$this->doc = Factory::getDocument();

			if (!empty($this->params->get('dadata_checkout_enabled', '1')))
			{
				HTMLHelper::_('behavior.keepalive');
				HTMLHelper::_('jquery.framework');
				HTMLHelper::_('script', 'plg_jshopping_dadata_suggestions/jquery.suggestions.js',
					['relative' => true, 'version' => 'auto']);
				HTMLHelper::_('script', 'plg_jshopping_dadata_suggestions/script.js',
					['relative' => true, 'version' => 'auto']);
				HTMLHelper::_('stylesheet', 'plg_jshopping_dadata_suggestions/suggestions.css',
					['relative' => true, 'version' => 'auto']);

				$this->doc->addScriptOptions('plg_jshopping_dadata_suggestions', [
					'token' => $this->params->get('dadata_api_key', '')
				], false);
			}
		}
	}

	public function onBeforeDisplayCheckoutStep2View(&$view)
	{

		if (empty($this->params->get('dadata_checkout_enabled', '1')))
		{
			return;
		}

		if (empty($this->params->get('dadata_api_key', '')))
		{
			return;
		}

		$use_address_field = $this->params->get('dadata_checkout_use_address_field', '2');
		$address_field     = $this->params->get('dadata_checkout_address_field', '');

		switch ($use_address_field)
		{
			case '1':

				$path = JPluginHelper::getLayoutPath('jshopping', 'dadata_suggestions');

				ob_start();
				$input_layout = 'address';
				$input_name   = 'dadata_address_field';
				include $path;
				$view->_tmpl_address_html_4 .= PHP_EOL . ob_get_clean();

				ob_start();
				$input_layout = 'address';
				$input_name   = 'd_dadata_address_field';
				include $path;
				$view->_tmpl_address_html_7 .= PHP_EOL . ob_get_clean();

				$this->doc->addScriptOptions('plg_jshopping_dadata_suggestions', [
					'checkout' => [
						'element' => 'dadata_address_field'
					]
				], true);

				unset($input_layout, $input_name);

				break;
			case '2':
				if (!empty($address_field) && !empty($view->config_fields[$address_field]))
				{
					$view->config_fields[$address_field]['display']        = 1;
					$view->config_fields['d_' . $address_field]['display'] = 1;
				}

				$this->doc->addScriptOptions('plg_jshopping_dadata_suggestions', [
					'checkout' => [
						'element' => $address_field
					]
				], true);

				break;
			default:
				break;
		}

		if ($use_address_field !== '0')
		{
			$default_fields = ['zip', 'state', 'city', 'street', 'home', 'apartment'];

			switch ($this->params->get('dadata_checkout_hide_default', '2'))
			{
				case 1:
					foreach ($default_fields as $field)
					{
						$view->config_fields[$field]['display']        = 0;
						$view->config_fields['d_' . $field]['display'] = 0;

						$view->_tmpl_address_html_4 .= PHP_EOL . '<input type="hidden" id="' . $field . '" name="' . $field . '" value="' . $view->user->{$field} . '" />';
						$view->_tmpl_address_html_7 .= PHP_EOL . '<input type="hidden" id="d_' . $field . '" name="d_' . $field . '" value="' . $view->user->{'d_' . $field} . '" />';
					}
					break;
				case 2:
					foreach ($default_fields as $field)
					{
						$view->config_fields[$field]['display']        = 1;
						$view->config_fields['d_' . $field]['display'] = 1;
					}
					break;
				default:
					foreach ($default_fields as $field)
					{
						if ($view->config_fields[$field]['display'] == 0)
						{
							$view->_tmpl_address_html_4 .= PHP_EOL . '<input type="hidden" id="' . $field . '" name="' . $field . '" value="' . $view->user->{$field} . '" />';
						}
						if ($view->config_fields['d_' . $field]['display'] == 0)
						{
							$view->_tmpl_address_html_7 .= PHP_EOL . '<input type="hidden" id="d_' . $field . '" name="d_' . $field . '" value="' . $view->user->{'d_' . $field} . '" />';
						}
					}
					break;
			}
		}

		if ($use_address_field !== '0')
		{
			$this->doc->addScriptOptions('plg_jshopping_dadata_suggestions', [
				'checkout' => [
					'use_address_field' => 1
				]
			], true);
		}
	}
}