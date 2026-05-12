/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!***********************************************************!*\
  !*** ./resources/js/backend/modules/import/erp-import.js ***!
  \***********************************************************/
document.addEventListener('click', function (event) {
  var _window$setLoading, _window;
  var button = event.target.closest('[data-action="erp-import"]');
  if (!button) {
    return;
  }
  var url = button.dataset.url;
  if (!url) {
    return;
  }
  (_window$setLoading = (_window = window).setLoading) === null || _window$setLoading === void 0 || _window$setLoading.call(_window, button, true);
  if (!window.axiosRequest || typeof window.axiosRequest.post !== 'function') {
    var _window$notify, _window2, _window$setLoading2, _window3;
    (_window$notify = (_window2 = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window2, 'error', 'Iceri aktarma basarisiz');
    (_window$setLoading2 = (_window3 = window).setLoading) === null || _window$setLoading2 === void 0 || _window$setLoading2.call(_window3, button, false);
    return;
  }
  window.axiosRequest.post(url, {}, {
    onSuccess: function onSuccess(result) {
      var _window$notify2, _window4;
      (_window$notify2 = (_window4 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window4, 'success', result.message || 'Iceri aktarma baslatildi');
    },
    onError: function onError() {
      var _window$notify3, _window5;
      (_window$notify3 = (_window5 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window5, 'error', 'Iceri aktarma basarisiz');
    },
    onComplete: function onComplete() {
      var _window$setLoading3, _window6;
      (_window$setLoading3 = (_window6 = window).setLoading) === null || _window$setLoading3 === void 0 || _window$setLoading3.call(_window6, button, false);
    }
  });
});
/******/ })()
;