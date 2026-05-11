/* global window */

function customConfirm(message) {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';

        const box = document.createElement('div');
        box.className = 'confirm-box';

        box.innerHTML = `
            <p>${message}</p>
            <div class="confirm-buttons">
                <button class="yes" data-selector="yes">Evet</button>
                <button class="no" data-selector="no">Hayır</button>
            </div>
        `;

        overlay.appendChild(box);
        document.body.appendChild(overlay);

        box.querySelector('[data-selector="yes"]').addEventListener('click', () => {
            overlay.remove();
            resolve(true);
        });

        box.querySelector('[data-selector="no"]').addEventListener('click', () => {
            overlay.remove();
            resolve(false);
        });
    });
}

window.customConfirm = customConfirm;

