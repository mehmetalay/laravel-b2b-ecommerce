<template>
    <div>
        <ServerDataTable
            title="Slider Yönetimi"
            :endpoint="endpointWithType"
            :columns="columns"
            :filters="filters"
            selectable
            :filter-config="{
                search: false,
                category: false,
                brand: false,
                status: true,
                stock: false
            }"
            status-label="Durum"
            :per-page="50"
            column-toggle
            storage-key="sliders-table"
            loaded-event-component="sliders"
            empty-text="Slider bulunamadı"
            :extra-filters-active="extraFiltersActive"
            @selection-change="onSelectionChange"
            @rows-change="onRowsChange"
            @clear-extra-filters="onClearExtraFilters"
        >
            <template #header-actions>
                <a :href="createUrl" class="btn btn-primary btn-sm">
                    <i class="las la-plus"></i> Yeni
                </a>
            </template>

            <template #bulk-actions="{ selectedIds: slotSelectedIds, clearSelection }">
                <div class="d-flex align-items-center flex-wrap">
                    <span class="mr-3">{{ slotSelectedIds.length }} kayıt seçildi</span>
                    <button
                        type="button"
                        class="btn btn-sm btn-success mr-2 mb-2"
                        :disabled="bulkLoading || slotSelectedIds.length === 0 || isSorting"
                        @click="runBulkAction('activate', slotSelectedIds, clearSelection)"
                    >
                        <span v-if="bulkLoading" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        {{ bulkLoading ? 'İşleniyor...' : 'Aktif Yap' }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-warning mr-2 mb-2"
                        :disabled="bulkLoading || slotSelectedIds.length === 0 || isSorting"
                        @click="runBulkAction('deactivate', slotSelectedIds, clearSelection)"
                    >
                        <span v-if="bulkLoading" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        {{ bulkLoading ? 'İşleniyor...' : 'Pasif Yap' }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-danger mb-2"
                        :disabled="bulkLoading || slotSelectedIds.length === 0 || isSorting"
                        @click="runBulkAction('delete', slotSelectedIds, clearSelection)"
                    >
                        <span v-if="bulkLoading" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        {{ bulkLoading ? 'İşleniyor...' : 'Sil' }}
                    </button>
                </div>
            </template>

            <template #extra-filters>
                <div class="vue-dt-filter-item">
                    <label class="vue-dt-label">Slider Türü</label>
                    <select
                        v-model="selectedType"
                        class="form-control form-control-sm"
                        @change="onTypeFilterChange"
                    >
                        <option value="">Tüm Tipler</option>
                        <option
                            v-for="option in normalizedTypeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </template>

            <template #cell-admin_image="{ row }">
                <img
                    v-if="typeof row.admin_image === 'string' && row.admin_image"
                    :src="row.admin_image"
                    :alt="typeof row.type_text === 'string' ? row.type_text : 'Slider Görseli'"
                    width="50"
                    height="50"
                    loading="lazy"
                    class="table-product-image"
                >
                <i v-else class="las la-image h4"></i>
            </template>

            <template #cell-status="{ row }">
                <div class="custom-control custom-switch">
                    <input
                        :id="`slider-status-${row.id}`"
                        type="checkbox"
                        class="custom-control-input"
                        :checked="Number(row.status_value || 0) === 1"
                        :disabled="inlineEdit.isLoading(statusLoadingKey(row)) || isSorting || isDeleting"
                        @change="onStatusToggle(row, $event)"
                    >
                    <label class="custom-control-label" :for="`slider-status-${row.id}`"></label>
                </div>
            </template>

            <template #cell-sort_order="{ row, value }">
                <div v-if="isEditingSortOrder(row)" class="d-flex align-items-center justify-content-center">
                    <input
                        :data-inline-sort-order-input="row.id"
                        v-model="editingSortOrderValue"
                        type="number"
                        min="1"
                        class="form-control form-control-sm text-center"
                        style="max-width:100px;"
                        :disabled="inlineEdit.isLoading(sortOrderLoadingKey(row)) || isSorting || isDeleting"
                        @keydown.enter.prevent="saveSortOrderEdit(row)"
                        @keydown.esc.prevent="cancelSortOrderEdit(row)"
                        @blur="onSortOrderBlur(row)"
                    >
                </div>
                <span
                    v-else
                    class="inline-edit-trigger inline-edit-trigger--center"
                    title="Düzenlemek için tıkla"
                    @click="startSortOrderEdit(row)"
                >
                    <span>{{ value ?? row.sort_order ?? '-' }}</span>
                    <i class="las la-pen inline-edit-icon"></i>
                </span>
                <input
                    type="hidden"
                    data-sort-order-input
                    :data-id="row.id"
                    :value="row.sort_order ?? value ?? 1"
                >
            </template>

            <template #cell-actions="{ row }">
                <a
                    href="javascript:;"
                    class="btn btn-secondary font-15 p-2 mr-1 slider-sort-handle"
                    :class="{ disabled: isSortDisabled || isSorting || isDeleting || isEditingAnySortOrder }"
                    :title="isSortDisabled ? sortDisabledTitle : 'Sürükle bırak'"
                    :aria-disabled="isSortDisabled || isSorting || isDeleting || isEditingAnySortOrder"
                >
                    <i class="las la-arrows-alt"></i>
                </a>

                <a
                    v-if="typeof row.edit_url === 'string' && row.edit_url"
                    :href="String(row.edit_url)"
                    class="btn btn-info font-15 p-2 mr-1"
                    title="Düzenle"
                >
                    <i class="las la-edit"></i>
                </a>

                <a
                    href="javascript:;"
                    class="btn btn-danger font-15 p-2"
                    :class="{ disabled: isRowDeleting(row.id) || isSorting }"
                    title="Sil"
                    @click.prevent="onDelete(row)"
                >
                    <i
                        class="las"
                        :class="isRowDeleting(row.id) ? 'la-spinner la-spin' : 'la-trash'"
                    ></i>
                </a>
            </template>
        </ServerDataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import Sortable from 'sortablejs';
import ServerDataTable from './ServerDataTable.vue';
import http from '../lib/http';
import { useServerDatatableInlineEdit } from '../composables/useServerDatatableInlineEdit';
import {
    SERVER_DATATABLE_LOADED_EVENT,
    dispatchServerDatatableRefresh,
    type ServerDatatableLoadedDetail,
} from '../shared/serverDatatableEvents';

interface SliderRow {
    id: number | string;
    admin_image?: string | null;
    type?: string;
    type_text?: string;
    status?: string;
    status_value?: number;
    sort_order?: number;
    edit_url?: string;
    delete_url?: string;
    inline_update_url?: string;
}

interface SliderTypeOption {
    value: string;
    label: string;
}

const props = defineProps<{
    currentType?: string;
    typeOptions?: SliderTypeOption[];
    createUrl?: string;
    indexUrl?: string;
    sortUrl?: string;
    endpoint?: string;
}>();

const createUrl = props.createUrl || '/aka/settings/design-settings/sliders/create';
const sortUrl = props.sortUrl || '/aka/settings/design-settings/sliders/sort';
const endpoint = props.endpoint || '/admin/api/sliders';
const bulkEndpoint = '/admin/api/sliders/bulk';
const defaultType = (props.currentType || 'slider').trim();

const rows = ref<SliderRow[]>([]);
const selectedIds = ref<Array<string | number>>([]);
const selectedType = ref(defaultType);
const deletingIds = ref<Record<string, boolean>>({});
const bulkLoading = ref(false);
const isSorting = ref(false);
const currentStatus = ref('');
const currentPage = ref(1);

const normalizedTypeOptions = computed<SliderTypeOption[]>(() => {
    if (Array.isArray(props.typeOptions) && props.typeOptions.length > 0) {
        return props.typeOptions;
    }

    return [
        { value: 'slider', label: 'Slider' },
        { value: 'payment_slider', label: 'Ödeme Slider' },
        { value: 'category_slider', label: 'Kategori Slider' },
        { value: 'campaign_slider', label: 'Kampanya Slider' },
    ];
});

const columns = [
    { key: 'admin_image', label: 'Görsel', className: 'text-left' },
    { key: 'type_text', label: 'Tip' },
    { key: 'status', label: 'Durum', className: 'text-center' },
    { key: 'sort_order', label: 'Sıra', className: 'text-center' },
    { key: 'actions', label: 'İşlemler', className: 'text-center' },
];

const filters = {
    categories: [],
    brands: [],
    statusOptions: [
        { value: '', label: 'Tüm Durumlar' },
        { value: '1', label: 'Aktif' },
        { value: '0', label: 'Pasif' },
    ],
    stockStatusOptions: [],
};

const endpointWithType = computed(() => {
    const params = new URLSearchParams();

    if (selectedType.value !== '') {
        params.set('type', selectedType.value);
    }

    const query = params.toString();
    return query ? `${endpoint}?${query}` : endpoint;
});

const sortDisabledTitle = 'Sıralama için filtreleri temizleyip ilk sayfada olun.';

const isDeleting = computed<boolean>(() => Object.values(deletingIds.value).some(Boolean));
const extraFiltersActive = computed<boolean>(() => {
    return selectedType.value !== '' && selectedType.value !== defaultType;
});

const isSortDisabled = computed<boolean>(() => {
    return selectedType.value === ''
        || currentStatus.value !== ''
        || currentPage.value !== 1;
});

const notify = (type: 'success' | 'error' | 'warning', message: string): void => {
    const maybeNotify = (window as typeof window & { notify?: (level: string, text: string) => void }).notify;
    if (typeof maybeNotify === 'function') {
        maybeNotify(type, message);
    }
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

const syncStateFromQuery = (): void => {
    const params = new URLSearchParams(window.location.search);

    currentStatus.value = (params.get('status') || '').trim();

    const pageValue = Number.parseInt(params.get('page') || '1', 10);
    currentPage.value = Number.isFinite(pageValue) && pageValue > 0 ? pageValue : 1;
};

const getPerPage = (): number => {
    const params = new URLSearchParams(window.location.search);
    const perPage = Number.parseInt(params.get('per_page') || '50', 10);
    return Number.isFinite(perPage) && perPage > 0 ? perPage : 50;
};

const dispatchRefresh = (page = 1): void => {
    dispatchServerDatatableRefresh({
        component: 'sliders',
        page,
    });
};

const onTypeFilterChange = (): void => {
    dispatchRefresh(1);
};

const onClearExtraFilters = (): void => {
    selectedType.value = defaultType;
    dispatchRefresh(1);
};

const onSelectionChange = (ids: Array<string | number>): void => {
    selectedIds.value = ids;
};

const onRowsChange = (nextRows: Array<Record<string, unknown>>) => {
    rows.value = nextRows as SliderRow[];
};

const isRowDeleting = (id: unknown): boolean => {
    return deletingIds.value[String(id)] === true;
};

const inlineDatatableEdit = useServerDatatableInlineEdit({
    resolveErrorMessage,
    notify: (type, message) => notify(type, message),
    dispatchRefresh: () => dispatchRefresh(currentPage.value),
    getCurrentPage: () => currentPage.value,
    getPerPage,
    loadingKeyPrefix: 'slider',
});

const {
    inlineEdit,
    editingSortOrderValue,
    isEditingSortOrder,
    statusLoadingKey,
    sortOrderLoadingKey,
    startSortOrderEdit: startSortOrderEditBase,
    cancelSortOrderEdit,
    saveSortOrderEdit,
    onSortOrderBlur,
    onStatusToggle,
} = inlineDatatableEdit;

const startSortOrderEdit = async (row: Record<string, unknown>): Promise<void> => {
    if (isSorting.value || isDeleting.value) {
        return;
    }

    await startSortOrderEditBase(row as unknown as { id: number; sort_order?: number });
};

const isEditingAnySortOrder = computed<boolean>(() => {
    return rows.value.some((row) => isEditingSortOrder(row as unknown as { id: number }));
});

const onDelete = async (row: Record<string, unknown>) => {
    const id = row.id;
    const deleteUrl = typeof row.delete_url === 'string' ? row.delete_url : '';

    if (!deleteUrl || isSorting.value || isRowDeleting(id)) {
        return;
    }

    if (!window.confirm('Bu kaydı silmek istediğinizden emin misiniz?')) {
        return;
    }

    const rowKey = String(id);
    deletingIds.value[rowKey] = true;

    try {
        const response = await http.delete(deleteUrl, {
            suppressValidationToast: true,
        });

        const message = typeof response?.data?.message === 'string'
            ? response.data.message
            : 'İşlem başarıyla tamamlandı.';

        notify('success', message);
        dispatchRefresh(currentPage.value);
    } catch (error) {
        notify('error', resolveErrorMessage(error, 'İşlem başarısız.'));
    } finally {
        deletingIds.value[rowKey] = false;
    }
};

const runBulkAction = async (
    action: 'activate' | 'deactivate' | 'delete',
    ids: Array<string | number>,
    clearSelection: () => void
): Promise<void> => {
    if (bulkLoading.value || ids.length === 0 || isSorting.value) {
        return;
    }

    if (action === 'delete') {
        const confirmed = window.confirm(`${ids.length} kayıt silinecek. Devam etmek istiyor musunuz?`);
        if (!confirmed) {
            return;
        }
    }

    bulkLoading.value = true;

    try {
        const response = await http.patch(bulkEndpoint, {
            action,
            ids,
        }, {
            suppressValidationToast: true,
        });

        const message = typeof response?.data?.message === 'string'
            ? response.data.message
            : 'İşlem başarıyla tamamlandı.';

        notify('success', message);
        clearSelection();
        selectedIds.value = [];
        dispatchRefresh(currentPage.value);
    } catch (error) {
        notify('error', resolveErrorMessage(error, 'İşlem başarısız.'));
    } finally {
        bulkLoading.value = false;
    }
};

type ServerDatatableLoadedEvent = CustomEvent<ServerDatatableLoadedDetail>;

let sortable: Sortable | null = null;
let sortableTbody: HTMLTableSectionElement | null = null;

const destroySortable = (): void => {
    sortable?.destroy();
    sortable = null;
    sortableTbody = null;
};

const initSortable = (): void => {
    const root = document.querySelector<HTMLElement>('[data-vue="sliders-index"]');
    const tbody = root?.querySelector<HTMLTableSectionElement>('tbody');

    if (!tbody) {
        destroySortable();
        return;
    }

    if (sortable && sortableTbody === tbody) {
        return;
    }

    destroySortable();

    if (tbody.querySelectorAll('.slider-sort-handle').length === 0) {
        return;
    }

    sortable = Sortable.create(tbody, {
        animation: 150,
        handle: '.slider-sort-handle',
        onEnd: async () => {
            if (isSortDisabled.value || isSorting.value || isDeleting.value || isEditingAnySortOrder.value) {
                notify('warning', sortDisabledTitle);
                dispatchRefresh(currentPage.value);
                return;
            }

            const order = Array.from(tbody.querySelectorAll<HTMLTableRowElement>('tr[data-id]'))
                .map((tableRow, index) => ({
                    id: Number(tableRow.dataset.id || 0),
                    sort_order: index + 1,
                }))
                .filter((item) => item.id > 0);

            if (order.length === 0) {
                return;
            }

            try {
                isSorting.value = true;
                const response = await http.post(sortUrl, {
                    value: selectedType.value || null,
                    order,
                }, {
                    suppressValidationToast: true,
                });

                const message = typeof response?.data?.message === 'string'
                    ? response.data.message
                    : 'Sıralama güncellendi.';
                notify('success', message);
                dispatchRefresh(currentPage.value);
            } catch (error) {
                notify('error', resolveErrorMessage(error, 'Sıralama güncellenemedi.'));
                dispatchRefresh(currentPage.value);
            } finally {
                isSorting.value = false;
            }
        },
    });

    sortableTbody = tbody;
};

const handleDatatableLoaded = (event: Event): void => {
    const customEvent = event as ServerDatatableLoadedEvent;
    if (customEvent?.detail?.component !== 'sliders') {
        return;
    }

    syncStateFromQuery();
    initSortable();
};

onMounted(() => {
    syncStateFromQuery();
    window.addEventListener(SERVER_DATATABLE_LOADED_EVENT, handleDatatableLoaded as EventListener);
    window.setTimeout(initSortable, 150);
});

onBeforeUnmount(() => {
    window.removeEventListener(SERVER_DATATABLE_LOADED_EVENT, handleDatatableLoaded as EventListener);
    destroySortable();
});
</script>

<style scoped>
.slider-sort-handle.disabled {
    pointer-events: none;
    opacity: 0.45;
    cursor: not-allowed;
}

.table-product-image {
    object-fit: cover;
}

.inline-edit-trigger {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.inline-edit-trigger--center {
    justify-content: center;
}

.inline-edit-trigger .inline-edit-icon {
    opacity: 0;
    transition: opacity .15s ease;
    font-size: 14px;
}

.inline-edit-trigger:hover .inline-edit-icon {
    opacity: 1;
}
</style>
