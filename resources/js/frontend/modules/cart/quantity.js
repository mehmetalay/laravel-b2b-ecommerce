function registerQuantityHandlers(context) {
    const updateCartQuantity = (url, quantity, button = null) => {
        if (!url) {
            return;
        }

        if (button) {
            window.setLoading?.(button, true);
        }

        context.request.post(url, { quantity }, {
            onSuccess: (data) => {
                context.updateAllCartsSafe();
                window.notify?.('success', data?.message || context.messages.saveFailed);
            },
            onError: (data) => {
                const type = data?.status === 'warning' ? 'warning' : 'error';
                const message = data?.message || context.messages.requestError;
                window.notify?.(type, message);
            },
            onValidationError: (errors) => {
                context.notifyValidationError(errors, context.messages.requestError);
            },
            onComplete: () => {
                if (button) {
                    window.setLoading?.(button, false);
                }
            },
        });
    };

    document.addEventListener('change', (event) => {
        const quantityInput = event.target.closest('[data-selector="qty-value"]');
        if (!quantityInput) {
            return;
        }

        event.preventDefault();
        updateCartQuantity(quantityInput.dataset.url, quantityInput.value);
    });

    document.addEventListener(
        'focusout',
        (event) => {
            const input = event.target.closest('[data-selector="cart-update-explanation"]');
            if (!input) {
                return;
            }

            const oldValue = input.dataset.oldValue || '';
            const newValue = input.value || '';
            if (oldValue === newValue) {
                return;
            }

            context.request.post(input.dataset.url, { explanation: newValue }, {
                onSuccess: (data) => {
                    window.notify?.('success', data?.message || context.messages.saveFailed);
                    input.dataset.oldValue = newValue;
                },
                onError: (data) => {
                    const type = data?.status === 'warning' ? 'warning' : 'error';
                    window.notify?.(type, data?.message || context.messages.requestError);
                },
                onValidationError: (errors) => {
                    context.notifyValidationError(errors, context.messages.requestError);
                },
            });
        },
        true
    );
}

export { registerQuantityHandlers };
