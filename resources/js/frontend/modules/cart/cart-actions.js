function registerCartActions(context) {
    document.addEventListener('click', (event) => {
        const deleteAllBtn = event.target.closest('[data-js="delete-all-cart"]');
        if (deleteAllBtn) {
            event.preventDefault();

            context.showConfirm({
                text: context.messages.deleteAllConfirm,
                confirmButtonText: context.messages.confirmDeleteAll,
            }).then((isConfirmed) => {
                if (!isConfirmed) {
                    return;
                }

                window.setLoading?.(deleteAllBtn, true);
                context.request.post(context.routes.deleteAll, {}, {
                    onSuccess: () => {
                        context.updateAllCartsSafe();
                    },
                    onError: () => {
                        window.notify?.('error', context.messages.requestError);
                    },
                    onValidationError: (errors) => {
                        context.notifyValidationError(errors, context.messages.requestError);
                    },
                    onComplete: () => {
                        window.setLoading?.(deleteAllBtn, false);
                    },
                });
            });

            return;
        }

        const deleteProductBtn = event.target.closest('[data-js="delete-product-cart"]');
        if (!deleteProductBtn) {
            return;
        }

        event.preventDefault();
        context.showConfirm({
            text: context.messages.deleteProductConfirm,
            confirmButtonText: context.messages.confirmDeleteProduct,
        }).then((isConfirmed) => {
            if (!isConfirmed) {
                return;
            }

            window.setLoading?.(deleteProductBtn, true);
            context.request.del(deleteProductBtn.dataset.url, {}, {
                onSuccess: () => {
                    context.updateAllCartsSafe();
                    window.notify?.('success', context.messages.productRemoved);
                },
                onError: () => {
                    window.notify?.('error', context.messages.requestError);
                },
                onValidationError: (errors) => {
                    context.notifyValidationError(errors, context.messages.requestError);
                },
                onComplete: () => {
                    window.setLoading?.(deleteProductBtn, false);
                },
            });
        });
    });
}

export { registerCartActions };
