document.addEventListener('DOMContentLoaded', () => {
    const code = window.location.hash.slice(1);

    if (!code) return;

    const targetElement = document.querySelector(`[data-code="${code}"]`);

    if (!targetElement) return;

    const offset = 250;

    const top =
        targetElement.getBoundingClientRect().top +
        window.pageYOffset -
        offset;

    window.scrollTo({
        top,
        behavior: 'smooth'
    });
});

function setFilter(value) {
    const form = document.getElementById('product-filter');

    if (!form) return;

    let hiddenInput = form.querySelector('input[name="sorting"]');

    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'sorting';

        form.appendChild(hiddenInput);
    }

    hiddenInput.value = value;

    form.submit();
}

function setViewType(type) {
    const url = new URL(window.location.href);

    url.searchParams.set('view', type);

    window.location.href = url;
}