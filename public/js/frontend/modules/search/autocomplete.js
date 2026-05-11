/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**************************************************************!*\
  !*** ./resources/js/frontend/modules/search/autocomplete.js ***!
  \**************************************************************/
document.addEventListener('DOMContentLoaded', function () {
  var forms = document.querySelectorAll('.js-search-autocomplete');
  forms.forEach(function (form) {
    var input = form.querySelector('.js-search-input');
    var box = form.querySelector('.js-search-suggestion-box');
    var suggestionUrl = form.dataset.suggestionUrl;
    var timer = null;
    input.addEventListener('input', function () {
      var q = input.value.trim();
      clearTimeout(timer);
      if (q.length < 2) {
        box.classList.remove('active');
        box.innerHTML = '';
        return;
      }
      timer = setTimeout(function () {
        axiosRequest.get(suggestionUrl, {
          q: q
        }, {
          onSuccess: function onSuccess(result) {
            renderSuggestions(result.data || [], q);
          }
        });
      }, 300);
    });
    function renderSuggestions(products, q) {
      if (!products.length) {
        box.classList.remove('active');
        box.innerHTML = '';
        return;
      }
      var html = '';
      products.forEach(function (product) {
        html += "\n                    <a href=\"".concat(product.url, "\" class=\"search-suggestion-item\">\n                        <img src=\"").concat(product.image, "\" alt=\"").concat(escapeHtml(product.name), "\">\n                        <div>\n                            <div class=\"search-suggestion-title\">").concat(highlight(product.name, q), "</div>\n                            ").concat(product.sku ? "<div class=\"search-suggestion-sku\">Stok kodu: ".concat(escapeHtml(product.sku), "</div>") : '', "\n                        </div>\n                    </a>\n                ");
      });
      html += "\n                <a href=\"".concat(form.action, "?q=").concat(encodeURIComponent(q), "\" class=\"search-suggestion-all\">\n                    T\xDCM \xDCR\xDCNLER\u0130 G\xD6STER\n                </a>\n            ");
      box.innerHTML = html;
      box.classList.add('active');
    }
    document.addEventListener('click', function (e) {
      if (!form.contains(e.target)) {
        box.classList.remove('active');
      }
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        box.classList.remove('active');
      }
    });
  });
  function escapeHtml(value) {
    return String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
  }
  function highlight(text, keyword) {
    var safeText = escapeHtml(text);
    var safeKeyword = escapeHtml(keyword);
    if (!safeKeyword) return safeText;
    var regex = new RegExp("(".concat(safeKeyword, ")"), 'gi');
    return safeText.replace(regex, '<strong>$1</strong>');
  }
});
/******/ })()
;