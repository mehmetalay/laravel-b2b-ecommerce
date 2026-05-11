document.addEventListener('click', async (event) => {
    const logoutTrigger = event.target.closest('[data-selector="logout"]');
    if (!logoutTrigger) {
        return;
    }

    event.preventDefault();
    const url = logoutTrigger.getAttribute('data-url') || '/logout';

    const isSuccess = await autoLogout(url);
    if (isSuccess) {
        window.location.href = '/giris';
    }
});

async function autoLogout(url) {
    if (!window.axiosRequest || typeof window.axiosRequest.post !== 'function') {
        window.notify?.('error', 'Cikis islemi su anda kullanilamiyor.');
        return false;
    }

    let hasError = false;

    try {
        await window.axiosRequest.post(url, {}, {
            onSuccess: (result) => {
                console.log('Oturum kapatildi:', result);
            },
            onError: (errorPayload) => {
                hasError = true;
                console.error('Logout basarisiz:', errorPayload);
                window.notify?.('error', errorPayload?.message || 'Cikis islemi basarisiz.');
            },
        });
    } catch (error) {
        hasError = true;
        console.error('Logout sirasinda hata:', error);
        window.notify?.('error', 'Cikis sirasinda bir hata olustu.');
    }

    return !hasError;
}
