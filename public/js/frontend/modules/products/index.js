/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************************************!*\
  !*** ./resources/js/frontend/modules/products/index.js ***!
  \*********************************************************/
document.addEventListener('DOMContentLoaded', function () {
  var code = window.location.hash.slice(1);
  if (!code) return;
  var targetElement = document.querySelector("[data-code=\"".concat(code, "\"]"));
  if (!targetElement) return;
  var offset = 250;
  var top = targetElement.getBoundingClientRect().top + window.pageYOffset - offset;
  window.scrollTo({
    top: top,
    behavior: 'smooth'
  });
});
function setFilter(value) {
  var form = document.getElementById('product-filter');
  if (!form) return;
  var hiddenInput = form.querySelector('input[name="sorting"]');
  if (!hiddenInput) {
    hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'sorting';
    form.appendChild(hiddenInput);
  }
  hiddenInput.value = value;
  form.submit();
}
function setViewType(type) {
  var url = new URL(window.location.href);
  url.searchParams.set('view', type);
  window.location.href = url;
}
/******/ })()
;