import '../../../shared/loading';
import '../../../shared/notifications';
import '../../../shared/request-axios';
import { firstValidationMessage, serializeForm } from '../../../shared/forms/helpers';

(function initSubDealerResetPasswordPage() {
    const form = document.querySelector('[data-js="subdealer-reset-password-form"]');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const config = document.querySelector('[data-js="subdealer-reset-password-config"]');
    const requestErrorMessage =
        config?.getAttribute('data-request-error') || 'İstek sırasında bir hata oluştu.';
    const successMessage = config?.getAttribute('data-success-message') || '';
    const confirmButtonText = config?.getAttribute('data-confirm-button-text') || 'Tamam';
    const loadingSubmitText =
        config?.getAttribute('data-loading-submit-text') || 'Değiştiriliyor, lütfen bekleyin';

    const notifyAuthValidationError = (errors) => {
        window.notify?.('error', firstValidationMessage(errors) || requestErrorMessage);
    };
    const notifyAuthRequestError = (errorPayload) => {
        const notifyType = errorPayload?.status === 'warning' ? 'warning' : 'error';
        window.notify?.(
            notifyType,
            errorPayload?.message || requestErrorMessage
        );
    };

    const setButtonLoadingText = (button, text) => {
        if (!button) {
            return { restore: () => {} };
        }

        const originalHtml = button.innerHTML;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ${text}`;
        return {
            restore: () => {
                button.innerHTML = originalHtml;
            },
        };
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
        const buttonState = setButtonLoadingText(submitButton, loadingSubmitText);
        window.setLoading?.(submitButton, true);

        window.axiosRequest.post(form.action, serializeForm(form), {
            onSuccess: (data) => {
                window.Swal?.fire({
                    icon: 'success',
                    text: successMessage || data.message,
                    confirmButtonText,
                    timer: 5000,
                    timerProgressBar: true,
                    willClose: () => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    },
                });
            },
            onValidationError: (errors) => {
                window.setLoading?.(submitButton, false);
                buttonState.restore();
                notifyAuthValidationError(errors);
            },
            onError: (errorPayload) => {
                window.setLoading?.(submitButton, false);
                buttonState.restore();
                notifyAuthRequestError(errorPayload);
            },
        });
    });
})();
