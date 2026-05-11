document.addEventListener('click', (event) => {
    const trigger = event.target.closest("[data-modal-trigger='send-b2b-mail']");
    if (trigger) {
        event.preventDefault();

        const dealerEmail = trigger.dataset.dealerEmail || '';
        const mailType = trigger.dataset.mailType;
        const refId = trigger.dataset.refId;

        const modal = document.querySelector("[data-modal='send-b2b-mail']");
        if (!modal) {
            return;
        }

        const recipientEmailInput = modal.querySelector('[data-recipient-email]');
        const mailTypeInput = modal.querySelector('[data-mail-type]');
        const refIdInput = modal.querySelector('[data-ref-id]');
        const modalTitle = modal.querySelector('[data-modal-title]');

        if (recipientEmailInput) {
            recipientEmailInput.value = dealerEmail;
        }
        if (mailTypeInput) {
            mailTypeInput.value = mailType || '';
        }
        if (refIdInput) {
            refIdInput.value = refId || '';
        }

        const titleMap = {
            order: 'Sipariş Maili Gönder',
            payment: 'Ödeme Dekontu Gönder',
            statement: 'Müşteri Ekstresi Gönder',
        };

        if (modalTitle) {
            modalTitle.textContent = titleMap[mailType] || 'Mail Gönder';
        }

        if (window.bootstrap?.Modal) {
            new window.bootstrap.Modal(modal).show();
        }
        return;
    }

    const sendBtn = event.target.closest('[data-send-mail]');
    if (!sendBtn) {
        return;
    }

    const modal = event.target.closest('[data-modal]');
    const form = modal?.querySelector('[data-mail-form]');
    if (!modal || !form) {
        return;
    }

    const recipientEmail = form.querySelector('[data-recipient-email]')?.value || '';
    const mailType = form.querySelector('[data-mail-type]')?.value || '';
    const refId = form.querySelector('[data-ref-id]')?.value || '';

    sendBtn.disabled = true;
    const oldText = sendBtn.textContent;
    sendBtn.textContent = 'Gönderiliyor...';

    window.axiosRequest?.post('/mail/send', {
        recipient_email: recipientEmail,
        type: mailType,
        ref_id: refId,
    }, {
        onSuccess: (result) => {
            window.notify?.('success', result?.message || 'Mail gönderildi.');
            window.bootstrap?.Modal.getInstance(modal)?.hide();
        },
        onValidationError: (errors) => {
            const firstErrorGroup = Object.values(errors || {})[0];
            const firstError = Array.isArray(firstErrorGroup) ? firstErrorGroup[0] : firstErrorGroup;
            if (firstError) {
                window.notify?.('error', firstError);
            }
        },
        onError: (payload) => {
            if (payload?.status === 'success' || payload?.success) {
                window.notify?.('success', payload?.message || 'Mail gönderildi.');
                window.bootstrap?.Modal.getInstance(modal)?.hide();
                return;
            }

            window.notify?.('error', payload?.message || payload?.error || 'Mail gönderilemedi.');
        },
        onComplete: () => {
            sendBtn.disabled = false;
            sendBtn.textContent = oldText;
        },
    });
});
