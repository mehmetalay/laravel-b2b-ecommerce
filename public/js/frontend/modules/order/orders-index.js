/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/shared/confirm.js":
/*!****************************************!*\
  !*** ./resources/js/shared/confirm.js ***!
  \****************************************/
/***/ (() => {

/* global window */

function customConfirm(message) {
  return new Promise(function (resolve) {
    var overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';
    var box = document.createElement('div');
    box.className = 'confirm-box';
    box.innerHTML = "\n            <p>".concat(message, "</p>\n            <div class=\"confirm-buttons\">\n                <button class=\"yes\" data-selector=\"yes\">Evet</button>\n                <button class=\"no\" data-selector=\"no\">Hay\u0131r</button>\n            </div>\n        ");
    overlay.appendChild(box);
    document.body.appendChild(overlay);
    box.querySelector('[data-selector="yes"]').addEventListener('click', function () {
      overlay.remove();
      resolve(true);
    });
    box.querySelector('[data-selector="no"]').addEventListener('click', function () {
      overlay.remove();
      resolve(false);
    });
  });
}
window.customConfirm = customConfirm;

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
/*!*************************************************************!*\
  !*** ./resources/js/frontend/modules/order/orders-index.js ***!
  \*************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shared_confirm__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../shared/confirm */ "./resources/js/shared/confirm.js");
/* harmony import */ var _shared_confirm__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_shared_confirm__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _shared_loading__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../shared/loading */ "./resources/js/shared/loading.js");
/* harmony import */ var _shared_loading__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_shared_loading__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _shared_notifications__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../shared/notifications */ "./resources/js/shared/notifications.js");
/* harmony import */ var _shared_notifications__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_shared_notifications__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _shared_request_axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../shared/request-axios */ "./resources/js/shared/request-axios.js");




(function initOrdersIndexPage() {
  var pageRoot = document.querySelector('[data-js="orders-index-page"]');
  if (!pageRoot) {
    return;
  }
  var approveConfirmMessage = pageRoot.dataset.approveConfirm || 'Onaylıyor musunuz?';
  var approvedBadgeText = pageRoot.dataset.approvedBadge || 'Onaylandı';
  var genericErrorMessage = pageRoot.dataset.genericError || 'Bir hata oluştu';
  var approveOrder = function approveOrder(button) {
    var _window$setLoading, _window, _window$customConfirm, _window2;
    var orderId = button.dataset.id;
    if (!orderId) {
      return;
    }
    var row = document.getElementById("order-".concat(orderId));
    (_window$setLoading = (_window = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window, button, true);
    (_window$customConfirm = (_window2 = window).customConfirm) === null || _window$customConfirm === void 0 || _window$customConfirm.call(_window2, approveConfirmMessage).then(function (confirmed) {
      if (!confirmed) {
        var _window$setLoading2, _window3;
        (_window$setLoading2 = (_window3 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window3, button, false);
        return;
      }
      if (!window.axiosRequest || typeof window.axiosRequest.post !== 'function') {
        var _window$notify, _window4, _window$setLoading3, _window5;
        (_window$notify = (_window4 = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window4, 'error', genericErrorMessage);
        (_window$setLoading3 = (_window5 = window).setLoading) === null || _window$setLoading3 === void 0 || _window$setLoading3.call(_window5, button, false);
        return;
      }
      window.axiosRequest.post("/orders/".concat(orderId, "/dealer-approve"), {}, {
        onSuccess: function onSuccess(response) {
          var _window$notify3, _window7;
          if (!row) {
            return;
          }
          if ((response === null || response === void 0 ? void 0 : response.status) !== 'success') {
            var _window$notify2, _window6;
            (_window$notify2 = (_window6 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window6, 'error', (response === null || response === void 0 ? void 0 : response.message) || genericErrorMessage);
            return;
          }
          var statusCell = row.querySelector('td:nth-child(5)');
          if (statusCell) {
            statusCell.innerHTML = "<span class=\"badge alert-primary\">".concat(approvedBadgeText, "</span>");
          }
          var approveBtn = row.querySelector('[data-js="approve-order"]');
          if (approveBtn) {
            approveBtn.remove();
          }
          (_window$notify3 = (_window7 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window7, 'success', response.message);
        },
        onValidationError: function onValidationError(errors) {
          var _errors$firstKey, _window$notify4, _window8;
          var firstKey = Object.keys(errors || {})[0];
          var message = firstKey ? (_errors$firstKey = errors[firstKey]) === null || _errors$firstKey === void 0 ? void 0 : _errors$firstKey[0] : null;
          (_window$notify4 = (_window8 = window).notify) === null || _window$notify4 === void 0 || _window$notify4.call(_window8, 'error', message || genericErrorMessage);
        },
        onError: function onError(errorPayload) {
          var _window$notify5, _window9;
          (_window$notify5 = (_window9 = window).notify) === null || _window$notify5 === void 0 || _window$notify5.call(_window9, 'error', (errorPayload === null || errorPayload === void 0 ? void 0 : errorPayload.message) || genericErrorMessage);
        },
        onComplete: function onComplete() {
          var _window$setLoading4, _window0;
          (_window$setLoading4 = (_window0 = window).setLoading) === null || _window$setLoading4 === void 0 || _window$setLoading4.call(_window0, button, false);
        }
      });
    });
  };
  var showOrderModal = function showOrderModal(button) {
    var url = button.dataset.url;
    if (!url || !window.axios) {
      return;
    }
    window.axios.get(url).then(function (response) {
      var modalElement = document.querySelector('.order-show');
      var modalBody = document.querySelector('.order-show .modal-body');
      if (!modalElement || !modalBody || !window.bootstrap) {
        return;
      }
      modalBody.innerHTML = response.data;
      var modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
      modal.show();
    })["catch"](function () {
      var _window$notify6, _window1;
      (_window$notify6 = (_window1 = window).notify) === null || _window$notify6 === void 0 || _window$notify6.call(_window1, 'error', genericErrorMessage);
    });
  };
  document.addEventListener('click', function (event) {
    var showButton = event.target.closest('[data-js="order-show"]');
    if (showButton) {
      event.preventDefault();
      showOrderModal(showButton);
      return;
    }
    var approveButton = event.target.closest('[data-js="approve-order"]');
    if (approveButton) {
      event.preventDefault();
      approveOrder(approveButton);
    }
  });
})();
})();

/******/ })()
;