function getQueryParams() {
    return Object.fromEntries(new URLSearchParams(window.location.search));
}

document.addEventListener('click', async (event) => {
    const downloadLinkBtn = event.target.closest('[data-download-file]');
    if (!downloadLinkBtn) {
        return;
    }

    event.preventDefault();
    window.setLoading?.(downloadLinkBtn, true);

    try {
        const baseUrl = downloadLinkBtn.dataset.downloadUrl;
        const fileName = downloadLinkBtn.dataset.fileName || 'dosya';
        const query = getQueryParams();

        const url = new URL(baseUrl, window.location.origin);
        Object.entries(query).forEach(([key, value]) => {
            if (value !== null && value !== '') {
                url.searchParams.set(key, value);
            }
        });

        const response = await fetch(url.toString(), { credentials: 'include' });
        if (!response.ok) {
            throw new Error('Dosya indirme başarısız.');
        }

        const blob = await response.blob();
        const blobUrl = window.URL.createObjectURL(blob);

        const anchor = document.createElement('a');
        anchor.href = blobUrl;
        anchor.download = fileName;

        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
        window.URL.revokeObjectURL(blobUrl);
    } catch (error) {
        console.error('İndirme hatası:', error);
        window.notify?.('error', 'Dosya indirilemedi. Lütfen tekrar deneyin.');
    } finally {
        window.setLoading?.(downloadLinkBtn, false);
    }
});
