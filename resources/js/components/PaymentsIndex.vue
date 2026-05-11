<template>
    <div>
        <ServerDataTable
            title="Ödeme Raporları"
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
            status-label="Durum"
            stock-label="İşlem Tipi"
            stock-all-label="Tümü"
            stock-query-key="process_type"
            :stock-query-fallback-keys="['refund_status', 'stock_status']"
            :extra-filters-active="extraFiltersActive"
            :per-page="100"
            selectable
            column-toggle
            storage-key="payments-table"
            loaded-event-component="payments"
            empty-text="Ödeme bulunamadı"
            @selection-change="onSelectionChange"
            @rows-change="onRowsChange"
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
                            @click="exportPayments('filtered')"
                        >
                            CSV: Filtrelenenleri Dışa Aktar
                        </button>
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="isAnyExportBusy || selectedIds.length === 0"
                            @click="exportPayments('selected')"
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
                    <label class="vue-dt-label">Plasiyer</label>
                    <select v-model="salesmanId" class="form-control form-control-sm" @change="applyCustomFilters">
                        <option value="">Tümü</option>
                        <option v-for="option in salesmanOptions" :key="`salesman-${option.value}`" :value="String(option.value)">
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <div class="vue-dt-filter-item">
                    <label class="vue-dt-label">Banka</label>
                    <select v-model="bankIntegrationId" class="form-control form-control-sm" @change="applyCustomFilters">
                        <option value="">Tümü</option>
                        <option v-for="option in bankOptions" :key="`bank-${option.value}`" :value="String(option.value)">
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <div class="vue-dt-filter-item">
                    <label class="vue-dt-label">Tarih Başlangıç</label>
                    <input v-model="dateFrom" type="date" class="form-control form-control-sm" @change="applyCustomFilters">
                </div>

                <div class="vue-dt-filter-item">
                    <label class="vue-dt-label">Tarih Bitiş</label>
                    <input v-model="dateTo" type="date" class="form-control form-control-sm" @change="applyCustomFilters">
                </div>

            </template>

            <template #bulk-actions="{ selectedIds: slotSelectedIds }">
                <div class="d-flex align-items-center flex-wrap">
                    <span>{{ slotSelectedIds.length }} kayıt seçildi.</span>
                </div>
            </template>

            <template #cell-dealer_name="{ row }">
                <span>{{ row.dealer_name || '-' }}</span>
                <div v-if="row.formatted_phone_number" class="text-muted" style="font-size:12px;">
                    {{ row.formatted_phone_number }}
                </div>
            </template>

            <template #cell-bank_info="{ row }">
                <span>{{ row.bank_integration_name || '-' }}</span>
                <div class="text-muted" style="font-size:12px;">
                    {{ row.oid || '-' }}
                </div>
            </template>

            <template #cell-payment_info="{ row }">
                <div>
                    {{ row.formatted_amount_paid || '0,00' }} TL
                    <small class="text-muted">({{ row.amount_paid_usd || '0,00' }} USD)</small>
                </div>
                <div class="text-muted" style="font-size:12px;">
                    Taksit: {{ row.installment || 0 }}<br>
                    Komisyon: (%{{ row.commission_rate || 0 }}) {{ row.formatted_commission_amount || '0,00' }} TL<br>
                    Dolar Kuru: {{ row.usd_rate_info || '-' }}
                </div>
            </template>

            <template #cell-card_info="{ row }">
                <span>{{ row.card_name || '-' }}</span>
                <div class="text-muted" style="font-size:12px;">
                    {{ row.card_number || '-' }}
                </div>
            </template>

            <template #cell-option_3d_payment_label="{ row }">
                <span :class="row.option_3d_payment_class || 'badge bg-secondary'">{{ row.option_3d_payment_label || '-' }}</span>
            </template>

            <template #cell-status_label="{ row }">
                <span :class="row.status_class || 'badge bg-secondary'">{{ row.status_label || '-' }}</span>
                <div v-if="row.failure_reason" class="text-muted" style="font-size:12px;">
                    {{ row.failure_reason }}
                </div>
            </template>

            <template #cell-email_sent_label="{ row }">
                <span :class="row.email_sent_class || 'badge bg-secondary'">{{ row.email_sent_label || '-' }}</span>
            </template>

            <template #cell-erp_status_label="{ row }">
                <span :class="row.erp_status_class || 'badge bg-secondary'">{{ row.erp_status_label || '-' }}</span>
            </template>

            <template #cell-actions="{ row }">
                <div class="d-flex align-items-center justify-content-center flex-wrap">
                    <a
                        v-if="row.can_view_receipt && typeof row.receipt_url === 'string' && row.receipt_url"
                        :href="String(row.receipt_url)"
                        title="PDF Görüntüle"
                        class="btn btn-danger btn-sm mr-2 mb-1"
                        target="_blank"
                    >
                        PDF Görüntüle
                    </a>

                    <template v-if="row.refund_status_label">
                        <span :class="row.refund_status_class || 'badge bg-secondary'">{{ row.refund_status_label }}</span>
                        <small v-if="row.formatted_refund_date" class="text-muted ml-2">{{ row.formatted_refund_date }}</small>
                    </template>

                    <template v-else-if="row.status_is_success">
                        <button
                            v-if="row.can_refund && row.action_type === 'cancel'"
                            type="button"
                            class="btn btn-danger btn-sm mr-1 mb-1"
                            :disabled="isRefundLoading(Number(row.id))"
                            @click="updateRefundStatus(row, 'cancelled')"
                        >
                            {{ isRefundLoading(Number(row.id)) ? 'İşleniyor...' : 'İptal Et' }}
                        </button>

                        <button
                            v-if="row.can_refund && row.action_type === 'refund'"
                            type="button"
                            class="btn btn-warning btn-sm mb-1"
                            :disabled="isRefundLoading(Number(row.id))"
                            @click="updateRefundStatus(row, 'refunded')"
                        >
                            {{ isRefundLoading(Number(row.id)) ? 'İşleniyor...' : 'İade Et' }}
                        </button>
                    </template>
                </div>
            </template>
        </ServerDataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ServerDataTable from './ServerDataTable.vue';
import http from '../lib/http';

interface Option {
    value: string | number;
    label: string;
}

interface PaymentRow {
    id: number;
    status_is_success?: boolean;
    can_view_receipt?: boolean;
    refund_status?: string | null;
    refund_status_label?: string | null;
    refund_status_class?: string | null;
    formatted_refund_date?: string | null;
    can_refund?: boolean;
    action_type?: 'cancel' | 'refund' | null;
    refund_url?: string;
}

type ExportScope = 'filtered' | 'selected';
type RefundStatus = 'cancelled' | 'refunded';

const props = withDefaults(defineProps<{
    endpoint?: string;
    salesmanOptions?: Option[];
    bankOptions?: Option[];
}>(), {
    endpoint: '/admin/api/payments',
    salesmanOptions: () => [],
    bankOptions: () => [],
});

const readQuery = (key: string): string => {
    const params = new URLSearchParams(window.location.search);
    return params.get(key) || '';
};

const salesmanId = ref(readQuery('salesman_id'));
const bankIntegrationId = ref(readQuery('bank_integration_id'));
const dateFrom = ref(readQuery('date_from'));
const dateTo = ref(readQuery('date_to'));

const selectedIds = ref<Array<string | number>>([]);
const rows = ref<PaymentRow[]>([]);
const extraFiltersActive = computed(() => (
    salesmanId.value.trim() !== ''
    || bankIntegrationId.value.trim() !== ''
    || dateFrom.value.trim() !== ''
    || dateTo.value.trim() !== ''
));

const exportLoading = ref(false);
const exportQueueLoading = ref(false);
const exportMenuOpen = ref(false);
const exportMenuRef = ref<HTMLElement | null>(null);
const pollingTimer = ref<number | null>(null);
const pollingAttempt = ref(0);
const maxPollingAttempt = 120;

const refundLoadingIds = ref<Set<number>>(new Set());

const endpoint = computed(() => {
    const params = new URLSearchParams();

    if (salesmanId.value) {
        params.set('salesman_id', salesmanId.value);
    }

    if (bankIntegrationId.value) {
        params.set('bank_integration_id', bankIntegrationId.value);
    }

    if (dateFrom.value) {
        params.set('date_from', dateFrom.value);
    }

    if (dateTo.value) {
        params.set('date_to', dateTo.value);
    }

    const query = params.toString();
    return query ? `${props.endpoint}?${query}` : props.endpoint;
});

const filters = {
    categories: [],
    brands: [],
    statusOptions: [
        { value: '', label: 'Tümü' },
        { value: 'SUCCESS', label: 'Başarılı' },
        { value: 'FAILED', label: 'Başarısız' },
    ],
    stockStatusOptions: [
        { value: '', label: 'Tümü' },
        { value: 'payment', label: 'Ödeme' },
        { value: 'refunded', label: 'İade' },
        { value: 'cancelled', label: 'İptal' },
    ],
};

const columns = [
    { key: 'id', label: 'ID', className: 'text-left' },
    { key: 'salesman_name', label: 'Plasiyer' },
    { key: 'dealer_name', label: 'Bayi' },
    { key: 'bank_info', label: 'Banka Entegrasyonu', className: 'text-center' },
    { key: 'payment_info', label: 'Ödeme Bilgisi', className: 'text-center' },
    { key: 'card_info', label: 'Kart Bilgisi', className: 'text-center' },
    { key: 'explanation', label: 'Açıklama' },
    { key: 'option_3d_payment_label', label: '3D Ödeme', className: 'text-center' },
    { key: 'status_label', label: 'Durumu', className: 'text-center' },
    { key: 'email_sent_label', label: 'Mail Durumu', className: 'text-center' },
    { key: 'erp_status_label', label: 'ERP Durumu', className: 'text-center' },
    { key: 'formatted_completed_at', label: 'Tarih', className: 'text-center' },
    { key: 'actions', label: 'İşlemler', className: 'text-center' },
];

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

const onRowsChange = (nextRows: Array<Record<string, unknown>>) => {
    rows.value = nextRows as PaymentRow[];
};

const setOrDelete = (url: URL, key: string, value: string): void => {
    if (value.trim() !== '') {
        url.searchParams.set(key, value.trim());
        return;
    }

    url.searchParams.delete(key);
};

const syncCustomFilters = (triggerRefresh = true): void => {
    const url = new URL(window.location.href);

    setOrDelete(url, 'salesman_id', salesmanId.value);
    setOrDelete(url, 'bank_integration_id', bankIntegrationId.value);
    setOrDelete(url, 'date_from', dateFrom.value);
    setOrDelete(url, 'date_to', dateTo.value);

    url.searchParams.delete('page');

    const target = `${url.pathname}${url.search}${url.hash}`;
    const current = `${window.location.pathname}${window.location.search}${window.location.hash}`;

    if (target !== current) {
        window.history.pushState({}, '', target);
    }

    if (triggerRefresh) {
        window.dispatchEvent(new PopStateEvent('popstate'));
    }
};

const applyCustomFilters = (): void => {
    syncCustomFilters(true);
};

const clearCustomFilters = (triggerRefresh = true): void => {
    salesmanId.value = '';
    bankIntegrationId.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    syncCustomFilters(triggerRefresh);
};

const onClearExtraFilters = (): void => {
    clearCustomFilters(false);
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

    const allowedKeys = [
        'search',
        'q',
        'status',
        'process_type',
        'refund_status',
        'stock_status',
        'salesman_id',
        'bank_integration_id',
        'date_from',
        'date_to',
    ];

    allowedKeys.forEach((key) => {
        const value = query.get(key);
        if (value !== null && value !== '') {
            params[key] = value;
        }
    });

    return params;
};

const exportPayments = async (scope: ExportScope): Promise<void> => {
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

        const response = await http.get(`/admin/api/payments/export?${params.toString()}`, {
            responseType: 'blob',
        });

        const blob = response.data as Blob;
        const headers = response.headers as Record<string, string>;
        const fromHeader = parseContentDispositionFilename(String(headers['content-disposition'] || ''));
        const fallback = `payments-${scope}.csv`;

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

        const response = await http.post('/admin/api/payments/exports', payload);
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

const isRefundLoading = (rowId: number): boolean => refundLoadingIds.value.has(rowId);

const updateRefundStatus = async (row: Record<string, unknown>, status: RefundStatus): Promise<void> => {
    const rowId = Number(row.id || 0);
    const refundUrl = String(row.refund_url || '');

    if (!rowId || !refundUrl || refundLoadingIds.value.has(rowId)) {
        return;
    }

    const next = new Set(refundLoadingIds.value);
    next.add(rowId);
    refundLoadingIds.value = next;

    try {
        const response = await http.post(refundUrl, { status });

        if (response.data?.success !== true) {
            notify('error', response.data?.message || 'İşlem başarısız');
            return;
        }

        row.refund_status = status;
        row.refund_status_label = status === 'cancelled' ? 'İptal Edildi' : 'İade Edildi';
        row.refund_status_class = status === 'cancelled' ? 'badge bg-primary' : 'badge bg-info';
        row.can_refund = false;
        row.action_type = null;

        notify('success', response.data?.message || 'İşlem tamamlandı');
    } catch (_error) {
        notify('error', 'İşlem sırasında hata oluştu');
    } finally {
        const updated = new Set(refundLoadingIds.value);
        updated.delete(rowId);
        refundLoadingIds.value = updated;
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

const salesmanOptions = props.salesmanOptions;
const bankOptions = props.bankOptions;
</script>
