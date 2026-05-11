/* global window */

function notify(type, message) {
    let container = document.querySelector('.notifications');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notifications';
        document.body.appendChild(container);
    }

    const el = document.createElement('div');
    el.className = `notification ${type}`;
    el.innerText = message;

    container.appendChild(el);

    setTimeout(() => {
        el.remove();
    }, 4000);
}

window.notify = notify;

