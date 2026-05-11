import { applyCurrencyNotePrefix } from './shared';

function registerCashesCreateBindings() {
    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLSelectElement)) {
            return;
        }

        if (
            target.getAttribute('data-action') !== 'cashes-currency-change' &&
            target.id !== 'currency_type'
        ) {
            return;
        }

        const form = target.closest('[data-js="cashes-add-edit-form"]');
        if (!form) {
            return;
        }

        const notesElement = form.querySelector('#notes');
        const notePrefix = target.getAttribute('data-note-prefix') || 'Nakit';

        applyCurrencyNotePrefix({
            selectElement: target,
            notesElement,
            notePrefix,
        });
    });
}

export { registerCashesCreateBindings };
