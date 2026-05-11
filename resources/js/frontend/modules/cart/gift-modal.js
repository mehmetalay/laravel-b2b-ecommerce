function createGiftModalController(context) {
    const getGiftModalElement = () =>
        document.querySelector('[data-js="free-product-gift-modal"]') ||
        document.getElementById('freeProductGiftModal');

    const createModalQueries = (modalEl) => {
        const one = (primarySelector, fallbackSelector) =>
            modalEl.querySelector(primarySelector) || modalEl.querySelector(fallbackSelector);

        const all = (primarySelector, fallbackSelector) => {
            const primaryList = modalEl.querySelectorAll(primarySelector);
            if (primaryList.length > 0) {
                return Array.from(primaryList);
            }

            return Array.from(modalEl.querySelectorAll(fallbackSelector));
        };

        return {
            limitInput: () => one('[data-role="gift-limit"]', '#gift-limit'),
            totalLabel: () => one('[data-role="gift-total-selected"]', '#gift-total-selected'),
            remainingLabel: () => one('[data-role="gift-remaining-text"]', '#gift-remaining-text'),
            warning: () => one('[data-role="gift-selection-warning"]', '#gift-selection-warning'),
            saveButton: () => one('[data-role="save-free-product-gifts"]', '#save-free-product-gifts'),
            giftForm: () => one('[data-role="free-product-gift-form"]', '#free-product-gift-form'),
            qtyInputs: () => all('[data-role="gift-qty-input"]', '.gift-qty-input'),
            plusButtons: () =>
                all(
                    '[data-role="gift-qty-button"][data-action="plus"]',
                    '.gift-qty-btn[data-action="plus"]'
                ),
            qtyInputByGiftId: (giftId) =>
                one(
                    `[data-role="gift-qty-input"][data-gift-id="${giftId}"]`,
                    `.gift-qty-input[data-gift-id="${giftId}"]`
                ),
        };
    };

    const parseLimit = (queries) =>
        Number.parseInt(queries.limitInput()?.value || '0', 10) || 0;

    const calculateGiftTotal = (queries) => {
        let total = 0;
        queries.qtyInputs().forEach((input) => {
            total += Number.parseInt(input.value || '0', 10) || 0;
        });

        const totalLabel = queries.totalLabel();
        if (totalLabel) {
            totalLabel.textContent = String(total);
        }

        return total;
    };

    const updateGiftUI = (queries) => {
        const limit = parseLimit(queries);
        const total = calculateGiftTotal(queries);
        const remainingText = queries.remainingLabel();

        if (remainingText) {
            const remaining = limit - total;
            if (remaining > 0) {
                remainingText.textContent = `(${remaining} adet daha seçmelisiniz)`;
                remainingText.classList.remove('text-success');
                remainingText.classList.add('text-warning');
            } else {
                remainingText.textContent = '(limit doldu)';
                remainingText.classList.remove('text-warning');
                remainingText.classList.add('text-success');
            }
        }

        const warning = queries.warning();
        if (warning) {
            warning.style.display = total > limit ? '' : 'none';
        }

        queries.plusButtons().forEach((button) => {
            button.disabled = total >= limit;
        });

        const saveButton = queries.saveButton();
        if (saveButton) {
            saveButton.disabled = total !== limit;
        }

        return total;
    };

    const setGiftQty = (queries, giftId, nextValue) => {
        const input = queries.qtyInputByGiftId(giftId);
        if (!input) {
            return;
        }

        const limit = parseLimit(queries);
        const newValue = Math.max(0, Number.parseInt(nextValue || '0', 10) || 0);
        const currentTotal = calculateGiftTotal(queries);
        const currentValue = Number.parseInt(input.value || '0', 10) || 0;
        const nextTotal = currentTotal - currentValue + newValue;
        const warning = queries.warning();

        if (limit > 0 && nextTotal > limit) {
            if (warning) {
                warning.style.display = '';
            }
            return;
        }

        if (warning) {
            warning.style.display = 'none';
        }

        input.value = String(newValue);
        calculateGiftTotal(queries);
    };

    const openGiftSelectionModal = (campaignId) => {
        if (!window.axios || !campaignId) {
            return;
        }

        window.axios
            .get(context.routes.freeProductModal, { params: { campaign_id: campaignId } })
            .then((response) => {
                const html = typeof response.data === 'string' ? response.data : '';
                if (!html) {
                    return;
                }

                const mount = document.getElementById('global-modals') || document.body;
                getGiftModalElement()?.remove();
                mount.insertAdjacentHTML('beforeend', html);

                const modalEl = getGiftModalElement();
                if (!modalEl || !window.bootstrap) {
                    return;
                }

                const queries = createModalQueries(modalEl);
                const modal = new window.bootstrap.Modal(modalEl);
                modal.show();
                calculateGiftTotal(queries);
                updateGiftUI(queries);

                modalEl.addEventListener('input', (event) => {
                    const target = event.target;
                    if (!(target instanceof HTMLInputElement)) {
                        return;
                    }

                    if (!target.name || !target.name.startsWith('gifts[')) {
                        return;
                    }

                    calculateGiftTotal(queries);
                    updateGiftUI(queries);
                });

                queries.saveButton()?.addEventListener('click', () => {
                    const total = updateGiftUI(queries);
                    const limit = parseLimit(queries);

                    if (total !== limit) {
                        const message = context.messages.giftCountRequiredTemplate.replace(
                            '{limit}',
                            String(limit)
                        );
                        window.notify?.('error', message);
                        return;
                    }

                    const form = queries.giftForm();
                    if (!(form instanceof HTMLFormElement)) {
                        return;
                    }

                    const formData = new FormData(form);
                    context.request.post(context.routes.selectGifts, formData, {
                        onSuccess: () => {
                            modal.hide();
                            context.updateAllCartsSafe();
                        },
                        onError: (payload) => {
                            window.notify?.(
                                'error',
                                payload?.message || context.messages.giftSelectSaveFailed
                            );
                        },
                        onValidationError: (errors) => {
                            context.notifyValidationError(
                                errors,
                                context.messages.giftSelectSaveFailed
                            );
                        },
                    });
                });
            })
            .catch(() => {
                window.notify?.('error', context.messages.giftSelectSaveFailed);
            });
    };

    const registerEvents = () => {
        document.addEventListener('click', (event) => {
            const selectGiftsBtn = event.target.closest('[data-selector="select-gifts"]');
            if (selectGiftsBtn) {
                event.preventDefault();
                const campaignId = selectGiftsBtn.dataset.campaignId;
                const cartModalEl = document.getElementById('cartCampaignModal');

                if (cartModalEl && cartModalEl.classList.contains('show') && window.bootstrap) {
                    cartModalEl.addEventListener(
                        'hidden.bs.modal',
                        () => openGiftSelectionModal(campaignId),
                        { once: true }
                    );
                    window.bootstrap.Modal.getOrCreateInstance(cartModalEl).hide();
                    return;
                }

                openGiftSelectionModal(campaignId);
                return;
            }

            const addSameProductGiftBtn = event.target.closest(
                '[data-selector="add-same-product-gift"]'
            );
            if (addSameProductGiftBtn) {
                event.preventDefault();
                const cartId = addSameProductGiftBtn.dataset.cartId;
                const campaignId = addSameProductGiftBtn.dataset.campaignId;

                context.request.post(
                    context.routes.addSameProductGift,
                    { cart_id: cartId, campaign_id: campaignId },
                    {
                        onSuccess: () => {
                            context.updateAllCartsSafe();
                        },
                        onError: (payload) => {
                            window.notify?.('error', payload?.message || context.messages.requestError);
                            context.updateAllCartsSafe();
                        },
                    }
                );
                return;
            }

            const qtyButton = event.target.closest(
                '[data-role="gift-qty-button"], #freeProductGiftModal .gift-qty-btn'
            );
            if (!qtyButton) {
                return;
            }

            const modalEl = qtyButton.closest('[data-js="free-product-gift-modal"]') || getGiftModalElement();
            if (!modalEl) {
                return;
            }

            const queries = createModalQueries(modalEl);
            const giftId = qtyButton.dataset.giftId;
            const action = qtyButton.dataset.action;
            const input = queries.qtyInputByGiftId(giftId);
            const currentValue = Number.parseInt(input?.value || '0', 10) || 0;

            if (action === 'plus') {
                setGiftQty(queries, giftId, currentValue + 1);
            }

            if (action === 'minus') {
                setGiftQty(queries, giftId, currentValue - 1);
            }

            updateGiftUI(queries);
        });

        document.addEventListener('shown.bs.modal', (event) => {
            const modalEl = event.target;
            if (!(modalEl instanceof HTMLElement)) {
                return;
            }

            if (
                modalEl.getAttribute('data-js') !== 'free-product-gift-modal' &&
                modalEl.id !== 'freeProductGiftModal'
            ) {
                return;
            }

            const queries = createModalQueries(modalEl);
            calculateGiftTotal(queries);
            const warning = queries.warning();
            if (warning) {
                warning.style.display = 'none';
            }
        });
    };

    return {
        openGiftSelectionModal,
        registerEvents,
    };
}

export { createGiftModalController };
