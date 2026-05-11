/* global window */

function setLoading(button, isLoading) {
    const el = button?.jquery ? button[0] : button;
    if (!el) {
        return;
    }

    el.classList[isLoading ? 'add' : 'remove']('btn-loading');
    el.disabled = isLoading;
}

window.setLoading = setLoading;

