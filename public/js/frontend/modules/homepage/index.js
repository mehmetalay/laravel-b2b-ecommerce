/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************************************!*\
  !*** ./resources/js/frontend/modules/homepage/index.js ***!
  \*********************************************************/
/* global axiosRequest, window */

var Homepage = {
  init: function init() {
    var _this = this;
    var sections = this.detectSections();
    if (!sections.length) {
      return;
    }
    axiosRequest.post('/ajax/homepage', {
      sections: sections
    }, {
      onSuccess: function onSuccess(response) {
        if (sections.includes('sliders')) {
          document.getElementById('hp-sliders').innerHTML = response.sliders || '';
        }
        if (sections.includes('payments')) {
          document.getElementById('hp-payments').innerHTML = response.payments || '';
        }
        if (sections.includes('categories')) {
          document.getElementById('hp-categories').innerHTML = response.categories || '';
        }
        if (sections.includes('campaigns')) {
          document.getElementById('hp-campaigns').innerHTML = response.campaigns || '';
        }
        if (sections.includes('brands')) {
          document.getElementById('hp-brands').innerHTML = response.brands || '';
        }
        if (sections.includes('blocks')) {
          document.getElementById('hp-blocks').innerHTML = response.blocks || '';
        }
        _this.initSliders();
      }
    });
  },
  detectSections: function detectSections() {
    return Array.from(document.querySelectorAll('[data-hp]')).map(function (element) {
      return element.dataset.hp;
    });
  },
  initSliders: function initSliders() {
    if (document.querySelector('.theme-slider')) {
      var _window$initGeneralSl, _window;
      (_window$initGeneralSl = (_window = window).initGeneralSliders) === null || _window$initGeneralSl === void 0 || _window$initGeneralSl.call(_window);
    }
    if (document.querySelector('.category-slider')) {
      var _window$initCategoryS, _window2;
      (_window$initCategoryS = (_window2 = window).initCategorySliders) === null || _window$initCategoryS === void 0 || _window$initCategoryS.call(_window2);
    }
    if (document.querySelector('.campaign-slider')) {
      var _window$initCampaignS, _window3;
      (_window$initCampaignS = (_window3 = window).initCampaignSliders) === null || _window$initCampaignS === void 0 || _window$initCampaignS.call(_window3);
    }
    if (document.querySelector('.brand-slider')) {
      var _window$initBrandSlid, _window4;
      (_window$initBrandSlid = (_window4 = window).initBrandSliders) === null || _window$initBrandSlid === void 0 || _window$initBrandSlid.call(_window4);
    }
    if (document.querySelector('.product-box-slider')) {
      var _window$initProductSl, _window5;
      (_window$initProductSl = (_window5 = window).initProductSliders) === null || _window$initProductSl === void 0 || _window$initProductSl.call(_window5);
    }
  }
};
window.Homepage = Homepage;
/******/ })()
;