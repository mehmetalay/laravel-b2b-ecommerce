function initCampaignDeleteRow() {
    if (document.body.dataset.jsBoundCampaignDeleteRow === '1') {
        return;
    }
    document.body.dataset.jsBoundCampaignDeleteRow = '1';

    document.body.addEventListener('click', (event) => {
        const trigger =
            event.target.closest('[data-action="delete-row"]') ||
            event.target.closest('.delete-row');

        if (!trigger) {
            return;
        }

        event.preventDefault();
        trigger.closest('tr')?.remove();

        const clearButton = document.getElementById('clear-all-products');
        const productRows = document.querySelectorAll('#products-table tbody tr');
        if (clearButton && productRows.length === 0) {
            clearButton.style.display = 'none';
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCampaignDeleteRow);
} else {
    initCampaignDeleteRow();
}
