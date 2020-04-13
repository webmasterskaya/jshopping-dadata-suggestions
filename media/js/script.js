/**
 * @package    JShopping - DaData suggestions
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - webmasterskaya.xyz
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

if (!Object.assign) {
	Object.defineProperty(Object, 'assign', {
		enumerable: false,
		configurable: true,
		writable: true,
		value: function(target, firstSource) {
			'use strict';
			if (target === undefined || target === null) {
				throw new TypeError('Cannot convert first argument to object');
			}

			var to = Object(target);
			for (var i = 1; i < arguments.length; i++) {
				var nextSource = arguments[i];
				if (nextSource === undefined || nextSource === null) {
					continue;
				}

				var keysArray = Object.keys(Object(nextSource));
				for (var nextIndex = 0, len = keysArray.length; nextIndex <
				len; nextIndex++) {
					var nextKey = keysArray[nextIndex];
					var desc = Object.getOwnPropertyDescriptor(nextSource,
						nextKey);
					if (desc !== undefined && desc.enumerable) {
						to[nextKey] = nextSource[nextKey];
					}
				}
			}
			return to;
		},
	});
}

jQuery(function() {
	if(typeof Joomla.getOptions('plg_jshopping_dadata_suggestions') !== 'undefined'){
		var plgDaDataSuggestions = Joomla.getOptions('plg_jshopping_dadata_suggestions');
		var helper = new suggestionHelper(plgDaDataSuggestions);
	}
});

function suggestionHelper(_params) {
	var _helper = this,
		_elements = {},
		_suggestions = {
			hint: false,
			token: _params.token,
			onSelectNothing: function(query) {
				/*if (this.value != '') {
					this.value = window.lastValue ? window.lastValue : '';
				}
				else {
					_helper.setSelected(this.name, {});
				}*/
			},
		};

	var formatPostalCode = function(suggestion) {
			return suggestion.data.postal_code || '';
		}.bind(_helper),
		formatRegion = function(suggestion) {
			return suggestion.data.region_with_type || '';
		}.bind(_helper),
		formatCity = function(suggestion) {
			var address = suggestion.data;
			if (!!address.city_with_type) {
				return address.city_with_type;
			}
			else {
				return address.settlement_with_type;
			}
		}.bind(_helper),
		formatStreet = function(suggestion) {
			return suggestion.data.street_with_type || '';
		}.bind(_helper),
		formatHouse = function(suggestion) {
			var address = suggestion.data,
				house = [];
			if (address.house) {
				house.push(address.house);
			}
			if (address.block_type) {
				house.push(address.block_type);
			}
			if (address.block) {
				house.push(address.block);
			}
			return house.join(' ').trim() || '';
		}.bind(_helper),
		formatApartment = function(suggestion) {
			var address = suggestion.data,
				apartment = [];
			if (address.flat_type) {
				apartment.push(address.flat_type);
			}
			if (address.flat) {
				apartment.push(address.flat);
			}
			return apartment.join(' ').trim() || '';
		}.bind(_helper),
		formatPatronymic = function(suggestion) {
			return suggestion.data.patronymic || '';
		}.bind(_helper);

	for (var field in _params.fields) {
		if (!_params.fields.hasOwnProperty(field)) {
			continue;
		}
		_elements[field] = jQuery('[name="' + field + '"]');
		if (_elements[field].type !== 'hidden') {

			var options = Object.assign({}, _params.fields[field]);

			if (!!_params.fields[field].formatSelected) {
				options.formatSelected = eval(
					_params.fields[field].formatSelected);
			}

			if (!!_params.fields[field].constraints &&
				typeof _params.fields[field].constraints === 'string') {
				options.constraints = _elements[_params.fields[field].constraints];
			}

			_elements[field].suggestions(
				Object.assign({}, _suggestions, options)).bind(jQuery);
		}
	}

	return _helper;
}
