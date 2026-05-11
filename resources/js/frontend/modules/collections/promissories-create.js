import {
    applyCurrencyNotePrefix,
    firstValidationMessage,
    formatDateForTR,
    hideModalById,
    parseTurkishDateToIso,
    requestPost,
    setButtonLoadingText,
    showModalById,
} from './shared';

(function initPromissoriesCreatePage() {
    const config = document.querySelector('[data-js="promissories-create-config"]');
    if (!config) {
        return;
    }

    const createTitle = config.getAttribute('data-create-title') || 'Senet oluştur';
    const editTitle = config.getAttribute('data-edit-title') || 'Senet düzenle';
    const requiredFieldsMessage =
        config.getAttribute('data-required-fields-message') || 'Lütfen gerekli alanları doldurunuz.';
    const processingText =
        config.getAttribute('data-processing-text') || 'İşleminiz yapılıyor, lütfen bekleyin';
    const requestErrorMessage =
        config.getAttribute('data-request-error') || 'İstek sırasında bir hata oluştu.';
    const saveUrl = config.getAttribute('data-save-url') || '';
    const currentAccountName = config.getAttribute('data-current-account-name') || '';
    const notePrefix = config.getAttribute('data-note-prefix') || 'Senet';
    const notifyCollectionValidationError = (errors) => {
        window.notify?.('error', firstValidationMessage(errors) || requestErrorMessage);
    };
    const notifyCollectionRequestError = (errorPayload) => {
        window.notify?.(
            'error',
            errorPayload?.warning || errorPayload?.error || errorPayload?.message || requestErrorMessage
        );
    };
    const notifyCollectionWarning = (payload, fallbackMessage) => {
        window.notify?.(
            payload?.warning ? 'warning' : 'error',
            payload?.warning || payload?.error || fallbackMessage
        );
    };

    const tableBody = document.querySelector('#promissory-table tbody');
    const saveButtonContainer = document.getElementById('save-button');
    const saveButton = document.querySelector('[data-js="save-button"]');
    const modalForm = document.getElementById('promissory-modal-form');
    const modalButton = document.getElementById('promissory-modal-button');
    const notesField = document.getElementById('notes');
    const collectionDateField = document.getElementById('collection_date');
    const modalLabel = document.getElementById('promissory-modal-label');
    const pieceInput = document.getElementById('piece');
    const maturityDayInput = document.getElementById('maturity_day');

    const modalFields = {
        serial_number: document.getElementById('serial_number'),
        maturity_date: document.getElementById('maturity_date'),
        clio_type: document.getElementById('clio_type'),
        debtor: document.getElementById('debtor'),
        amount: document.getElementById('amount'),
        currency_type: document.getElementById('currency_type'),
    };

    if (!tableBody || !saveButton || !modalForm || !modalButton) {
        return;
    }

    const getPieceRow = () => pieceInput?.closest('.row');

    const rowCellsToPayload = (row) => {
        const cells = row.querySelectorAll('td');
        return {
            serial_number: cells[0]?.textContent?.trim() || '',
            maturity_date: parseTurkishDateToIso(cells[1]?.textContent?.trim() || ''),
            clio_type: cells[2]?.textContent?.trim() || '',
            debtor: cells[3]?.textContent?.trim() || '',
            amount: cells[4]?.textContent?.trim() || '',
            currency_type: cells[5]?.textContent?.trim() || '',
        };
    };

    const getModalValues = () => ({
        serialNumber: modalFields.serial_number?.value?.trim() || '',
        maturityDate: modalFields.maturity_date?.value || '',
        clioType: modalFields.clio_type?.value?.trim() || '',
        debtor: modalFields.debtor?.value?.trim() || '',
        amount: modalFields.amount?.value?.trim() || '',
        currencyType: modalFields.currency_type?.value?.trim() || '',
    });

    const renderRow = (values, maturityDate) => `<tr>
        <td>${values.serialNumber}</td>
        <td>${maturityDate}</td>
        <td>${values.clioType}</td>
        <td>${values.debtor}</td>
        <td>${values.amount}</td>
        <td>${values.currencyType}</td>
        <td>
            <a href="javascript:;" data-js="edit-row"><span class="badge alert-info"><i class="fa-solid fa-pencil"></i></span></a>
            <a href="javascript:;" data-js="delete-row"><span class="badge alert-danger"><i class="fa-solid fa-trash"></i></span></a>
        </td>
    </tr>`;

    const resetModal = () => {
        modalForm.reset();
        const editingRow = tableBody.querySelector('.editing-row');
        editingRow?.classList.remove('editing-row');
    };

    const setDebtorByClioType = () => {
        if (!modalFields.clio_type || !modalFields.debtor) {
            return;
        }

        if (modalFields.clio_type.value === 'Kendisi') {
            modalFields.debtor.value = currentAccountName;
            modalFields.debtor.disabled = true;
            return;
        }

        modalFields.debtor.value = '';
        modalFields.debtor.disabled = false;
    };

    document.addEventListener('click', (event) => {
        const createTrigger = event.target.closest('[data-js="promissory-modal"]');
        if (createTrigger) {
            event.preventDefault();
            modalButton.setAttribute('data-type', 'add');
            modalLabel.textContent = createTitle;
            const pieceRow = getPieceRow();
            if (pieceRow) {
                pieceRow.style.display = '';
            }
            resetModal();
            setDebtorByClioType();
            showModalById('promissory-modal');
            return;
        }

        const editTrigger = event.target.closest('[data-js="edit-row"]');
        if (editTrigger) {
            event.preventDefault();
            const row = editTrigger.closest('tr');
            if (!row) {
                return;
            }

            const cells = row.querySelectorAll('td');
            const maturityDateText = cells[1]?.textContent?.trim() || '';
            const [day, month, year] = maturityDateText.split('.');
            const formattedMaturityDate = day && month && year ? `${year}-${month}-${day}` : '';

            modalFields.serial_number.value = cells[0]?.textContent?.trim() || '';
            modalFields.maturity_date.value = formattedMaturityDate;
            modalFields.clio_type.value = cells[2]?.textContent?.trim() || '';
            modalFields.debtor.value = cells[3]?.textContent?.trim() || '';
            modalFields.amount.value = cells[4]?.textContent?.trim() || '';
            modalFields.currency_type.value = cells[5]?.textContent?.trim() || '';

            tableBody.querySelector('.editing-row')?.classList.remove('editing-row');
            row.classList.add('editing-row');

            modalButton.setAttribute('data-type', 'update');
            modalLabel.textContent = editTitle;
            const pieceRow = getPieceRow();
            if (pieceRow) {
                pieceRow.style.display = 'none';
            }

            showModalById('promissory-modal');
            setDebtorByClioType();
            return;
        }

        const deleteTrigger = event.target.closest('[data-js="delete-row"]');
        if (deleteTrigger) {
            event.preventDefault();
            deleteTrigger.closest('tr')?.remove();
            if (!tableBody.querySelector('tr') && saveButtonContainer) {
                saveButtonContainer.style.display = 'none';
            }
        }
    });

    modalButton.addEventListener('click', (event) => {
        event.preventDefault();

        const action = modalButton.getAttribute('data-type');
        const values = getModalValues();

        if (action === 'add') {
            if (
                !values.serialNumber ||
                !values.maturityDate ||
                !values.clioType ||
                !values.debtor ||
                !values.amount ||
                !values.currencyType
            ) {
                window.notify?.('warning', requiredFieldsMessage);
                return;
            }

            const piece = Number.parseInt(pieceInput?.value || '1', 10) || 1;
            const maturityDay = Number.parseInt(maturityDayInput?.value || '1', 10) || 1;
            const baseMaturityDate = new Date(values.maturityDate);

            for (let i = 0; i < piece; i += 1) {
                const clonedMaturityDate = new Date(baseMaturityDate);

                if (i > 0) {
                    clonedMaturityDate.setMonth(clonedMaturityDate.getMonth() + i);
                    clonedMaturityDate.setDate(maturityDay);

                    if (clonedMaturityDate.getDate() !== maturityDay) {
                        clonedMaturityDate.setDate(0);
                    }
                }

                const maturityDate = formatDateForTR(clonedMaturityDate);
                tableBody.insertAdjacentHTML('beforeend', renderRow(values, maturityDate));
            }

            if (saveButtonContainer) {
                saveButtonContainer.style.display = '';
            }
        } else if (action === 'update') {
            const row = tableBody.querySelector('.editing-row');
            if (!row) {
                return;
            }

            const cells = row.querySelectorAll('td');
            cells[0].textContent = values.serialNumber;
            cells[1].textContent = formatDateForTR(values.maturityDate);
            cells[2].textContent = values.clioType;
            cells[3].textContent = values.debtor;
            cells[4].textContent = values.amount;
            cells[5].textContent = values.currencyType;
            row.classList.remove('editing-row');
        }

        hideModalById('promissory-modal');
        resetModal();
    });

    const modalElement = document.getElementById('promissory-modal');
    modalElement?.addEventListener('hidden.bs.modal', () => {
        tableBody.querySelector('.editing-row')?.classList.remove('editing-row');
    });

    modalFields.clio_type?.addEventListener('change', setDebtorByClioType);
    modalFields.currency_type?.addEventListener('change', () => {
        applyCurrencyNotePrefix({
            selectElement: modalFields.currency_type,
            notesElement: notesField,
            notePrefix,
        });
    });

    saveButton.addEventListener('click', (event) => {
        event.preventDefault();

        const restoreButtonText = setButtonLoadingText(saveButton, processingText);
        window.setLoading?.(saveButton, true);

        const promissories = Array.from(tableBody.querySelectorAll('tr')).map((row) =>
            rowCellsToPayload(row)
        );
        const csrfToken = document.querySelector('input[name="_token"]')?.value || '';

        requestPost(
            saveUrl,
            {
                collection_date: collectionDateField?.value || '',
                notes: notesField?.value || '',
                promissories,
                _token: csrfToken,
            },
            {
                onSuccess: (response) => {
                    if (response.success) {
                        tableBody.innerHTML = '';
                        if (collectionDateField) {
                            collectionDateField.value = '';
                        }
                        if (notesField) {
                            notesField.value = '';
                        }
                        window.location.href = response.href;
                        return;
                    }

                    restoreButtonText();
                    window.setLoading?.(saveButton, false);
                    notifyCollectionWarning(response);
                },
                onValidationError: (errors) => {
                    restoreButtonText();
                    window.setLoading?.(saveButton, false);
                    notifyCollectionValidationError(errors);
                },
                onError: (errorPayload) => {
                    restoreButtonText();
                    window.setLoading?.(saveButton, false);
                    notifyCollectionRequestError(errorPayload);
                },
            }
        );
    });
})();
