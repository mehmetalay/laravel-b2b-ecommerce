/* global window */

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-component="current-account"]');
    const searchInput = document.querySelector('[data-target="current-account-search"]');
    const listContainer = document.querySelector('[data-target="current-account-list"]');

    if (!modal || !listContainer) {
        return;
    }

    const modalInstance = window.bootstrap?.Modal?.getOrCreateInstance(modal);

    const renderList = (response) => {
        listContainer.innerHTML = response?.html ?? response?.data ?? '';
    };

    const fetchList = (url = '/current-accounts') => {
        const search = searchInput?.value || '';

        window.axiosRequest.get(url, { search }, {
            onSuccess: (response) => {
                renderList(response);
            },
        });
    };

    const debounce = (callback, delay = 500) => {
        let timeoutId;

        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => callback(...args), delay);
        };
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-action="current-account"]');
        if (!trigger) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        window.axiosRequest.get('/current-accounts', {}, {
            onSuccess: (response) => {
                if (searchInput) {
                    searchInput.value = '';
                }

                renderList(response);
                modalInstance?.show();
            },
        });
    });

    if (searchInput) {
        searchInput.addEventListener('keyup', debounce(() => fetchList(), 500));
    }

    document.addEventListener('click', (event) => {
        const paginationLink = event.target.closest('[data-target="current-account-list"] .pagination a');
        if (!paginationLink) {
            return;
        }

        event.preventDefault();
        fetchList(paginationLink.getAttribute('href'));
    });

    document.addEventListener('click', (event) => {
        const selectButton = event.target.closest('[data-action="current-account-select"]');
        if (!selectButton) {
            return;
        }

        event.preventDefault();

        const currentAccountId = selectButton.dataset.id;
        if (!currentAccountId) {
            return;
        }

        window.setLoading?.(selectButton, true);

        window.axiosRequest.post(`/current-accounts/${currentAccountId}/select`, {}, {
            onSuccess: () => {
                modalInstance?.hide();
                window.location.reload();
            },
            onError: (payload) => {
                const type = payload?.status === 'warning' ? 'warning' : 'error';
                const message = payload?.message || 'İşlem sırasında bir hata oluştu.';
                window.notify?.(type, message);
            },
            onComplete: () => {
                window.setLoading?.(selectButton, false);
            },
        });
    });
});
