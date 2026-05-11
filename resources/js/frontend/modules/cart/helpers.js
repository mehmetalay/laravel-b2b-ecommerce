function parseConfig(pageRoot) {
    const configEl = pageRoot.querySelector('[data-js="cart-index-config"]');
    const config = configEl ? configEl.dataset : {};

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
            updatePriceTemplate: config.updatePriceUrlTemplate || '/sepet/update/price/{id}',
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
            giftCountRequiredTemplate: config.msgGiftCountRequiredTemplate || 'Toplam {limit} adet hediye seçmelisiniz.',
        },
        isOrderConfirmation: config.isOrderConfirmation === '1',
    };
}

function firstValidationMessage(errors) {
    if (!errors || typeof errors !== 'object') {
        return null;
    }

    const firstKey = Object.keys(errors)[0];
    if (!firstKey) {
        return null;
    }

    const message = errors[firstKey];
    return Array.isArray(message) ? message[0] : message;
}

function createRequestWrapper(messages) {
    const call = (method, url, data, callbacks = {}) => {
        if (!window.axiosRequest || typeof window.axiosRequest[method] !== 'function') {
            window.notify?.('error', messages.requestError);
            callbacks.onComplete?.();
            return;
        }

        window.axiosRequest[method](url, data, callbacks);
    };

    return {
        post: (url, data, callbacks = {}) => call('post', url, data, callbacks),
        del: (url, data, callbacks = {}) => call('delete', url, data, callbacks),
    };
}

function showConfirm(messages, { text, confirmButtonText }) {
    if (!window.Swal || typeof window.Swal.fire !== 'function') {
        return Promise.resolve(false);
    }

    return window.Swal.fire({
        title: messages.confirmTitle,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: messages.confirmCancel,
    }).then((result) => result.isConfirmed);
}

function hideModalElements(selector) {
    if (!window.bootstrap) {
        return;
    }

    document.querySelectorAll(selector).forEach((modalEl) => {
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
    });
}

function updateAllCartsSafe() {
    if (typeof window.updateAllCarts === 'function') {
        return window.updateAllCarts();
    }

    return Promise.resolve();
}

function notifyValidationError(errors, fallbackMessage) {
    const message = firstValidationMessage(errors) || fallbackMessage;
    window.notify?.('error', message);
}

function createCartContext(pageRoot) {
    const parsed = parseConfig(pageRoot);
    const request = createRequestWrapper(parsed.messages);

    return {
        pageRoot,
        routes: parsed.routes,
        messages: parsed.messages,
        isOrderConfirmation: parsed.isOrderConfirmation,
        request,
        updateAllCartsSafe,
        showConfirm: (payload) => showConfirm(parsed.messages, payload),
        hideModalElements,
        firstValidationMessage,
        notifyValidationError: (errors, fallback) => notifyValidationError(errors, fallback || parsed.messages.requestError),
    };
}

export { createCartContext };
