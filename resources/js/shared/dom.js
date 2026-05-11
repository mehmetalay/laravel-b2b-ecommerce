/* global window */

function qs(selector, parent = document) {
    return parent.querySelector(selector);
}

function qsa(selector, parent = document) {
    return Array.from(parent.querySelectorAll(selector));
}

function removeEmptyInputs(form) {
    Array.from(form.elements).forEach((el) => {
        if (!el.value) {
            el.removeAttribute('name');
        }
    });
}

function getQueryParams() {
    return Object.fromEntries(new URLSearchParams(window.location.search));
}

function toggleAllByDataTarget() {
    qsa('[data-toggle-id]').forEach((el) => {
        el.style.display = 'none';
    });

    qsa('[data-toggle]').forEach((el) => {
        let target = null;

        if (el.tagName === 'SELECT') {
            target = el.selectedOptions[0]?.dataset.target;
        } else if (el.type === 'checkbox' && el.checked) {
            target = el.dataset.target;
        } else if (el.type === 'radio' && el.checked) {
            target = el.dataset.target;
        }

        if (target) {
            const targetEl = qs(`[data-toggle-id="${target}"]`);
            if (targetEl) {
                targetEl.style.display = 'block';
            }
        }
    });
}

function bindDataToggleEvents() {
    document.addEventListener('change', (e) => {
        if (e.target.matches('[data-toggle]')) {
            toggleAllByDataTarget();
        }
    });
}

window.qs = qs;
window.qsa = qsa;
window.removeEmptyInputs = removeEmptyInputs;
window.getQueryParams = getQueryParams;
window.toggleAllByDataTarget = toggleAllByDataTarget;
window.bindDataToggleEvents = bindDataToggleEvents;

