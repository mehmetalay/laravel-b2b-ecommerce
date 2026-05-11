export type TableQueryState = {
    search: string;
    category_id: string;
    brand_id: string;
    status: string;
    stock_status: string;
    page: number;
    per_page: number;
};

export type HistoryMode = 'push' | 'replace' | 'none';

type QuerySyncOptions = {
    stockKey?: string;
    stockFallbackKeys?: string[];
};

const toPositiveInt = (value: string | null, fallback: number): number => {
    const parsed = Number(value ?? fallback);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
};

export const readTableQueryState = (defaultPerPage: number, options: QuerySyncOptions = {}): TableQueryState => {
    const params = new URLSearchParams(window.location.search);
    const stockKey = options.stockKey || 'stock_status';
    const fallbackKeys = Array.isArray(options.stockFallbackKeys) ? options.stockFallbackKeys : [];
    const stockValue = params.get(stockKey)
        || fallbackKeys.map((key) => params.get(key) || '').find((value) => value !== '')
        || '';

    return {
        search: params.get('search') || '',
        category_id: params.get('category_id') || '',
        brand_id: params.get('brand_id') || '',
        status: params.get('status') || '',
        stock_status: stockValue,
        page: toPositiveInt(params.get('page'), 1),
        per_page: toPositiveInt(params.get('per_page'), defaultPerPage),
    };
};

export const writeTableQueryState = (
    state: TableQueryState,
    defaultPerPage: number,
    mode: HistoryMode = 'replace',
    options: QuerySyncOptions = {}
): void => {
    if (mode === 'none') {
        return;
    }

    const url = new URL(window.location.href);

    const setOrDelete = (key: string, value: string): void => {
        if (value.trim() !== '') {
            url.searchParams.set(key, value.trim());
            return;
        }

        url.searchParams.delete(key);
    };

    setOrDelete('search', state.search);
    setOrDelete('category_id', state.category_id);
    setOrDelete('brand_id', state.brand_id);
    setOrDelete('status', state.status);
    const stockKey = options.stockKey || 'stock_status';
    const fallbackKeys = Array.isArray(options.stockFallbackKeys) ? options.stockFallbackKeys : [];
    setOrDelete(stockKey, state.stock_status);
    fallbackKeys.forEach((key) => {
        if (key !== stockKey) {
            url.searchParams.delete(key);
        }
    });

    if (state.page > 1) {
        url.searchParams.set('page', String(state.page));
    } else {
        url.searchParams.delete('page');
    }

    if (state.per_page !== defaultPerPage) {
        url.searchParams.set('per_page', String(state.per_page));
    } else {
        url.searchParams.delete('per_page');
    }

    const target = `${url.pathname}${url.search}${url.hash}`;
    const current = `${window.location.pathname}${window.location.search}${window.location.hash}`;

    if (target === current) {
        return;
    }

    if (mode === 'push') {
        window.history.pushState({}, '', target);
        return;
    }

    window.history.replaceState({}, '', target);
};
