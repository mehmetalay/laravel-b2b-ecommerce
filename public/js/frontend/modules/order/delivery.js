/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************************************!*\
  !*** ./resources/js/frontend/modules/order/delivery.js ***!
  \*********************************************************/
/* global window */

document.addEventListener('DOMContentLoaded', function () {
  var deliveryTypeElement = document.querySelector('[data-js="delivery-type"]') || document.getElementById('delivery_type');
  if (!deliveryTypeElement) {
    return;
  }
  var deliveryMap = {
    Kargo: {
      wrapper: '[data-delivery-field="cargo"]',
      required: ['cargo_company_id'],
      optional: ['warehouse_name', 'pickup_person', 'transit_note']
    },
    Ambar: {
      wrapper: '[data-delivery-field="ambar"]',
      required: ['warehouse_name'],
      optional: ['cargo_company_id', 'pickup_person', 'transit_note']
    },
    'Depo Teslim': {
      wrapper: '[data-delivery-field="depo"]',
      required: ['pickup_person'],
      optional: ['cargo_company_id', 'warehouse_name', 'transit_note']
    },
    'Transit Sevk': {
      wrapper: '[data-delivery-field="transit"]',
      required: ['transit_note'],
      optional: ['cargo_company_id', 'warehouse_name', 'pickup_person']
    }
  };
  var allWrappers = document.querySelectorAll('[data-delivery-field]');
  var allInputKeys = ['cargo_company_id', 'warehouse_name', 'pickup_person', 'transit_note'];
  var inputSelectors = {
    cargo_company_id: '[data-js="cargo-company-id"]',
    warehouse_name: '[data-js="warehouse-name"]',
    pickup_person: '[data-js="pickup-person"]',
    transit_note: '[data-js="transit-note"]'
  };
  var getInputElement = function getInputElement(id) {
    return document.querySelector(inputSelectors[id]) || document.getElementById(id);
  };
  var setFieldState = function setFieldState(id, _ref) {
    var required = _ref.required,
      enabled = _ref.enabled;
    var element = getInputElement(id);
    if (!element) {
      return;
    }
    element.required = !!required;
    element.disabled = !enabled;
    if (!enabled) {
      element.value = '';
    }
  };
  var updateDeliveryFields = function updateDeliveryFields() {
    var _document$querySelect;
    var selected = deliveryTypeElement.value;
    allWrappers.forEach(function (wrapper) {
      return wrapper.classList.add('d-none');
    });
    allInputKeys.forEach(function (id) {
      return setFieldState(id, {
        required: false,
        enabled: false
      });
    });
    if (!selected || !deliveryMap[selected]) {
      return;
    }
    var config = deliveryMap[selected];
    (_document$querySelect = document.querySelector(config.wrapper)) === null || _document$querySelect === void 0 || _document$querySelect.classList.remove('d-none');
    config.required.forEach(function (id) {
      return setFieldState(id, {
        required: true,
        enabled: true
      });
    });
    config.optional.forEach(function (id) {
      return setFieldState(id, {
        required: false,
        enabled: false
      });
    });
  };
  var updateAddressVisibilityByDeliveryType = function updateAddressVisibilityByDeliveryType(deliveryType) {
    var _window$clearPreview, _window2;
    var wrapper = document.querySelector('[data-js="shipping-address-wrapper"]') || document.getElementById('shipping-address-wrapper');
    var select = document.querySelector('[data-js="shipping-address-id"]') || document.getElementById('shipping_address_id');
    if (!wrapper || !select) {
      return;
    }
    if (deliveryType === 'Kargo' || deliveryType === 'Transit Sevk') {
      wrapper.classList.remove('d-none');
      select.required = true;
      if (!select.value) {
        var _window$refreshAddres, _window;
        (_window$refreshAddres = (_window = window).refreshAddressSelect) === null || _window$refreshAddres === void 0 || _window$refreshAddres.call(_window);
      }
      return;
    }
    wrapper.classList.add('d-none');
    select.required = false;
    select.value = '';
    (_window$clearPreview = (_window2 = window).clearPreview) === null || _window$clearPreview === void 0 || _window$clearPreview.call(_window2);
  };
  deliveryTypeElement.addEventListener('change', function () {
    updateDeliveryFields();
    updateAddressVisibilityByDeliveryType(deliveryTypeElement.value);
  });
});
/******/ })()
;