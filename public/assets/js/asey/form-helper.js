const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function csrfFetch(url, options = {}) {
    const isFormData = options.body instanceof FormData;

    options.headers = {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {})
    };

    if (!isFormData) {
        options.headers['Content-Type'] = 'application/json';
        options.headers['Accept'] = 'application/json';
    }

    options.credentials = 'same-origin';

    return fetch(url, options);
}

document.addEventListener("DOMContentLoaded", () => {
    applyPhoneMask();
    applyIdentityNumberMask();
});

// Telefon Maskesi
function applyPhoneMask(container = document) {
    container.querySelectorAll('[data-mask-phone]').forEach(input => {
        Inputmask({ mask: "(999) 999-9999" }).mask(input);

        input.addEventListener('keyup', () => {
            const raw = input.inputmask.unmaskedvalue();

            if (raw.startsWith('90') || raw.startsWith('0')) {
                input.value = '';
            }
        });
    });
}

// TC Kimlik No Maskesi
function applyIdentityNumberMask(container = document) {
    container.querySelectorAll('[data-mask-identity-number]').forEach(input => {
        Inputmask({ mask: "99999999999" }).mask(input);
    });
}

// sendRequest
async function sendRequest({
    url,
    method = 'POST',
    data = {},
    onSuccess,
    onError,
    onValidationError,
    onComplete
}) {
    try {
        let body;
        let headers = {};

        const isDelete = method.toUpperCase() === 'DELETE';
        const hasFile = Object.values(data).some(value => value instanceof File);
        const isJson = !hasFile && !isDelete;

        if (isJson) {
            body = JSON.stringify(data);
            headers['Content-Type'] = 'application/json';
        } else {
            body = new FormData();
            // Eğer DELETE ise _method ekle
            if (isDelete) {
                body.append('_method', 'DELETE');
            }

            for (const key in data) {
                body.append(key, data[key]);
            }
        }

        const response = await csrfFetch(url, {
            method: isDelete ? 'POST' : method,
            headers,
            body
        });

        const result = await response.json();

        if (result.success) {
            onSuccess?.(result);
        } else if (result.errors) {
            onValidationError?.(result.errors);
        } else {
            notify(result.warning ? 'warning' : 'error', result.message);
            onError?.(result);
        }
    } catch (err) {
        notify('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
        onError?.(err);
    } finally {
        onComplete?.();
    }
}

// showAlert
function showAlert(message, type, showButton = true) {
    Swal.fire({
        icon: type,
        title: message,
        showConfirmButton: showButton,
        confirmButtonText: 'Tamam',
        customClass: {
            confirmButton: 'swal2-custom-button',
            cancelButton: 'swal2-custom-button'
        },
        timer: showButton ? undefined : 3000,
        timerProgressBar: !showButton
    });
}

// Validation
function handleFormValidationErrors(errors, form) {
    let firstInvalidInput = null;

    form.querySelectorAll('[name]').forEach(input => {
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

// clearFormErrors
function clearFormErrors(form) {
    form.querySelectorAll('input, select, textarea').forEach(input => {
        input.classList.remove('is-invalid', 'is-valid');

        const existingError = input.parentNode.querySelector('.invalid-feedback');
        if (existingError) existingError.remove();
    });
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var previewSelector = $(input).attr("data-preview");
            var previewElement = $(previewSelector);

            if (previewElement.length) {
                previewElement.css("background-image", "url(" + e.target.result + ")").hide().fadeIn(650);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).on("change", "input[type='file']", function () {
    readURL(this);
});

$(document).on("click", "[data-image-delete]", function () {
    var inputSelector = $(this).data("target");
    var previewSelector = $(this).data("preview");
    $(previewSelector).css("background-image", "none");
    $(inputSelector).val("");
});

// toggle fonksiyonu
function toggleAllTargets() {
    $('[data-toggle-id]').hide();

    $('[data-toggle-target]').each(function () {
        const selectedOption = $(this).find('option:selected');
        const targetSelector = selectedOption.data('show-target');

        if (targetSelector) {
            $('[data-toggle-id="' + targetSelector + '"]').show();
        }
    });

    $('[data-toggle-checkbox]').each(function () {
        const checkbox = $(this);
        const targetSelector = checkbox.data('toggle-checkbox');

        if (checkbox.is(':checked')) {
            $('[data-toggle-id="' + targetSelector + '"]').show();
        }
    });

    $('[data-toggle-radio]').each(function () {
        const radioName = $(this).attr('name');
        const selectedRadio = $('input[name="' + radioName + '"]:checked');
        const targetSelector = selectedRadio.data('toggle-radio');

        if (targetSelector) {
            $('[data-toggle-id="' + targetSelector + '"]').show();
        }
    });
}

$('[data-toggle-target]').on('change', toggleAllTargets);
$('[data-toggle-checkbox]').on('change', toggleAllTargets);
$('[data-toggle-radio]').on('change', toggleAllTargets);