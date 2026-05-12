function getClearAllButton() {
    return (
        document.querySelector('[data-js="clear-all-products"]') ||
        document.getElementById('clear-all-products')
    );
}

function getProductsTableBody() {
    const table =
        document.querySelector('[data-js="products-table"]') ||
        document.getElementById('products-table');

    return table?.querySelector('tbody') || null;
}

function initHomepageBlockClearProducts() {
    const clearButton = getClearAllButton();
    const productsTableBody = getProductsTableBody();

    if (!clearButton || !productsTableBody) {
        return;
    }

    if (clearButton.dataset.jsBoundClearProducts === '1') {
        return;
    }
    clearButton.dataset.jsBoundClearProducts = '1';

    clearButton.addEventListener('click', () => {
        productsTableBody.innerHTML = '';
        clearButton.style.display = 'none';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHomepageBlockClearProducts);
} else {
    initHomepageBlockClearProducts();
}
