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
	 *
	 * @since 1.0.0
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
				HTMLHelper::_('jquery.framework');
				HTMLHelper::_('script', 'plg_jshopping_dadata_suggestions/jquery.suggestions.js',
					['relative' => true, 'version' => 'auto']);
				HTMLHelper::_('script', 'plg_jshopping_dadata_suggestions/script.js',
					['relative' => true, 'version' => 'auto']);
				HTMLHelper::_('stylesheet', 'plg_jshopping_dadata_suggestions/suggestions.min.css',
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

		$use_address_field = $this->params->get('dadata_checkout_use_address_field', '1');
		$address_field     = $this->params->get('dadata_checkout_address_field', '');

		switch ($use_address_field)
		{
			// 2 - для совместимости с предыдущей версией
			case '1':
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
			$mode           = $this->params->get('dadata_checkout_hide_default', '2');

			$this->prepareDefaultFields($mode, $default_fields, $view);
		}

		if ($use_address_field !== '0')
		{
			$this->doc->addScriptOptions('plg_jshopping_dadata_suggestions', [
				'checkout' => [
					'use_address_field' => 1
				]
			], true);
		}

		$use_fio_field = $this->params->get('dadata_checkout_use_fio_field', '1');
		$fio_field     = $this->params->get('dadata_checkout_fio_field', '');

		switch ($use_fio_field)
		{
			case '1':
				if (!empty($fio_field) && !empty($view->config_fields[$fio_field]))
				{
					$view->config_fields[$fio_field]['display']        = 1;
					$view->config_fields['d_' . $fio_field]['display'] = 1;
				}

				$this->doc->addScriptOptions('plg_jshopping_dadata_suggestions', [
					'checkout' => [
						'fio_element' => $fio_field
					]
				], true);

				break;
			default:
				break;
		}

		if ($use_fio_field !== '0')
		{
			$default_fields = ['f_name', 'l_name', 'm_name'];
			$mode           = $this->params->get('dadata_checkout_hide_default_fio', '2');

			$this->prepareDefaultFields($mode, $default_fields, $view);
		}
	}

	protected function prepareDefaultFields($mode, $default_fields, &$view)
	{
		switch ($mode)
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

					$this->addToSuggestionFieldCollection($field);
					$this->addToSuggestionFieldCollection('d_' . $field);
				}
				break;
			default:
				foreach ($default_fields as $field)
				{
					if ($view->config_fields[$field]['display'] == 1)
					{
						$this->addToSuggestionFieldCollection($field);
						$this->addToSuggestionFieldCollection('d_' . $field);
					}

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

	protected function addToSuggestionFieldCollection($field)
	{
		$target = '';
		$prefix = strpos($field, 'd_') === 0 ? 'd_' : '';
		switch ($field)
		{
			case 'f_name':
			case 'd_f_name':
				$target = [
					'type'   => 'NAME',
					'params' => [
						'parts' => ['NAME']
					]
				];
				break;
			case 'l_name':
			case 'd_l_name':
				$target = [
					'type'   => 'NAME',
					'params' => [
						'parts' => ['SURNAME']
					]
				];
				break;
			case 'm_name':
			case 'd_m_name':
				$target = [
					'type'           => 'NAME',
					'params'         => [
						'parts' => ['PATRONYMIC']
					],
					'formatSelected' => 'formatPatronymic'
				];
				break;
			case 'zip':
			case 'd_zip':
				$target = [
					'type'           => 'ADDRESS',
					'formatSelected' => 'formatPostalCode'
				];
				break;
			case 'city':
			case 'd_city':
				$target = [
					'type'           => 'ADDRESS',
					'formatSelected' => 'formatCity',
					'bounds'         => 'city-settlement',
					'constraints'    => $prefix . 'state'
				];
				break;
			case 'state':
			case 'd_state':
				$target = [
					'type'           => 'ADDRESS',
					'formatSelected' => 'formatRegion',
					'bounds'         => 'region',
					'constraints'    => [
						'locations' => [
							'country_iso_code' => 'RU'
						]
					]
				];
				break;
			case 'street':
			case 'd_street':
				$target = [
					'type'           => 'ADDRESS',
					'formatSelected' => 'formatStreet',
					'bounds'         => 'street',
					'constraints'    => $prefix . 'city'
				];
				break;
			case 'home':
			case 'd_home':
				$target = [
					'type'           => 'ADDRESS',
					'formatSelected' => 'formatHouse',
					'bounds'         => 'house-block',
					'constraints'    => $prefix . 'street'
				];
				break;
			case 'apartment':
			case 'd_apartment':
				$target = [
					'type'           => 'ADDRESS',
					'bounds'         => 'flat',
					'formatSelected' => 'formatApartment'
				];
				break;
			default:
				break;
		}
		if (!empty($target))
		{
			$this->doc->addScriptOptions('plg_jshopping_dadata_suggestions', [
				'fields' => [
					$field => $target
				]
			], true);
		}
	}
}