/* global axiosRequest, bootstrap, setLoading, Swal, window */

document.addEventListener('DOMContentLoaded', () => {
    const qs = (selector, parent = document) => parent.querySelector(selector);

    const getForm = (name) => qs(`[data-form="${name}"]`);
    const getModal = (name) => qs(`[data-modal="${name}"]`);
    const getSettings = () => {
        const submitOrderForm = getForm('submit-order');

        return {
            isOrderConfirmation: submitOrderForm?.dataset.isOrderConfirmation === '1',
            routes: {
                orderStore: submitOrderForm?.dataset.orderStoreUrl || '/orders',
                orderPreview: submitOrderForm?.dataset.orderPreviewUrl || '/orders/preview',
            },
        };
    };

    const showModal = (name) => {
        const modal = getModal(name);
        if (!modal) {
            return;
        }

        bootstrap.Modal.getOrCreateInstance(modal).show();
    };

    const hideModal = (name) => {
        const modal = getModal(name);
        if (!modal) {
            return;
        }

        bootstrap.Modal.getOrCreateInstance(modal).hide();
    };

    const serializeForm = (form) => {
        const data = {};
        new FormData(form).forEach((value, key) => {
            data[key] = value;
        });
        return data;
    };

    const buildConfirmationRows = (rows = []) => {
        const tbody = qs('[data-target="order-confirmation-tbody"]');
        if (!tbody) {
            return;
        }

        tbody.innerHTML = '';

        rows.forEach((row) => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${row.stock_code ?? ''}</td>
                    <td>${row.product_name ?? ''}</td>
                    <td class="text-center">${row.quantity ?? ''}</td>
                    <td class="text-center">${row.price ?? ''}</td>
                    <td class="text-center">%${row.discount ?? ''}</td>
                    <td class="text-center">%${row.vat ?? ''}</td>
                    <td class="text-center">${row.net_price ?? ''}</td>
                    <td class="text-center">${row.total ?? ''}</td>
                </tr>
            `);
        });
    };

    const submitOrder = (button, preview = false) => {
        const formName = button.dataset.formTarget;
        const form = getForm(formName);

        if (!form) {
            return;
        }

        setLoading(button, true);

        const settings = getSettings();
        const url = preview ? settings.routes.orderPreview : settings.routes.orderStore;

        axiosRequest.post(url, serializeForm(form), {
            onSuccess: (response) => {
                if (preview) {
                    buildConfirmationRows(response.rows);
                    form.querySelector('[name="order_preview_token"]').value = response.token;

                    hideModal('submit-order');
                    showModal('order-confirmation');
                    return;
                }

                hideModal('submit-order');
                hideModal('order-confirmation');

                if (response.trigger_order_service) {
                    fetch(`/service/eta/order/${response.order_id}`)
                        .catch(() => console.warn('ETA servisine g\u00F6nderilemedi'));
                }

                window.location.href = response.redirect;
            },
            onError: (response) => {
                if (response && response.warnings && response.warnings.length > 0) {
                    const icons = {
                        removed: '\u274C',
                        quantity_updated: '\uD83D\uDD04',
                        discount_updated: '\uD83D\uDCB0',
                    };

                    const listItems = response.warnings.map((warning) => {
                        const icon = icons[warning.action] || '\u26A0\uFE0F';
                        return `<li style="text-align:left;margin-bottom:6px;">${icon} ${warning.message}</li>`;
                    }).join('');

                    hideModal('submit-order');
                    hideModal('order-confirmation');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Sepetiniz G\u00FCncellendi',
                        html: `<ul style="list-style:none;padding:0;margin:0;">${listItems}</ul>`,
                        confirmButtonText: 'Tamam',
                        allowOutsideClick: false,
                    }).then(() => {
                        window.location.reload();
                    });

                    return;
                }

                if (response?.message) {
                    const notifyType = response.status === 'warning' ? 'warning' : 'error';
                    window.notify?.(notifyType, response.message);
                }

                if (response && response.reload) {
                    window.location.reload();
                }
            },
            onComplete: () => {
                setLoading(button, false);
            },
        });
    };

    document.body.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) {
            return;
        }

        event.preventDefault();

        switch (button.dataset.action) {
        case 'submit-order':
        {
            const settings = getSettings();
            submitOrder(button, settings.isOrderConfirmation);
            break;
        }
        case 'confirm-submit-order':
            submitOrder(button, false);
            break;
        default:
            break;
        }
    });
});
