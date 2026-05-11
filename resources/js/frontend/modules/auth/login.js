import '../../../shared/loading';
import '../../../shared/notifications';
import '../../../shared/request-axios';
import { firstValidationMessage, serializeForm } from '../../../shared/forms/helpers';
import { hideModalPrimitive, showModalPrimitive } from '../../../shared/ui/modal';

(function initAuthLoginPage() {
    const form = document.querySelector('[data-js="auth-login-form"]');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const config = document.querySelector('[data-js="auth-login-config"]');
    const requestErrorMessage =
        config?.getAttribute('data-request-error') || 'İstek sırasında bir hata oluştu.';
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

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
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
                window.notify?.('success', data.message || requestErrorMessage);
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

    const changePasswordForm = document.getElementById('changePasswordModalForm');
    if (changePasswordForm instanceof HTMLFormElement) {
        changePasswordForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const submitButton =
                changePasswordForm.querySelector('button[type="submit"]') ||
                document.querySelector('button[form="changePasswordModalForm"]');

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
                }
            );
        });
    }

    document.addEventListener('click', (event) => {
        const toggleButton = event.target.closest('[data-action="toggle-password"], [data-toggle="password"]');
        if (!toggleButton) {
            return;
        }

        const passwordInput =
            form.querySelector('#password') || form.querySelector('input[name="password"]');

        if (!(passwordInput instanceof HTMLInputElement)) {
            return;
        }

        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';

        toggleButton.classList.toggle('is-on', isHidden);
        toggleButton.setAttribute('aria-pressed', isHidden ? 'true' : 'false');

        const showLabel =
            toggleButton.getAttribute('data-label-show') || 'Şifreyi göster';
        const hideLabel =
            toggleButton.getAttribute('data-label-hide') || 'Şifreyi gizle';

        toggleButton.setAttribute('aria-label', isHidden ? hideLabel : showLabel);
    });
})();
