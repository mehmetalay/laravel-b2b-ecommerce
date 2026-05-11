<template>
    <div>
        <ServerDataTable
            title="Siparişler"
            :endpoint="endpoint"
            :columns="columns"
            :filters="filters"
            :filter-config="{
                search: true,
                category: false,
                brand: false,
                status: true,
                stock: true
            }"
            status-label="Sipariş Durumu"
            stock-label="ERP Durumu"
            stock-all-label="Tümü"
            :extra-filters-active="extraFiltersActive"
            :per-page="100"
            selectable
            column-toggle
            storage-key="orders-table"
            loaded-event-component="orders"
            empty-text="Sipariş bulunamadı"
            @selection-change="onSelectionChange"
            @clear-extra-filters="onClearExtraFilters"
        >
            <template #header-actions>
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
                            @click="exportOrders('filtered')"
                        >
                            CSV: Filtrelenenleri Dışa Aktar
                        </button>
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="isAnyExportBusy || selectedIds.length === 0"
                            @click="exportOrders('selected')"
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

            <template #extra-filters>
                <div class="vue-dt-filter-item">
                    <label class="vue-dt-label">Tarih Başlangıç</label>
                    <input v-model="firstDate" type="date" class="form-control form-control-sm" @change="onDateFilterChange">
                </div>
                <div class="vue-dt-filter-item">
                    <label class="vue-dt-label">Tarih Bitiş</label>
                    <input v-model="lastDate" type="date" class="form-control form-control-sm" @change="onDateFilterChange">
                </div>
            </template>

            <template #bulk-actions="{ selectedIds: slotSelectedIds }">
                <div class="d-flex align-items-center flex-wrap">
                    <span>{{ slotSelectedIds.length }} kayıt seçildi.</span>
                </div>
            </template>

            <template #cell-creator_type_label="{ row }">
                <span :class="row.creator_type_class || 'badge bg-secondary'">{{ row.creator_type_label || '-' }}</span>
            </template>

            <template #cell-dealer_name="{ row }">
                <span>{{ row.dealer_name || '-' }}</span>
                <div v-if="row.sub_dealer_name" class="text-muted" style="font-size:12px;">
                    Alt Bayi: {{ row.sub_dealer_name }}
                </div>
            </template>

            <template #cell-email_sent_label="{ row }">
                <span :class="row.email_sent_class || 'badge bg-secondary'">{{ row.email_sent_label || '-' }}</span>
            </template>

            <template #cell-order_status="{ row }">
                <span :class="`badge ${row.order_status_class || 'bg-secondary'}`">{{ row.order_status || '-' }}</span>
            </template>

            <template #cell-erp_status_label="{ row }">
                <span :class="row.erp_status_class || 'badge bg-secondary'">{{ row.erp_status_label || '-' }}</span>
            </template>

            <template #cell-actions="{ row }">
                <a
                    v-if="typeof row.show_url === 'string' && row.show_url"
                    :href="String(row.show_url)"
                    title="Detay"
                    class="btn btn-info font-15 p-2"
                >
                    <i class="las la-eye"></i>
                </a>
            </template>
        </ServerDataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ServerDataTable from './ServerDataTable.vue';
import http from '../lib/http';
import { dispatchServerDatatableRefresh } from '../shared/serverDatatableEvents';

type ExportScope = 'filtered' | 'selected';

const readQueryDate = (key: string): string => {
    const params = new URLSearchParams(window.location.search);
    return params.get(key) || '';
};

const firstDate = ref(readQueryDate('first_date'));
const lastDate = ref(readQueryDate('last_date'));
const selectedIds = ref<Array<string | number>>([]);
const extraFiltersActive = computed(() => firstDate.value.trim() !== '' || lastDate.value.trim() !== '');

const exportLoading = ref(false);
const exportQueueLoading = ref(false);
const exportMenuOpen = ref(false);
const exportMenuRef = ref<HTMLElement | null>(null);
const pollingTimer = ref<number | null>(null);
const pollingAttempt = ref(0);
const maxPollingAttempt = 120;

const endpoint = computed(() => {
    const params = new URLSearchParams();

    if (firstDate.value) {
        params.set('first_date', firstDate.value);
    }

    if (lastDate.value) {
        params.set('last_date', lastDate.value);
    }

    const query = params.toString();
    return query ? `/admin/api/orders?${query}` : '/admin/api/orders';
});

const columns = [
    { key: 'id', label: '#', className: 'text-left' },
    { key: 'creator_type_label', label: 'Kullanıcı Türü', className: 'text-center' },
    { key: 'salesman_name', label: 'Plasiyer' },
    { key: 'dealer_name', label: 'Bayi' },
    { key: 'total_amount', label: 'Toplam Sipariş Tutarı', className: 'text-center' },
    { key: 'formatted_created_at', label: 'Sipariş Tarihi', className: 'text-center' },
    { key: 'email_sent_label', label: 'Mail Durumu', className: 'text-center' },
    { key: 'order_status', label: 'Sipariş Durumu', className: 'text-center' },
    { key: 'erp_status_label', label: 'ERP Durumu', className: 'text-center' },
    { key: 'actions', label: 'İşlemler', className: 'text-center' },
];

const filters = {
    categories: [],
    brands: [],
    statusOptions: [
        { value: '', label: 'Tümü' },
    ],
    stockStatusOptions: [
        { value: '', label: 'Tümü' },
    ],
};

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

const onSelectionChange = (ids: Array<string | number>) => {
    selectedIds.value = ids;
};

const syncDateQuery = (triggerRefresh = true): void => {
    const url = new URL(window.location.href);

    if (firstDate.value) {
        url.searchParams.set('first_date', firstDate.value);
    } else {
        url.searchParams.delete('first_date');
    }

    if (lastDate.value) {
        url.searchParams.set('last_date', lastDate.value);
    } else {
        url.searchParams.delete('last_date');
    }

    const next = `${url.pathname}${url.search}${url.hash}`;
    const current = `${window.location.pathname}${window.location.search}${window.location.hash}`;

    if (next !== current) {
        window.history.pushState({}, '', next);
    }

    if (triggerRefresh) {
        dispatchServerDatatableRefresh({
            component: 'orders',
        });
    }
};

const onDateFilterChange = (): void => {
    syncDateQuery(true);
};

const clearDateFilters = (triggerRefresh = true): void => {
    firstDate.value = '';
    lastDate.value = '';
    syncDateQuery(triggerRefresh);
};

const onClearExtraFilters = (): void => {
    clearDateFilters(false);
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

    const allowedKeys = ['search', 'q', 'status', 'stock_status', 'first_date', 'last_date'];
    allowedKeys.forEach((key) => {
        const value = query.get(key);
        if (value !== null && value !== '') {
            params[key] = value;
        }
    });

    return params;
};

const exportOrders = async (scope: ExportScope): Promise<void> => {
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

        const response = await http.get(`/admin/api/orders/export?${params.toString()}`, {
            responseType: 'blob',
        });

        const blob = response.data as Blob;
        const headers = response.headers as Record<string, string>;
        const fromHeader = parseContentDispositionFilename(String(headers['content-disposition'] || ''));
        const fallback = `orders-${scope}.csv`;

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

        const response = await http.post('/admin/api/orders/exports', payload);
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
</script>
