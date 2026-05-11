<template>
    <section class="vue-server-data-table">
        <div class="widget-content widget-content-area br-6 vue-datatable-card">
            <div class="vue-dt-header d-flex justify-content-between align-items-center mb-3">
                <h5 class="vue-dt-title mb-0">{{ title }}</h5>
                <div class="vue-dt-header-actions">
                    <slot
                        name="header-actions"
                        :refresh="refresh"
                        :loading="loading || isRefreshing"
                    ></slot>
                    <button
                        v-if="showRefresh"
                        type="button"
                        class="btn btn-outline-secondary btn-sm"
                        :disabled="loading || isRefreshing"
                        @click="refresh"
                    >
                        <span v-if="loading || isRefreshing" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        <i v-else class="las la-sync"></i>
                    </button>
                    <details v-if="columnToggle && tableColumns.length > 0" class="vue-dt-columns-toggle">
                        <summary class="btn btn-outline-secondary btn-sm">Kolonlar</summary>
                        <div class="vue-dt-columns-menu">
                            <label
                                v-for="column in tableColumns"
                                :key="`column-toggle-${column.key}`"
                                class="vue-dt-columns-item"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isColumnVisible(column.key)"
                                    @change="toggleColumnVisibility(column.key, $event)"
                                >
                                <span>{{ column.label }}</span>
                            </label>
                        </div>
                    </details>
                    <div class="vue-dt-header-per-page">
                        <label for="vue-dt-per-page" class="vue-dt-sr-only">Sayfa başına kayıt</label>
                        <select id="vue-dt-per-page" v-model.number="perPageLocal" class="form-control form-control-sm vue-dt-per-page">
                            <option v-for="option in perPageOptions" :key="`per-page-${option}`" :value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="vue-dt-filter-grid mb-3" :class="{ 'vue-dt-filter-grid--no-clear': !hasActiveFilters }">
                <div v-if="mergedFilterConfig.search" class="vue-dt-filter-item">
                    <label class="vue-dt-label">Ara</label>
                    <input
                        v-model="searchInput"
                        class="form-control form-control-sm"
                        type="text"
                        autocomplete="off"
                    >
                </div>
                <div v-if="mergedFilterConfig.category" class="vue-dt-filter-item">
                    <label class="vue-dt-label">Kategori</label>
                    <select v-model="selectedCategoryId" class="form-control form-control-sm">
                        <option value="">Tüm Kategoriler</option>
                        <option
                            v-for="option in filtersLocal.categories"
                            :key="`category-${option.value}`"
                            :value="String(option.value)"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div v-if="mergedFilterConfig.brand" class="vue-dt-filter-item">
                    <label class="vue-dt-label">Marka</label>
                    <select v-model="selectedBrandId" class="form-control form-control-sm">
                        <option value="">Tüm Markalar</option>
                        <option
                            v-for="option in filtersLocal.brands"
                            :key="`brand-${option.value}`"
                            :value="String(option.value)"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div v-if="mergedFilterConfig.status" class="vue-dt-filter-item">
                    <label class="vue-dt-label">{{ statusLabel }}</label>
                    <select v-model="selectedStatus" class="form-control form-control-sm">
                        <option value="">Tüm Durumlar</option>
                        <option
                            v-for="option in statusSelectOptions"
                            :key="`status-${option.value}`"
                            :value="String(option.value)"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div v-if="mergedFilterConfig.stock" class="vue-dt-filter-item">
                    <label class="vue-dt-label">{{ stockLabel }}</label>
                    <select v-model="selectedStockStatus" class="form-control form-control-sm">
                        <option value="">{{ stockAllLabel }}</option>
                        <option
                            v-for="option in stockStatusSelectOptions"
                            :key="`stock-${option.value}`"
                            :value="String(option.value)"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <slot name="extra-filters"></slot>
                <div v-if="hasActiveFilters" class="vue-dt-filter-item vue-dt-clear-item">
                    <label class="vue-dt-label d-block">&nbsp;</label>
                    <button type="button" class="btn btn-outline-secondary btn-sm vue-dt-clear-btn" @click="clearFilters">
                        Filtreleri Temizle
                    </button>
                </div>
            </div>

            <div v-if="selectable" class="vue-dt-bulk-toolbar mb-3 d-flex align-items-center justify-content-between flex-wrap">
                <slot
                    name="bulk-actions"
                    :selected-ids="selectedIdsArray"
                    :clear-selection="clearSelection"
                >
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="mr-3">{{ selectedIdsArray.length }} kayıt seçildi</span>
                        <button type="button" class="btn btn-sm btn-success mr-2 mb-2" @click="emitBulkAction('activate')">
                            Aktif Yap
                        </button>
                        <button type="button" class="btn btn-sm btn-warning mr-2 mb-2" @click="emitBulkAction('deactivate')">
                            Pasif Yap
                        </button>
                        <button type="button" class="btn btn-sm btn-danger mb-2" @click="emitBulkAction('delete')">
                            Sil
                        </button>
                    </div>
                </slot>
            </div>

            <div class="table-responsive mb-4 vue-dt-table-wrap">
                <div v-if="errorMessage && rows.length > 0" class="vue-dt-inline-error">
                    {{ errorMessage }}
                </div>
                <div v-if="isRefreshing" class="vue-dt-loading-overlay">
                    <span class="vue-dt-loading-chip">Güncelleniyor...</span>
                </div>
                <table class="table table-hover datatable-theme-table" style="width: 100%">
                    <thead>
                        <tr>
                            <th v-if="selectable" class="text-center" style="width: 44px;">
                                <input
                                    ref="headerSelectRef"
                                    type="checkbox"
                                    :checked="allCurrentPageSelected"
                                    @change="toggleCurrentPageSelection($event)"
                                >
                            </th>
                            <th v-for="column in visibleColumns" :key="column.key" :class="column.className || ''">
                                {{ column.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isInitialLoading" v-for="skeletonIndex in skeletonRowCount" :key="`skeleton-${skeletonIndex}`">
                            <td v-if="selectable" class="vue-dt-skeleton-cell">
                                <span class="vue-dt-skeleton-line"></span>
                            </td>
                            <td v-for="cellIndex in visibleColumnCount" :key="`skeleton-cell-${skeletonIndex}-${cellIndex}`" class="vue-dt-skeleton-cell">
                                <span class="vue-dt-skeleton-line"></span>
                            </td>
                        </tr>
                        <tr v-else-if="errorMessage && rows.length === 0">
                            <td :colspan="totalColumnCount" class="text-center text-danger py-3">{{ errorMessage }}</td>
                        </tr>
                        <tr v-else-if="rows.length === 0">
                            <td :colspan="totalColumnCount" class="text-center text-muted py-3">{{ emptyText }}</td>
                        </tr>
                        <tr v-else v-for="(row, rowIndex) in rows" :key="resolveRowKey(row, rowIndex)" :data-id="resolveRowIdentifier(row) ?? ''">
                            <td v-if="selectable" class="text-center">
                                <input
                                    type="checkbox"
                                    :checked="isRowSelected(row)"
                                    :disabled="resolveRowIdentifier(row) === null"
                                    @change="toggleRowSelection(row, $event)"
                                >
                            </td>
                            <td v-for="column in visibleColumns" :key="column.key" :class="column.className || ''">
                                <slot
                                    :name="`cell-${column.key}`"
                                    :row="row"
                                    :column="column"
                                    :value="row[column.key]"
                                >
                                    <template v-if="column.key !== 'actions'">
                                        {{ row[column.key] ?? '-' }}
                                    </template>
                                </slot>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!loading && !errorMessage" class="vue-dt-footer d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted mb-2 mb-md-0">{{ rangeText }}</small>
                <nav aria-label="Ürün sayfalama" class="ml-md-auto vue-dt-pagination-nav">
                    <ul class="pagination mb-0 datatable-pagination">
                        <li class="page-item" :class="{ disabled: page <= 1 }">
                            <button
                                type="button"
                                class="page-link page-link-nav"
                                :disabled="page <= 1"
                                @click="goToPage(page - 1)"
                            >
                                Önceki
                            </button>
                        </li>
                        <li
                            v-for="pageNumber in visiblePages"
                            :key="pageNumber"
                            class="page-item"
                            :class="{ active: pageNumber === page }"
                        >
                            <button type="button" class="page-link" @click="goToPage(pageNumber)">
                                {{ pageNumber }}
                            </button>
                        </li>
                        <li class="page-item" :class="{ disabled: page >= meta.last_page }">
                            <button
                                type="button"
                                class="page-link page-link-nav"
                                :disabled="page >= meta.last_page"
                                @click="goToPage(page + 1)"
                            >
                                Sonraki
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import http from '../lib/http';
import { createEmptyTableMeta, type ServerTableMeta, normalizeTableMeta } from '../composables/useServerTable';
import {
    readTableQueryState,
    writeTableQueryState,
    type HistoryMode,
    type TableQueryState,
} from '../composables/useQuerySync';
import {
    SERVER_DATATABLE_LOADED_EVENT,
    SERVER_DATATABLE_REFRESH_EVENT,
    type ServerDatatableLoadedDetail,
    type ServerDatatableRefreshDetail,
} from '../shared/serverDatatableEvents';

interface Column {
    key: string;
    label: string;
    type?: 'text' | 'image' | 'link';
    className?: string;
}

interface Option {
    value: string | number;
    label: string;
}

interface FilterOptions {
    categories: Option[];
    brands: Option[];
    statusOptions: Option[];
        stockStatusOptions: Option[];
        erpStatusOptions?: Option[];
}

interface FilterConfig {
    search: boolean;
    category: boolean;
    brand: boolean;
    status: boolean;
    stock: boolean;
}

interface TableResponse {
    data: Array<Record<string, unknown>>;
    meta: ServerTableMeta;
    filters?: Partial<FilterOptions>;
}

const defaultFilters = (): FilterOptions => ({
    categories: [],
    brands: [],
    statusOptions: [
        { value: '', label: 'Tüm Durumlar' },
        { value: '1', label: 'Aktif' },
        { value: '0', label: 'Pasif' },
    ],
    stockStatusOptions: [
        { value: '', label: 'Tüm Stoklar' },
        { value: 'in_stock', label: 'Stokta' },
        { value: 'out_of_stock', label: 'Stokta Değil' },
    ],
});

const defaultFilterConfig = (): FilterConfig => ({
    search: true,
    category: true,
    brand: true,
    status: true,
    stock: true,
});

const normalizeOptions = (options?: Option[]): Option[] => (Array.isArray(options) ? options : []);

const normalizeFilters = (incoming?: Partial<FilterOptions>): FilterOptions => ({
    categories: normalizeOptions(incoming?.categories),
    brands: normalizeOptions(incoming?.brands),
    statusOptions: normalizeOptions(incoming?.statusOptions).length > 0
        ? normalizeOptions(incoming?.statusOptions)
        : defaultFilters().statusOptions,
    stockStatusOptions: normalizeOptions(incoming?.stockStatusOptions).length > 0
        ? normalizeOptions(incoming?.stockStatusOptions)
        : normalizeOptions(incoming?.erpStatusOptions).length > 0
            ? normalizeOptions(incoming?.erpStatusOptions)
        : defaultFilters().stockStatusOptions,
});

const props = withDefaults(
    defineProps<{
        title?: string;
        columns?: Column[];
        endpoint: string;
        filters?: FilterOptions;
        perPage?: number;
        emptyText?: string;
        debounceMs?: number;
        loadedEventComponent?: string;
        filterConfig?: Partial<FilterConfig>;
        statusLabel?: string;
        stockLabel?: string;
        stockAllLabel?: string;
        selectable?: boolean;
        rowKey?: string;
        columnToggle?: boolean;
        storageKey?: string;
        showRefresh?: boolean;
        extraFiltersActive?: boolean;
        stockQueryKey?: string;
        stockQueryFallbackKeys?: string[];
    }>(),
    {
        title: 'Ürünler',
        columns: () => [],
        filters: () => ({
            categories: [],
            brands: [],
            statusOptions: [
                { value: '', label: 'Tüm Durumlar' },
                { value: '1', label: 'Aktif' },
                { value: '0', label: 'Pasif' },
            ],
            stockStatusOptions: [
                { value: '', label: 'Tüm Stoklar' },
                { value: 'in_stock', label: 'Stokta' },
                { value: 'out_of_stock', label: 'Stokta Değil' },
            ],
        }),
        perPage: 50,
        emptyText: 'Kayıt bulunamadı',
        debounceMs: 350,
        loadedEventComponent: '',
        filterConfig: () => ({
            search: true,
            category: true,
            brand: true,
            status: true,
            stock: true,
        }),
        statusLabel: 'Ürün Durumu',
        stockLabel: 'Stok Durumu',
        stockAllLabel: 'Tüm Stoklar',
        selectable: false,
        rowKey: 'id',
        columnToggle: false,
        storageKey: '',
        showRefresh: true,
        extraFiltersActive: false,
        stockQueryKey: 'stock_status',
        stockQueryFallbackKeys: () => [],
    }
);
const emit = defineEmits<{
    loaded: [];
    'selection-change': [Array<string | number>];
    'bulk-action': [{ action: 'activate' | 'deactivate' | 'delete'; ids: Array<string | number> }];
    'rows-change': [Array<Record<string, unknown>>];
    'clear-extra-filters': [];
}>();

const tableColumns = computed<Column[]>(() => (Array.isArray(props.columns) ? props.columns : []));
const mergedFilterConfig = computed<FilterConfig>(() => ({
    ...defaultFilterConfig(),
    ...(props.filterConfig || {}),
}));
const perPageOptions = computed(() => Array.from(new Set([50, 100, props.perPage])).sort((a, b) => a - b));
const rows = ref<Array<Record<string, unknown>>>([]);
const loading = ref(false);
const isInitialLoading = ref(true);
const isRefreshing = ref(false);
const errorMessage = ref('');
const searchInput = ref('');
const selectedCategoryId = ref('');
const selectedBrandId = ref('');
const selectedStatus = ref('');
const selectedStockStatus = ref('');
const page = ref(1);
const perPageLocal = ref(props.perPage);
const initialized = ref(false);
const isApplyingQueryState = ref(false);
const filtersLocal = ref<FilterOptions>(normalizeFilters(props.filters));
const meta = ref<ServerTableMeta>(createEmptyTableMeta(props.perPage));
const selectedIds = ref<Set<string | number>>(new Set());
const headerSelectRef = ref<HTMLInputElement | null>(null);
const visibleColumnKeys = ref<Set<string>>(new Set());

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const visibleColumns = computed<Column[]>(() => {
    const allowedKeys = visibleColumnKeys.value;
    if (allowedKeys.size === 0) {
        return tableColumns.value;
    }

    return tableColumns.value.filter((column) => allowedKeys.has(column.key));
});
const visibleColumnCount = computed(() => (visibleColumns.value.length > 0 ? visibleColumns.value.length : 1));
const totalColumnCount = computed(() => visibleColumnCount.value + (props.selectable ? 1 : 0));
const selectedIdsArray = computed(() => Array.from(selectedIds.value));
const currentPageRowIds = computed(() => rows.value
    .map((row) => resolveRowIdentifier(row))
    .filter((id): id is string | number => id !== null));
const allCurrentPageSelected = computed(() => (
    currentPageRowIds.value.length > 0
    && currentPageRowIds.value.every((id) => selectedIds.value.has(id))
));
const someCurrentPageSelected = computed(() => (
    currentPageRowIds.value.some((id) => selectedIds.value.has(id))
    && !allCurrentPageSelected.value
));
const statusSelectOptions = computed(() => filtersLocal.value.statusOptions.filter((option) => String(option.value) !== ''));
const stockStatusSelectOptions = computed(() => filtersLocal.value.stockStatusOptions.filter((option) => String(option.value) !== ''));
const hasActiveFilters = computed(() => (
    (mergedFilterConfig.value.search && searchInput.value.trim() !== '')
    || (mergedFilterConfig.value.category && selectedCategoryId.value !== '')
    || (mergedFilterConfig.value.brand && selectedBrandId.value !== '')
    || (mergedFilterConfig.value.status && selectedStatus.value !== '')
    || (mergedFilterConfig.value.stock && selectedStockStatus.value !== '')
    || props.extraFiltersActive
    || page.value > 1
));
const skeletonRowCount = computed(() => Math.min(8, Math.max(4, perPageLocal.value)));
const columnStorageName = computed(() => (
    props.storageKey ? `datatable.columns.${props.storageKey}` : ''
));

const visiblePages = computed(() => {
    const pages: number[] = [];
    const start = Math.max(1, page.value - 2);
    const end = Math.min(meta.value.last_page || 1, page.value + 2);

    for (let i = start; i <= end; i += 1) {
        pages.push(i);
    }

    return pages;
});

const rangeText = computed(() => {
    const total = meta.value.total || 0;
    const from = meta.value.from || 0;
    const to = meta.value.to || 0;

    return `${from} ile ${to} arası gösteriliyor, toplam ${total} kayıt`;
});

const getQueryState = (): TableQueryState => ({
    search: mergedFilterConfig.value.search ? searchInput.value.trim() : '',
    category_id: mergedFilterConfig.value.category ? selectedCategoryId.value : '',
    brand_id: mergedFilterConfig.value.brand ? selectedBrandId.value : '',
    status: mergedFilterConfig.value.status ? selectedStatus.value : '',
    stock_status: mergedFilterConfig.value.stock ? selectedStockStatus.value : '',
    page: page.value,
    per_page: perPageLocal.value,
});

const readQueryState = (): TableQueryState => readTableQueryState(props.perPage, {
    stockKey: props.stockQueryKey,
    stockFallbackKeys: props.stockQueryFallbackKeys,
});
const writeQueryState = (mode: HistoryMode = 'replace'): void => {
    writeTableQueryState(getQueryState(), props.perPage, mode, {
        stockKey: props.stockQueryKey,
        stockFallbackKeys: props.stockQueryFallbackKeys,
    });
};

const applyQueryState = (state: TableQueryState): void => {
    isApplyingQueryState.value = true;
    searchInput.value = state.search;
    selectedCategoryId.value = state.category_id;
    selectedBrandId.value = state.brand_id;
    selectedStatus.value = state.status;
    selectedStockStatus.value = state.stock_status;
    page.value = state.page;
    perPageLocal.value = state.per_page;
    isApplyingQueryState.value = false;
};

const getAllColumnKeys = (): string[] => tableColumns.value.map((column) => column.key);

const persistVisibleColumns = (): void => {
    if (!props.columnToggle || !columnStorageName.value) {
        return;
    }
    if (typeof window === 'undefined') {
        return;
    }

    try {
        const payload = JSON.stringify(Array.from(visibleColumnKeys.value));
        window.localStorage.setItem(columnStorageName.value, payload);
    } catch (_error) {
        // no-op
    }
};

const hydrateVisibleColumns = (): void => {
    const allKeys = getAllColumnKeys();
    if (allKeys.length === 0) {
        visibleColumnKeys.value = new Set();
        return;
    }

    if (!props.columnToggle) {
        visibleColumnKeys.value = new Set(allKeys);
        return;
    }

    let nextKeys = new Set(allKeys);
    if (columnStorageName.value) {
        if (typeof window === 'undefined') {
            visibleColumnKeys.value = nextKeys;
            return;
        }
        try {
            const raw = window.localStorage.getItem(columnStorageName.value);
            if (raw) {
                const parsed = JSON.parse(raw) as unknown;
                if (Array.isArray(parsed)) {
                    const filtered = parsed
                        .filter((item): item is string => typeof item === 'string')
                        .filter((key) => allKeys.includes(key));
                    if (filtered.length > 0) {
                        nextKeys = new Set(filtered);
                    }
                }
            }
        } catch (_error) {
            nextKeys = new Set(allKeys);
        }
    }

    visibleColumnKeys.value = nextKeys;
};

const isColumnVisible = (columnKey: string): boolean => visibleColumnKeys.value.has(columnKey);

const toggleColumnVisibility = (columnKey: string, event: Event): void => {
    const input = event.target as HTMLInputElement;
    const checked = input.checked;
    const nextKeys = new Set(visibleColumnKeys.value);

    if (checked) {
        nextKeys.add(columnKey);
        visibleColumnKeys.value = nextKeys;
        persistVisibleColumns();
        return;
    }

    if (nextKeys.size <= 1) {
        input.checked = true;
        return;
    }

    nextKeys.delete(columnKey);
    visibleColumnKeys.value = nextKeys;
    persistVisibleColumns();
};

const resolveRowIdentifier = (row: Record<string, unknown>): string | number | null => {
    const value = row[props.rowKey];
    if (typeof value === 'string' || typeof value === 'number') {
        return value;
    }

    return null;
};

const resolveRowKey = (row: Record<string, unknown>, fallbackIndex: number): string | number => {
    return resolveRowIdentifier(row) ?? fallbackIndex;
};

const emitSelectionChange = (): void => {
    emit('selection-change', selectedIdsArray.value);
};

const clearSelection = (): void => {
    selectedIds.value = new Set();
    emitSelectionChange();
};

const isRowSelected = (row: Record<string, unknown>): boolean => {
    const id = resolveRowIdentifier(row);
    return id !== null && selectedIds.value.has(id);
};

const toggleRowSelection = (row: Record<string, unknown>, event: Event): void => {
    const id = resolveRowIdentifier(row);
    if (id === null) {
        return;
    }

    const input = event.target as HTMLInputElement;
    const next = new Set(selectedIds.value);
    if (input.checked) {
        next.add(id);
    } else {
        next.delete(id);
    }

    selectedIds.value = next;
    emitSelectionChange();
};

const toggleCurrentPageSelection = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const next = new Set(selectedIds.value);

    if (input.checked) {
        currentPageRowIds.value.forEach((id) => next.add(id));
    } else {
        currentPageRowIds.value.forEach((id) => next.delete(id));
    }

    selectedIds.value = next;
    emitSelectionChange();
};

const emitBulkAction = (action: 'activate' | 'deactivate' | 'delete'): void => {
    emit('bulk-action', {
        action,
        ids: selectedIdsArray.value,
    });
};

const fetchRows = async () => {
    const firstLoad = isInitialLoading.value;
    loading.value = true;
    isRefreshing.value = !firstLoad;
    errorMessage.value = '';

    try {
        const response = await http.get<TableResponse>(props.endpoint, {
            params: {
                search: mergedFilterConfig.value.search ? (searchInput.value.trim() || undefined) : undefined,
                category_id: mergedFilterConfig.value.category ? (selectedCategoryId.value || undefined) : undefined,
                brand_id: mergedFilterConfig.value.brand ? (selectedBrandId.value || undefined) : undefined,
                status: mergedFilterConfig.value.status ? (selectedStatus.value || undefined) : undefined,
                [props.stockQueryKey]: mergedFilterConfig.value.stock ? (selectedStockStatus.value || undefined) : undefined,
                page: page.value,
                per_page: perPageLocal.value,
            },
        });

        rows.value = Array.isArray(response.data?.data) ? response.data.data : [];
        emit('rows-change', rows.value);
        meta.value = normalizeTableMeta(response.data?.meta, perPageLocal.value);
        clearSelection();

        if (response.data?.filters && Object.keys(response.data.filters).length > 0) {
            filtersLocal.value = normalizeFilters(response.data.filters);
        }

        if (meta.value.current_page !== page.value) {
            page.value = meta.value.current_page;
        }

        await nextTick();
        emit('loaded');
        window.dispatchEvent(new CustomEvent<ServerDatatableLoadedDetail>(SERVER_DATATABLE_LOADED_EVENT, {
            detail: {
                component: props.loadedEventComponent || '',
                reason: 'fetch-complete',
                source: 'server-datatable',
            },
        }));
    } catch (_error) {
        if (firstLoad) {
            rows.value = [];
        }
        errorMessage.value = 'Veriler alınırken bir hata oluştu.';
    } finally {
        loading.value = false;
        isInitialLoading.value = false;
        isRefreshing.value = false;
    }
};

const goToPage = (targetPage: number) => {
    if (targetPage < 1 || targetPage > meta.value.last_page || targetPage === page.value) {
        return;
    }

    page.value = targetPage;
    writeQueryState('push');
    void fetchRows();
};

const clearFilters = () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
        debounceTimer = null;
    }

    emit('clear-extra-filters');
    searchInput.value = '';
    selectedCategoryId.value = '';
    selectedBrandId.value = '';
    selectedStatus.value = '';
    selectedStockStatus.value = '';
    page.value = 1;
    writeQueryState('push');
    void fetchRows();
};

const refresh = async () => {
    clearSelection();
    await fetchRows();
};

const setPage = (targetPage: number, historyMode: HistoryMode = 'push'): void => {
    if (!Number.isFinite(targetPage) || targetPage < 1) {
        return;
    }

    const nextPage = Math.trunc(targetPage);
    if (nextPage === page.value) {
        return;
    }

    page.value = nextPage;
    writeQueryState(historyMode);
};

const handleTableRefreshEvent = (event: Event) => {
    const customEvent = event as CustomEvent<ServerDatatableRefreshDetail>;
    const targetComponent = customEvent?.detail?.component ?? null;
    const currentComponent = props.loadedEventComponent || null;

    if (!targetComponent || targetComponent !== currentComponent) {
        return;
    }

    const rawPage = customEvent?.detail?.page;
    const parsedPage = typeof rawPage === 'string' ? Number(rawPage) : Number(rawPage ?? NaN);
    if (Number.isFinite(parsedPage) && parsedPage > 0) {
        setPage(parsedPage);
    }

    void fetchRows();
};

defineExpose({
    refresh,
    setPage,
});

watch(searchInput, () => {
    if (!initialized.value || isApplyingQueryState.value) {
        return;
    }

    page.value = 1;

    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        writeQueryState('push');
        void fetchRows();
    }, props.debounceMs);
});

watch([selectedCategoryId, selectedBrandId, selectedStatus, selectedStockStatus], () => {
    if (!initialized.value || isApplyingQueryState.value) {
        return;
    }

    page.value = 1;
    writeQueryState('push');
    void fetchRows();
});

watch(perPageLocal, () => {
    if (!initialized.value || isApplyingQueryState.value) {
        return;
    }

    page.value = 1;
    writeQueryState('push');
    void fetchRows();
});

watch([allCurrentPageSelected, someCurrentPageSelected], () => {
    if (headerSelectRef.value) {
        headerSelectRef.value.indeterminate = someCurrentPageSelected.value;
    }
});

watch(tableColumns, () => {
    hydrateVisibleColumns();
}, { immediate: true });

const handlePopState = () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
        debounceTimer = null;
    }

    applyQueryState(readQueryState());
    void fetchRows();
};

onMounted(async () => {
    applyQueryState(readQueryState());
    await fetchRows();
    initialized.value = true;
    window.addEventListener('popstate', handlePopState);
    window.addEventListener(SERVER_DATATABLE_REFRESH_EVENT, handleTableRefreshEvent as EventListener);
});

onBeforeUnmount(() => {
    window.removeEventListener('popstate', handlePopState);
    window.removeEventListener(SERVER_DATATABLE_REFRESH_EVENT, handleTableRefreshEvent as EventListener);

    if (debounceTimer) {
        clearTimeout(debounceTimer);
        debounceTimer = null;
    }
});
</script>

<style scoped>
.vue-datatable-card {
    background: #fff;
    border-radius: 6px;
    padding: 20px;
    box-shadow: 0 0 20px rgb(0 0 0 / 6%);
    border: 1px solid #edf2f9;
}

.vue-dt-header {
    border-bottom: 1px solid #f1f1f1;
    padding-bottom: 14px;
}

.vue-dt-title {
    color: #3b3f5c;
    font-size: 18px;
    font-weight: 600;
}

.vue-dt-header-per-page {
    display: flex;
    align-items: center;
}

.vue-dt-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.vue-dt-columns-toggle {
    position: relative;
}

.vue-dt-columns-toggle summary {
    list-style: none;
}

.vue-dt-columns-toggle summary::-webkit-details-marker {
    display: none;
}

.vue-dt-columns-menu {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    min-width: 180px;
    z-index: 20;
    border: 1px solid #d3dae7;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 8px 24px rgb(27 85 226 / 12%);
    padding: 8px 10px;
}

.vue-dt-columns-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #3b3f5c;
    margin-bottom: 6px;
    cursor: pointer;
}

.vue-dt-columns-item:last-child {
    margin-bottom: 0;
}

.vue-dt-filter-grid {
    display: grid;
    grid-template-columns: minmax(220px, 1.4fr) repeat(4, minmax(160px, 1fr)) auto;
    gap: 12px;
    align-items: end;
}

.vue-dt-filter-grid--no-clear {
    grid-template-columns: minmax(220px, 1.4fr) repeat(4, minmax(160px, 1fr));
}

.vue-dt-filter-item {
    min-width: 0;
}

.vue-dt-filter-grid :deep(.vue-dt-filter-item) {
    min-width: 0;
}

.vue-dt-label {
    color: #3b3f5c;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
}

.vue-dt-filter-item .form-control,
.vue-dt-per-page {
    height: 36px;
}

.vue-dt-filter-grid :deep(.vue-dt-label) {
    color: #3b3f5c;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
}

.vue-dt-filter-grid :deep(.vue-dt-filter-item .form-control) {
    height: 36px;
}

.vue-dt-clear-item {
    min-width: auto;
}

.vue-dt-filter-grid :deep(.vue-dt-clear-item) {
    min-width: auto;
}

.vue-dt-clear-btn {
    height: 36px;
    white-space: nowrap;
}

.vue-dt-filter-grid :deep(.vue-dt-clear-btn) {
    height: 36px;
    white-space: nowrap;
}

.vue-dt-per-page {
    width: 90px;
}

.vue-dt-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.vue-dt-footer {
    padding-top: 6px;
}

.vue-dt-table-wrap {
    position: relative;
}

.vue-dt-inline-error {
    margin-bottom: 10px;
    padding: 8px 12px;
    border: 1px solid #f4c7cb;
    border-radius: 6px;
    color: #8d1b21;
    background: #fdf1f2;
    font-size: 12px;
}

.vue-dt-loading-overlay {
    position: absolute;
    inset: 0;
    z-index: 5;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 12px;
    background: rgb(255 255 255 / 45%);
    pointer-events: none;
}

.vue-dt-loading-chip {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid #d3dae7;
    background: #fff;
    color: #1b55e2;
    font-size: 12px;
    font-weight: 600;
}

.vue-dt-skeleton-cell {
    padding: 12px 10px;
}

.vue-dt-skeleton-line {
    display: block;
    height: 14px;
    width: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #edf2f9 25%, #f8fafd 50%, #edf2f9 75%);
    background-size: 200% 100%;
    animation: vue-dt-skeleton 1.2s ease-in-out infinite;
}

@keyframes vue-dt-skeleton {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

.datatable-theme-table thead th {
    color: #1b55e2;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
    border-bottom: 1px solid #e9eef7;
    white-space: nowrap;
    padding: 12px 10px;
    font-size: 12px;
}

.datatable-theme-table tbody td {
    padding: 12px 10px;
    vertical-align: middle;
    border-top: 1px solid #f3f5f9;
}

.datatable-theme-table tbody tr:nth-child(even) {
    background-color: rgb(27 85 226 / 2%);
}

.datatable-theme-table tbody tr:hover {
    background-color: rgb(27 85 226 / 6%);
}

.table-product-image {
    border-radius: 4px;
    object-fit: cover;
}

.datatable-pagination .page-item .page-link {
    min-width: 40px;
    height: 40px;
    line-height: 38px;
    text-align: center;
    border-radius: 999px;
    border: 1px solid #d3dae7;
    color: #3b3f5c;
    padding: 0;
    margin: 0 4px;
    background: #fff;
    font-size: 13px;
    transition: all 0.2s ease;
}

.datatable-pagination .page-item .page-link.page-link-nav {
    min-width: 94px;
    border-radius: 12px;
    padding: 0 16px;
}

.datatable-pagination .page-item.active .page-link {
    color: #fff;
    border-color: #1b55e2;
    background-color: #1b55e2;
}

.datatable-pagination .page-item.disabled .page-link {
    color: #a2a8b3;
    background: #fff;
    border-color: #e4e9f1;
    opacity: 0.65;
}

.datatable-pagination .page-item:not(.disabled):not(.active) .page-link:hover {
    color: #1b55e2;
    border-color: #1b55e2;
}

@media (max-width: 1200px) {
    .vue-dt-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .vue-dt-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 12px;
    }

    .vue-dt-filter-grid {
        grid-template-columns: 1fr;
    }

    .vue-dt-pagination-nav {
        width: 100%;
    }

    .datatable-pagination {
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .datatable-pagination .page-item .page-link.page-link-nav {
        min-width: 86px;
    }
}
</style>
