function registerCampaignHandlers(context, giftModalController) {
    document.addEventListener('click', (event) => {
        const applyCampaignBtn = event.target.closest('[data-selector="apply-campaign"]');
        if (applyCampaignBtn) {
            event.preventDefault();
            const campaignId = applyCampaignBtn.dataset.campaignId;

            context.request.post(context.routes.applyCampaign, { campaign_id: campaignId }, {
                onSuccess: (response) => {
                    context.updateAllCartsSafe();
                    if (!response.requires_gift_selection) {
                        return;
                    }

                    const cartModalEl = document.getElementById('cartCampaignModal');
                    if (cartModalEl && cartModalEl.classList.contains('show') && window.bootstrap) {
                        cartModalEl.addEventListener('hidden.bs.modal', () => giftModalController.openGiftSelectionModal(response.campaign_id), { once: true });
                        window.bootstrap.Modal.getOrCreateInstance(cartModalEl).hide();
                        return;
                    }

                    giftModalController.openGiftSelectionModal(response.campaign_id);
                },
                onError: (payload) => {
                    window.notify?.('error', payload?.message || context.messages.campaignApplyFailed);
                    context.updateAllCartsSafe();
                },
                onValidationError: (errors) => {
                    context.notifyValidationError(errors, context.messages.campaignApplyFailed);
                },
            });
            return;
        }

        const removeAllBtn = event.target.closest('[data-selector="remove-all-campaigns"]');
        if (removeAllBtn) {
            event.preventDefault();
            context.request.post(context.routes.removeAllCampaigns, {}, {
                onSuccess: () => {
                    context.updateAllCartsSafe();
                },
                onError: (payload) => {
                    window.notify?.('error', payload?.message || context.messages.requestError);
                    context.updateAllCartsSafe();
                },
            });
            return;
        }

        const removeSingleBtn = event.target.closest('[data-selector="remove-single-campaign"]');
        if (!removeSingleBtn) {
            return;
        }

        event.preventDefault();
        const campaignId = removeSingleBtn.dataset.campaignId;
        context.request.post(context.routes.removeSingleCampaign, { campaign_id: campaignId }, {
            onSuccess: () => {
                context.updateAllCartsSafe();
            },
            onError: (payload) => {
                window.notify?.('error', payload?.message || context.messages.requestError);
                context.updateAllCartsSafe();
            },
        });
    });
}

export { registerCampaignHandlers };
