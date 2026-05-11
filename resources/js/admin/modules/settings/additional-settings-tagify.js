function initAdditionalSettingsTagify() {
    const inputs = document.querySelectorAll('.tagify');
    if (!inputs.length) {
        return;
    }

    if (typeof window.Tagify !== 'function') {
        return;
    }

    inputs.forEach((input) => {
        if (input.dataset.jsBoundTagify === '1') {
            return;
        }

        input.dataset.jsBoundTagify = '1';
        new window.Tagify(input);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdditionalSettingsTagify);
} else {
    initAdditionalSettingsTagify();
}
