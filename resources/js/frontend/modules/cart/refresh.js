/* global window */

let campaignModalRefreshTimer = null;

function refreshCartSection(section) {
    const target = document.querySelector(`[data-js="cart-${section}"]`);
    if (!target) {
        return Promise.resolve();
    }

    return window.axios.get(`/sepet/${section}`)
        .then((response) => {
            target.innerHTML = response.data;
        });
}

function refreshCampaignModalBody() {
    const modalEl = document.getElementById('cartCampaignModal');
    if (!modalEl) {
        return;
    }

    const body = modalEl.querySelector('[data-js="cart-campaign-modal-body"]');
    if (!body) {
        return;
    }

    clearTimeout(campaignModalRefreshTimer);
    campaignModalRefreshTimer = setTimeout(() => {
        fetch('/sepet/campaign/modal/body')
            .then((response) => response.text())
            .then((html) => {
                body.innerHTML = html;
            })
            .catch(console.error);
    }, 150);
}

async function updateAllCarts() {
    const sections = ['list', 'summary', 'header', 'count'];

    await Promise.all(sections.map((section) => refreshCartSection(section)));

    refreshCampaignModalBody();
}

window.updateAllCarts = updateAllCarts;
window.refreshCampaignModalBody = refreshCampaignModalBody;

export { refreshCartSection, refreshCampaignModalBody, updateAllCarts };
