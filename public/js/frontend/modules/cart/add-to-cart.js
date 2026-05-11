/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!***********************************************************!*\
  !*** ./resources/js/frontend/modules/cart/add-to-cart.js ***!
  \***********************************************************/
/* global window */

function openPaymentTypeModal(addToCartFunc) {
  var _window$bootstrap;
  var modalElement = document.querySelector('[data-js="payment-type-modal"]') || document.getElementById('paymentTypeModal');
  if (!modalElement || !((_window$bootstrap = window.bootstrap) !== null && _window$bootstrap !== void 0 && _window$bootstrap.Modal)) {
    return;
  }
  var modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalElement);
  modalInstance.show();
  modalElement.querySelectorAll('[data-js="select-payment-type"]').forEach(function (button) {
    button.onclick = function onPaymentTypeSelect() {
      var paymentType = this.getAttribute('data-payment-type');
      if (typeof window.setCartPaymentType !== 'function') {
        return;
      }
      window.setCartPaymentType(paymentType, {
        refreshCart: false,
        onSuccess: function onSuccess() {
          modalInstance.hide();
          addToCartFunc(paymentType, true);
        },
        onError: function onError() {
          // Keep modal state unchanged on failed selection.
        }
      });
    };
  });
}
document.addEventListener('click', function (event) {
  var _window$setLoading, _window;
  var button = event.target.closest('[data-js="add-to-cart"]');
  if (!button) {
    return;
  }
  event.preventDefault();
  (_window$setLoading = (_window = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window, button, true);
  var viewType = button.getAttribute('data-view-type');
  var container = button.closest(viewType === 'list' ? 'tr' : 'div');
  var quantityInput = container === null || container === void 0 ? void 0 : container.querySelector('input[name*="quantity"]');
  var quantity = quantityInput ? quantityInput.value : 1;
  var productId = button.getAttribute('data-product-id');
  var originalHtml = button.innerHTML;
  var successIcon = "\n        <svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"\n            fill=\"none\" stroke=\"white\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"cart-drop\">\n            <rect x=\"10\" y=\"2\" width=\"4\" height=\"4\" rx=\"1\" class=\"product-box\" />\n            <circle cx=\"9\" cy=\"21\" r=\"1\"></circle>\n            <circle cx=\"20\" cy=\"21\" r=\"1\"></circle>\n            <path d=\"M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6\" class=\"cart-path\"></path>\n        </svg>\n    ";
  var _addToCart = function addToCart() {
    var paymentType = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
    var skipReset = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    window.axiosRequest.post('/sepet/sepete-ekle', {
      quantity: quantity,
      product_id: productId,
      payment_type: paymentType
    }, {
      onSuccess: function onSuccess() {
        var _window$updateAllCart, _window2;
        (_window$updateAllCart = (_window2 = window).updateAllCarts) === null || _window$updateAllCart === void 0 || _window$updateAllCart.call(_window2);
        button.classList.remove('bg-dark');
        button.classList.add('bg-danger');
        button.innerHTML = successIcon;
      },
      onError: function onError(payload) {
        if (payload.payment_type_selection) {
          openPaymentTypeModal(_addToCart);
        }
        if (payload.stock && quantityInput) {
          quantityInput.value = payload.stock;
        }
        if (payload.message) {
          var _window$notify, _window3;
          var type = payload.status === 'warning' ? 'warning' : 'error';
          (_window$notify = (_window3 = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window3, type, payload.message);
        }
      },
      onComplete: function onComplete() {
        var _window$setLoading2, _window4;
        (_window$setLoading2 = (_window4 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window4, button, false);
        if (!skipReset) {
          setTimeout(function () {
            button.innerHTML = originalHtml;
            button.classList.add('bg-dark');
            button.classList.remove('bg-danger');
          }, 3000);
        }
      }
    });
  };
  _addToCart();
});
/******/ })()
;