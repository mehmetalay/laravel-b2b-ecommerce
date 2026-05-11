import '../../../shared/confirm';
import '../../../shared/loading';
import '../../../shared/notifications';
import '../../../shared/request-axios';

(function initOrdersIndexPage() {
    const pageRoot = document.querySelector('[data-js="orders-index-page"]');
    if (!pageRoot) {
        return;
    }

    const approveConfirmMessage = pageRoot.dataset.approveConfirm || 'Onaylıyor musunuz?';
    const approvedBadgeText = pageRoot.dataset.approvedBadge || 'Onaylandı';
    const genericErrorMessage = pageRoot.dataset.genericError || 'Bir hata oluştu';

    const approveOrder = (button) => {
        const orderId = button.dataset.id;
        if (!orderId) {
            return;
        }

        const row = document.getElementById(`order-${orderId}`);
        window.setLoading?.(button, true);

        window.customConfirm?.(approveConfirmMessage).then((confirmed) => {
            if (!confirmed) {
                window.setLoading?.(button, false);
                return;
            }

            if (!window.axiosRequest || typeof window.axiosRequest.post !== 'function') {
                window.notify?.('error', genericErrorMessage);
                window.setLoading?.(button, false);
                return;
            }

            window.axiosRequest.post(`/orders/${orderId}/dealer-approve`, {}, {
                onSuccess: (response) => {
                    if (!row) {
                        return;
                    }

                    if (response?.status !== 'success') {
                        window.notify?.('error', response?.message || genericErrorMessage);
                        return;
                    }

                    const statusCell = row.querySelector('td:nth-child(5)');
                    if (statusCell) {
                        statusCell.innerHTML = `<span class="badge alert-primary">${approvedBadgeText}</span>`;
                    }

                    const approveBtn = row.querySelector('[data-js="approve-order"]');
                    if (approveBtn) {
                        approveBtn.remove();
                    }

                    window.notify?.('success', response.message);
                },
                onValidationError: (errors) => {
                    const firstKey = Object.keys(errors || {})[0];
                    const message = firstKey ? errors[firstKey]?.[0] : null;
                    window.notify?.('error', message || genericErrorMessage);
                },
                onError: (errorPayload) => {
                    window.notify?.('error', errorPayload?.message || genericErrorMessage);
                },
                onComplete: () => {
                    window.setLoading?.(button, false);
                },
            });
        });
    };

    const showOrderModal = (button) => {
        const url = button.dataset.url;
        if (!url || !window.axios) {
            return;
        }

        window.axios
            .get(url)
            .then((response) => {
                const modalElement = document.querySelector('.order-show');
                const modalBody = document.querySelector('.order-show .modal-body');

                if (!modalElement || !modalBody || !window.bootstrap) {
                    return;
                }

                modalBody.innerHTML = response.data;
                const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            })
            .catch(() => {
                window.notify?.('error', genericErrorMessage);
            });
    };

    document.addEventListener('click', (event) => {
        const showButton = event.target.closest('[data-js="order-show"]');
        if (showButton) {
            event.preventDefault();
            showOrderModal(showButton);
            return;
        }

        const approveButton = event.target.closest('[data-js="approve-order"]');
        if (approveButton) {
            event.preventDefault();
            approveOrder(approveButton);
        }
    });
})();
