document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-action="erp-import"]');
    if (!button) {
        return;
    }

    const url = button.dataset.url;
    if (!url) {
        return;
    }

    window.setLoading?.(button, true);

    if (!window.axiosRequest || typeof window.axiosRequest.post !== 'function') {
        window.notify?.('error', 'Iceri aktarma basarisiz');
        window.setLoading?.(button, false);
        return;
    }

    window.axiosRequest.post(url, {}, {
        onSuccess: (result) => {
            window.notify?.('success', result.message || 'Iceri aktarma baslatildi');
        },
        onError: () => {
            window.notify?.('error', 'Iceri aktarma basarisiz');
        },
        onComplete: () => {
            window.setLoading?.(button, false);
        },
    });
});
