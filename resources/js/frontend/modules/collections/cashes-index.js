import {
    firstValidationMessage,
    hideModalById,
    requestPost,
    serializeForm,
    setButtonLoadingText,
    showModalById,
} from './shared';
import { registerCashesCreateBindings } from './cashes-create';

(function initCashesIndexPage() {
    const config = document.querySelector('[data-js="cashes-index-config"]');
    if (!config) {
        return;
    }

    const processingText =
        config.getAttribute('data-processing-text') || 'İşleminiz yapılıyor, lütfen bekleyin';
    const requestErrorMessage =
        config.getAttribute('data-request-error') || 'İstek sırasında bir hata oluştu.';
    const emptyRowText = config.getAttribute('data-empty-row-text') || 'Veri yok';
    const notifyCollectionValidationError = (errors) => {
        window.notify?.('error', firstValidationMessage(errors) || requestErrorMessage);
    };
    const notifyCollectionRequestError = (errorPayload) => {
        window.notify?.('error', errorPayload?.message || requestErrorMessage);
    };
    const notifyCollectionWarning = (payload) => {
        window.notify?.(
            payload?.warning ? 'warning' : 'error',
            payload?.warning ? payload.warning : payload?.error
        );
    };

    registerCashesCreateBindings();

    const formatPriceInputs = (scope = document) => {
        const toFormatterInput = (inputElement) => {
            if (typeof window.jQuery === 'function') {
                return window.jQuery(inputElement);
            }

            return inputElement;
        };

        const toNumericCaret = (value) => (Number.isFinite(value) ? value : 0);

        const inputs = scope.querySelectorAll("input[data-format='price']");
        inputs.forEach((input) => {
            if (input.dataset.formattedBound === '1') {
                return;
            }

            input.dataset.formattedBound = '1';

            input.addEventListener('input', () => {
                const formatterInput = toFormatterInput(input);
                if (typeof window.getCaretPosition !== 'function') {
                    return;
                }

                const caretPos = toNumericCaret(window.getCaretPosition(formatterInput));
                const originalVal = input.value;
                if (typeof window.formatCurrency === 'function') {
                    window.formatCurrency(formatterInput);
                }
                const newVal = input.value;
                const diff = newVal.length - originalVal.length;
                if (typeof window.setCaretPosition === 'function') {
                    window.setCaretPosition(formatterInput, caretPos + diff);
                }
            });

            input.addEventListener('blur', () => {
                const formatterInput = toFormatterInput(input);
                if (typeof window.formatCurrency === 'function') {
                    window.formatCurrency(formatterInput, 'blur');
                }
            });
        });
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-js="add-edit"]');
        if (!trigger) {
            return;
        }

        event.preventDefault();

        const url = trigger.getAttribute('data-url');
        const title = trigger.getAttribute('data-title') || '';
        if (!url || !window.axios) {
            return;
        }

        window.axios
            .get(url)
            .then((response) => {
                const modalTitle = document.getElementById('add-edit-modal-label');
                const modalBody = document.getElementById('add-edit-modal-body');
                if (modalTitle) {
                    modalTitle.innerHTML = title;
                }
                if (modalBody) {
                    modalBody.innerHTML = response.data;
                    formatPriceInputs(modalBody);
                }
                showModalById('add-edit-modal');
            })
            .catch(() => {
                window.notify?.('error', requestErrorMessage);
            });
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.id !== 'add-edit-modal-form') {
            return;
        }

        event.preventDefault();

        const submitButton = document.getElementById('add-edit-modal-button');
        const restoreButtonText = setButtonLoadingText(submitButton, processingText);
        window.setLoading?.(submitButton, true);

        requestPost(form.action, serializeForm(form), {
            onSuccess: (data) => {
                if (data.success) {
                    const tableBody = document.getElementById('table-body');
                    if (tableBody) {
                        if (data.type === 'add') {
                            const emptyRow = Array.from(tableBody.querySelectorAll('tr')).find((row) =>
                                row.textContent?.includes(emptyRowText)
                            );
                            emptyRow?.remove();
                            tableBody.insertAdjacentHTML('afterbegin', data.row);
                        } else {
                            const targetRow = document.getElementById(`parent-${data.id}`);
                            if (targetRow) {
                                targetRow.outerHTML = data.row;
                            }
                        }
                    }

                    window.notify?.('success', data.message);
                    hideModalById('add-edit-modal');
                    restoreButtonText();
                    window.setLoading?.(submitButton, false);
                    return;
                }

                restoreButtonText();
                window.setLoading?.(submitButton, false);
                notifyCollectionWarning(data);
            },
            onValidationError: (errors) => {
                restoreButtonText();
                window.setLoading?.(submitButton, false);
                notifyCollectionValidationError(errors);
            },
            onError: (errorPayload) => {
                restoreButtonText();
                window.setLoading?.(submitButton, false);
                notifyCollectionRequestError(errorPayload);
            },
        });
    });
})();
