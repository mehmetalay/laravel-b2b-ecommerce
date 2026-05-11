/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/admin/modules/payments/payment-link-installments.js":
/*!**************************************************************************!*\
  !*** ./resources/js/admin/modules/payments/payment-link-installments.js ***!
  \**************************************************************************/
/***/ (() => {

function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function getManualBankIntegrationSelect() {
  return document.querySelector('[data-js="manual-bank-integration"]') || document.getElementById('manual_bank_integration_id');
}
function getManualInstallmentSelect() {
  return document.querySelector('[data-js="manual-installment"]') || document.getElementById('manual_installment');
}
function setDisabledPlaceholder(select, text) {
  select.innerHTML = "<option value=\"\" selected hidden>".concat(text, "</option>");
  select.disabled = true;
}
function setBaseOptions(select) {
  select.innerHTML = '<option value="" selected hidden>SEÇ</option>';
}
function getSelectedInstallmentValue(installmentSelect) {
  return installmentSelect.dataset.selectedManualInstallment || '';
}
function loadInstallments(_x, _x2) {
  return _loadInstallments.apply(this, arguments);
}
function _loadInstallments() {
  _loadInstallments = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(bankIntegrationId, installmentSelect) {
    var response, installments, selectedManualInstallment, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          if (bankIntegrationId) {
            _context.n = 1;
            break;
          }
          setDisabledPlaceholder(installmentSelect, 'SEÇ');
          return _context.a(2);
        case 1:
          setDisabledPlaceholder(installmentSelect, 'Yükleniyor...');
          if (!(!window.axiosRequest || typeof window.axiosRequest.rawGet !== 'function')) {
            _context.n = 2;
            break;
          }
          setDisabledPlaceholder(installmentSelect, 'Taksit bulunamadı');
          return _context.a(2);
        case 2:
          _context.p = 2;
          _context.n = 3;
          return window.axiosRequest.rawGet("/ajax/bank-integrations/".concat(bankIntegrationId, "/installments"));
        case 3:
          response = _context.v;
          installments = Array.isArray(response === null || response === void 0 ? void 0 : response.data) ? response.data : [];
          setBaseOptions(installmentSelect);
          if (!(installments.length === 0)) {
            _context.n = 4;
            break;
          }
          setDisabledPlaceholder(installmentSelect, 'Taksit bulunamadı');
          return _context.a(2);
        case 4:
          selectedManualInstallment = getSelectedInstallmentValue(installmentSelect);
          installments.forEach(function (installmentItem) {
            var _installmentItem$inst;
            var option = document.createElement('option');
            var installmentValue = String((_installmentItem$inst = installmentItem === null || installmentItem === void 0 ? void 0 : installmentItem.installment) !== null && _installmentItem$inst !== void 0 ? _installmentItem$inst : '');
            option.value = installmentValue;
            option.textContent = installmentValue;
            if (installmentValue === String(selectedManualInstallment)) {
              option.selected = true;
            }
            installmentSelect.appendChild(option);
          });
          installmentSelect.disabled = false;
          _context.n = 6;
          break;
        case 5:
          _context.p = 5;
          _t = _context.v;
          setDisabledPlaceholder(installmentSelect, 'Taksit bulunamadı');
          if (typeof window.notify === 'function') {
            window.notify('error', 'Taksitler yüklenemedi.');
          }
        case 6:
          return _context.a(2);
      }
    }, _callee, null, [[2, 5]]);
  }));
  return _loadInstallments.apply(this, arguments);
}
function initPaymentLinkInstallments() {
  var bankIntegrationSelect = getManualBankIntegrationSelect();
  var installmentSelect = getManualInstallmentSelect();
  if (!bankIntegrationSelect || !installmentSelect) {
    return;
  }
  if (bankIntegrationSelect.dataset.jsBoundManualInstallments === '1') {
    return;
  }
  bankIntegrationSelect.dataset.jsBoundManualInstallments = '1';
  var syncInstallments = function syncInstallments() {
    loadInstallments(bankIntegrationSelect.value, installmentSelect);
  };
  syncInstallments();
  bankIntegrationSelect.addEventListener('change', syncInstallments);
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPaymentLinkInstallments);
} else {
  initPaymentLinkInstallments();
}

/***/ }),

/***/ "./resources/js/admin/modules/payments/payment-link-transaction-toggle.js":
/*!********************************************************************************!*\
  !*** ./resources/js/admin/modules/payments/payment-link-transaction-toggle.js ***!
  \********************************************************************************/
/***/ (() => {

function getTransactionTypeSelect() {
  return document.querySelector('[data-js="transaction-type"]') || document.getElementById('transaction_type');
}
function getManualOptions() {
  return document.querySelector('[data-js="manual-options"]') || document.getElementById('manual-options');
}
function initPaymentLinkTransactionToggle() {
  var transactionTypeSelect = getTransactionTypeSelect();
  var manualOptions = getManualOptions();
  if (!transactionTypeSelect || !manualOptions) {
    return;
  }
  if (transactionTypeSelect.dataset.jsBoundTransactionToggle === '1') {
    return;
  }
  transactionTypeSelect.dataset.jsBoundTransactionToggle = '1';
  var syncVisibility = function syncVisibility() {
    manualOptions.style.display = transactionTypeSelect.value === '3' ? '' : 'none';
  };
  syncVisibility();
  transactionTypeSelect.addEventListener('change', syncVisibility);
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPaymentLinkTransactionToggle);
} else {
  initPaymentLinkTransactionToggle();
}

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
/*!******************************************************!*\
  !*** ./resources/js/admin/modules/payments/index.js ***!
  \******************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _payment_link_transaction_toggle__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./payment-link-transaction-toggle */ "./resources/js/admin/modules/payments/payment-link-transaction-toggle.js");
/* harmony import */ var _payment_link_transaction_toggle__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_payment_link_transaction_toggle__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _payment_link_installments__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./payment-link-installments */ "./resources/js/admin/modules/payments/payment-link-installments.js");
/* harmony import */ var _payment_link_installments__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_payment_link_installments__WEBPACK_IMPORTED_MODULE_1__);


})();

/******/ })()
;