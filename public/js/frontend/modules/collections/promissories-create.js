/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/frontend/modules/collections/shared.js":
/*!*************************************************************!*\
  !*** ./resources/js/frontend/modules/collections/shared.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   applyCurrencyNotePrefix: () => (/* binding */ applyCurrencyNotePrefix),
/* harmony export */   firstValidationMessage: () => (/* reexport safe */ _shared_forms_helpers__WEBPACK_IMPORTED_MODULE_3__.firstValidationMessage),
/* harmony export */   formatDateForTR: () => (/* binding */ formatDateForTR),
/* harmony export */   hideModalById: () => (/* binding */ hideModalById),
/* harmony export */   parseTurkishDateToIso: () => (/* binding */ parseTurkishDateToIso),
/* harmony export */   requestPost: () => (/* binding */ requestPost),
/* harmony export */   serializeForm: () => (/* reexport safe */ _shared_forms_helpers__WEBPACK_IMPORTED_MODULE_3__.serializeForm),
/* harmony export */   setButtonLoadingText: () => (/* binding */ setButtonLoadingText),
/* harmony export */   showModalById: () => (/* binding */ showModalById)
/* harmony export */ });
/* harmony import */ var _shared_loading__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../shared/loading */ "./resources/js/shared/loading.js");
/* harmony import */ var _shared_loading__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_shared_loading__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _shared_notifications__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../shared/notifications */ "./resources/js/shared/notifications.js");
/* harmony import */ var _shared_notifications__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_shared_notifications__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _shared_request_axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../shared/request-axios */ "./resources/js/shared/request-axios.js");
/* harmony import */ var _shared_forms_helpers__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../shared/forms/helpers */ "./resources/js/shared/forms/helpers.js");
/* harmony import */ var _shared_ui_modal__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../../shared/ui/modal */ "./resources/js/shared/ui/modal.js");
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }





function setButtonLoadingText(button, text) {
  if (!button) {
    return function () {};
  }
  var originalHtml = button.innerHTML;
  button.innerHTML = "<span class=\"spinner-border spinner-border-sm me-2\" role=\"status\" aria-hidden=\"true\"></span> ".concat(text);
  return function () {
    button.innerHTML = originalHtml;
  };
}
function showModalById(modalId) {
  (0,_shared_ui_modal__WEBPACK_IMPORTED_MODULE_4__.showModalPrimitive)(modalId, {
    resolveBy: function resolveBy(value) {
      return document.getElementById(value);
    }
  });
}
function hideModalById(modalId) {
  (0,_shared_ui_modal__WEBPACK_IMPORTED_MODULE_4__.hideModalPrimitive)(modalId, {
    resolveBy: function resolveBy(value) {
      return document.getElementById(value);
    }
  });
}
function appendFormData(formData, key, value) {
  if (value === undefined || value === null) {
    formData.append(key, '');
    return;
  }
  if (Array.isArray(value)) {
    value.forEach(function (item, index) {
      appendFormData(formData, "".concat(key, "[").concat(index, "]"), item);
    });
    return;
  }
  if (_typeof(value) === 'object' && !(value instanceof File)) {
    Object.keys(value).forEach(function (childKey) {
      appendFormData(formData, "".concat(key, "[").concat(childKey, "]"), value[childKey]);
    });
    return;
  }
  formData.append(key, value);
}
function toFormData(data) {
  var formData = new FormData();
  if (!data || _typeof(data) !== 'object') {
    return formData;
  }
  Object.keys(data).forEach(function (key) {
    appendFormData(formData, key, data[key]);
  });
  return formData;
}
function resolvePostTransport() {
  var _window$axiosRequest;
  var rawPost = (_window$axiosRequest = window.axiosRequest) === null || _window$axiosRequest === void 0 ? void 0 : _window$axiosRequest.rawPost;
  if (typeof rawPost === 'function') {
    return function (url, payload) {
      return rawPost.call(window.axiosRequest, url, payload);
    };
  }
  if (window.axios && typeof window.axios.post === 'function') {
    return function (url, payload) {
      return window.axios.post(url, payload);
    };
  }
  return null;
}
function extractResponsePayload(response) {
  if (response && _typeof(response) === 'object' && 'data' in response) {
    return response.data || {};
  }
  return response || {};
}
function extractValidationErrors(error) {
  var _response$data;
  var response = error === null || error === void 0 ? void 0 : error.response;
  if ((response === null || response === void 0 ? void 0 : response.status) === 422 && (_response$data = response.data) !== null && _response$data !== void 0 && _response$data.errors && _typeof(response.data.errors) === 'object') {
    return response.data.errors;
  }
  return null;
}
function extractErrorPayload(error) {
  var _error$response;
  return (error === null || error === void 0 || (_error$response = error.response) === null || _error$response === void 0 ? void 0 : _error$response.data) || error;
}
function requestPost(url, data) {
  var _ref = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {},
    onSuccess = _ref.onSuccess,
    onError = _ref.onError,
    onValidationError = _ref.onValidationError,
    onComplete = _ref.onComplete;
  var postTransport = resolvePostTransport();
  if (!postTransport) {
    onError === null || onError === void 0 || onError({
      message: 'Request helper is not available.'
    });
    onComplete === null || onComplete === void 0 || onComplete();
    return;
  }
  postTransport(url, toFormData(data)).then(function (response) {
    onSuccess === null || onSuccess === void 0 || onSuccess(extractResponsePayload(response));
  })["catch"](function (error) {
    var validationErrors = extractValidationErrors(error);
    if (validationErrors) {
      onValidationError === null || onValidationError === void 0 || onValidationError(validationErrors);
      return;
    }
    onError === null || onError === void 0 || onError(extractErrorPayload(error));
  })["finally"](function () {
    onComplete === null || onComplete === void 0 || onComplete();
  });
}
function parseTurkishDateToIso(dateText) {
  var _String$split = String(dateText).split('.'),
    _String$split2 = _slicedToArray(_String$split, 3),
    day = _String$split2[0],
    month = _String$split2[1],
    year = _String$split2[2];
  if (!day || !month || !year) {
    return '';
  }
  var date = new Date("".concat(year, "-").concat(month, "-").concat(day));
  if (Number.isNaN(date.getTime())) {
    return '';
  }
  return date.toISOString().split('T')[0];
}
function formatDateForTR(dateValue) {
  var date = new Date(dateValue);
  if (Number.isNaN(date.getTime())) {
    return '';
  }
  return date.toLocaleDateString('tr-TR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
}
function applyCurrencyNotePrefix(_ref2) {
  var selectElement = _ref2.selectElement,
    notesElement = _ref2.notesElement,
    notePrefix = _ref2.notePrefix;
  var selectedCurrency = selectElement === null || selectElement === void 0 ? void 0 : selectElement.value;
  if (!selectedCurrency || !notesElement) {
    return;
  }
  var notesText = notesElement.value.split('.').filter(function (line) {
    return !line.startsWith(notePrefix);
  });
  var newNote = "".concat(notePrefix, " ").concat(selectedCurrency, " Tahsilat");
  notesElement.value = [newNote].concat(_toConsumableArray(notesText)).join('.').trim();
}


/***/ }),

/***/ "./resources/js/shared/forms/helpers.js":
/*!**********************************************!*\
  !*** ./resources/js/shared/forms/helpers.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   firstValidationMessage: () => (/* binding */ firstValidationMessage),
/* harmony export */   serializeForm: () => (/* binding */ serializeForm)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
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
function serializeForm(form) {
  return Object.fromEntries(new FormData(form).entries());
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

/***/ }),

/***/ "./resources/js/shared/ui/modal.js":
/*!*****************************************!*\
  !*** ./resources/js/shared/ui/modal.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   hideModalPrimitive: () => (/* binding */ hideModalPrimitive),
/* harmony export */   resolveModalElement: () => (/* binding */ resolveModalElement),
/* harmony export */   showModalPrimitive: () => (/* binding */ showModalPrimitive)
/* harmony export */ });
function resolveModalElement(target) {
  var _ref = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
    resolveBy = _ref.resolveBy;
  if (target instanceof HTMLElement) {
    return target;
  }
  if (typeof target !== 'string') {
    return null;
  }
  if (typeof resolveBy === 'function') {
    return resolveBy(target);
  }
  return document.querySelector(target);
}
function showModalPrimitive(target, options) {
  var _window$$$fn;
  var modalElement = resolveModalElement(target, options);
  if (!modalElement) {
    return;
  }
  var modalController = resolveBootstrapModalController(modalElement);
  if (modalController && typeof modalController.show === 'function') {
    modalController.show();
    return;
  }
  if (window.$ && typeof ((_window$$$fn = window.$.fn) === null || _window$$$fn === void 0 ? void 0 : _window$$$fn.modal) === 'function') {
    window.$(modalElement).modal('show');
  }
}
function hideModalPrimitive(target, options) {
  var _window$$$fn2;
  var modalElement = resolveModalElement(target, options);
  if (!modalElement) {
    return;
  }
  var modalController = resolveBootstrapModalController(modalElement);
  if (modalController && typeof modalController.hide === 'function') {
    modalController.hide();
    return;
  }
  if (window.$ && typeof ((_window$$$fn2 = window.$.fn) === null || _window$$$fn2 === void 0 ? void 0 : _window$$$fn2.modal) === 'function') {
    window.$(modalElement).modal('hide');
  }
}
function resolveBootstrapModalController(modalElement) {
  var _window$bootstrap;
  var Modal = (_window$bootstrap = window.bootstrap) === null || _window$bootstrap === void 0 ? void 0 : _window$bootstrap.Modal;
  if (!Modal) {
    return null;
  }
  if (typeof Modal.getOrCreateInstance === 'function') {
    return Modal.getOrCreateInstance(modalElement);
  }
  if (typeof Modal === 'function') {
    return new Modal(modalElement);
  }
  return null;
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
/*!**************************************************************************!*\
  !*** ./resources/js/frontend/modules/collections/promissories-create.js ***!
  \**************************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./shared */ "./resources/js/frontend/modules/collections/shared.js");
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

(function initPromissoriesCreatePage() {
  var _modalFields$clio_typ2, _modalFields$currency2;
  var config = document.querySelector('[data-js="promissories-create-config"]');
  if (!config) {
    return;
  }
  var createTitle = config.getAttribute('data-create-title') || 'Senet oluştur';
  var editTitle = config.getAttribute('data-edit-title') || 'Senet düzenle';
  var requiredFieldsMessage = config.getAttribute('data-required-fields-message') || 'Lütfen gerekli alanları doldurunuz.';
  var processingText = config.getAttribute('data-processing-text') || 'İşleminiz yapılıyor, lütfen bekleyin';
  var requestErrorMessage = config.getAttribute('data-request-error') || 'İstek sırasında bir hata oluştu.';
  var saveUrl = config.getAttribute('data-save-url') || '';
  var currentAccountName = config.getAttribute('data-current-account-name') || '';
  var notePrefix = config.getAttribute('data-note-prefix') || 'Senet';
  var notifyCollectionValidationError = function notifyCollectionValidationError(errors) {
    var _window$notify, _window;
    (_window$notify = (_window = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window, 'error', (0,_shared__WEBPACK_IMPORTED_MODULE_0__.firstValidationMessage)(errors) || requestErrorMessage);
  };
  var notifyCollectionRequestError = function notifyCollectionRequestError(errorPayload) {
    var _window$notify2, _window2;
    (_window$notify2 = (_window2 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window2, 'error', (errorPayload === null || errorPayload === void 0 ? void 0 : errorPayload.warning) || (errorPayload === null || errorPayload === void 0 ? void 0 : errorPayload.error) || (errorPayload === null || errorPayload === void 0 ? void 0 : errorPayload.message) || requestErrorMessage);
  };
  var notifyCollectionWarning = function notifyCollectionWarning(payload, fallbackMessage) {
    var _window$notify3, _window3;
    (_window$notify3 = (_window3 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window3, payload !== null && payload !== void 0 && payload.warning ? 'warning' : 'error', (payload === null || payload === void 0 ? void 0 : payload.warning) || (payload === null || payload === void 0 ? void 0 : payload.error) || fallbackMessage);
  };
  var tableBody = document.querySelector('#promissory-table tbody');
  var saveButtonContainer = document.getElementById('save-button');
  var saveButton = document.querySelector('[data-js="save-button"]');
  var modalForm = document.getElementById('promissory-modal-form');
  var modalButton = document.getElementById('promissory-modal-button');
  var notesField = document.getElementById('notes');
  var collectionDateField = document.getElementById('collection_date');
  var modalLabel = document.getElementById('promissory-modal-label');
  var pieceInput = document.getElementById('piece');
  var maturityDayInput = document.getElementById('maturity_day');
  var modalFields = {
    serial_number: document.getElementById('serial_number'),
    maturity_date: document.getElementById('maturity_date'),
    clio_type: document.getElementById('clio_type'),
    debtor: document.getElementById('debtor'),
    amount: document.getElementById('amount'),
    currency_type: document.getElementById('currency_type')
  };
  if (!tableBody || !saveButton || !modalForm || !modalButton) {
    return;
  }
  var getPieceRow = function getPieceRow() {
    return pieceInput === null || pieceInput === void 0 ? void 0 : pieceInput.closest('.row');
  };
  var rowCellsToPayload = function rowCellsToPayload(row) {
    var _cells$, _cells$2, _cells$3, _cells$4, _cells$5, _cells$6;
    var cells = row.querySelectorAll('td');
    return {
      serial_number: ((_cells$ = cells[0]) === null || _cells$ === void 0 || (_cells$ = _cells$.textContent) === null || _cells$ === void 0 ? void 0 : _cells$.trim()) || '',
      maturity_date: (0,_shared__WEBPACK_IMPORTED_MODULE_0__.parseTurkishDateToIso)(((_cells$2 = cells[1]) === null || _cells$2 === void 0 || (_cells$2 = _cells$2.textContent) === null || _cells$2 === void 0 ? void 0 : _cells$2.trim()) || ''),
      clio_type: ((_cells$3 = cells[2]) === null || _cells$3 === void 0 || (_cells$3 = _cells$3.textContent) === null || _cells$3 === void 0 ? void 0 : _cells$3.trim()) || '',
      debtor: ((_cells$4 = cells[3]) === null || _cells$4 === void 0 || (_cells$4 = _cells$4.textContent) === null || _cells$4 === void 0 ? void 0 : _cells$4.trim()) || '',
      amount: ((_cells$5 = cells[4]) === null || _cells$5 === void 0 || (_cells$5 = _cells$5.textContent) === null || _cells$5 === void 0 ? void 0 : _cells$5.trim()) || '',
      currency_type: ((_cells$6 = cells[5]) === null || _cells$6 === void 0 || (_cells$6 = _cells$6.textContent) === null || _cells$6 === void 0 ? void 0 : _cells$6.trim()) || ''
    };
  };
  var getModalValues = function getModalValues() {
    var _modalFields$serial_n, _modalFields$maturity, _modalFields$clio_typ, _modalFields$debtor, _modalFields$amount, _modalFields$currency;
    return {
      serialNumber: ((_modalFields$serial_n = modalFields.serial_number) === null || _modalFields$serial_n === void 0 || (_modalFields$serial_n = _modalFields$serial_n.value) === null || _modalFields$serial_n === void 0 ? void 0 : _modalFields$serial_n.trim()) || '',
      maturityDate: ((_modalFields$maturity = modalFields.maturity_date) === null || _modalFields$maturity === void 0 ? void 0 : _modalFields$maturity.value) || '',
      clioType: ((_modalFields$clio_typ = modalFields.clio_type) === null || _modalFields$clio_typ === void 0 || (_modalFields$clio_typ = _modalFields$clio_typ.value) === null || _modalFields$clio_typ === void 0 ? void 0 : _modalFields$clio_typ.trim()) || '',
      debtor: ((_modalFields$debtor = modalFields.debtor) === null || _modalFields$debtor === void 0 || (_modalFields$debtor = _modalFields$debtor.value) === null || _modalFields$debtor === void 0 ? void 0 : _modalFields$debtor.trim()) || '',
      amount: ((_modalFields$amount = modalFields.amount) === null || _modalFields$amount === void 0 || (_modalFields$amount = _modalFields$amount.value) === null || _modalFields$amount === void 0 ? void 0 : _modalFields$amount.trim()) || '',
      currencyType: ((_modalFields$currency = modalFields.currency_type) === null || _modalFields$currency === void 0 || (_modalFields$currency = _modalFields$currency.value) === null || _modalFields$currency === void 0 ? void 0 : _modalFields$currency.trim()) || ''
    };
  };
  var renderRow = function renderRow(values, maturityDate) {
    return "<tr>\n        <td>".concat(values.serialNumber, "</td>\n        <td>").concat(maturityDate, "</td>\n        <td>").concat(values.clioType, "</td>\n        <td>").concat(values.debtor, "</td>\n        <td>").concat(values.amount, "</td>\n        <td>").concat(values.currencyType, "</td>\n        <td>\n            <a href=\"javascript:;\" data-js=\"edit-row\"><span class=\"badge alert-info\"><i class=\"fa-solid fa-pencil\"></i></span></a>\n            <a href=\"javascript:;\" data-js=\"delete-row\"><span class=\"badge alert-danger\"><i class=\"fa-solid fa-trash\"></i></span></a>\n        </td>\n    </tr>");
  };
  var resetModal = function resetModal() {
    modalForm.reset();
    var editingRow = tableBody.querySelector('.editing-row');
    editingRow === null || editingRow === void 0 || editingRow.classList.remove('editing-row');
  };
  var setDebtorByClioType = function setDebtorByClioType() {
    if (!modalFields.clio_type || !modalFields.debtor) {
      return;
    }
    if (modalFields.clio_type.value === 'Kendisi') {
      modalFields.debtor.value = currentAccountName;
      modalFields.debtor.disabled = true;
      return;
    }
    modalFields.debtor.value = '';
    modalFields.debtor.disabled = false;
  };
  document.addEventListener('click', function (event) {
    var createTrigger = event.target.closest('[data-js="promissory-modal"]');
    if (createTrigger) {
      event.preventDefault();
      modalButton.setAttribute('data-type', 'add');
      modalLabel.textContent = createTitle;
      var pieceRow = getPieceRow();
      if (pieceRow) {
        pieceRow.style.display = '';
      }
      resetModal();
      setDebtorByClioType();
      (0,_shared__WEBPACK_IMPORTED_MODULE_0__.showModalById)('promissory-modal');
      return;
    }
    var editTrigger = event.target.closest('[data-js="edit-row"]');
    if (editTrigger) {
      var _cells$7, _cells$8, _cells$9, _cells$0, _cells$1, _cells$10, _tableBody$querySelec;
      event.preventDefault();
      var row = editTrigger.closest('tr');
      if (!row) {
        return;
      }
      var cells = row.querySelectorAll('td');
      var maturityDateText = ((_cells$7 = cells[1]) === null || _cells$7 === void 0 || (_cells$7 = _cells$7.textContent) === null || _cells$7 === void 0 ? void 0 : _cells$7.trim()) || '';
      var _maturityDateText$spl = maturityDateText.split('.'),
        _maturityDateText$spl2 = _slicedToArray(_maturityDateText$spl, 3),
        day = _maturityDateText$spl2[0],
        month = _maturityDateText$spl2[1],
        year = _maturityDateText$spl2[2];
      var formattedMaturityDate = day && month && year ? "".concat(year, "-").concat(month, "-").concat(day) : '';
      modalFields.serial_number.value = ((_cells$8 = cells[0]) === null || _cells$8 === void 0 || (_cells$8 = _cells$8.textContent) === null || _cells$8 === void 0 ? void 0 : _cells$8.trim()) || '';
      modalFields.maturity_date.value = formattedMaturityDate;
      modalFields.clio_type.value = ((_cells$9 = cells[2]) === null || _cells$9 === void 0 || (_cells$9 = _cells$9.textContent) === null || _cells$9 === void 0 ? void 0 : _cells$9.trim()) || '';
      modalFields.debtor.value = ((_cells$0 = cells[3]) === null || _cells$0 === void 0 || (_cells$0 = _cells$0.textContent) === null || _cells$0 === void 0 ? void 0 : _cells$0.trim()) || '';
      modalFields.amount.value = ((_cells$1 = cells[4]) === null || _cells$1 === void 0 || (_cells$1 = _cells$1.textContent) === null || _cells$1 === void 0 ? void 0 : _cells$1.trim()) || '';
      modalFields.currency_type.value = ((_cells$10 = cells[5]) === null || _cells$10 === void 0 || (_cells$10 = _cells$10.textContent) === null || _cells$10 === void 0 ? void 0 : _cells$10.trim()) || '';
      (_tableBody$querySelec = tableBody.querySelector('.editing-row')) === null || _tableBody$querySelec === void 0 || _tableBody$querySelec.classList.remove('editing-row');
      row.classList.add('editing-row');
      modalButton.setAttribute('data-type', 'update');
      modalLabel.textContent = editTitle;
      var _pieceRow = getPieceRow();
      if (_pieceRow) {
        _pieceRow.style.display = 'none';
      }
      (0,_shared__WEBPACK_IMPORTED_MODULE_0__.showModalById)('promissory-modal');
      setDebtorByClioType();
      return;
    }
    var deleteTrigger = event.target.closest('[data-js="delete-row"]');
    if (deleteTrigger) {
      var _deleteTrigger$closes;
      event.preventDefault();
      (_deleteTrigger$closes = deleteTrigger.closest('tr')) === null || _deleteTrigger$closes === void 0 || _deleteTrigger$closes.remove();
      if (!tableBody.querySelector('tr') && saveButtonContainer) {
        saveButtonContainer.style.display = 'none';
      }
    }
  });
  modalButton.addEventListener('click', function (event) {
    event.preventDefault();
    var action = modalButton.getAttribute('data-type');
    var values = getModalValues();
    if (action === 'add') {
      if (!values.serialNumber || !values.maturityDate || !values.clioType || !values.debtor || !values.amount || !values.currencyType) {
        var _window$notify4, _window4;
        (_window$notify4 = (_window4 = window).notify) === null || _window$notify4 === void 0 || _window$notify4.call(_window4, 'warning', requiredFieldsMessage);
        return;
      }
      var piece = Number.parseInt((pieceInput === null || pieceInput === void 0 ? void 0 : pieceInput.value) || '1', 10) || 1;
      var maturityDay = Number.parseInt((maturityDayInput === null || maturityDayInput === void 0 ? void 0 : maturityDayInput.value) || '1', 10) || 1;
      var baseMaturityDate = new Date(values.maturityDate);
      for (var i = 0; i < piece; i += 1) {
        var clonedMaturityDate = new Date(baseMaturityDate);
        if (i > 0) {
          clonedMaturityDate.setMonth(clonedMaturityDate.getMonth() + i);
          clonedMaturityDate.setDate(maturityDay);
          if (clonedMaturityDate.getDate() !== maturityDay) {
            clonedMaturityDate.setDate(0);
          }
        }
        var maturityDate = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.formatDateForTR)(clonedMaturityDate);
        tableBody.insertAdjacentHTML('beforeend', renderRow(values, maturityDate));
      }
      if (saveButtonContainer) {
        saveButtonContainer.style.display = '';
      }
    } else if (action === 'update') {
      var row = tableBody.querySelector('.editing-row');
      if (!row) {
        return;
      }
      var cells = row.querySelectorAll('td');
      cells[0].textContent = values.serialNumber;
      cells[1].textContent = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.formatDateForTR)(values.maturityDate);
      cells[2].textContent = values.clioType;
      cells[3].textContent = values.debtor;
      cells[4].textContent = values.amount;
      cells[5].textContent = values.currencyType;
      row.classList.remove('editing-row');
    }
    (0,_shared__WEBPACK_IMPORTED_MODULE_0__.hideModalById)('promissory-modal');
    resetModal();
  });
  var modalElement = document.getElementById('promissory-modal');
  modalElement === null || modalElement === void 0 || modalElement.addEventListener('hidden.bs.modal', function () {
    var _tableBody$querySelec2;
    (_tableBody$querySelec2 = tableBody.querySelector('.editing-row')) === null || _tableBody$querySelec2 === void 0 || _tableBody$querySelec2.classList.remove('editing-row');
  });
  (_modalFields$clio_typ2 = modalFields.clio_type) === null || _modalFields$clio_typ2 === void 0 || _modalFields$clio_typ2.addEventListener('change', setDebtorByClioType);
  (_modalFields$currency2 = modalFields.currency_type) === null || _modalFields$currency2 === void 0 || _modalFields$currency2.addEventListener('change', function () {
    (0,_shared__WEBPACK_IMPORTED_MODULE_0__.applyCurrencyNotePrefix)({
      selectElement: modalFields.currency_type,
      notesElement: notesField,
      notePrefix: notePrefix
    });
  });
  saveButton.addEventListener('click', function (event) {
    var _window$setLoading, _window5, _document$querySelect;
    event.preventDefault();
    var restoreButtonText = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.setButtonLoadingText)(saveButton, processingText);
    (_window$setLoading = (_window5 = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window5, saveButton, true);
    var promissories = Array.from(tableBody.querySelectorAll('tr')).map(function (row) {
      return rowCellsToPayload(row);
    });
    var csrfToken = ((_document$querySelect = document.querySelector('input[name="_token"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.value) || '';
    (0,_shared__WEBPACK_IMPORTED_MODULE_0__.requestPost)(saveUrl, {
      collection_date: (collectionDateField === null || collectionDateField === void 0 ? void 0 : collectionDateField.value) || '',
      notes: (notesField === null || notesField === void 0 ? void 0 : notesField.value) || '',
      promissories: promissories,
      _token: csrfToken
    }, {
      onSuccess: function onSuccess(response) {
        var _window$setLoading2, _window6;
        if (response.success) {
          tableBody.innerHTML = '';
          if (collectionDateField) {
            collectionDateField.value = '';
          }
          if (notesField) {
            notesField.value = '';
          }
          window.location.href = response.href;
          return;
        }
        restoreButtonText();
        (_window$setLoading2 = (_window6 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window6, saveButton, false);
        notifyCollectionWarning(response);
      },
      onValidationError: function onValidationError(errors) {
        var _window$setLoading3, _window7;
        restoreButtonText();
        (_window$setLoading3 = (_window7 = window).setLoading) === null || _window$setLoading3 === void 0 || _window$setLoading3.call(_window7, saveButton, false);
        notifyCollectionValidationError(errors);
      },
      onError: function onError(errorPayload) {
        var _window$setLoading4, _window8;
        restoreButtonText();
        (_window$setLoading4 = (_window8 = window).setLoading) === null || _window$setLoading4 === void 0 || _window$setLoading4.call(_window8, saveButton, false);
        notifyCollectionRequestError(errorPayload);
      }
    });
  });
})();
})();

/******/ })()
;