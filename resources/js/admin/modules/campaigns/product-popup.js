import { hideModalPrimitive, showModalPrimitive } from '../../../shared/ui/modal';

function getCampaignProductModal() {
    return (
        document.querySelector('[data-js="campaign-product-modal"]') ||
        document.getElementById('product-modal')
    );
}

function getProductListTable() {
    return (
        document.querySelector('[data-js="campaign-product-list"]') ||
        document.getElementById('product-list')
    );
}

function getProductListBody() {
    return getProductListTable()?.querySelector('tbody') || null;
}

function getSearchInput() {
    return (
        document.querySelector('[data-js="campaign-product-search"]') ||
        document.getElementById('search-product')
    );
}

function getTransferButton() {
    return (
        document.querySelector('[data-js="campaign-transfer-selected"]') ||
        document.getElementById('transfer-selected')
    );
}

function getSelectAllButton() {
    return (
        document.querySelector('[data-js="campaign-select-all"]') ||
        document.getElementById('select-all')
    );
}

function getCategoryFilter() {
    return (
        document.querySelector('[data-js="campaign-filter-category"]') ||
        document.getElementById('filter-category')
    );
}

function getBrandFilter() {
    return (
        document.querySelector('[data-js="campaign-filter-brand"]') ||
        document.getElementById('filter-brand')
    );
}

function getColumnCount() {
    const count = getProductListTable()?.querySelectorAll('thead th').length || 0;
    return count > 0 ? count : 5;
}

function createCell(content, className = '') {
    const cell = document.createElement('td');
    if (className) {
        cell.className = className;
    }

    if (typeof content === 'string') {
        cell.textContent = content;
    } else if (content instanceof Node) {
        cell.appendChild(content);
    }

    return cell;
}

function renderTableMessage(message, className = '') {
    const tbody = getProductListBody();
    if (!tbody) {
        return;
    }

    const row = document.createElement('tr');
    row.className = 'text-center';
    const cell = createCell(message, className);
    cell.colSpan = getColumnCount();
    row.appendChild(cell);

    tbody.replaceChildren(row);
}

function renderLoading() {
    const spinner = document.createElement('span');
    spinner.className = 'spinner-border text-primary m-2';
    spinner.setAttribute('role', 'status');
    spinner.setAttribute('aria-hidden', 'true');

    const cell = document.createElement('td');
    cell.colSpan = getColumnCount();
    cell.appendChild(spinner);

    const row = document.createElement('tr');
    row.className = 'text-center';
    row.appendChild(cell);

    const tbody = getProductListBody();
    if (!tbody) {
        return;
    }
    tbody.replaceChildren(row);
}

function renderProductRows(products) {
    const tbody = getProductListBody();
    if (!tbody) {
        return;
    }

    if (!products.length) {
        renderTableMessage('Sonuç bulunamadı.');
        return;
    }

    const fragment = document.createDocumentFragment();
    const useExtendedColumns = getColumnCount() >= 5;

    products.forEach((product) => {
        const row = document.createElement('tr');
        row.dataset.id = String(product.id ?? '');
        row.dataset.name = product.name || '';
        row.dataset.code = product.code || '';

        const checkboxInput = document.createElement('input');
        checkboxInput.type = 'checkbox';

        row.appendChild(createCell(checkboxInput));
        row.appendChild(createCell(product.name || ''));
        row.appendChild(createCell(product.code || ''));
        if (useExtendedColumns) {
            row.appendChild(createCell(product.brand_name || ''));
            row.appendChild(createCell(product.category_name || ''));
        }

        fragment.appendChild(row);
    });

    tbody.replaceChildren(fragment);
}

const state = {
    currentTargetId: null,
    multiple: true,
    inputName: null,
    formId: null,
    debounceTimer: null,
};

function toggleTransferButton() {
    const transferButton = getTransferButton();
    const tbody = getProductListBody();

    if (!transferButton || !tbody) {
        return;
    }

    const selectedCount = tbody.querySelectorAll('input[type="checkbox"]:checked').length;
    transferButton.disabled = selectedCount === 0;
}

function resetPopupState() {
    const searchInput = getSearchInput();
    const transferButton = getTransferButton();
    const tbody = getProductListBody();

    if (searchInput) {
        searchInput.value = '';
    }

    if (transferButton) {
        transferButton.disabled = true;
    }

    if (tbody) {
        tbody.replaceChildren();
    }
}

function normalizeProducts(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (Array.isArray(payload?.data)) {
        return payload.data;
    }

    return [];
}

async function fetchProducts() {
    const searchInput = getSearchInput();
    const query = searchInput?.value?.trim() || '';
    const categoryId = getCategoryFilter()?.value || '';
    const brandId = getBrandFilter()?.value || '';

    if (query.length < 2) {
        const tbody = getProductListBody();
        if (tbody) {
            tbody.replaceChildren();
        }
        toggleTransferButton();
        return;
    }

    renderLoading();

    try {
        const response = await window.axiosRequest.rawGet('/aka/catalog/products/search', {
            q: query,
            category_id: categoryId,
            brand_id: brandId,
        });

        renderProductRows(normalizeProducts(response?.data));
    } catch (_error) {
        renderTableMessage('Bir hata oluştu.', 'text-danger');
    }

    toggleTransferButton();
}

function getTargetProductsBody() {
    if (!state.currentTargetId) {
        return null;
    }

    const targetContainer = document.getElementById(state.currentTargetId);
    if (!targetContainer) {
        return null;
    }

    if (targetContainer.tagName === 'TABLE') {
        return targetContainer.querySelector('tbody');
    }

    return targetContainer?.querySelector('table tbody') || null;
}

function getInputNameForTarget() {
    if (state.inputName) {
        return state.inputName;
    }

    if ((state.currentTargetId || '').toLowerCase().includes('gift')) {
        return 'rules[0][extra][gifts][]';
    }

    return 'products[]';
}

function getInputFormId() {
    return state.formId || 'campaign-form';
}

function hasHiddenValue(body, value) {
    const inputs = body.querySelectorAll('input[type="hidden"]');
    return Array.from(inputs).some((input) => input.value === String(value));
}

function appendSelectedRows() {
    const sourceBody = getProductListBody();
    const targetBody = getTargetProductsBody();

    if (!sourceBody || !targetBody) {
        return;
    }

    const selectedRows = sourceBody.querySelectorAll('input[type="checkbox"]:checked');
    const inputName = getInputNameForTarget();

    let appendedCount = 0;

    selectedRows.forEach((checkbox) => {
        const sourceRow = checkbox.closest('tr');
        if (!sourceRow) {
            return;
        }

        if (!state.multiple) {
            targetBody.replaceChildren();
        }

        const productId = sourceRow.dataset.id || '';
        const productName = sourceRow.dataset.name || '';
        const productCode = sourceRow.dataset.code || '';

        if (hasHiddenValue(targetBody, productId)) {
            return;
        }

        const newRow = document.createElement('tr');
        newRow.dataset.id = productId;

        const nameCell = document.createElement('td');
        nameCell.appendChild(document.createTextNode(productName));

        const codeWrap = document.createElement('div');
        const small = document.createElement('small');
        small.className = 'text-muted';
        small.textContent = productCode;
        codeWrap.appendChild(small);
        nameCell.appendChild(codeWrap);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = inputName;
        input.value = productId;
        input.setAttribute('form', getInputFormId());
        nameCell.appendChild(input);

        const actionCell = document.createElement('td');
        actionCell.className = 'text-center';

        const removeButton = document.createElement('a');
        removeButton.href = 'javascript:;';
        removeButton.className = 'btn btn-danger btn-sm delete-row';
        removeButton.innerHTML = '<i class="las la-trash"></i>';
        actionCell.appendChild(removeButton);

        newRow.appendChild(nameCell);
        newRow.appendChild(actionCell);

        targetBody.appendChild(newRow);
        appendedCount += 1;
    });

    return appendedCount;
}

function openProductPopup(targetId, multiple = true, inputName = null, formId = null) {
    state.currentTargetId = targetId;
    state.multiple = multiple !== false;
    state.inputName = inputName || null;
    state.formId = formId || null;

    const modalElement = getCampaignProductModal();
    if (modalElement) {
        showModalPrimitive(modalElement);
    }

    resetPopupState();
}

function parseMultiple(value) {
    if (typeof value === 'boolean') {
        return value;
    }

    if (value === undefined || value === null) {
        return true;
    }

    return String(value) !== 'false';
}

function initCampaignProductPopup() {
    if (document.body.dataset.jsBoundCampaignProductPopup === '1') {
        return;
    }
    document.body.dataset.jsBoundCampaignProductPopup = '1';

    document.body.addEventListener('click', (event) => {
        const openTrigger =
            event.target.closest('[data-action="open-product-popup"]') ||
            event.target.closest('.open-product-popup');

        if (openTrigger) {
            event.preventDefault();
            const targetId = openTrigger.dataset.target;
            openProductPopup(
                targetId,
                parseMultiple(openTrigger.dataset.multiple),
                openTrigger.dataset.inputName,
                openTrigger.dataset.formId
            );
            return;
        }

        const selectAllButton = getSelectAllButton();
        if (selectAllButton && event.target.closest('#select-all, [data-js="campaign-select-all"]')) {
            event.preventDefault();
            const tbody = getProductListBody();
            tbody?.querySelectorAll('input[type="checkbox"]').forEach((input) => {
                input.checked = true;
            });
            toggleTransferButton();
            return;
        }

        const transferButton = getTransferButton();
        if (transferButton && event.target.closest('#transfer-selected, [data-js="campaign-transfer-selected"]')) {
            event.preventDefault();
            const appendedCount = appendSelectedRows();

            if (appendedCount > 0) {
                const clearButton = document.getElementById('clear-all-products');
                if (clearButton) {
                    clearButton.style.display = '';
                }
            }

            const modalElement = getCampaignProductModal();
            if (modalElement) {
                hideModalPrimitive(modalElement);
            }
        }
    });

    document.body.addEventListener('change', (event) => {
        const changed = event.target;

        const isProductCheckbox =
            changed.matches('input[type="checkbox"]') &&
            changed.closest('[data-js="campaign-product-list"], #product-list');

        if (isProductCheckbox) {
            toggleTransferButton();
            return;
        }

        const isFilter =
            changed.matches('[data-js="campaign-filter-category"], #filter-category') ||
            changed.matches('[data-js="campaign-filter-brand"], #filter-brand');

        if (isFilter) {
            fetchProducts();
        }
    });

    document.body.addEventListener('input', (event) => {
        const searchInput = event.target.closest(
            '[data-js="campaign-product-search"], #search-product'
        );

        if (!searchInput) {
            return;
        }

        if (state.debounceTimer) {
            clearTimeout(state.debounceTimer);
        }

        state.debounceTimer = setTimeout(() => {
            fetchProducts();
        }, 500);
    });
}

window.openProductPopup = openProductPopup;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCampaignProductPopup);
} else {
    initCampaignProductPopup();
}