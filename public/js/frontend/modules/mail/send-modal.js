/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**********************************************************!*\
  !*** ./resources/js/frontend/modules/mail/send-modal.js ***!
  \**********************************************************/
document.addEventListener('click', function (event) {
  var _form$querySelector, _form$querySelector2, _form$querySelector3, _window$axiosRequest;
  var trigger = event.target.closest("[data-modal-trigger='send-b2b-mail']");
  if (trigger) {
    var _window$bootstrap;
    event.preventDefault();
    var dealerEmail = trigger.dataset.dealerEmail || '';
    var _mailType = trigger.dataset.mailType;
    var _refId = trigger.dataset.refId;
    var _modal = document.querySelector("[data-modal='send-b2b-mail']");
    if (!_modal) {
      return;
    }
    var recipientEmailInput = _modal.querySelector('[data-recipient-email]');
    var mailTypeInput = _modal.querySelector('[data-mail-type]');
    var refIdInput = _modal.querySelector('[data-ref-id]');
    var modalTitle = _modal.querySelector('[data-modal-title]');
    if (recipientEmailInput) {
      recipientEmailInput.value = dealerEmail;
    }
    if (mailTypeInput) {
      mailTypeInput.value = _mailType || '';
    }
    if (refIdInput) {
      refIdInput.value = _refId || '';
    }
    var titleMap = {
      order: 'Sipariş Maili Gönder',
      payment: 'Ödeme Dekontu Gönder',
      statement: 'Müşteri Ekstresi Gönder'
    };
    if (modalTitle) {
      modalTitle.textContent = titleMap[_mailType] || 'Mail Gönder';
    }
    if ((_window$bootstrap = window.bootstrap) !== null && _window$bootstrap !== void 0 && _window$bootstrap.Modal) {
      new window.bootstrap.Modal(_modal).show();
    }
    return;
  }
  var sendBtn = event.target.closest('[data-send-mail]');
  if (!sendBtn) {
    return;
  }
  var modal = event.target.closest('[data-modal]');
  var form = modal === null || modal === void 0 ? void 0 : modal.querySelector('[data-mail-form]');
  if (!modal || !form) {
    return;
  }
  var recipientEmail = ((_form$querySelector = form.querySelector('[data-recipient-email]')) === null || _form$querySelector === void 0 ? void 0 : _form$querySelector.value) || '';
  var mailType = ((_form$querySelector2 = form.querySelector('[data-mail-type]')) === null || _form$querySelector2 === void 0 ? void 0 : _form$querySelector2.value) || '';
  var refId = ((_form$querySelector3 = form.querySelector('[data-ref-id]')) === null || _form$querySelector3 === void 0 ? void 0 : _form$querySelector3.value) || '';
  sendBtn.disabled = true;
  var oldText = sendBtn.textContent;
  sendBtn.textContent = 'Gönderiliyor...';
  (_window$axiosRequest = window.axiosRequest) === null || _window$axiosRequest === void 0 || _window$axiosRequest.post('/mail/send', {
    recipient_email: recipientEmail,
    type: mailType,
    ref_id: refId
  }, {
    onSuccess: function onSuccess(result) {
      var _window$notify, _window, _window$bootstrap2;
      (_window$notify = (_window = window).notify) === null || _window$notify === void 0 || _window$notify.call(_window, 'success', (result === null || result === void 0 ? void 0 : result.message) || 'Mail gönderildi.');
      (_window$bootstrap2 = window.bootstrap) === null || _window$bootstrap2 === void 0 || (_window$bootstrap2 = _window$bootstrap2.Modal.getInstance(modal)) === null || _window$bootstrap2 === void 0 || _window$bootstrap2.hide();
    },
    onValidationError: function onValidationError(errors) {
      var firstErrorGroup = Object.values(errors || {})[0];
      var firstError = Array.isArray(firstErrorGroup) ? firstErrorGroup[0] : firstErrorGroup;
      if (firstError) {
        var _window$notify2, _window2;
        (_window$notify2 = (_window2 = window).notify) === null || _window$notify2 === void 0 || _window$notify2.call(_window2, 'error', firstError);
      }
    },
    onError: function onError(payload) {
      var _window$notify4, _window4;
      if ((payload === null || payload === void 0 ? void 0 : payload.status) === 'success' || payload !== null && payload !== void 0 && payload.success) {
        var _window$notify3, _window3, _window$bootstrap3;
        (_window$notify3 = (_window3 = window).notify) === null || _window$notify3 === void 0 || _window$notify3.call(_window3, 'success', (payload === null || payload === void 0 ? void 0 : payload.message) || 'Mail gönderildi.');
        (_window$bootstrap3 = window.bootstrap) === null || _window$bootstrap3 === void 0 || (_window$bootstrap3 = _window$bootstrap3.Modal.getInstance(modal)) === null || _window$bootstrap3 === void 0 || _window$bootstrap3.hide();
        return;
      }
      (_window$notify4 = (_window4 = window).notify) === null || _window$notify4 === void 0 || _window$notify4.call(_window4, 'error', (payload === null || payload === void 0 ? void 0 : payload.message) || (payload === null || payload === void 0 ? void 0 : payload.error) || 'Mail gönderilemedi.');
    },
    onComplete: function onComplete() {
      sendBtn.disabled = false;
      sendBtn.textContent = oldText;
    }
  });
});
/******/ })()
;