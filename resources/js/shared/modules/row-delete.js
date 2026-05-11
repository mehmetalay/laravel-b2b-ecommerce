document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-selector="row-delete"]');
    if (!button) {
        return;
    }

    const url = button.dataset.url;
    if (!url) {
        return;
    }

    window.setLoading?.(button, true);

    const confirmed = await window.customConfirm?.('Bu kaydi silmek istediginize emin misiniz?');
    if (!confirmed) {
        window.setLoading?.(button, false);
        return;
    }

    const row = button.closest('tr');

    if (!window.axiosRequest || typeof window.axiosRequest.delete !== 'function') {
        window.notify?.('error', 'Silme islemi su anda kullanilamiyor.');
        window.setLoading?.(button, false);
        return;
    }

    window.axiosRequest.delete(url, {}, {
        onSuccess: () => {
            row?.remove();
            window.notify?.('success', 'Kayit basariyla silindi.');
        },
        onError: (errorPayload) => {
            window.notify?.('error', errorPayload?.message || 'Silme islemi basarisiz.');
        },
        onComplete: () => {
            window.setLoading?.(button, false);
        },
    });
});
