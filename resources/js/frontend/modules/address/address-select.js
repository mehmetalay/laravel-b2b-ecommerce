/* global axiosRequest, window */

const getAddressSelect = () =>
    document.querySelector('[data-js="shipping-address-id"]') ||
    document.getElementById('shipping_address_id');

const getAddressActions = () =>
    document.querySelector('[data-js="address-action-buttons"]') ||
    document.getElementById('address-action-buttons');

const getAddressPreview = () =>
    document.querySelector('[data-js="address-preview"]') ||
    document.getElementById('address-preview');

const resetAddressPreviewState = () => {
    const actions = getAddressActions();
    const preview = getAddressPreview();

    if (actions) {
        actions.classList.add('d-none');
    }

    if (preview) {
        preview.innerHTML = '';
        preview.classList.add('d-none');
    }
};

const renderAddressOptions = (select, addresses = []) => {
    select.innerHTML = '<option value="" hidden selected>Seç</option>';

    let defaultId = null;

    addresses.forEach((address) => {
        const option = document.createElement('option');
        option.value = address.id;
        option.textContent = `${address.title} - ${address.city}/${address.district}`;
        select.appendChild(option);

        if (address.is_default && !defaultId) {
            defaultId = address.id;
        }
    });

    return defaultId;
};

const refreshAddressSelect = (selectedId = null) => {
    axiosRequest.get('/addresses/list', {}, {
        onSuccess: (response) => {
            const select = getAddressSelect();
            if (!select) {
                return;
            }

            resetAddressPreviewState();

            const defaultId = renderAddressOptions(select, response.addresses || []);

            if (selectedId) {
                select.value = selectedId;
            } else if (defaultId) {
                select.value = defaultId;
            }

            if (select.value && !Number.isNaN(Number(select.value))) {
                select.dispatchEvent(new Event('change'));
            }
        },
    });
};

window.refreshAddressSelect = refreshAddressSelect;

