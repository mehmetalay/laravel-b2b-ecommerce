/* global window */

function openPaymentTypeModal(addToCartFunc) {
    const modalElement =
        document.querySelector('[data-js="payment-type-modal"]') ||
        document.getElementById('paymentTypeModal');

    if (!modalElement || !window.bootstrap?.Modal) {
        return;
    }

    const modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalElement);
    modalInstance.show();

    modalElement.querySelectorAll('[data-js="select-payment-type"]').forEach((button) => {
        button.onclick = function onPaymentTypeSelect() {
            const paymentType = this.getAttribute('data-payment-type');

            if (typeof window.setCartPaymentType !== 'function') {
                return;
            }

            window.setCartPaymentType(paymentType, {
                refreshCart: false,
                onSuccess: () => {
                    modalInstance.hide();
                    addToCartFunc(paymentType, true);
                },
                onError: () => {
                    // Keep modal state unchanged on failed selection.
                },
            });
        };
    });
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-js="add-to-cart"]');
    if (!button) {
        return;
    }

    event.preventDefault();
    window.setLoading?.(button, true);

    const viewType = button.getAttribute('data-view-type');
    const container = button.closest(viewType === 'list' ? 'tr' : 'div');
    const quantityInput = container?.querySelector('input[name*="quantity"]');
    const quantity = quantityInput ? quantityInput.value : 1;
    const productId = button.getAttribute('data-product-id');
    const originalHtml = button.innerHTML;

    const successIcon = `
        <svg width="22" height="22" viewBox="0 0 24 24"
            fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cart-drop">
            <rect x="10" y="2" width="4" height="4" rx="1" class="product-box" />
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" class="cart-path"></path>
        </svg>
    `;

    const addToCart = (paymentType = null, skipReset = false) => {
        window.axiosRequest.post('/sepet/sepete-ekle', {
            quantity,
            product_id: productId,
            payment_type: paymentType,
        }, {
            onSuccess: () => {
                window.updateAllCarts?.();
                button.classList.remove('bg-dark');
                button.classList.add('bg-danger');
                button.innerHTML = successIcon;
            },
            onError: (payload) => {
                if (payload.payment_type_selection) {
                    openPaymentTypeModal(addToCart);
                }

                if (payload.stock && quantityInput) {
                    quantityInput.value = payload.stock;
                }

                if (payload.message) {
                    const type = payload.status === 'warning' ? 'warning' : 'error';
                    window.notify?.(type, payload.message);
                }
            },
            onComplete: () => {
                window.setLoading?.(button, false);

                if (!skipReset) {
                    setTimeout(() => {
                        button.innerHTML = originalHtml;
                        button.classList.add('bg-dark');
                        button.classList.remove('bg-danger');
                    }, 3000);
                }
            },
        });
    };

    addToCart();
});
