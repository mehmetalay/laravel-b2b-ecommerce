function resolveModalElement(target, { resolveBy } = {}) {
    if (target instanceof HTMLElement) {
        return target;
    }

    if (typeof target !== 'string') {
        return null;
    }

    if (typeof resolveBy === 'function') {
        return resolveBy(target);
    }

    return document.querySelector(target);
}

function showModalPrimitive(target, options) {
    const modalElement = resolveModalElement(target, options);
    if (!modalElement) {
        return;
    }

    const modalController = resolveBootstrapModalController(modalElement);
    if (modalController && typeof modalController.show === 'function') {
        modalController.show();
        return;
    }

    if (window.$ && typeof window.$.fn?.modal === 'function') {
        window.$(modalElement).modal('show');
    }
}

function hideModalPrimitive(target, options) {
    const modalElement = resolveModalElement(target, options);
    if (!modalElement) {
        return;
    }

    const modalController = resolveBootstrapModalController(modalElement);
    if (modalController && typeof modalController.hide === 'function') {
        modalController.hide();
        return;
    }

    if (window.$ && typeof window.$.fn?.modal === 'function') {
        window.$(modalElement).modal('hide');
    }
}

function resolveBootstrapModalController(modalElement) {
    const Modal = window.bootstrap?.Modal;
    if (!Modal) {
        return null;
    }

    if (typeof Modal.getOrCreateInstance === 'function') {
        return Modal.getOrCreateInstance(modalElement);
    }

    if (typeof Modal === 'function') {
        return new Modal(modalElement);
    }

    return null;
}

export { hideModalPrimitive, resolveModalElement, showModalPrimitive };
