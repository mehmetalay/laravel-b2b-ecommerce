/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/backend/modules/homepage-blocks/clear-products.js":
/*!************************************************************************!*\
  !*** ./resources/js/backend/modules/homepage-blocks/clear-products.js ***!
  \************************************************************************/
/***/ (() => {

function getClearAllButton() {
  return document.querySelector('[data-js="clear-all-products"]') || document.getElementById('clear-all-products');
}
function getProductsTableBody() {
  var table = document.querySelector('[data-js="products-table"]') || document.getElementById('products-table');
  return (table === null || table === void 0 ? void 0 : table.querySelector('tbody')) || null;
}
function initHomepageBlockClearProducts() {
  var clearButton = getClearAllButton();
  var productsTableBody = getProductsTableBody();
  if (!clearButton || !productsTableBody) {
    return;
  }
  if (clearButton.dataset.jsBoundClearProducts === '1') {
    return;
  }
  clearButton.dataset.jsBoundClearProducts = '1';
  clearButton.addEventListener('click', function () {
    productsTableBody.innerHTML = '';
    clearButton.style.display = 'none';
  });
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initHomepageBlockClearProducts);
} else {
  initHomepageBlockClearProducts();
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
/*!***************************************************************!*\
  !*** ./resources/js/backend/modules/homepage-blocks/index.js ***!
  \***************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _clear_products__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./clear-products */ "./resources/js/backend/modules/homepage-blocks/clear-products.js");
/* harmony import */ var _clear_products__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_clear_products__WEBPACK_IMPORTED_MODULE_0__);

})();

/******/ })()
;