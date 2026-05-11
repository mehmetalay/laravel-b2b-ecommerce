/* global window */

function setCartPaymentType(paymentType, options = {}) {
    if (!paymentType) {
        options.onComplete?.();
        return;
    }

    window.axiosRequest.post('/sepet/set/payment-type', {
        payment_type: paymentType
    }, {
        onSuccess: (response) => {
            const shouldNotifySuccess = options.notifySuccess !== false;
            const shouldRefreshCart = options.refreshCart !== false;
            const shouldUpdatePaymentLabel = options.updatePaymentLabel !== false;

            if (shouldNotifySuccess && response.message) {
                window.notify('success', response.message);
            }

            if (shouldRefreshCart && typeof window.updateAllCarts === 'function') {
                window.updateAllCarts();
            }

            if (shouldUpdatePaymentLabel && response.payment_type_text) {
                const paymentTypeLabel = document.getElementById('current-payment-type');
                if (paymentTypeLabel) {
                    paymentTypeLabel.textContent = response.payment_type_text;
                }
            }

            options.onSuccess?.(response);
        },
        onError: (payload) => {
            const status = payload?.status;
            const message = payload?.message || 'İşlem sırasında bir hata oluştu.';

            if (status === 'warning') {
                window.notify('warning', message);
            } else if (status === 'error') {
                window.notify('error', message);
            } else {
                window.notify('error', message);
            }

            options.onError?.(payload);
        },
        onComplete: () => {
            options.onComplete?.();
        }
    });
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-payment]');
    if (!trigger) {
        return;
    }

    event.preventDefault();

    const paymentType = trigger.getAttribute('data-payment');
    setCartPaymentType(paymentType);
});

window.setCartPaymentType = setCartPaymentType;

export { setCartPaymentType };
export default setCartPaymentType;
