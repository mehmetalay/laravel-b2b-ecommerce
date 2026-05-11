/* global window */

import { hideModalPrimitive, showModalPrimitive } from './ui/modal';

window.showModal = (name) => {
    showModalPrimitive(name, {
        resolveBy: (value) => document.querySelector(`[data-modal="${value}"]`),
    });
};

window.hideModal = (name) => {
    hideModalPrimitive(name, {
        resolveBy: (value) => document.querySelector(`[data-modal="${value}"]`),
    });
};
