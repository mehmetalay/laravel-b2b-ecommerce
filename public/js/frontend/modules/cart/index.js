/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/frontend/modules/cart/backup.js":
/*!******************************************************!*\
  !*** ./resources/js/frontend/modules/cart/backup.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   registerBackupHandlers: () => (/* binding */ registerBackupHandlers)
/* harmony export */ });
function registerBackupHandlers(context) {
  document.addEventListener('click', function (event) {
    var _document$getElementB2, _window$setLoading3, _window4;
    var backupCartBtn = event.target.closest('[data-js="backup-the-cart"]');
    if (backupCartBtn) {
      var _document$getElementB, _window$setLoading, _window;
      event.preventDefault();
      var cartName = (_document$getElementB = document.getElementById('cart_name')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.value;
      (_window$setLoading = (_window = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window, backupCartBtn, true);
      context.request.post(context.routes.backupCart, {
        cart_name: cartName
      }, {
        onSuccess: function onSuccess() {
          context.hideModalElements('.cart-export-modal');
          window.location.reload();
        },
        onError: function onError(data) {
          var _window$notify, _window2;
          var type = (data === null || data === void 0 ? void 0 : data.status) === 'warning' ? 'warning' : 'error';
          (_window$notify = (_window2 = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window2, type, (data === null || data === void 0 ? void 0 : data.message) || context.messages.requestError);
        },
        onValidationError: function onValidationError(errors) {
          context.notifyValidationError(errors, context.messages.requestError);
        },
        onComplete: function onComplete() {
          var _window$setLoading2, _window3;
          (_window$setLoading2 = (_window3 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window3, backupCartBtn, false);
        }
      });
      return;
    }
    var importCartBtn = event.target.closest('[data-js="import-cart"]');
    if (!importCartBtn) {
      return;
    }
    event.preventDefault();
    var backedUpCartId = (_document$getElementB2 = document.getElementById('backed_up_cart_id')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.value;
    (_window$setLoading3 = (_window4 = window).setLoading) === null || _window$setLoading3 === void 0 || _window$setLoading3.call(_window4, importCartBtn, true);
    context.request.post(context.routes.importCart, {
      backed_up_cart_id: backedUpCartId
    }, {
      onSuccess: function onSuccess() {
        context.hideModalElements('.import-backed-up-cart');
        window.location.reload();
      },
      onError: function onError(data) {
        var _window$notify2, _window5;
        var type = (data === null || data === void 0 ? void 0 : data.status) === 'warning' ? 'warning' : 'error';
        (_window$notify2 = (_window5 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window5, type, (data === null || data === void 0 ? void 0 : data.message) || context.messages.requestError);
      },
      onValidationError: function onValidationError(errors) {
        context.notifyValidationError(errors, context.messages.requestError);
      },
      onComplete: function onComplete() {
        var _window$setLoading4, _window6;
        (_window$setLoading4 = (_window6 = window).setLoading) === null || _window$setLoading4 === void 0 || _window$setLoading4.call(_window6, importCartBtn, false);
      }
    });
  });
}


/***/ }),

/***/ "./resources/js/frontend/modules/cart/campaign.js":
/*!********************************************************!*\
  !*** ./resources/js/frontend/modules/cart/campaign.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   registerCampaignHandlers: () => (/* binding */ registerCampaignHandlers)
/* harmony export */ });
function registerCampaignHandlers(context, giftModalController) {
  document.addEventListener('click', function (event) {
    var applyCampaignBtn = event.target.closest('[data-selector="apply-campaign"]');
    if (applyCampaignBtn) {
      event.preventDefault();
      var _campaignId = applyCampaignBtn.dataset.campaignId;
      context.request.post(context.routes.applyCampaign, {
        campaign_id: _campaignId
      }, {
        onSuccess: function onSuccess(response) {
          context.updateAllCartsSafe();
          if (!response.requires_gift_selection) {
            return;
          }
          var cartModalEl = document.getElementById('cartCampaignModal');
          if (cartModalEl && cartModalEl.classList.contains('show') && window.bootstrap) {
            cartModalEl.addEventListener('hidden.bs.modal', function () {
              return giftModalController.openGiftSelectionModal(response.campaign_id);
            }, {
              once: true
            });
            window.bootstrap.Modal.getOrCreateInstance(cartModalEl).hide();
            return;
          }
          giftModalController.openGiftSelectionModal(response.campaign_id);
        },
        onError: function onError(payload) {
          var _window$notify, _window;
          (_window$notify = (_window = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window, 'error', (payload === null || payload === void 0 ? void 0 : payload.message) || context.messages.campaignApplyFailed);
          context.updateAllCartsSafe();
        },
        onValidationError: function onValidationError(errors) {
          context.notifyValidationError(errors, context.messages.campaignApplyFailed);
        }
      });
      return;
    }
    var removeAllBtn = event.target.closest('[data-selector="remove-all-campaigns"]');
    if (removeAllBtn) {
      event.preventDefault();
      context.request.post(context.routes.removeAllCampaigns, {}, {
        onSuccess: function onSuccess() {
          context.updateAllCartsSafe();
        },
        onError: function onError(payload) {
          var _window$notify2, _window2;
          (_window$notify2 = (_window2 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window2, 'error', (payload === null || payload === void 0 ? void 0 : payload.message) || context.messages.requestError);
          context.updateAllCartsSafe();
        }
      });
      return;
    }
    var removeSingleBtn = event.target.closest('[data-selector="remove-single-campaign"]');
    if (!removeSingleBtn) {
      return;
    }
    event.preventDefault();
    var campaignId = removeSingleBtn.dataset.campaignId;
    context.request.post(context.routes.removeSingleCampaign, {
      campaign_id: campaignId
    }, {
      onSuccess: function onSuccess() {
        context.updateAllCartsSafe();
      },
      onError: function onError(payload) {
        var _window$notify3, _window3;
        (_window$notify3 = (_window3 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window3, 'error', (payload === null || payload === void 0 ? void 0 : payload.message) || context.messages.requestError);
        context.updateAllCartsSafe();
      }
    });
  });
}


/***/ }),

/***/ "./resources/js/frontend/modules/cart/cart-actions.js":
/*!************************************************************!*\
  !*** ./resources/js/frontend/modules/cart/cart-actions.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   registerCartActions: () => (/* binding */ registerCartActions)
/* harmony export */ });
function registerCartActions(context) {
  document.addEventListener('click', function (event) {
    var deleteAllBtn = event.target.closest('[data-js="delete-all-cart"]');
    if (deleteAllBtn) {
      event.preventDefault();
      context.showConfirm({
        text: context.messages.deleteAllConfirm,
        confirmButtonText: context.messages.confirmDeleteAll
      }).then(function (isConfirmed) {
        var _window$setLoading, _window;
        if (!isConfirmed) {
          return;
        }
        (_window$setLoading = (_window = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window, deleteAllBtn, true);
        context.request.post(context.routes.deleteAll, {}, {
          onSuccess: function onSuccess() {
            context.updateAllCartsSafe();
          },
          onError: function onError() {
            var _window$notify, _window2;
            (_window$notify = (_window2 = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window2, 'error', context.messages.requestError);
          },
          onValidationError: function onValidationError(errors) {
            context.notifyValidationError(errors, context.messages.requestError);
          },
          onComplete: function onComplete() {
            var _window$setLoading2, _window3;
            (_window$setLoading2 = (_window3 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window3, deleteAllBtn, false);
          }
        });
      });
      return;
    }
    var deleteProductBtn = event.target.closest('[data-js="delete-product-cart"]');
    if (!deleteProductBtn) {
      return;
    }
    event.preventDefault();
    context.showConfirm({
      text: context.messages.deleteProductConfirm,
      confirmButtonText: context.messages.confirmDeleteProduct
    }).then(function (isConfirmed) {
      var _window$setLoading3, _window4;
      if (!isConfirmed) {
        return;
      }
      (_window$setLoading3 = (_window4 = window).setLoading) === null || _window$setLoading3 === void 0 || _window$setLoading3.call(_window4, deleteProductBtn, true);
      context.request.del(deleteProductBtn.dataset.url, {}, {
        onSuccess: function onSuccess() {
          var _window$notify2, _window5;
          context.updateAllCartsSafe();
          (_window$notify2 = (_window5 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window5, 'success', context.messages.productRemoved);
        },
        onError: function onError() {
          var _window$notify3, _window6;
          (_window$notify3 = (_window6 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window6, 'error', context.messages.requestError);
        },
        onValidationError: function onValidationError(errors) {
          context.notifyValidationError(errors, context.messages.requestError);
        },
        onComplete: function onComplete() {
          var _window$setLoading4, _window7;
          (_window$setLoading4 = (_window7 = window).setLoading) === null || _window$setLoading4 === void 0 || _window$setLoading4.call(_window7, deleteProductBtn, false);
        }
      });
    });
  });
}


/***/ }),

/***/ "./resources/js/frontend/modules/cart/discount.js":
/*!********************************************************!*\
  !*** ./resources/js/frontend/modules/cart/discount.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   registerDiscountHandlers: () => (/* binding */ registerDiscountHandlers)
/* harmony export */ });
function registerDiscountHandlers(context) {
  document.addEventListener('click', function (event) {
    var generalDiscountBtn = event.target.closest('[data-js="general-discount-apply"]');
    if (generalDiscountBtn) {
      var _generalDiscountBtn$p, _window$setLoading, _window;
      event.preventDefault();
      var input = (_generalDiscountBtn$p = generalDiscountBtn.parentElement) === null || _generalDiscountBtn$p === void 0 ? void 0 : _generalDiscountBtn$p.querySelector('input');
      var discount = input === null || input === void 0 ? void 0 : input.value;
      var currency = generalDiscountBtn.dataset.currency;
      (_window$setLoading = (_window = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window, generalDiscountBtn, true);
      context.request.post(context.routes.generalDiscount, {
        discount: discount,
        currency: currency
      }, {
        onSuccess: function onSuccess(data) {
          var _window$notify, _window2;
          context.updateAllCartsSafe();
          (_window$notify = (_window2 = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window2, 'success', (data === null || data === void 0 ? void 0 : data.message) || context.messages.saveFailed);
          if (input) {
            input.value = '';
          }
        },
        onError: function onError(data) {
          var _window$notify2, _window3;
          var type = (data === null || data === void 0 ? void 0 : data.status) === 'warning' ? 'warning' : 'error';
          (_window$notify2 = (_window3 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window3, type, (data === null || data === void 0 ? void 0 : data.message) || context.messages.requestError);
        },
        onValidationError: function onValidationError(errors) {
          context.notifyValidationError(errors, context.messages.requestError);
        },
        onComplete: function onComplete() {
          var _window$setLoading2, _window4;
          (_window$setLoading2 = (_window4 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window4, generalDiscountBtn, false);
        }
      });
      return;
    }
    var cancelDiscountBtn = event.target.closest('[data-js="cancel-all-discounts"]');
    if (!cancelDiscountBtn) {
      return;
    }
    event.preventDefault();
    context.showConfirm({
      text: context.messages.cancelAllDiscountsConfirm,
      confirmButtonText: context.messages.confirmCancelDiscounts
    }).then(function (isConfirmed) {
      var _window$setLoading3, _window5;
      if (!isConfirmed) {
        return;
      }
      var currency = cancelDiscountBtn.dataset.currency;
      (_window$setLoading3 = (_window5 = window).setLoading) === null || _window$setLoading3 === void 0 || _window$setLoading3.call(_window5, cancelDiscountBtn, true);
      context.request.post(context.routes.cancelAllDiscounts, {
        currency: currency
      }, {
        onSuccess: function onSuccess(data) {
          var _window$notify3, _window6;
          context.updateAllCartsSafe();
          (_window$notify3 = (_window6 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window6, 'success', (data === null || data === void 0 ? void 0 : data.message) || context.messages.saveFailed);
        },
        onError: function onError(data) {
          var _window$notify4, _window7;
          var type = (data === null || data === void 0 ? void 0 : data.status) === 'warning' ? 'warning' : 'error';
          (_window$notify4 = (_window7 = window).notify) === null || _window$notify4 === void 0 || _window$notify4.call(_window7, type, (data === null || data === void 0 ? void 0 : data.message) || context.messages.requestError);
        },
        onValidationError: function onValidationError(errors) {
          context.notifyValidationError(errors, context.messages.requestError);
        },
        onComplete: function onComplete() {
          var _window$setLoading4, _window8;
          (_window$setLoading4 = (_window8 = window).setLoading) === null || _window$setLoading4 === void 0 || _window$setLoading4.call(_window8, cancelDiscountBtn, false);
        }
      });
    });
  });
  document.addEventListener('change', function (event) {
    var _discountInput$parent;
    var discountInput = event.target.closest('[data-selector="cart-update-discount"]');
    if (!discountInput) {
      return;
    }
    event.preventDefault();
    var valueInput = (_discountInput$parent = discountInput.parentElement) === null || _discountInput$parent === void 0 ? void 0 : _discountInput$parent.querySelector('input');
    var discount = valueInput ? valueInput.value : null;
    var url = discountInput.dataset.url;
    context.request.post(url, {
      discount: discount
    }, {
      onSuccess: function onSuccess(data) {
        var _window$notify5, _window9;
        context.updateAllCartsSafe();
        (_window$notify5 = (_window9 = window).notify) === null || _window$notify5 === void 0 || _window$notify5.call(_window9, 'success', (data === null || data === void 0 ? void 0 : data.message) || context.messages.saveFailed);
      },
      onError: function onError(data) {
        var _window$notify6, _window0;
        var type = (data === null || data === void 0 ? void 0 : data.status) === 'warning' ? 'warning' : 'error';
        (_window$notify6 = (_window0 = window).notify) === null || _window$notify6 === void 0 || _window$notify6.call(_window0, type, (data === null || data === void 0 ? void 0 : data.message) || context.messages.requestError);
      },
      onValidationError: function onValidationError(errors) {
        context.notifyValidationError(errors, context.messages.requestError);
      }
    });
  });
}


/***/ }),

/***/ "./resources/js/frontend/modules/cart/gift-modal.js":
/*!**********************************************************!*\
  !*** ./resources/js/frontend/modules/cart/gift-modal.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createGiftModalController: () => (/* binding */ createGiftModalController)
/* harmony export */ });
function createGiftModalController(context) {
  var getGiftModalElement = function getGiftModalElement() {
    return document.querySelector('[data-js="free-product-gift-modal"]') || document.getElementById('freeProductGiftModal');
  };
  var createModalQueries = function createModalQueries(modalEl) {
    var one = function one(primarySelector, fallbackSelector) {
      return modalEl.querySelector(primarySelector) || modalEl.querySelector(fallbackSelector);
    };
    var all = function all(primarySelector, fallbackSelector) {
      var primaryList = modalEl.querySelectorAll(primarySelector);
      if (primaryList.length > 0) {
        return Array.from(primaryList);
      }
      return Array.from(modalEl.querySelectorAll(fallbackSelector));
    };
    return {
      limitInput: function limitInput() {
        return one('[data-role="gift-limit"]', '#gift-limit');
      },
      totalLabel: function totalLabel() {
        return one('[data-role="gift-total-selected"]', '#gift-total-selected');
      },
      remainingLabel: function remainingLabel() {
        return one('[data-role="gift-remaining-text"]', '#gift-remaining-text');
      },
      warning: function warning() {
        return one('[data-role="gift-selection-warning"]', '#gift-selection-warning');
      },
      saveButton: function saveButton() {
        return one('[data-role="save-free-product-gifts"]', '#save-free-product-gifts');
      },
      giftForm: function giftForm() {
        return one('[data-role="free-product-gift-form"]', '#free-product-gift-form');
      },
      qtyInputs: function qtyInputs() {
        return all('[data-role="gift-qty-input"]', '.gift-qty-input');
      },
      plusButtons: function plusButtons() {
        return all('[data-role="gift-qty-button"][data-action="plus"]', '.gift-qty-btn[data-action="plus"]');
      },
      qtyInputByGiftId: function qtyInputByGiftId(giftId) {
        return one("[data-role=\"gift-qty-input\"][data-gift-id=\"".concat(giftId, "\"]"), ".gift-qty-input[data-gift-id=\"".concat(giftId, "\"]"));
      }
    };
  };
  var parseLimit = function parseLimit(queries) {
    var _queries$limitInput;
    return Number.parseInt(((_queries$limitInput = queries.limitInput()) === null || _queries$limitInput === void 0 ? void 0 : _queries$limitInput.value) || '0', 10) || 0;
  };
  var calculateGiftTotal = function calculateGiftTotal(queries) {
    var total = 0;
    queries.qtyInputs().forEach(function (input) {
      total += Number.parseInt(input.value || '0', 10) || 0;
    });
    var totalLabel = queries.totalLabel();
    if (totalLabel) {
      totalLabel.textContent = String(total);
    }
    return total;
  };
  var updateGiftUI = function updateGiftUI(queries) {
    var limit = parseLimit(queries);
    var total = calculateGiftTotal(queries);
    var remainingText = queries.remainingLabel();
    if (remainingText) {
      var remaining = limit - total;
      if (remaining > 0) {
        remainingText.textContent = "(".concat(remaining, " adet daha se\xE7melisiniz)");
        remainingText.classList.remove('text-success');
        remainingText.classList.add('text-warning');
      } else {
        remainingText.textContent = '(limit doldu)';
        remainingText.classList.remove('text-warning');
        remainingText.classList.add('text-success');
      }
    }
    var warning = queries.warning();
    if (warning) {
      warning.style.display = total > limit ? '' : 'none';
    }
    queries.plusButtons().forEach(function (button) {
      button.disabled = total >= limit;
    });
    var saveButton = queries.saveButton();
    if (saveButton) {
      saveButton.disabled = total !== limit;
    }
    return total;
  };
  var setGiftQty = function setGiftQty(queries, giftId, nextValue) {
    var input = queries.qtyInputByGiftId(giftId);
    if (!input) {
      return;
    }
    var limit = parseLimit(queries);
    var newValue = Math.max(0, Number.parseInt(nextValue || '0', 10) || 0);
    var currentTotal = calculateGiftTotal(queries);
    var currentValue = Number.parseInt(input.value || '0', 10) || 0;
    var nextTotal = currentTotal - currentValue + newValue;
    var warning = queries.warning();
    if (limit > 0 && nextTotal > limit) {
      if (warning) {
        warning.style.display = '';
      }
      return;
    }
    if (warning) {
      warning.style.display = 'none';
    }
    input.value = String(newValue);
    calculateGiftTotal(queries);
  };
  var openGiftSelectionModal = function openGiftSelectionModal(campaignId) {
    if (!window.axios || !campaignId) {
      return;
    }
    window.axios.get(context.routes.freeProductModal, {
      params: {
        campaign_id: campaignId
      }
    }).then(function (response) {
      var _getGiftModalElement, _queries$saveButton;
      var html = typeof response.data === 'string' ? response.data : '';
      if (!html) {
        return;
      }
      var mount = document.getElementById('global-modals') || document.body;
      (_getGiftModalElement = getGiftModalElement()) === null || _getGiftModalElement === void 0 || _getGiftModalElement.remove();
      mount.insertAdjacentHTML('beforeend', html);
      var modalEl = getGiftModalElement();
      if (!modalEl || !window.bootstrap) {
        return;
      }
      var queries = createModalQueries(modalEl);
      var modal = new window.bootstrap.Modal(modalEl);
      modal.show();
      calculateGiftTotal(queries);
      updateGiftUI(queries);
      modalEl.addEventListener('input', function (event) {
        var target = event.target;
        if (!(target instanceof HTMLInputElement)) {
          return;
        }
        if (!target.name || !target.name.startsWith('gifts[')) {
          return;
        }
        calculateGiftTotal(queries);
        updateGiftUI(queries);
      });
      (_queries$saveButton = queries.saveButton()) === null || _queries$saveButton === void 0 || _queries$saveButton.addEventListener('click', function () {
        var total = updateGiftUI(queries);
        var limit = parseLimit(queries);
        if (total !== limit) {
          var _window$notify, _window;
          var message = context.messages.giftCountRequiredTemplate.replace('{limit}', String(limit));
          (_window$notify = (_window = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window, 'error', message);
          return;
        }
        var form = queries.giftForm();
        if (!(form instanceof HTMLFormElement)) {
          return;
        }
        var formData = new FormData(form);
        context.request.post(context.routes.selectGifts, formData, {
          onSuccess: function onSuccess() {
            modal.hide();
            context.updateAllCartsSafe();
          },
          onError: function onError(payload) {
            var _window$notify2, _window2;
            (_window$notify2 = (_window2 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window2, 'error', (payload === null || payload === void 0 ? void 0 : payload.message) || context.messages.giftSelectSaveFailed);
          },
          onValidationError: function onValidationError(errors) {
            context.notifyValidationError(errors, context.messages.giftSelectSaveFailed);
          }
        });
      });
    })["catch"](function () {
      var _window$notify3, _window3;
      (_window$notify3 = (_window3 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window3, 'error', context.messages.giftSelectSaveFailed);
    });
  };
  var registerEvents = function registerEvents() {
    document.addEventListener('click', function (event) {
      var selectGiftsBtn = event.target.closest('[data-selector="select-gifts"]');
      if (selectGiftsBtn) {
        event.preventDefault();
        var campaignId = selectGiftsBtn.dataset.campaignId;
        var cartModalEl = document.getElementById('cartCampaignModal');
        if (cartModalEl && cartModalEl.classList.contains('show') && window.bootstrap) {
          cartModalEl.addEventListener('hidden.bs.modal', function () {
            return openGiftSelectionModal(campaignId);
          }, {
            once: true
          });
          window.bootstrap.Modal.getOrCreateInstance(cartModalEl).hide();
          return;
        }
        openGiftSelectionModal(campaignId);
        return;
      }
      var addSameProductGiftBtn = event.target.closest('[data-selector="add-same-product-gift"]');
      if (addSameProductGiftBtn) {
        event.preventDefault();
        var cartId = addSameProductGiftBtn.dataset.cartId;
        var _campaignId = addSameProductGiftBtn.dataset.campaignId;
        context.request.post(context.routes.addSameProductGift, {
          cart_id: cartId,
          campaign_id: _campaignId
        }, {
          onSuccess: function onSuccess() {
            context.updateAllCartsSafe();
          },
          onError: function onError(payload) {
            var _window$notify4, _window4;
            (_window$notify4 = (_window4 = window).notify) === null || _window$notify4 === void 0 || _window$notify4.call(_window4, 'error', (payload === null || payload === void 0 ? void 0 : payload.message) || context.messages.requestError);
            context.updateAllCartsSafe();
          }
        });
        return;
      }
      var qtyButton = event.target.closest('[data-role="gift-qty-button"], #freeProductGiftModal .gift-qty-btn');
      if (!qtyButton) {
        return;
      }
      var modalEl = qtyButton.closest('[data-js="free-product-gift-modal"]') || getGiftModalElement();
      if (!modalEl) {
        return;
      }
      var queries = createModalQueries(modalEl);
      var giftId = qtyButton.dataset.giftId;
      var action = qtyButton.dataset.action;
      var input = queries.qtyInputByGiftId(giftId);
      var currentValue = Number.parseInt((input === null || input === void 0 ? void 0 : input.value) || '0', 10) || 0;
      if (action === 'plus') {
        setGiftQty(queries, giftId, currentValue + 1);
      }
      if (action === 'minus') {
        setGiftQty(queries, giftId, currentValue - 1);
      }
      updateGiftUI(queries);
    });
    document.addEventListener('shown.bs.modal', function (event) {
      var modalEl = event.target;
      if (!(modalEl instanceof HTMLElement)) {
        return;
      }
      if (modalEl.getAttribute('data-js') !== 'free-product-gift-modal' && modalEl.id !== 'freeProductGiftModal') {
        return;
      }
      var queries = createModalQueries(modalEl);
      calculateGiftTotal(queries);
      var warning = queries.warning();
      if (warning) {
        warning.style.display = 'none';
      }
    });
  };
  return {
    openGiftSelectionModal: openGiftSelectionModal,
    registerEvents: registerEvents
  };
}


/***/ }),

/***/ "./resources/js/frontend/modules/cart/helpers.js":
/*!*******************************************************!*\
  !*** ./resources/js/frontend/modules/cart/helpers.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createCartContext: () => (/* binding */ createCartContext)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function parseConfig(pageRoot) {
  var configEl = pageRoot.querySelector('[data-js="cart-index-config"]');
  var config = configEl ? configEl.dataset : {};
  return {
    routes: {
      orderStore: config.orderStoreUrl || '/orders',
      orderPreview: config.orderPreviewUrl || '/orders/preview',
      deleteAll: config.deleteAllUrl || '/sepet/tumunu-sil',
      generalDiscount: config.generalDiscountUrl || '/sepet/discount/general',
      cancelAllDiscounts: config.cancelAllDiscountsUrl || '/sepet/discount/all-cancel',
      backupCart: config.backupCartUrl || '/sepet/import',
      importCart: config.importCartUrl || '/sepet/export',
      applyCampaign: config.applyCampaignUrl || '/sepet/campaign/apply',
      removeAllCampaigns: config.removeAllCampaignsUrl || '/sepet/campaign/remove',
      removeSingleCampaign: config.removeSingleCampaignUrl || '/sepet/campaign/remove-single',
      freeProductModal: config.freeProductModalUrl || '/sepet/campaign/free-product/modal',
      selectGifts: config.selectGiftsUrl || '/sepet/campaign/free-product/select-gifts',
      addSameProductGift: config.addSameProductGiftUrl || '/sepet/campaign/free-product/add-same-product',
      updatePriceTemplate: config.updatePriceUrlTemplate || '/sepet/update/price/{id}'
    },
    messages: {
      confirmTitle: config.msgConfirmTitle || 'Emin misiniz?',
      deleteAllConfirm: config.msgDeleteAllConfirm || 'Sepeti boşaltmak istediğinize emin misiniz?',
      deleteProductConfirm: config.msgDeleteProductConfirm || 'Ürünü sepetten silmek istediğinize emin misiniz?',
      cancelAllDiscountsConfirm: config.msgCancelAllDiscountsConfirm || 'Sepet indirimini iptal etmek istediğinize emin misiniz?',
      confirmDeleteAll: config.msgConfirmDeleteAll || 'Evet, boşalt',
      confirmDeleteProduct: config.msgConfirmDeleteProduct || 'Evet, sil',
      confirmCancelDiscounts: config.msgConfirmCancelDiscounts || 'Evet, iptal et',
      confirmCancel: config.msgConfirmCancel || 'Hayır',
      requestError: config.msgRequestError || 'Bir hata oluştu.',
      saveFailed: config.msgSaveFailed || 'İşlem başarısız',
      productRemoved: config.msgProductRemoved || 'Ürün sepetten silindi',
      campaignApplyFailed: config.msgCampaignApplyFailed || 'Kampanya uygulanamadı.',
      giftSelectSaveFailed: config.msgGiftSelectSaveFailed || 'Hediye seçimi kaydedilemedi.',
      giftCountRequiredTemplate: config.msgGiftCountRequiredTemplate || 'Toplam {limit} adet hediye seçmelisiniz.'
    },
    isOrderConfirmation: config.isOrderConfirmation === '1'
  };
}
function firstValidationMessage(errors) {
  if (!errors || _typeof(errors) !== 'object') {
    return null;
  }
  var firstKey = Object.keys(errors)[0];
  if (!firstKey) {
    return null;
  }
  var message = errors[firstKey];
  return Array.isArray(message) ? message[0] : message;
}
function createRequestWrapper(messages) {
  var call = function call(method, url, data) {
    var callbacks = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
    if (!window.axiosRequest || typeof window.axiosRequest[method] !== 'function') {
      var _window$notify, _window, _callbacks$onComplete;
      (_window$notify = (_window = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window, 'error', messages.requestError);
      (_callbacks$onComplete = callbacks.onComplete) === null || _callbacks$onComplete === void 0 || _callbacks$onComplete.call(callbacks);
      return;
    }
    window.axiosRequest[method](url, data, callbacks);
  };
  return {
    post: function post(url, data) {
      var callbacks = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return call('post', url, data, callbacks);
    },
    del: function del(url, data) {
      var callbacks = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return call('delete', url, data, callbacks);
    }
  };
}
function _showConfirm(messages, _ref) {
  var text = _ref.text,
    confirmButtonText = _ref.confirmButtonText;
  if (!window.Swal || typeof window.Swal.fire !== 'function') {
    return Promise.resolve(false);
  }
  return window.Swal.fire({
    title: messages.confirmTitle,
    text: text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: confirmButtonText,
    cancelButtonText: messages.confirmCancel
  }).then(function (result) {
    return result.isConfirmed;
  });
}
function hideModalElements(selector) {
  if (!window.bootstrap) {
    return;
  }
  document.querySelectorAll(selector).forEach(function (modalEl) {
    var modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.hide();
  });
}
function updateAllCartsSafe() {
  if (typeof window.updateAllCarts === 'function') {
    return window.updateAllCarts();
  }
  return Promise.resolve();
}
function _notifyValidationError(errors, fallbackMessage) {
  var _window$notify2, _window2;
  var message = firstValidationMessage(errors) || fallbackMessage;
  (_window$notify2 = (_window2 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window2, 'error', message);
}
function createCartContext(pageRoot) {
  var parsed = parseConfig(pageRoot);
  var request = createRequestWrapper(parsed.messages);
  return {
    pageRoot: pageRoot,
    routes: parsed.routes,
    messages: parsed.messages,
    isOrderConfirmation: parsed.isOrderConfirmation,
    request: request,
    updateAllCartsSafe: updateAllCartsSafe,
    showConfirm: function showConfirm(payload) {
      return _showConfirm(parsed.messages, payload);
    },
    hideModalElements: hideModalElements,
    firstValidationMessage: firstValidationMessage,
    notifyValidationError: function notifyValidationError(errors, fallback) {
      return _notifyValidationError(errors, fallback || parsed.messages.requestError);
    }
  };
}


/***/ }),

/***/ "./resources/js/frontend/modules/cart/price-modal.js":
/*!***********************************************************!*\
  !*** ./resources/js/frontend/modules/cart/price-modal.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   registerPriceModal: () => (/* binding */ registerPriceModal)
/* harmony export */ });
function registerPriceModal(context) {
  var modalEl = document.querySelector('[data-selector="edit-price-modal"]');
  if (!modalEl || modalEl.dataset.bound === '1') {
    return;
  }
  var rowIdInput = modalEl.querySelector('[data-selector="row-id"]');
  var listPriceEl = modalEl.querySelector('[data-selector="list-price"]');
  var discountEl = modalEl.querySelector('[data-selector="discount-rate"]');
  var netPriceEl = modalEl.querySelector('[data-selector="net-price"]');
  var saveBtn = modalEl.querySelector('[data-selector="save-price-btn"]');
  if (!rowIdInput || !listPriceEl || !discountEl || !netPriceEl || !saveBtn) {
    return;
  }
  modalEl.dataset.bound = '1';
  modalEl.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    if (!button) {
      return;
    }
    var rowId = button.getAttribute('data-id');
    var listPrice = Number.parseFloat(button.getAttribute('data-list-price'));
    var discount = Number.parseFloat(button.getAttribute('data-discount'));
    rowIdInput.value = rowId || '';
    listPriceEl.value = Number.isFinite(listPrice) ? listPrice.toFixed(6) : '0.000000';
    discountEl.value = Number.isFinite(discount) ? discount.toFixed(6) : '0.000000';
    var netPrice = Number.parseFloat(listPriceEl.value) * (1 - Number.parseFloat(discountEl.value) / 100);
    netPriceEl.value = Number.isFinite(netPrice) ? netPrice.toFixed(6) : '0.000000';
  });
  discountEl.addEventListener('input', function () {
    var listPrice = Number.parseFloat(listPriceEl.value);
    var discount = Number.parseFloat(discountEl.value) || 0;
    var netPrice = listPrice * (1 - discount / 100);
    netPriceEl.value = Number.isFinite(netPrice) ? netPrice.toFixed(6) : '0.000000';
  });
  netPriceEl.addEventListener('input', function () {
    var listPrice = Number.parseFloat(listPriceEl.value);
    var netPrice = Number.parseFloat(netPriceEl.value) || 0;
    var discount = (1 - netPrice / listPrice) * 100;
    discountEl.value = Number.isFinite(discount) ? discount.toFixed(6) : '0.000000';
  });
  saveBtn.addEventListener('click', function () {
    var _window$setLoading, _window;
    var id = rowIdInput.value;
    if (!id) {
      return;
    }
    var payload = {
      id: id,
      discount: Number.parseFloat(discountEl.value),
      net_price: Number.parseFloat(netPriceEl.value)
    };
    var url = context.routes.updatePriceTemplate.replace('{id}', id);
    (_window$setLoading = (_window = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window, saveBtn, true);
    context.request.post(url, payload, {
      onSuccess: function onSuccess(data) {
        var _window$notify, _window2;
        context.updateAllCartsSafe();
        (_window$notify = (_window2 = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window2, 'success', (data === null || data === void 0 ? void 0 : data.message) || context.messages.saveFailed);
        if (window.bootstrap) {
          window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }
      },
      onError: function onError(data) {
        var _window$notify2, _window3;
        var type = (data === null || data === void 0 ? void 0 : data.status) === 'warning' ? 'warning' : 'error';
        (_window$notify2 = (_window3 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window3, type, (data === null || data === void 0 ? void 0 : data.message) || context.messages.requestError);
      },
      onValidationError: function onValidationError(errors) {
        context.notifyValidationError(errors, context.messages.requestError);
      },
      onComplete: function onComplete() {
        var _window$setLoading2, _window4;
        (_window$setLoading2 = (_window4 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window4, saveBtn, false);
      }
    });
  });
}


/***/ }),

/***/ "./resources/js/frontend/modules/cart/quantity.js":
/*!********************************************************!*\
  !*** ./resources/js/frontend/modules/cart/quantity.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   registerQuantityHandlers: () => (/* binding */ registerQuantityHandlers)
/* harmony export */ });
function registerQuantityHandlers(context) {
  var updateCartQuantity = function updateCartQuantity(url, quantity) {
    var button = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    if (!url) {
      return;
    }
    if (button) {
      var _window$setLoading, _window;
      (_window$setLoading = (_window = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window, button, true);
    }
    context.request.post(url, {
      quantity: quantity
    }, {
      onSuccess: function onSuccess(data) {
        var _window$notify, _window2;
        context.updateAllCartsSafe();
        (_window$notify = (_window2 = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window2, 'success', (data === null || data === void 0 ? void 0 : data.message) || context.messages.saveFailed);
      },
      onError: function onError(data) {
        var _window$notify2, _window3;
        var type = (data === null || data === void 0 ? void 0 : data.status) === 'warning' ? 'warning' : 'error';
        var message = (data === null || data === void 0 ? void 0 : data.message) || context.messages.requestError;
        (_window$notify2 = (_window3 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window3, type, message);
      },
      onValidationError: function onValidationError(errors) {
        context.notifyValidationError(errors, context.messages.requestError);
      },
      onComplete: function onComplete() {
        if (button) {
          var _window$setLoading2, _window4;
          (_window$setLoading2 = (_window4 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window4, button, false);
        }
      }
    });
  };
  document.addEventListener('change', function (event) {
    var quantityInput = event.target.closest('[data-selector="qty-value"]');
    if (!quantityInput) {
      return;
    }
    event.preventDefault();
    updateCartQuantity(quantityInput.dataset.url, quantityInput.value);
  });
  document.addEventListener('focusout', function (event) {
    var input = event.target.closest('[data-selector="cart-update-explanation"]');
    if (!input) {
      return;
    }
    var oldValue = input.dataset.oldValue || '';
    var newValue = input.value || '';
    if (oldValue === newValue) {
      return;
    }
    context.request.post(input.dataset.url, {
      explanation: newValue
    }, {
      onSuccess: function onSuccess(data) {
        var _window$notify3, _window5;
        (_window$notify3 = (_window5 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window5, 'success', (data === null || data === void 0 ? void 0 : data.message) || context.messages.saveFailed);
        input.dataset.oldValue = newValue;
      },
      onError: function onError(data) {
        var _window$notify4, _window6;
        var type = (data === null || data === void 0 ? void 0 : data.status) === 'warning' ? 'warning' : 'error';
        (_window$notify4 = (_window6 = window).notify) === null || _window$notify4 === void 0 || _window$notify4.call(_window6, type, (data === null || data === void 0 ? void 0 : data.message) || context.messages.requestError);
      },
      onValidationError: function onValidationError(errors) {
        context.notifyValidationError(errors, context.messages.requestError);
      }
    });
  }, true);
}


/***/ }),

/***/ "./resources/js/shared/loading.js":
/*!****************************************!*\
  !*** ./resources/js/shared/loading.js ***!
  \****************************************/
/***/ (() => {

/* global window */

function setLoading(button, isLoading) {
  var el = button !== null && button !== void 0 && button.jquery ? button[0] : button;
  if (!el) {
    return;
  }
  el.classList[isLoading ? 'add' : 'remove']('btn-loading');
  el.disabled = isLoading;
}
window.setLoading = setLoading;

/***/ }),

/***/ "./resources/js/shared/notifications.js":
/*!**********************************************!*\
  !*** ./resources/js/shared/notifications.js ***!
  \**********************************************/
/***/ (() => {

/* global window */

function notify(type, message) {
  var container = document.querySelector('.notifications');
  if (!container) {
    container = document.createElement('div');
    container.className = 'notifications';
    document.body.appendChild(container);
  }
  var el = document.createElement('div');
  el.className = "notification ".concat(type);
  el.innerText = message;
  container.appendChild(el);
  setTimeout(function () {
    el.remove();
  }, 4000);
}
window.notify = notify;

/***/ }),

/***/ "./resources/js/shared/request-axios.js":
/*!**********************************************!*\
  !*** ./resources/js/shared/request-axios.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AxiosRequest: () => (/* binding */ AxiosRequest),
/* harmony export */   axiosRequest: () => (/* binding */ axiosRequest),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* global axios, window */
var AxiosRequest = /*#__PURE__*/function () {
  function AxiosRequest() {
    var _document$querySelect;
    _classCallCheck(this, AxiosRequest);
    var csrfToken = (_document$querySelect = document.querySelector('meta[name="csrf-token"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.content;
    if (csrfToken) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.withCredentials = true;
  }
  return _createClass(AxiosRequest, [{
    key: "rawGet",
    value: function rawGet(url) {
      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var config = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return axios(_objectSpread(_objectSpread({}, config), {}, {
        method: 'GET',
        url: url,
        params: params
      }));
    }
  }, {
    key: "rawPost",
    value: function rawPost(url) {
      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var config = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return axios(_objectSpread(_objectSpread({}, config), {}, {
        method: 'POST',
        url: url,
        data: data
      }));
    }
  }, {
    key: "rawPut",
    value: function rawPut(url) {
      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var config = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return axios(_objectSpread(_objectSpread({}, config), {}, {
        method: 'PUT',
        url: url,
        data: data
      }));
    }
  }, {
    key: "rawDelete",
    value: function rawDelete(url) {
      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var config = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return axios(_objectSpread(_objectSpread({}, config), {}, {
        method: 'DELETE',
        url: url,
        data: data
      }));
    }
  }, {
    key: "extractBusinessResult",
    value: function extractBusinessResult(response) {
      return response.data;
    }
  }, {
    key: "extractValidationErrors",
    value: function extractValidationErrors(result) {
      return (result === null || result === void 0 ? void 0 : result.errors) || null;
    }
  }, {
    key: "extractErrorContext",
    value: function extractErrorContext(err) {
      var _response$data;
      var response = err === null || err === void 0 ? void 0 : err.response;
      var validationErrors = (response === null || response === void 0 ? void 0 : response.status) === 422 && (_response$data = response.data) !== null && _response$data !== void 0 && _response$data.errors ? response.data.errors : null;
      return {
        response: response,
        validationErrors: validationErrors,
        payload: (response === null || response === void 0 ? void 0 : response.data) || err
      };
    }
  }, {
    key: "shouldNotifyError",
    value: function shouldNotifyError(_ref) {
      var validationErrors = _ref.validationErrors;
      return !validationErrors;
    }
  }, {
    key: "dispatchValidationCallbacks",
    value: function dispatchValidationCallbacks(onValidationError, validationErrors) {
      onValidationError === null || onValidationError === void 0 || onValidationError(validationErrors);
    }
  }, {
    key: "dispatchErrorCallbacks",
    value: function dispatchErrorCallbacks(onError, payload) {
      onError === null || onError === void 0 || onError(payload);
    }
  }, {
    key: "dispatchBusinessResult",
    value: function dispatchBusinessResult(_ref2) {
      var result = _ref2.result,
        onSuccess = _ref2.onSuccess,
        onError = _ref2.onError,
        onValidationError = _ref2.onValidationError;
      if (result.status === 'success') {
        onSuccess === null || onSuccess === void 0 || onSuccess(result);
        return;
      }
      var validationErrors = this.extractValidationErrors(result);
      if (validationErrors) {
        this.dispatchValidationCallbacks(onValidationError, validationErrors);
        return;
      }
      this.dispatchErrorCallbacks(onError, result);
    }
  }, {
    key: "dispatchRequestError",
    value: function dispatchRequestError(_ref3) {
      var err = _ref3.err,
        onError = _ref3.onError,
        onValidationError = _ref3.onValidationError;
      var errorContext = this.extractErrorContext(err);
      if (errorContext.validationErrors) {
        this.dispatchValidationCallbacks(onValidationError, errorContext.validationErrors);
        return;
      }
      this.dispatchErrorCallbacks(onError, errorContext.payload);
    }
  }, {
    key: "request",
    value: function () {
      var _request = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(_ref4) {
        var url, _ref4$method, method, _ref4$data, data, onSuccess, onError, onValidationError, onComplete, normalizedMethod, response, formData, _formData, result, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              url = _ref4.url, _ref4$method = _ref4.method, method = _ref4$method === void 0 ? 'GET' : _ref4$method, _ref4$data = _ref4.data, data = _ref4$data === void 0 ? {} : _ref4$data, onSuccess = _ref4.onSuccess, onError = _ref4.onError, onValidationError = _ref4.onValidationError, onComplete = _ref4.onComplete;
              _context.p = 1;
              normalizedMethod = method.toUpperCase();
              if (!(normalizedMethod === 'GET')) {
                _context.n = 3;
                break;
              }
              _context.n = 2;
              return this.rawGet(url, data);
            case 2:
              response = _context.v;
              _context.n = 18;
              break;
            case 3:
              if (!(normalizedMethod === 'DELETE')) {
                _context.n = 5;
                break;
              }
              formData = new FormData();
              formData.append('_method', 'DELETE');
              Object.keys(data).forEach(function (k) {
                return formData.append(k, data[k]);
              });
              _context.n = 4;
              return this.rawPost(url, formData);
            case 4:
              response = _context.v;
              _context.n = 18;
              break;
            case 5:
              if (!Object.values(data).some(function (v) {
                return v instanceof File;
              })) {
                _context.n = 12;
                break;
              }
              _formData = new FormData();
              Object.keys(data).forEach(function (k) {
                return _formData.append(k, data[k]);
              });
              if (!(normalizedMethod === 'POST')) {
                _context.n = 7;
                break;
              }
              _context.n = 6;
              return this.rawPost(url, _formData);
            case 6:
              response = _context.v;
              _context.n = 11;
              break;
            case 7:
              if (!(normalizedMethod === 'PUT')) {
                _context.n = 9;
                break;
              }
              _context.n = 8;
              return this.rawPut(url, _formData);
            case 8:
              response = _context.v;
              _context.n = 11;
              break;
            case 9:
              _context.n = 10;
              return axios({
                method: method,
                url: url,
                data: _formData
              });
            case 10:
              response = _context.v;
            case 11:
              _context.n = 18;
              break;
            case 12:
              if (!(normalizedMethod === 'POST')) {
                _context.n = 14;
                break;
              }
              _context.n = 13;
              return this.rawPost(url, data);
            case 13:
              response = _context.v;
              _context.n = 18;
              break;
            case 14:
              if (!(normalizedMethod === 'PUT')) {
                _context.n = 16;
                break;
              }
              _context.n = 15;
              return this.rawPut(url, data);
            case 15:
              response = _context.v;
              _context.n = 18;
              break;
            case 16:
              _context.n = 17;
              return axios({
                method: method,
                url: url,
                data: data
              });
            case 17:
              response = _context.v;
            case 18:
              result = this.extractBusinessResult(response);
              this.dispatchBusinessResult({
                result: result,
                onSuccess: onSuccess,
                onError: onError,
                onValidationError: onValidationError
              });
              _context.n = 20;
              break;
            case 19:
              _context.p = 19;
              _t = _context.v;
              this.dispatchRequestError({
                err: _t,
                onError: onError,
                onValidationError: onValidationError
              });
            case 20:
              _context.p = 20;
              onComplete === null || onComplete === void 0 || onComplete();
              return _context.f(20);
            case 21:
              return _context.a(2);
          }
        }, _callee, this, [[1, 19, 20, 21]]);
      }));
      function request(_x) {
        return _request.apply(this, arguments);
      }
      return request;
    }()
  }, {
    key: "get",
    value: function get(url) {
      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var callbacks = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return this.request(_objectSpread({
        url: url,
        method: 'GET',
        data: params
      }, callbacks));
    }
  }, {
    key: "post",
    value: function post(url) {
      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var callbacks = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return this.request(_objectSpread({
        url: url,
        method: 'POST',
        data: data
      }, callbacks));
    }
  }, {
    key: "put",
    value: function put(url) {
      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var callbacks = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return this.request(_objectSpread({
        url: url,
        method: 'PUT',
        data: data
      }, callbacks));
    }
  }, {
    key: "delete",
    value: function _delete(url) {
      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var callbacks = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return this.request(_objectSpread({
        url: url,
        method: 'DELETE',
        data: data
      }, callbacks));
    }
  }]);
}();
var axiosRequest = new AxiosRequest();
window.AxiosRequest = AxiosRequest;
window.axiosRequest = axiosRequest;

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (axiosRequest);

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!*****************************************************!*\
  !*** ./resources/js/frontend/modules/cart/index.js ***!
  \*****************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shared_loading__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../shared/loading */ "./resources/js/shared/loading.js");
/* harmony import */ var _shared_loading__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_shared_loading__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _shared_notifications__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../shared/notifications */ "./resources/js/shared/notifications.js");
/* harmony import */ var _shared_notifications__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_shared_notifications__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _shared_request_axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../shared/request-axios */ "./resources/js/shared/request-axios.js");
/* harmony import */ var _backup__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./backup */ "./resources/js/frontend/modules/cart/backup.js");
/* harmony import */ var _campaign__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./campaign */ "./resources/js/frontend/modules/cart/campaign.js");
/* harmony import */ var _cart_actions__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./cart-actions */ "./resources/js/frontend/modules/cart/cart-actions.js");
/* harmony import */ var _discount__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./discount */ "./resources/js/frontend/modules/cart/discount.js");
/* harmony import */ var _gift_modal__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./gift-modal */ "./resources/js/frontend/modules/cart/gift-modal.js");
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./helpers */ "./resources/js/frontend/modules/cart/helpers.js");
/* harmony import */ var _price_modal__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./price-modal */ "./resources/js/frontend/modules/cart/price-modal.js");
/* harmony import */ var _quantity__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./quantity */ "./resources/js/frontend/modules/cart/quantity.js");











(function bootstrapCartIndexPage() {
  var pageRoot = document.querySelector('[data-js="cart-index-page"]');
  if (!pageRoot) {
    return;
  }
  var context = (0,_helpers__WEBPACK_IMPORTED_MODULE_8__.createCartContext)(pageRoot);
  var giftModalController = (0,_gift_modal__WEBPACK_IMPORTED_MODULE_7__.createGiftModalController)(context);
  (0,_cart_actions__WEBPACK_IMPORTED_MODULE_5__.registerCartActions)(context);
  (0,_quantity__WEBPACK_IMPORTED_MODULE_10__.registerQuantityHandlers)(context);
  (0,_discount__WEBPACK_IMPORTED_MODULE_6__.registerDiscountHandlers)(context);
  (0,_backup__WEBPACK_IMPORTED_MODULE_3__.registerBackupHandlers)(context);
  (0,_campaign__WEBPACK_IMPORTED_MODULE_4__.registerCampaignHandlers)(context, giftModalController);
  giftModalController.registerEvents();
  (0,_price_modal__WEBPACK_IMPORTED_MODULE_9__.registerPriceModal)(context);
})();
})();

/******/ })()
;