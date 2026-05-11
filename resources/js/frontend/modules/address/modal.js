/* global axiosRequest, window */

function initAddressModalFlow() {
    const form = document.querySelector('[data-form="address-form"]');
    if (!form) {
        return;
    }

    let context = null; // order | account

    const getShippingAddressSelect = () =>
        document.querySelector('[data-js="shipping-address-id"]') ||
        document.getElementById('shipping_address_id');
    const getAddressPreview = () =>
        document.querySelector('[data-js="address-preview"]') ||
        document.getElementById('address-preview');
    const getAddressActionButtons = () =>
        document.querySelector('[data-js="address-action-buttons"]') ||
        document.getElementById('address-action-buttons');
    const getAddressCardList = () =>
        document.querySelector('[data-js="address-card-list"]') ||
        document.getElementById('address-card-list');
    const getCitySelect = () =>
        document.querySelector('[data-js="city-id"]') ||
        document.getElementById('city_id');
    const getDistrictSelect = () =>
        document.querySelector('[data-js="district-id"]') ||
        document.getElementById('district_id');
    const getNeighborhoodSelect = () =>
        document.querySelector('[data-js="neighborhood-id"]') ||
        document.getElementById('neighborhood_id');

    const fillSelect = (select, items) => {
        if (!select) {
            return;
        }

        select.innerHTML = '<option hidden>Seçiniz</option>';
        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    };

    const clearSelect = (select) => {
        if (!select) {
            return;
        }

        select.innerHTML = '<option hidden>Seçiniz</option>';
    };

    const getSelectedAddressId = () => getShippingAddressSelect()?.value;

    const clearPreview = () => {
        const preview = getAddressPreview();
        const actionButtons = getAddressActionButtons();
        const shippingSelect = getShippingAddressSelect();

        if (preview) {
            preview.innerHTML = '';
            preview.classList.add('d-none');
        }

        if (actionButtons) {
            actionButtons.classList.add('d-none');
        }

        if (shippingSelect) {
            shippingSelect.value = '';
        }
    };

    const renderPreview = (address) => {
        const preview = getAddressPreview();
        const actionButtons = getAddressActionButtons();

        if (!preview) {
            return;
        }

        preview.classList.remove('d-none');
        actionButtons?.classList.remove('d-none');

        preview.innerHTML = `
            <strong>${address.title}</strong><br>
            ${address.company_name}<br>
            ${address.address}<br>
            ${address.city} / ${address.district} / ${address.neighborhood}
        `;
    };

    const resetForm = () => {
        form.reset();

        const idInput = form.querySelector('[name="id"]');
        if (idInput) {
            idInput.value = '';
        }

        clearSelect(getDistrictSelect());
        clearSelect(getNeighborhoodSelect());
    };

    const fillForm = (data) => {
        Object.keys(data).forEach((key) => {
            if (!form[key]) {
                return;
            }

            if (form[key].type === 'checkbox') {
                form[key].checked = !!data[key];
            } else {
                form[key].value = data[key] ?? '';
            }
        });

        if (!data.city_id) {
            return;
        }

        axiosRequest.get(`/locations/districts/${data.city_id}`, {}, {
            onSuccess: (response) => {
                const districtSelect = getDistrictSelect();
                fillSelect(districtSelect, response.data);

                if (districtSelect) {
                    districtSelect.value = data.district_id;
                }

                axiosRequest.get(`/locations/neighborhoods/${data.district_id}`, {}, {
                    onSuccess: (neighborhoodResponse) => {
                        const neighborhoodSelect = getNeighborhoodSelect();
                        fillSelect(neighborhoodSelect, neighborhoodResponse.data);

                        if (neighborhoodSelect) {
                            neighborhoodSelect.value = data.neighborhood_id;
                        }
                    },
                });
            },
        });
    };

    const loadAddress = (id) => {
        if (!id) {
            return;
        }

        axiosRequest.get(`/addresses/${id}`, {}, {
            onSuccess: (response) => {
                fillForm(response.data);
                window.hideModal?.('submit-order');
                window.showModal?.('address-form');
            },
        });
    };

    const buildCard = (address) => `
        <div class="col-md-4" data-js="address-card" data-address-id="${address.id}">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">${address.title}</h5>
                    <p class="card-text small text-muted">
                        ${address.company_name}<br>
                        ${address.address}<br>
                        ${address.city} / ${address.district} / ${address.neighborhood}
                    </p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-secondary text-white" data-action="edit-address" data-id="${address.id}">Düzenle</button>
                        <button class="btn btn-sm btn-danger text-white" data-action="delete-address" data-id="${address.id}">Sil</button>
                    </div>
                </div>
            </div>
        </div>`;

    const refreshAddressCards = () => {
        axiosRequest.get('/addresses/list', {}, {
            onSuccess: (response) => {
                const cardList = getAddressCardList();
                if (!cardList) {
                    return;
                }

                cardList.innerHTML = '';
                response.addresses.forEach((address) => {
                    cardList.insertAdjacentHTML('beforeend', buildCard(address));
                });
            },
        });
    };

    const removeAddressCard = (id, trigger = null) => {
        const selector = `[data-js="address-card"][data-address-id="${id}"]`;
        const card = document.querySelector(selector)
            || trigger?.closest('[data-js="address-card"]')
            || trigger?.closest('.col-md-4');
        card?.remove();
    };

    const saveAddress = () => {
        window.clearFormErrors?.(form);

        axiosRequest.post('/addresses/store', new FormData(form), {
            onSuccess: (response) => {
                window.hideModal?.('address-form');

                if (context === 'order') {
                    window.refreshAddressSelect?.(response.address.id);
                    window.showModal?.('submit-order');
                }

                if (context === 'account') {
                    refreshAddressCards();
                }
            },
            onValidationError: (errors) => {
                window.handleFormValidationErrors?.(errors, form);
            },
        });
    };

    const deleteAddress = async (id, trigger = null) => {
        if (!id || typeof window.customConfirm !== 'function') {
            return;
        }

        const confirmed = await window.customConfirm('Adres silinsin mi?');
        if (!confirmed) {
            return;
        }

        axiosRequest.delete(`/addresses/${id}`, {}, {
            onSuccess: () => {
                clearPreview();

                const isOrderContext = context === 'order' || !!trigger?.closest('[data-modal="submit-order"]');
                if (isOrderContext) {
                    window.refreshAddressSelect?.();
                    return;
                }

                removeAddressCard(id, trigger);
            },
        });
    };

    document.body.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) {
            return;
        }

        switch (button.dataset.action) {
        case 'add-address':
            context = button.closest('[data-modal="submit-order"]') ? 'order' : 'account';
            resetForm();
            window.hideModal?.('submit-order');
            window.showModal?.('address-form');
            break;
        case 'edit-address':
            context = button.closest('[data-modal="submit-order"]') ? 'order' : 'account';
            loadAddress(button.dataset.id || getSelectedAddressId());
            break;
        case 'delete-address':
            deleteAddress(button.dataset.id || getSelectedAddressId(), button);
            break;
        case 'save-address':
            saveAddress();
            break;
        default:
            break;
        }
    });

    const shippingSelect = getShippingAddressSelect();
    if (shippingSelect) {
        shippingSelect.addEventListener('change', () => {
            const id = shippingSelect.value;

            if (!id || Number.isNaN(Number(id))) {
                clearPreview();
                return;
            }

            axiosRequest.get(`/addresses/${id}`, {}, {
                onSuccess: (response) => renderPreview(response.data),
            });
        });
    }

    if (shippingSelect && typeof window.refreshAddressSelect === 'function') {
        window.refreshAddressSelect();
    }
}

document.addEventListener('DOMContentLoaded', initAddressModalFlow);

