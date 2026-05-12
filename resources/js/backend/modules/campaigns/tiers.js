function buildTierRow(index) {
    return `
        <tr class="tiered-row">
            <td><input type="number" name="rules[0][extra][tiers][${index}][min_quantity]" class="form-control" required form="campaign-form"></td>
            <td><input type="number" step="0.01" name="rules[0][extra][tiers][${index}][action_value]" class="form-control" required form="campaign-form"></td>
            <td>
                <select name="rules[0][extra][tiers][${index}][price_type]" class="form-control" form="campaign-form">
                    <option value="percent">Y&#252;zde</option>
                    <option value="fixed">Fiyat &#304;ndirimi</option>
                    <option value="net">Net Fiyat</option>
                </select>
            </td>
            <td><button type="button" class="btn btn-outline-danger remove-tier" data-action="remove-tier">-</button></td>
        </tr>
    `;
}

function findTierTableBody(trigger) {
    const parent = trigger.parentElement;
    if (!parent) {
        return null;
    }

    const siblingTable =
        Array.from(parent.children).find(
            (element) => element.tagName === 'TABLE' && element !== trigger
        ) || null;

    return siblingTable?.querySelector('tbody') || null;
}

function initCampaignTiers() {
    if (document.body.dataset.jsBoundCampaignTiers === '1') {
        return;
    }
    document.body.dataset.jsBoundCampaignTiers = '1';

    document.body.addEventListener('click', (event) => {
        const addTrigger =
            event.target.closest('[data-action="add-tier"]') ||
            event.target.closest('.add-tier');

        if (addTrigger) {
            event.preventDefault();

            const tbody = findTierTableBody(addTrigger);
            if (!tbody) {
                return;
            }

            const index = tbody.querySelectorAll('tr').length;
            tbody.insertAdjacentHTML('beforeend', buildTierRow(index));
            return;
        }

        const removeTrigger =
            event.target.closest('[data-action="remove-tier"]') ||
            event.target.closest('.remove-tier');

        if (!removeTrigger) {
            return;
        }

        event.preventDefault();
        removeTrigger.closest('tr')?.remove();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCampaignTiers);
} else {
    initCampaignTiers();
}

