import '../../../shared/loading';
import '../../../shared/notifications';
import '../../../shared/request-axios';
import { firstValidationMessage, serializeForm } from '../../../shared/forms/helpers';
import { hideModalPrimitive, showModalPrimitive } from '../../../shared/ui/modal';

function setButtonLoadingText(button, text) {
    if (!button) {
        return () => {};
    }

    const originalHtml = button.innerHTML;
    button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ${text}`;
    return () => {
        button.innerHTML = originalHtml;
    };
}

function showModalById(modalId) {
    showModalPrimitive(modalId, { resolveBy: (value) => document.getElementById(value) });
}

function hideModalById(modalId) {
    hideModalPrimitive(modalId, { resolveBy: (value) => document.getElementById(value) });
}

function appendFormData(formData, key, value) {
    if (value === undefined || value === null) {
        formData.append(key, '');
        return;
    }

    if (Array.isArray(value)) {
        value.forEach((item, index) => {
            appendFormData(formData, `${key}[${index}]`, item);
        });
        return;
    }

    if (typeof value === 'object' && !(value instanceof File)) {
        Object.keys(value).forEach((childKey) => {
            appendFormData(formData, `${key}[${childKey}]`, value[childKey]);
        });
        return;
    }

    formData.append(key, value);
}

function toFormData(data) {
    const formData = new FormData();
    if (!data || typeof data !== 'object') {
        return formData;
    }

    Object.keys(data).forEach((key) => {
        appendFormData(formData, key, data[key]);
    });

    return formData;
}

function resolvePostTransport() {
    const rawPost = window.axiosRequest?.rawPost;
    if (typeof rawPost === 'function') {
        return (url, payload) => rawPost.call(window.axiosRequest, url, payload);
    }

    if (window.axios && typeof window.axios.post === 'function') {
        return (url, payload) => window.axios.post(url, payload);
    }

    return null;
}

function extractResponsePayload(response) {
    if (response && typeof response === 'object' && 'data' in response) {
        return response.data || {};
    }

    return response || {};
}

function extractValidationErrors(error) {
    const response = error?.response;
    if (response?.status === 422 && response.data?.errors && typeof response.data.errors === 'object') {
        return response.data.errors;
    }

    return null;
}

function extractErrorPayload(error) {
    return error?.response?.data || error;
}

function requestPost(url, data, { onSuccess, onError, onValidationError, onComplete } = {}) {
    const postTransport = resolvePostTransport();
    if (!postTransport) {
        onError?.({ message: 'Request helper is not available.' });
        onComplete?.();
        return;
    }

    postTransport(url, toFormData(data))
        .then((response) => {
            onSuccess?.(extractResponsePayload(response));
        })
        .catch((error) => {
            const validationErrors = extractValidationErrors(error);
            if (validationErrors) {
                onValidationError?.(validationErrors);
                return;
            }

            onError?.(extractErrorPayload(error));
        })
        .finally(() => {
            onComplete?.();
        });
}

function parseTurkishDateToIso(dateText) {
    const [day, month, year] = String(dateText).split('.');
    if (!day || !month || !year) {
        return '';
    }

    const date = new Date(`${year}-${month}-${day}`);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toISOString().split('T')[0];
}

function formatDateForTR(dateValue) {
    const date = new Date(dateValue);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

function applyCurrencyNotePrefix({
    selectElement,
    notesElement,
    notePrefix,
}) {
    const selectedCurrency = selectElement?.value;
    if (!selectedCurrency || !notesElement) {
        return;
    }

    const notesText = notesElement.value
        .split('.')
        .filter((line) => !line.startsWith(notePrefix));
    const newNote = `${notePrefix} ${selectedCurrency} Tahsilat`;
    notesElement.value = [newNote, ...notesText].join('.').trim();
}

export {
    applyCurrencyNotePrefix,
    firstValidationMessage,
    formatDateForTR,
    hideModalById,
    parseTurkishDateToIso,
    requestPost,
    serializeForm,
    setButtonLoadingText,
    showModalById,
};
