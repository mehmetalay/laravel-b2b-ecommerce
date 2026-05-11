function registerDiscountHandlers(context) {
    document.addEventListener('click', (event) => {
        const generalDiscountBtn = event.target.closest('[data-js="general-discount-apply"]');
        if (generalDiscountBtn) {
            event.preventDefault();
            const input = generalDiscountBtn.parentElement?.querySelector('input');
            const discount = input?.value;
            const currency = generalDiscountBtn.dataset.currency;

            window.setLoading?.(generalDiscountBtn, true);
            context.request.post(context.routes.generalDiscount, { discount, currency }, {
                onSuccess: (data) => {
                    context.updateAllCartsSafe();
                    window.notify?.('success', data?.message || context.messages.saveFailed);
                    if (input) {
                        input.value = '';
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
                    window.setLoading?.(generalDiscountBtn, false);
                },
            });
            return;
        }

        const cancelDiscountBtn = event.target.closest('[data-js="cancel-all-discounts"]');
        if (!cancelDiscountBtn) {
            return;
        }

        event.preventDefault();
        context.showConfirm({
            text: context.messages.cancelAllDiscountsConfirm,
            confirmButtonText: context.messages.confirmCancelDiscounts,
        }).then((isConfirmed) => {
            if (!isConfirmed) {
                return;
            }

            const currency = cancelDiscountBtn.dataset.currency;
            window.setLoading?.(cancelDiscountBtn, true);

            context.request.post(context.routes.cancelAllDiscounts, { currency }, {
                onSuccess: (data) => {
                    context.updateAllCartsSafe();
                    window.notify?.('success', data?.message || context.messages.saveFailed);
                },
                onError: (data) => {
                    const type = data?.status === 'warning' ? 'warning' : 'error';
                    window.notify?.(type, data?.message || context.messages.requestError);
                },
                onValidationError: (errors) => {
                    context.notifyValidationError(errors, context.messages.requestError);
                },
                onComplete: () => {
                    window.setLoading?.(cancelDiscountBtn, false);
                },
            });
        });
    });

    document.addEventListener('change', (event) => {
        const discountInput = event.target.closest('[data-selector="cart-update-discount"]');
        if (!discountInput) {
            return;
        }

        event.preventDefault();
        const valueInput = discountInput.parentElement?.querySelector('input');
        const discount = valueInput ? valueInput.value : null;
        const url = discountInput.dataset.url;

        context.request.post(url, { discount }, {
            onSuccess: (data) => {
                context.updateAllCartsSafe();
                window.notify?.('success', data?.message || context.messages.saveFailed);
            },
            onError: (data) => {
                const type = data?.status === 'warning' ? 'warning' : 'error';
                window.notify?.(type, data?.message || context.messages.requestError);
            },
            onValidationError: (errors) => {
                context.notifyValidationError(errors, context.messages.requestError);
            },
        });
    });
}

export { registerDiscountHandlers };
