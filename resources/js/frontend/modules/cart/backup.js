function registerBackupHandlers(context) {
    document.addEventListener('click', (event) => {
        const backupCartBtn = event.target.closest('[data-js="backup-the-cart"]');
        if (backupCartBtn) {
            event.preventDefault();
            const cartName = document.getElementById('cart_name')?.value;

            window.setLoading?.(backupCartBtn, true);
            context.request.post(context.routes.backupCart, { cart_name: cartName }, {
                onSuccess: () => {
                    context.hideModalElements('.cart-export-modal');
                    window.location.reload();
                },
                onError: (data) => {
                    const type = data?.status === 'warning' ? 'warning' : 'error';
                    window.notify?.(type, data?.message || context.messages.requestError);
                },
                onValidationError: (errors) => {
                    context.notifyValidationError(errors, context.messages.requestError);
                },
                onComplete: () => {
                    window.setLoading?.(backupCartBtn, false);
                },
            });
            return;
        }

        const importCartBtn = event.target.closest('[data-js="import-cart"]');
        if (!importCartBtn) {
            return;
        }

        event.preventDefault();
        const backedUpCartId = document.getElementById('backed_up_cart_id')?.value;

        window.setLoading?.(importCartBtn, true);
        context.request.post(context.routes.importCart, { backed_up_cart_id: backedUpCartId }, {
            onSuccess: () => {
                context.hideModalElements('.import-backed-up-cart');
                window.location.reload();
            },
            onError: (data) => {
                const type = data?.status === 'warning' ? 'warning' : 'error';
                window.notify?.(type, data?.message || context.messages.requestError);
            },
            onValidationError: (errors) => {
                context.notifyValidationError(errors, context.messages.requestError);
            },
            onComplete: () => {
                window.setLoading?.(importCartBtn, false);
            },
        });
    });
}

export { registerBackupHandlers };
