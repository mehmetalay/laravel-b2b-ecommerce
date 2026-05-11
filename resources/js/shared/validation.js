/* global window */

function handleFormValidationErrors(errors, form) {
    let firstInvalidInput = null;

    form.querySelectorAll('[name]').forEach((input) => {
        const name = input.name;
        const errorMessage = errors[name]?.[0];

        if (errorMessage) {
            input.classList.add('is-invalid');

            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.innerText = errorMessage;

            input.parentNode.appendChild(errorDiv);

            if (!firstInvalidInput) {
                firstInvalidInput = input;
            }
        } else {
            input.classList.add('is-valid');
        }
    });

    if (firstInvalidInput) {
        firstInvalidInput.focus();
        firstInvalidInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function clearFormErrors(form) {
    form.querySelectorAll('input, select, textarea').forEach((input) => {
        input.classList.remove('is-invalid', 'is-valid');

        const existingError = input.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    });
}

window.handleFormValidationErrors = handleFormValidationErrors;
window.clearFormErrors = clearFormErrors;

