function initCampaignDateFilter() {
    const useDateFilter =
        document.querySelector('[data-js="campaign-use-date-filter"]') ||
        document.getElementById('use_date_filter');
    const dateFields =
        document.querySelector('[data-js="campaign-date-fields"]') ||
        document.getElementById('date-fields');

    if (!useDateFilter || !dateFields) {
        return;
    }

    if (useDateFilter.dataset.jsBoundDateFilter === '1') {
        return;
    }
    useDateFilter.dataset.jsBoundDateFilter = '1';

    const sync = () => {
        dateFields.style.display = useDateFilter.checked ? 'block' : 'none';
    };

    sync();
    useDateFilter.addEventListener('change', sync);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCampaignDateFilter);
} else {
    initCampaignDateFilter();
}

