import '../../../shared/loading';
import '../../../shared/notifications';
import '../../../shared/request-axios';
import { firstValidationMessage, serializeForm } from '../../../shared/forms/helpers';
import { hideModalPrimitive, showModalPrimitive } from '../../../shared/ui/modal';

(function initSubDealerLoginPage() {
    const form = document.querySelector('[data-js="subdealer-login-form"]');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const config = document.querySelector('[data-js="subdealer-login-config"]');
    const requestErrorMessage =
        config?.getAttribute('data-request-error') || 'İstek sırasında bir hata oluştu.';
    const loadingLoginText =
        config?.getAttribute('data-loading-login-text') || 'Giriş yapılıyor, lütfen bekleyin';
    const loadingChangePasswordText =
        config?.getAttribute('data-loading-change-password-text') ||
        'Değiştiriliyor, lütfen bekleyin';
    const contractTitle = config?.getAttribute('data-contract-title') || '';
    const changePasswordTitle = config?.getAttribute('data-change-password-title') || '';
    const confirmButtonText = config?.getAttribute('data-confirm-button-text') || 'Tamam';
    const passwordChangeSuccessMessage =
        config?.getAttribute('data-password-change-success') || '';
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

    const showModal = (selector) => {
        showModalPrimitive(selector, { resolveBy: (value) => document.querySelector(value) });
    };

    const hideModal = (selector) => {
        hideModalPrimitive(selector, { resolveBy: (value) => document.querySelector(value) });
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
        const buttonState = setButtonLoadingText(submitButton, loadingLoginText);
        window.setLoading?.(submitButton, true);

        window.axiosRequest.post(form.action, serializeForm(form), {
            onSuccess: (data) => {
                if (data.type === 'contract_form') {
                    window.Swal?.fire({
                        icon: 'warning',
                        title: contractTitle,
                        confirmButtonText,
                        text: data.message,
                        timer: 5000,
                        timerProgressBar: true,
                        willClose: () => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        },
                    });
                    return;
                }

                if (data.type === 'change_password') {
                    window.Swal?.fire({
                        icon: 'warning',
                        title: changePasswordTitle,
                        confirmButtonText,
                        text: data.message,
                        timer: 5000,
                        timerProgressBar: true,
                        willClose: () => {
                            window.setLoading?.(submitButton, false);
                            buttonState.restore();

                            const changePasswordForm = document.getElementById(
                                'changePasswordModalForm'
                            );
                            if (changePasswordForm instanceof HTMLFormElement) {
                                changePasswordForm.setAttribute('action', data.action || '');
                            }
                            showModal('#changePasswordModal');
                        },
                    });
                    return;
                }

                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                window.setLoading?.(submitButton, false);
                buttonState.restore();
                window.notify?.('success', data.message || requestErrorMessage);
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

    const changePasswordForm = document.getElementById('changePasswordModalForm');
    if (changePasswordForm instanceof HTMLFormElement) {
        changePasswordForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const submitButton =
                document.querySelector('button[form="changePasswordModalForm"]') ||
                changePasswordForm.querySelector('button[type="submit"]');

            const buttonState = setButtonLoadingText(submitButton, loadingChangePasswordText);
            window.setLoading?.(submitButton, true);

            window.axiosRequest.post(
                changePasswordForm.getAttribute('action') || '',
                serializeForm(changePasswordForm),
                {
                    onSuccess: (response) => {
                        window.Swal?.fire({
                            icon: 'success',
                            text: response.message || passwordChangeSuccessMessage,
                            confirmButtonText,
                            timer: 5000,
                            timerProgressBar: true,
                            willClose: () => {
                                hideModal('#changePasswordModal');
                                window.setLoading?.(submitButton, false);
                                buttonState.restore();
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
                }
            );
        });
    }
})();
