<template>
    <div>
        <ServerDataTable
            :title="title"
            :endpoint="endpoint"
            :columns="columns"
            :filters="filters"
            :filter-config="filterConfig"
            :status-label="statusLabel"
            :per-page="perPage"
            selectable
            column-toggle
            storage-key="products-table"
            loaded-event-component="products"
            :empty-text="emptyText"
            @selection-change="onSelectionChange"
            @rows-change="onRowsChange"
        >
            <template #header-actions>
                <a :href="createUrl" class="btn btn-primary btn-sm">
                    <i class="las la-plus"></i> Yeni
                </a>
                <div ref="exportMenuRef" class="position-relative">
                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        :disabled="isAnyExportBusy"
                        @click="exportMenuOpen = !exportMenuOpen"
                    >
                        <span v-if="isAnyExportBusy" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        {{ exportButtonText }}
                    </button>

                    <div
                        v-if="exportMenuOpen"
                        class="dropdown-menu dropdown-menu-right show"
                        style="display:block;"
                    >
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="isAnyExportBusy"
                            @click="exportProducts('filtered')"
                        >
                            CSV: Filtrelenenleri Dışa Aktar
                        </button>
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="isAnyExportBusy || selectedIds.length === 0"
                            @click="exportProducts('selected')"
                        >
                            CSV: Seçilenleri Dışa Aktar
                        </button>
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="isAnyExportBusy"
                            @click="queueExcelExport('filtered')"
                        >
                            Excel: Filtrelenenleri Kuyruğa Al
                        </button>
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="isAnyExportBusy || selectedIds.length === 0"
                            @click="queueExcelExport('selected')"
                        >
                            Excel: Seçilenleri Kuyruğa Al
                        </button>
                    </div>
                </div>
            </template>

            <template #bulk-actions="{ selectedIds: slotSelectedIds, clearSelection }">
                <div class="d-flex align-items-center flex-wrap">
                    <span class="mr-3">{{ slotSelectedIds.length }} kayıt seçildi</span>
                    <button
                        type="button"
                        class="btn btn-sm btn-success mr-2 mb-2"
                        :disabled="bulkLoading || slotSelectedIds.length === 0"
                        @click="runBulkAction('activate', slotSelectedIds, clearSelection)"
                    >
                        <span v-if="bulkLoading" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        {{ bulkLoading ? 'İşleniyor...' : 'Aktif Yap' }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-warning mr-2 mb-2"
                        :disabled="bulkLoading || slotSelectedIds.length === 0"
                        @click="runBulkAction('deactivate', slotSelectedIds, clearSelection)"
                    >
                        <span v-if="bulkLoading" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        {{ bulkLoading ? 'İşleniyor...' : 'Pasif Yap' }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-danger mb-2"
                        :disabled="bulkLoading || slotSelectedIds.length === 0"
                        @click="runBulkAction('delete', slotSelectedIds, clearSelection)"
                    >
                        <span v-if="bulkLoading" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        {{ bulkLoading ? 'İşleniyor...' : 'Sil' }}
                    </button>
                </div>
            </template>

            <template #cell-image_url="{ row }">
                <img
                    v-if="typeof row.image_url === 'string' && row.image_url"
                    :src="row.image_url"
                    :alt="typeof row.name === 'string' ? row.name : 'Ürün Görseli'"
                    width="50"
                    height="50"
                    loading="lazy"
                    class="table-product-image"
                >
                <i v-else class="las la-image h4"></i>
            </template>

            <template #cell-status="{ row }">
                <div class="custom-control custom-switch d-inline-block">
                    <input
                        :id="`product-status-${row.id}`"
                        type="checkbox"
                        class="custom-control-input"
                        :checked="Number(row.status_value || 0) === 1"
                        :disabled="inlineEdit.isLoading(statusLoadingKey(row))"
                        @change="onStatusToggle(row, $event)"
                    >
                    <label class="custom-control-label" :for="`product-status-${row.id}`"></label>
                </div>
            </template>

            <template #cell-edit_url="{ row }">
                <a
                    v-if="typeof row.edit_url === 'string' && row.edit_url"
                    :href="String(row.edit_url)"
                    class="btn btn-info font-15 p-2"
                    title="Düzenle"
                >
                    <i class="las la-edit"></i>
                </a>
                <span v-else>-</span>
            </template>
        </ServerDataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ServerDataTable from './ServerDataTable.vue';
import { useBulkActions } from '../composables/useBulkActions';
import { useServerDatatableInlineEdit } from '../composables/useServerDatatableInlineEdit';
import http from '../lib/http';
import { dispatchServerDatatableRefresh } from '../shared/serverDatatableEvents';

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
}

interface FilterConfig {
    search: boolean;
    category: boolean;
    brand: boolean;
    status: boolean;
    stock: boolean;
}

interface RowData {
    id: number;
    status?: string;
    status_value?: number;
    inline_update_url?: string;
}

type ExportScope = 'filtered' | 'selected';

const props = withDefaults(defineProps<{
    title?: string;
    endpoint: string;
    columns: Column[];
    filters?: FilterOptions;
    filterConfig?: Partial<FilterConfig>;
    statusLabel?: string;
    perPage?: number;
    emptyText?: string;
    createUrl?: string;
}>(), {
    title: 'Ürünler',
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
    filterConfig: () => ({
        search: true,
        category: true,
        brand: true,
        status: true,
        stock: true,
    }),
    statusLabel: 'Durum',
    perPage: 50,
    emptyText: 'Kayıt bulunamadı',
    createUrl: '/aka/products/create',
});

const { loading: bulkLoading, performBulkAction } = useBulkActions('products');
const selectedIds = ref<Array<string | number>>([]);
const rows = ref<RowData[]>([]);

const exportLoading = ref(false);
const exportQueueLoading = ref(false);
const exportMenuOpen = ref(false);
const exportMenuRef = ref<HTMLElement | null>(null);
const pollingTimer = ref<number | null>(null);
const pollingAttempt = ref(0);
const maxPollingAttempt = 120;

const isAnyExportBusy = computed(() => exportLoading.value || exportQueueLoading.value);
const exportButtonText = computed(() => {
    if (exportQueueLoading.value) {
        return 'Dışa aktarım hazırlanıyor...';
    }

    if (exportLoading.value) {
        return 'Dışa aktarılıyor...';
    }

    return 'Dışa Aktar';
});

const notify = (type: 'success' | 'error', message: string): void => {
    const maybeNotify = (window as typeof window & { notify?: (level: string, text: string) => void }).notify;
    if (typeof maybeNotify === 'function') {
        maybeNotify(type, message);
    }
};

const dispatchRefresh = () => {
    dispatchServerDatatableRefresh({
        component: 'products',
    });
};

const resolveErrorMessage = (error: unknown, fallback: string): string => {
    const maybeError = error as {
        response?: { message?: string; data?: { message?: string } };
        message?: string;
    };

    return maybeError?.response?.data?.message
        || maybeError?.response?.message
        || maybeError?.message
        || fallback;
};

const getCurrentPage = (): number => {
    const params = new URLSearchParams(window.location.search);
    const value = Number.parseInt(params.get('page') || '1', 10);
    return Number.isFinite(value) && value > 0 ? value : 1;
};

const getPerPage = (): number => {
    const params = new URLSearchParams(window.location.search);
    const value = Number.parseInt(params.get('per_page') || '50', 10);
    return Number.isFinite(value) && value > 0 ? value : 50;
};

const inlineDatatableEdit = useServerDatatableInlineEdit({
    resolveErrorMessage,
    notify: (type, message) => notify(type, message),
    dispatchRefresh,
    getCurrentPage,
    getPerPage,
    loadingKeyPrefix: 'product',
});

const {
    inlineEdit,
    statusLoadingKey,
    onStatusToggle,
} = inlineDatatableEdit;

const onSelectionChange = (ids: Array<string | number>) => {
    selectedIds.value = ids;
};

const onRowsChange = (nextRows: Array<Record<string, unknown>>) => {
    rows.value = nextRows as RowData[];
};

const runBulkAction = async (
    action: 'activate' | 'deactivate' | 'delete',
    ids: Array<string | number>,
    clearSelection: () => void
) => {
    const success = await performBulkAction('/admin/api/products/bulk', action, ids, {
        rows,
        getRowId: (row: RowData) => row.id,
        onSuccessLocalUpdate: (nextAction, nextIds, currentRows) => {
            const idSet = new Set(nextIds.map((id) => String(id)));

            if (nextAction === 'activate') {
                currentRows.forEach((currentRow) => {
                    const rowItem = currentRow as RowData;
                    if (idSet.has(String(rowItem.id))) {
                        rowItem.status = 'Aktif';
                        rowItem.status_value = 1;
                    }
                });
            }

            if (nextAction === 'deactivate') {
                currentRows.forEach((currentRow) => {
                    const rowItem = currentRow as RowData;
                    if (idSet.has(String(rowItem.id))) {
                        rowItem.status = 'Pasif';
                        rowItem.status_value = 0;
                    }
                });
            }

            if (nextAction === 'delete') {
                const remaining = currentRows.filter((currentRow) => !idSet.has(String((currentRow as RowData).id)));
                currentRows.splice(0, currentRows.length, ...remaining);
            }
        },
    });

    if (success) {
        clearSelection();
    }
};

const parseContentDispositionFilename = (headerValue: string): string | null => {
    if (!headerValue) {
        return null;
    }

    const utf8Match = headerValue.match(/filename\*=UTF-8''([^;]+)/i);
    if (utf8Match && utf8Match[1]) {
        return decodeURIComponent(utf8Match[1]);
    }

    const plainMatch = headerValue.match(/filename="?([^";]+)"?/i);
    if (plainMatch && plainMatch[1]) {
        return plainMatch[1];
    }

    return null;
};

const triggerDownload = (blob: Blob, filename: string): void => {
    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = filename;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    window.URL.revokeObjectURL(url);
};

const buildFilteredExportParams = (): Record<string, unknown> => {
    const params: Record<string, unknown> = {};
    const query = new URLSearchParams(window.location.search);

    const allowedKeys = ['search', 'q', 'category_id', 'brand_id', 'status', 'stock_status'];
    allowedKeys.forEach((key) => {
        const value = query.get(key);
        if (value !== null && value !== '') {
            params[key] = value;
        }
    });

    return params;
};

const exportProducts = async (scope: ExportScope): Promise<void> => {
    if (exportLoading.value || exportQueueLoading.value) {
        return;
    }

    if (scope === 'selected' && selectedIds.value.length === 0) {
        return;
    }

    exportLoading.value = true;
    exportMenuOpen.value = false;

    try {
        const params = new URLSearchParams();
        params.set('scope', scope);

        if (scope === 'selected') {
            selectedIds.value.forEach((id) => {
                params.append('ids[]', String(id));
            });
        }

        if (scope === 'filtered') {
            Object.entries(buildFilteredExportParams()).forEach(([key, value]) => {
                if (value !== '' && value !== null && value !== undefined) {
                    params.set(key, String(value));
                }
            });
        }

        const response = await http.get(`/admin/api/products/export?${params.toString()}`, {
            responseType: 'blob',
        });

        const blob = response.data as Blob;
        const headers = response.headers as Record<string, string>;
        const fromHeader = parseContentDispositionFilename(String(headers['content-disposition'] || ''));
        const fallback = `products-${scope}.csv`;

        triggerDownload(blob, fromHeader || fallback);
        notify('success', 'Dışa aktarma başlatıldı');
    } catch (_error) {
        notify('error', 'Dışa aktarma başarısız');
    } finally {
        exportLoading.value = false;
    }
};

const stopPolling = (): void => {
    if (pollingTimer.value !== null) {
        window.clearInterval(pollingTimer.value);
        pollingTimer.value = null;
    }

    pollingAttempt.value = 0;
    exportQueueLoading.value = false;
};

const startPolling = (exportJobId: number): void => {
    stopPolling();
    exportQueueLoading.value = true;

    pollingTimer.value = window.setInterval(async () => {
        pollingAttempt.value += 1;

        if (pollingAttempt.value > maxPollingAttempt) {
            stopPolling();
            notify('error', 'Dışa aktarma zaman aşımına uğradı');
            return;
        }

        try {
            const response = await http.get(`/admin/api/exports/${exportJobId}`);
            const data = (response.data || {}) as {
                status?: string;
                error?: string | null;
                download_url?: string | null;
            };

            if (data.status === 'completed') {
                stopPolling();
                notify('success', 'Excel dosyası hazır');
                if (typeof data.download_url === 'string' && data.download_url) {
                    window.location.href = data.download_url;
                }
                return;
            }

            if (data.status === 'failed') {
                stopPolling();
                notify('error', data.error || 'Dışa aktarma başarısız');
            }
        } catch (_error) {
            stopPolling();
            notify('error', 'Dışa aktarma durumu alınamadı');
        }
    }, 2000);
};

const queueExcelExport = async (scope: ExportScope): Promise<void> => {
    if (exportLoading.value || exportQueueLoading.value) {
        return;
    }

    if (scope === 'selected' && selectedIds.value.length === 0) {
        return;
    }

    exportLoading.value = true;
    exportMenuOpen.value = false;

    try {
        const payload: Record<string, unknown> = {
            format: 'xlsx',
            scope,
            filters: scope === 'filtered' ? buildFilteredExportParams() : {},
            ids: scope === 'selected' ? selectedIds.value : [],
        };

        const response = await http.post('/admin/api/products/exports', payload);
        const exportJobId = Number(response.data?.export_job_id || 0);

        if (!Number.isFinite(exportJobId) || exportJobId <= 0) {
            notify('error', 'Dışa aktarma kuyruğa alınamadı');
            return;
        }

        notify('success', 'Dışa aktarım kuyruğa alındı');
        startPolling(exportJobId);
    } catch (_error) {
        notify('error', 'Dışa aktarma kuyruğa alınamadı');
    } finally {
        exportLoading.value = false;
    }
};

const handleDocumentClick = (event: MouseEvent): void => {
    const target = event.target as Node | null;
    if (!target || !exportMenuRef.value) {
        return;
    }

    if (!exportMenuRef.value.contains(target)) {
        exportMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleDocumentClick);
    stopPolling();
});

const title = props.title;
const endpoint = props.endpoint;
const columns = props.columns;
const filters = props.filters;
const filterConfig = props.filterConfig;
const statusLabel = props.statusLabel;
const perPage = props.perPage;
const emptyText = props.emptyText;
const createUrl = props.createUrl;
</script>
