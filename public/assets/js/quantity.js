document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-selector="qty-plus"], [data-selector="qty-minus"]');
    if (!btn) return;

    const container = btn.closest('[data-selector="quantity-container"]');
    const input = container.querySelector('[data-selector="qty-value"]');
    const url = container.dataset.url || false;

    const boxExact = container.dataset.boxExact === 'true';
    const boxQty = parseInt(container.dataset.boxQuantity) || 1;
    let currentVal = parseInt(input.value) || 0;

    if (btn.dataset.selector === 'qty-plus') {
        currentVal += boxExact ? boxQty : 1;
    }

    if (btn.dataset.selector === 'qty-minus') {
        const minQty = boxExact ? boxQty : 1;
        if (currentVal > minQty) {
            currentVal -= boxExact ? boxQty : 1;
        }
    }

    input.value = currentVal;

    if (url) {
        if (typeof window.updateCartQuantity === 'function') {
            window.updateCartQuantity(url, currentVal, btn);
            return;
        }

        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
});
