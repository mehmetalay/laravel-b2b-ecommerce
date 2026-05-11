document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.js-search-autocomplete');

    forms.forEach(function (form) {
        const input = form.querySelector('.js-search-input');
        const box = form.querySelector('.js-search-suggestion-box');
        const suggestionUrl = form.dataset.suggestionUrl;

        let timer = null;

        input.addEventListener('input', function () {
            const q = input.value.trim();

            clearTimeout(timer);

            if (q.length < 2) {
                box.classList.remove('active');
                box.innerHTML = '';
                return;
            }

            timer = setTimeout(function () {
                axiosRequest.get(suggestionUrl, { q }, {
                    onSuccess: function (result) {
                        renderSuggestions(result.data || [], q);
                    }
                });
            }, 300);
        });

        function renderSuggestions(products, q) {
            if (!products.length) {
                box.classList.remove('active');
                box.innerHTML = '';
                return;
            }

            let html = '';

            products.forEach(function (product) {
                html += `
                    <a href="${product.url}" class="search-suggestion-item">
                        <img src="${product.image}" alt="${escapeHtml(product.name)}">
                        <div>
                            <div class="search-suggestion-title">${highlight(product.name, q)}</div>
                            ${product.sku ? `<div class="search-suggestion-sku">Stok kodu: ${escapeHtml(product.sku)}</div>` : ''}
                        </div>
                    </a>
                `;
            });

            html += `
                <a href="${form.action}?q=${encodeURIComponent(q)}" class="search-suggestion-all">
                    TÜM ÜRÜNLERİ GÖSTER
                </a>
            `;

            box.innerHTML = html;
            box.classList.add('active');
        }

        document.addEventListener('click', function (e) {
            if (!form.contains(e.target)) {
                box.classList.remove('active');
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                box.classList.remove('active');
            }
        });
    });

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function highlight(text, keyword) {
        const safeText = escapeHtml(text);
        const safeKeyword = escapeHtml(keyword);

        if (!safeKeyword) return safeText;

        const regex = new RegExp(`(${safeKeyword})`, 'gi');

        return safeText.replace(regex, '<strong>$1</strong>');
    }
});
