function getTransactionTypeSelect() {
    return (
        document.querySelector('[data-js="transaction-type"]') ||
        document.getElementById('transaction_type')
    );
}

function getManualOptions() {
    return (
        document.querySelector('[data-js="manual-options"]') ||
        document.getElementById('manual-options')
    );
}

function initPaymentLinkTransactionToggle() {
    const transactionTypeSelect = getTransactionTypeSelect();
    const manualOptions = getManualOptions();

    if (!transactionTypeSelect || !manualOptions) {
        return;
    }

    if (transactionTypeSelect.dataset.jsBoundTransactionToggle === '1') {
        return;
    }
    transactionTypeSelect.dataset.jsBoundTransactionToggle = '1';

    const syncVisibility = () => {
        manualOptions.style.display = transactionTypeSelect.value === '3' ? '' : 'none';
    };

    syncVisibility();
    transactionTypeSelect.addEventListener('change', syncVisibility);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPaymentLinkTransactionToggle);
} else {
    initPaymentLinkTransactionToggle();
}
