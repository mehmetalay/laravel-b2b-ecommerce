function firstValidationMessage(errors) {
    if (!errors || typeof errors !== 'object') {
        return null;
    }

    const firstKey = Object.keys(errors)[0];
    if (!firstKey) {
        return null;
    }

    const message = errors[firstKey];
    return Array.isArray(message) ? message[0] : message;
}

function serializeForm(form) {
    return Object.fromEntries(new FormData(form).entries());
}

export { firstValidationMessage, serializeForm };
