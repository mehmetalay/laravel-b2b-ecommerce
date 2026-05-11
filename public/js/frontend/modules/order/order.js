/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!******************************************************!*\
  !*** ./resources/js/frontend/modules/order/order.js ***!
  \******************************************************/
/* global axiosRequest, bootstrap, setLoading, Swal, window */

document.addEventListener('DOMContentLoaded', function () {
  var qs = function qs(selector) {
    var parent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : document;
    return parent.querySelector(selector);
  };
  var getForm = function getForm(name) {
    return qs("[data-form=\"".concat(name, "\"]"));
  };
  var getModal = function getModal(name) {
    return qs("[data-modal=\"".concat(name, "\"]"));
  };
  var getSettings = function getSettings() {
    var submitOrderForm = getForm('submit-order');
    return {
      isOrderConfirmation: (submitOrderForm === null || submitOrderForm === void 0 ? void 0 : submitOrderForm.dataset.isOrderConfirmation) === '1',
      routes: {
        orderStore: (submitOrderForm === null || submitOrderForm === void 0 ? void 0 : submitOrderForm.dataset.orderStoreUrl) || '/orders',
        orderPreview: (submitOrderForm === null || submitOrderForm === void 0 ? void 0 : submitOrderForm.dataset.orderPreviewUrl) || '/orders/preview'
      }
    };
  };
  var showModal = function showModal(name) {
    var modal = getModal(name);
    if (!modal) {
      return;
    }
    bootstrap.Modal.getOrCreateInstance(modal).show();
  };
  var hideModal = function hideModal(name) {
    var modal = getModal(name);
    if (!modal) {
      return;
    }
    bootstrap.Modal.getOrCreateInstance(modal).hide();
  };
  var serializeForm = function serializeForm(form) {
    var data = {};
    new FormData(form).forEach(function (value, key) {
      data[key] = value;
    });
    return data;
  };
  var buildConfirmationRows = function buildConfirmationRows() {
    var rows = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
    var tbody = qs('[data-target="order-confirmation-tbody"]');
    if (!tbody) {
      return;
    }
    tbody.innerHTML = '';
    rows.forEach(function (row) {
      var _row$stock_code, _row$product_name, _row$quantity, _row$price, _row$discount, _row$vat, _row$net_price, _row$total;
      tbody.insertAdjacentHTML('beforeend', "\n                <tr>\n                    <td>".concat((_row$stock_code = row.stock_code) !== null && _row$stock_code !== void 0 ? _row$stock_code : '', "</td>\n                    <td>").concat((_row$product_name = row.product_name) !== null && _row$product_name !== void 0 ? _row$product_name : '', "</td>\n                    <td class=\"text-center\">").concat((_row$quantity = row.quantity) !== null && _row$quantity !== void 0 ? _row$quantity : '', "</td>\n                    <td class=\"text-center\">").concat((_row$price = row.price) !== null && _row$price !== void 0 ? _row$price : '', "</td>\n                    <td class=\"text-center\">%").concat((_row$discount = row.discount) !== null && _row$discount !== void 0 ? _row$discount : '', "</td>\n                    <td class=\"text-center\">%").concat((_row$vat = row.vat) !== null && _row$vat !== void 0 ? _row$vat : '', "</td>\n                    <td class=\"text-center\">").concat((_row$net_price = row.net_price) !== null && _row$net_price !== void 0 ? _row$net_price : '', "</td>\n                    <td class=\"text-center\">").concat((_row$total = row.total) !== null && _row$total !== void 0 ? _row$total : '', "</td>\n                </tr>\n            "));
    });
  };
  var submitOrder = function submitOrder(button) {
    var preview = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    var formName = button.dataset.formTarget;
    var form = getForm(formName);
    if (!form) {
      return;
    }
    setLoading(button, true);
    var settings = getSettings();
    var url = preview ? settings.routes.orderPreview : settings.routes.orderStore;
    axiosRequest.post(url, serializeForm(form), {
      onSuccess: function onSuccess(response) {
        if (preview) {
          buildConfirmationRows(response.rows);
          form.querySelector('[name="order_preview_token"]').value = response.token;
          hideModal('submit-order');
          showModal('order-confirmation');
          return;
        }
        hideModal('submit-order');
        hideModal('order-confirmation');
        if (response.trigger_order_service) {
          fetch("/service/eta/order/".concat(response.order_id))["catch"](function () {
            return console.warn("ETA servisine g\xF6nderilemedi");
          });
        }
        window.location.href = response.redirect;
      },
      onError: function onError(response) {
        if (response && response.warnings && response.warnings.length > 0) {
          var icons = {
            removed: "\u274C",
            quantity_updated: "\uD83D\uDD04",
            discount_updated: "\uD83D\uDCB0"
          };
          var listItems = response.warnings.map(function (warning) {
            var icon = icons[warning.action] || "\u26A0\uFE0F";
            return "<li style=\"text-align:left;margin-bottom:6px;\">".concat(icon, " ").concat(warning.message, "</li>");
          }).join('');
          hideModal('submit-order');
          hideModal('order-confirmation');
          Swal.fire({
            icon: 'warning',
            title: "Sepetiniz G\xFCncellendi",
            html: "<ul style=\"list-style:none;padding:0;margin:0;\">".concat(listItems, "</ul>"),
            confirmButtonText: 'Tamam',
            allowOutsideClick: false
          }).then(function () {
            window.location.reload();
          });
          return;
        }
        if (response !== null && response !== void 0 && response.message) {
          var _window$notify, _window;
          var notifyType = response.status === 'warning' ? 'warning' : 'error';
          (_window$notify = (_window = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window, notifyType, response.message);
        }
        if (response && response.reload) {
          window.location.reload();
        }
      },
      onComplete: function onComplete() {
        setLoading(button, false);
      }
    });
  };
  document.body.addEventListener('click', function (event) {
    var button = event.target.closest('[data-action]');
    if (!button) {
      return;
    }
    event.preventDefault();
    switch (button.dataset.action) {
      case 'submit-order':
        {
          var settings = getSettings();
          submitOrder(button, settings.isOrderConfirmation);
          break;
        }
      case 'confirm-submit-order':
        submitOrder(button, false);
        break;
      default:
        break;
    }
  });
});
/******/ })()
;