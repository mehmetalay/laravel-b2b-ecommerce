function registerPriceModal(context) {
    const modalEl = document.querySelector('[data-selector="edit-price-modal"]');
    if (!modalEl || modalEl.dataset.bound === '1') {
        return;
    }

    const rowIdInput = modalEl.querySelector('[data-selector="row-id"]');
    const listPriceEl = modalEl.querySelector('[data-selector="list-price"]');
    const discountEl = modalEl.querySelector('[data-selector="discount-rate"]');
    const netPriceEl = modalEl.querySelector('[data-selector="net-price"]');
    const saveBtn = modalEl.querySelector('[data-selector="save-price-btn"]');

    if (!rowIdInput || !listPriceEl || !discountEl || !netPriceEl || !saveBtn) {
        return;
    }

    modalEl.dataset.bound = '1';

    modalEl.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        if (!button) {
            return;
        }

        const rowId = button.getAttribute('data-id');
        const listPrice = Number.parseFloat(button.getAttribute('data-list-price'));
        const discount = Number.parseFloat(button.getAttribute('data-discount'));

        rowIdInput.value = rowId || '';
        listPriceEl.value = Number.isFinite(listPrice) ? listPrice.toFixed(6) : '0.000000';
        discountEl.value = Number.isFinite(discount) ? discount.toFixed(6) : '0.000000';

        const netPrice = Number.parseFloat(listPriceEl.value) * (1 - (Number.parseFloat(discountEl.value) / 100));
        netPriceEl.value = Number.isFinite(netPrice) ? netPrice.toFixed(6) : '0.000000';
    });

    discountEl.addEventListener('input', () => {
        const listPrice = Number.parseFloat(listPriceEl.value);
        const discount = Number.parseFloat(discountEl.value) || 0;
        const netPrice = listPrice * (1 - (discount / 100));
        netPriceEl.value = Number.isFinite(netPrice) ? netPrice.toFixed(6) : '0.000000';
    });

    netPriceEl.addEventListener('input', () => {
        const listPrice = Number.parseFloat(listPriceEl.value);
        const netPrice = Number.parseFloat(netPriceEl.value) || 0;
        const discount = (1 - (netPrice / listPrice)) * 100;
        discountEl.value = Number.isFinite(discount) ? discount.toFixed(6) : '0.000000';
    });

    saveBtn.addEventListener('click', () => {
        const id = rowIdInput.value;
        if (!id) {
            return;
        }

        const payload = {
            id,
            discount: Number.parseFloat(discountEl.value),
            net_price: Number.parseFloat(netPriceEl.value),
        };

        const url = context.routes.updatePriceTemplate.replace('{id}', id);
        window.setLoading?.(saveBtn, true);

        context.request.post(url, payload, {
            onSuccess: (data) => {
                context.updateAllCartsSafe();
                window.notify?.('success', data?.message || context.messages.saveFailed);
                if (window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
            },
            onError: (data) => {
                const type = data?.status === 'warning' ? 'warning' : 'error';
                window.notify?.(type, data?.message || context.messages.requestError);
            },
            onValidationError: (errors) => {
                context.notifyValidationError(errors, context.messages.requestError);
            },
            onComplete: () => {
                window.setLoading?.(saveBtn, false);
            },
        });
    });
}

export { registerPriceModal };
