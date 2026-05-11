function getManualBankIntegrationSelect() {
    return (
        document.querySelector('[data-js="manual-bank-integration"]') ||
        document.getElementById('manual_bank_integration_id')
    );
}

function getManualInstallmentSelect() {
    return (
        document.querySelector('[data-js="manual-installment"]') ||
        document.getElementById('manual_installment')
    );
}

function setDisabledPlaceholder(select, text) {
    select.innerHTML = `<option value="" selected hidden>${text}</option>`;
    select.disabled = true;
}

function setBaseOptions(select) {
    select.innerHTML = '<option value="" selected hidden>SEÇ</option>';
}

function getSelectedInstallmentValue(installmentSelect) {
    return installmentSelect.dataset.selectedManualInstallment || '';
}

async function loadInstallments(bankIntegrationId, installmentSelect) {
    if (!bankIntegrationId) {
        setDisabledPlaceholder(installmentSelect, 'SEÇ');
        return;
    }

    setDisabledPlaceholder(installmentSelect, 'Yükleniyor...');

    if (!window.axiosRequest || typeof window.axiosRequest.rawGet !== 'function') {
        setDisabledPlaceholder(installmentSelect, 'Taksit bulunamadı');
        return;
    }

    try {
        const response = await window.axiosRequest.rawGet(`/ajax/bank-integrations/${bankIntegrationId}/installments`);
        const installments = Array.isArray(response?.data) ? response.data : [];

        setBaseOptions(installmentSelect);

        if (installments.length === 0) {
            setDisabledPlaceholder(installmentSelect, 'Taksit bulunamadı');
            return;
        }

        const selectedManualInstallment = getSelectedInstallmentValue(installmentSelect);

        installments.forEach((installmentItem) => {
            const option = document.createElement('option');
            const installmentValue = String(installmentItem?.installment ?? '');

            option.value = installmentValue;
            option.textContent = installmentValue;

            if (installmentValue === String(selectedManualInstallment)) {
                option.selected = true;
            }

            installmentSelect.appendChild(option);
        });

        installmentSelect.disabled = false;
    } catch (error) {
        setDisabledPlaceholder(installmentSelect, 'Taksit bulunamadı');
        if (typeof window.notify === 'function') {
            window.notify('error', 'Taksitler yüklenemedi.');
        }
    }
}

function initPaymentLinkInstallments() {
    const bankIntegrationSelect = getManualBankIntegrationSelect();
    const installmentSelect = getManualInstallmentSelect();

    if (!bankIntegrationSelect || !installmentSelect) {
        return;
    }

    if (bankIntegrationSelect.dataset.jsBoundManualInstallments === '1') {
        return;
    }
    bankIntegrationSelect.dataset.jsBoundManualInstallments = '1';

    const syncInstallments = () => {
        loadInstallments(bankIntegrationSelect.value, installmentSelect);
    };

    syncInstallments();
    bankIntegrationSelect.addEventListener('change', syncInstallments);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPaymentLinkInstallments);
} else {
    initPaymentLinkInstallments();
}
