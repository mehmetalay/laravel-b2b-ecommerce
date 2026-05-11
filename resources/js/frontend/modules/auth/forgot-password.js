import '../../../shared/loading';
import '../../../shared/notifications';
import '../../../shared/request-axios';
import { firstValidationMessage, serializeForm } from '../../../shared/forms/helpers';

(function initAuthForgotPasswordPage() {
    const form = document.querySelector('[data-js="auth-forgot-password-form"]');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const config = document.querySelector('[data-js="auth-forgot-password-config"]');
    const requestErrorMessage =
        config?.getAttribute('data-request-error') || 'İstek sırasında bir hata oluştu.';
    const successMessage = config?.getAttribute('data-success-message') || '';
    const confirmButtonText = config?.getAttribute('data-confirm-button-text') || 'Tamam';
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

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
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
                notifyAuthValidationError(errors);
            },
            onError: (errorPayload) => {
                window.setLoading?.(submitButton, false);
                notifyAuthRequestError(errorPayload);
            },
        });
    });
})();
