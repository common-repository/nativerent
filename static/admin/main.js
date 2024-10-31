function NativeRentAdmin_insertChange(insertSelect, autoSelectorName) {
	var autoSelectorSelect = document.querySelectorAll('select[name="' + autoSelectorName + '"]')[0];
	var optionsTexts = [];
	switch (insertSelect.value) {
		case 'before':
			optionsTexts['firstParagraph'] = 'первым абзацем (p)';
			optionsTexts['middleParagraph'] = 'средним абзацем (p)';
			optionsTexts['lastParagraph'] = 'последним абзацем (p)';
			optionsTexts['firstTitle'] = 'первым заголовком (h2)';
			optionsTexts['middleTitle'] = 'средним заголовком (h2)';
			optionsTexts['lastTitle'] = 'последним заголовком (h2)';
			optionsTexts[''] = '(задать свой селектор)';
			break;

		case 'after':
			optionsTexts['firstParagraph'] = 'первого абзаца (p)';
			optionsTexts['middleParagraph'] = 'среднего абзаца (p)';
			optionsTexts['lastParagraph'] = 'последнего абзаца (p)';
			optionsTexts['firstTitle'] = 'первого заголовка (h2)';
			optionsTexts['middleTitle'] = 'среднего заголовка (h2)';
			optionsTexts['lastTitle'] = 'последнего заголовка (h2)';
			optionsTexts[''] = '(задать свой селектор)';
			break;
	}

	var options = autoSelectorSelect.getElementsByTagName('option');
	for (var i = 0; i < options.length; i++) {
		options[i].innerText = optionsTexts[options[i].value];
	}
}

function NativeRentAdmin_autoSelectorChanged(autoSelectorSelect, customSelectorName) {
	var customSelectorInput = document.querySelectorAll('input[name="' + customSelectorName + '"]')[0];
	customSelectorInput.style.display = (autoSelectorSelect.value == '' ? '' : 'none');
}

function NativeRentAdmin_updateSelectors() {
	var NativeRentAdmin_insertChangeSelects = document.getElementsByClassName('NativeRentAdmin_insertChange');
	var NativeRentAdmin_autoSelectorSelects = document.getElementsByClassName('NativeRentAdmin_autoSelector');
	for (var i = 0; i < NativeRentAdmin_insertChangeSelects.length; i++) {
		NativeRentAdmin_insertChangeSelects[i].onchange();
		NativeRentAdmin_autoSelectorSelects[i].onchange();
	}
}

/**
 * @param {HTMLElement} el
 * @param {Number} num
 */
function NativeRentAdmin_changeNoInsertionFlag(el, num) {
	var td = document.getElementById('ntgb-config-placement-selectors-' + num.toString());
	if (td) {
		td.querySelectorAll(
			'.NativeRentAdmin_insertChange, .NativeRentAdmin_autoSelector, .NativeRentAdmin_customSelector'
		).forEach(function (node) {
			node.disabled = el.checked;
		});
	}
	var fallbackTxt = document.querySelector('textarea[name="nrent[adUnitsConfig][ntgb][' + num + '][settings][fallbackCode]"]');
	if (fallbackTxt) {
		fallbackTxt.disabled = el.checked;
	}
}

function NativeRentAdmin_submitEnable(formElement) {
	formElement.querySelector('input[type="submit"]').disabled = false;
	if (formElement.querySelector('#NativeRentAdmin_dropSiteCacheContainer input:checked')) {
		formElement.querySelector('#NativeRentAdmin_dropSiteCacheContainer').style.visibility = 'visible';
	}
}

function NativeRentAdmin_init() {
	var placementInputsGroupClass = 'NativeRentAdmin_placementSettings';
	var placementInputsSelector = '.NativeRentAdmin_insertChange, .NativeRentAdmin_autoSelector, .NativeRentAdmin_customSelector';
	var validationMessages = {
		duplicatedPlacement: 'Блок с такими настройками уже есть',
		fallbackCodeHasNR: 'Нельзя использовать код Native Rent в качестве заглушки',
	};

	var settingsForm = document.getElementById('NativeRentAdmin_settingsForm');
	var ntgbFallbackCodeSelector = 'textarea.NativeRentAdmin_fallbackCodeTextArea';
	var ntgbFallbackCodeInputs = document.querySelectorAll(
		ntgbFallbackCodeSelector);
	ntgbFallbackCodeInputs.forEach(function (el) {
		el.addEventListener('keyup', function () {
			NativeRentAdmin_submitEnable(settingsForm);
		});
	});

	var ntgbNumInput = document.getElementById('NativeRentAdmin_ntgbUnitsNum');
	var ntgbUnitConfigs = document.querySelectorAll('#NativeRentAdmin_settings-section-ntgb .ntgb-config-item');
	if (ntgbNumInput) {
		ntgbNumInput.addEventListener('change', function () {
			var label = this.parentNode.getElementsByClassName('_label');
			if (label.length > 0) {
				label[0].textContent = label[0].textContent.replace(
					this.value < 2 ? 'блока ' : 'блок ',
					this.value < 2 ? 'блок ' : 'блока '
				);
			}
			var revalidate = [];
			for (var i = 0; i < ntgbUnitConfigs.length; i++) {
				var unit = ntgbUnitConfigs[i];
				var unitNum = parseInt(unit.getAttribute('data-unit-num'));
				var value = parseInt(this.value);
				var inactiveInput = unit.querySelector('.ntgb-config-item-inactive-input');
				if (unitNum > value) {
					unit.classList.add('ntgb-config-item-inactive');
					if (inactiveInput) {
						inactiveInput.value = 1;
					}
					if (unit.classList.contains(placementInputsGroupClass)) {
						removeValidationError(unit);
					}
				} else {
					unit.classList.remove('ntgb-config-item-inactive');
					if (inactiveInput) {
						inactiveInput.value = 0;
					}
					revalidate.push(unit);
				}
			}

			revalidate.forEach(function (el) {
				if (el.classList.contains(placementInputsGroupClass)) {
					validateUnitPlacement(el);
				}
			});
		});
	}

	/**
	 * @param {HTMLElement} parent
	 * @return {String}
	 */
	var getPlacementValue = function (parent) {
		var val = '';
		parent.querySelectorAll(placementInputsSelector).forEach(function (el) {
			if (el.disabled) {
				return;
			}
			if (window.getComputedStyle(el).display === 'none') {
				return;
			}
			val += el.value;
		});

		return val;
	};

	/**
	 * @param {HTMLElement} el
	 * @return {boolean}
	 */
	var hasDuplicatedPlacementValue = function (el) {
		var unitID = el.getAttribute('data-unit-id');
		var unitType = el.getAttribute('data-unit-type');
		var changedValue = getPlacementValue(el);
		var sameElems = document.querySelectorAll('.' + placementInputsGroupClass + '[data-unit-type="' + unitType + '"]');
		for (var i = 0; i < sameElems.length; i++) {
			var sameEl = sameElems[i];
			var sameUnitID = sameEl.getAttribute('data-unit-id');
			if (unitID === sameUnitID) {
				continue;
			}
			if (window.getComputedStyle(sameEl).display === 'none') {
				continue;
			}
			if (sameEl.getAttribute('data-validation-error') == 1) {
				continue;
			}

			var pv = getPlacementValue(sameEl);
			if (pv !== '' && pv === changedValue) {
				return true;
			}
		}

		return false;
	};

	/**
	 * @param {HTMLElement} el
	 */
	var removeValidationError = function (el) {
		withValidationError(el, '');
	};

	/**
	 * @param {HTMLElement} el
	 * @param {String} msg
	 */
	var withValidationError = function (el, msg) {
		var sectionClass = 'NativeRentAdmin_withErrors';
		var msgClass = 'NativeRentAdmin_validationError';
		var msgEl = el.querySelector('.' + msgClass);
		if (!msgEl) {
			return;
		}
		if (msg === '') {
			el.classList.remove(sectionClass);
			el.setAttribute('data-validation-error', 0);
		} else {
			el.classList.add(sectionClass);
			el.setAttribute('data-validation-error', 1);
		}
		msgEl.textContent = msg;
	};

	/**
	 * @param {HTMLElement} el
	 */
	var validateUnitPlacement = function (el) {
		if (hasDuplicatedPlacementValue(el)) {
			withValidationError(el, validationMessages.duplicatedPlacement);
		} else {
			removeValidationError(el);
		}
	};

	/**
	 *
	 * @param {String} unitType
	 * @param {String|undefined} excludeUnitID
	 */
	var revalidateUnits = function (unitType, excludeUnitID) {
		var selector = 'tr[data-unit-type="' + unitType + '"]';
		if (excludeUnitID) {
			selector += ':not([data-unit-id="' + excludeUnitID + '"])';
		}
		var revalidate = document.querySelectorAll(selector);
		revalidate.forEach(function (el) {
			if (el.classList.contains(placementInputsGroupClass)) {
				validateUnitPlacement(el);
			}
			if (unitType === 'ntgb') {
				var fallbackCodeTxt = el.querySelector(ntgbFallbackCodeSelector);
				if (fallbackCodeTxt) {
					validateFallbackCode(fallbackCodeTxt);
				}
			}
		});
	};

	/**
	 * @param {HTMLElement} textarea
	 */
	var validateFallbackCode = function (textarea) {
		var code = textarea.value;
		code = code.replaceAll(/[\r\n\s]/gm, '');
		var pattern = new RegExp(
			/^((?!window\.(NRentCounter|NRentManager|NtgbManager)).)*$/);
		if (textarea.disabled || code.match(pattern)) {
			withValidationError(textarea.parentNode, '');
		} else {
			withValidationError(textarea.parentNode,
				validationMessages.fallbackCodeHasNR);
		}
	};

	// Forms with units placement settings.
	document.querySelectorAll(placementInputsSelector).forEach(function (el) {
		var handler = function () {
			var parent = el.closest('.' + placementInputsGroupClass);
			validateUnitPlacement(parent);

			var unitType = parent.getAttribute('data-unit-type');
			var unitID = parent.getAttribute('data-unit-id');
			revalidateUnits(unitType, unitID);
		};

		el.addEventListener('change', handler);
		el.addEventListener('keyup', handler);
	});

	// NTGB unit checkbox.
	document.querySelectorAll('.NativeRentAdmin_noInsertionFlag input[type="checkbox"]').forEach(function (el) {
		el.addEventListener('change', function () {
			var unitID = this.closest('.NativeRentAdmin_noInsertionFlag').getAttribute('data-unit-num');
			var placementSection = document.querySelector(
				'.' + placementInputsGroupClass + '[data-unit-type="ntgb"][data-unit-id="' + unitID + '"]'
			);
			var fallbackCodeInput = document.querySelector(
				'.ntgb-config-item[data-unit-id="' + unitID + '"] ' + ntgbFallbackCodeSelector
			);
			if (!this.checked) {
				validateUnitPlacement(placementSection);
				validateFallbackCode(fallbackCodeInput);
			} else {
				removeValidationError(placementSection);
				removeValidationError(fallbackCodeInput.parentNode);
			}

			revalidateUnits('ntgb', unitID);
		});
	});

	ntgbFallbackCodeInputs.forEach(function (el) {
		el.addEventListener('keyup', function () {
			validateFallbackCode(this);
		});
		el.addEventListener('change', function () {
			validateFallbackCode(this);
		});
	});

	settingsForm.addEventListener('submit', function (event) {
		event.preventDefault();
		if (this.querySelector('[data-validation-error="1"]')) {
			alert('Форма содержит ошибки. Исправьте перед сохранением.');
			return false;
		}
		this.submit();
	});
}

document.addEventListener('DOMContentLoaded', NativeRentAdmin_init);
