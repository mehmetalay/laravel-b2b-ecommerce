/* global window */

document.addEventListener('DOMContentLoaded', () => {
    const deliveryTypeElement =
        document.querySelector('[data-js="delivery-type"]') ||
        document.getElementById('delivery_type');

    if (!deliveryTypeElement) {
        return;
    }

    const deliveryMap = {
        Kargo: {
            wrapper: '[data-delivery-field="cargo"]',
            required: ['cargo_company_id'],
            optional: ['warehouse_name', 'pickup_person', 'transit_note'],
        },
        Ambar: {
            wrapper: '[data-delivery-field="ambar"]',
            required: ['warehouse_name'],
            optional: ['cargo_company_id', 'pickup_person', 'transit_note'],
        },
        'Depo Teslim': {
            wrapper: '[data-delivery-field="depo"]',
            required: ['pickup_person'],
            optional: ['cargo_company_id', 'warehouse_name', 'transit_note'],
        },
        'Transit Sevk': {
            wrapper: '[data-delivery-field="transit"]',
            required: ['transit_note'],
            optional: ['cargo_company_id', 'warehouse_name', 'pickup_person'],
        },
    };

    const allWrappers = document.querySelectorAll('[data-delivery-field]');
    const allInputKeys = ['cargo_company_id', 'warehouse_name', 'pickup_person', 'transit_note'];

    const inputSelectors = {
        cargo_company_id: '[data-js="cargo-company-id"]',
        warehouse_name: '[data-js="warehouse-name"]',
        pickup_person: '[data-js="pickup-person"]',
        transit_note: '[data-js="transit-note"]',
    };

    const getInputElement = (id) =>
        document.querySelector(inputSelectors[id]) ||
        document.getElementById(id);

    const setFieldState = (id, { required, enabled }) => {
        const element = getInputElement(id);
        if (!element) {
            return;
        }

        element.required = !!required;
        element.disabled = !enabled;

        if (!enabled) {
            element.value = '';
        }
    };

    const updateDeliveryFields = () => {
        const selected = deliveryTypeElement.value;

        allWrappers.forEach((wrapper) => wrapper.classList.add('d-none'));
        allInputKeys.forEach((id) => setFieldState(id, { required: false, enabled: false }));

        if (!selected || !deliveryMap[selected]) {
            return;
        }

        const config = deliveryMap[selected];
        document.querySelector(config.wrapper)?.classList.remove('d-none');

        config.required.forEach((id) => setFieldState(id, { required: true, enabled: true }));
        config.optional.forEach((id) => setFieldState(id, { required: false, enabled: false }));
    };

    const updateAddressVisibilityByDeliveryType = (deliveryType) => {
        const wrapper =
            document.querySelector('[data-js="shipping-address-wrapper"]') ||
            document.getElementById('shipping-address-wrapper');
        const select =
            document.querySelector('[data-js="shipping-address-id"]') ||
            document.getElementById('shipping_address_id');

        if (!wrapper || !select) {
            return;
        }

        if (deliveryType === 'Kargo' || deliveryType === 'Transit Sevk') {
            wrapper.classList.remove('d-none');
            select.required = true;

            if (!select.value) {
                window.refreshAddressSelect?.();
            }

            return;
        }

        wrapper.classList.add('d-none');
        select.required = false;
        select.value = '';
        window.clearPreview?.();
    };

    deliveryTypeElement.addEventListener('change', () => {
        updateDeliveryFields();
        updateAddressVisibilityByDeliveryType(deliveryTypeElement.value);
    });
});
