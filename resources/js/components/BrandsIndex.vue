<template>
    <div>
        <ServerDataTable
            title="Markalar"
            endpoint="/admin/api/brands"
            :columns="columns"
            :filters="filters"
            selectable
            :filter-config="{
                search: true,
                category: false,
                brand: false,
                status: true,
                stock: false
            }"
            status-label="Durum"
            :per-page="50"
            column-toggle
            storage-key="brands-table"
            loaded-event-component="brands"
            empty-text="Marka bulunamadı"
            @selection-change="onSelectionChange"
            @rows-change="onRowsChange"
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
                    :alt="typeof row.name === 'string' ? row.name : 'Marka Görseli'"
                    width="75"
                    height="40"
                    loading="lazy"
                    class="table-product-image"
                >
                <i v-else class="las la-image h4"></i>
            </template>

            <template #cell-name="{ row }">
                <div v-if="isEditingName(row)" class="d-flex align-items-center">
                    <input
                        :data-inline-name-input="row.id"
                        v-model="editingNameValue"
                        type="text"
                        class="form-control form-control-sm"
                        :disabled="inlineEdit.isLoading(nameLoadingKey(row))"
                        @keydown.enter.prevent="saveNameEdit(row)"
                        @keydown.esc.prevent="cancelNameEdit(row)"
                        @blur="onNameBlur(row)"
                    >
                </div>
                <span
                    v-else
                    class="inline-edit-trigger"
                    title="Düzenlemek için tıkla"
                    @click="startNameEdit(row)"
                >
                    <span>{{ row.name || '-' }}</span>
                    <i class="las la-pen inline-edit-icon"></i>
                </span>
            </template>

            <template #cell-status="{ row }">
                <div class="custom-control custom-switch">
                    <input
                        :id="`brand-status-${row.id}`"
                        type="checkbox"
                        class="custom-control-input"
                        :checked="Number(row.status_value || 0) === 1"
                        :disabled="inlineEdit.isLoading(statusLoadingKey(row))"
                        @change="onStatusToggle(row, $event)"
                    >
                    <label class="custom-control-label" :for="`brand-status-${row.id}`"></label>
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
                        :disabled="inlineEdit.isLoading(sortOrderLoadingKey(row))"
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
                    <span>{{ value ?? '-' }}</span>
                    <i class="las la-pen inline-edit-icon"></i>
                </span>
                <input
                    type="hidden"
                    data-sort-order-input
                    :data-id="row.id"
                    :data-sort-url="row.sort_order_url"
                    :value="row.sort_order ?? value ?? 1"
                >
            </template>

            <template #cell-actions="{ row }">
                <a
                    href="javascript:;"
                    class="btn btn-secondary p-2 mr-1 brand-sort-handle"
                    title="Sürükle bırak"
                >
                    <i class="las la-arrows-alt"></i>
                </a>
                <a
                    v-if="typeof row.edit_url === 'string' && row.edit_url"
                    :href="String(row.edit_url)"
                    class="btn btn-info p-2"
                    title="Düzenle"
                >
                    <i class="las la-edit"></i>
                </a>
            </template>
        </ServerDataTable>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import ServerDataTable from './ServerDataTable.vue';
import { useBulkActions } from '../composables/useBulkActions';
import { useServerDatatableSortable } from '../composables/useServerDatatableSortable';
import { useServerDatatableInlineEdit } from '../composables/useServerDatatableInlineEdit';
import { dispatchServerDatatableRefresh } from '../shared/serverDatatableEvents';

interface RowData {
    id: number;
    name?: string;
    status?: string;
    status_value?: number;
    sort_order?: number;
    sort_order_url?: string;
    inline_update_url?: string;
}

const props = defineProps<{
    createUrl?: string;
}>();
const createUrl = props.createUrl || '/aka/catalog/brands/create';

const { loading: bulkLoading, performBulkAction } = useBulkActions('brands');
const selectedIds = ref<Array<string | number>>([]);
const rows = ref<RowData[]>([]);

const columns = [
    { key: 'image_url', label: 'Resim', className: 'text-left' },
    { key: 'name', label: 'Marka Adı' },
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

const notify = (type: 'success' | 'error', message: string): void => {
    const maybeNotify = (window as typeof window & { notify?: (level: string, text: string) => void }).notify;
    if (typeof maybeNotify === 'function') {
        maybeNotify(type, message);
    }
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

const dispatchRefresh = () => {
    dispatchServerDatatableRefresh({
        component: 'brands',
    });
};

const resolveErrorMessage = (error: unknown, fallback: string): string => {
    const maybeError = error as {
        response?: { message?: string; data?: { message?: string } };
        message?: string;
    };

    return maybeError?.response?.message
        || maybeError?.response?.data?.message
        || maybeError?.message
        || fallback;
};

const inlineDatatableEdit = useServerDatatableInlineEdit({
    resolveErrorMessage,
    notify: (type, message) => notify(type, message),
    dispatchRefresh,
    getCurrentPage,
    getPerPage,
});

const {
    inlineEdit,
    editingNameValue,
    editingSortOrderValue,
    isEditingName,
    isEditingSortOrder,
    statusLoadingKey,
    nameLoadingKey,
    sortOrderLoadingKey,
    startNameEdit,
    cancelNameEdit,
    saveNameEdit,
    onNameBlur,
    onStatusToggle,
    startSortOrderEdit,
    cancelSortOrderEdit,
    saveSortOrderEdit,
    onSortOrderBlur,
} = inlineDatatableEdit;

useServerDatatableSortable({
    componentKey: 'brands',
    rootSelector: '[data-vue="brands-index"]',
    handleSelector: '.brand-sort-handle',
    endpoint: '/aka/catalog/brands/sort',
    buildPayload: (order) => ({ order }),
    onSuccess: () => {
        notify('success', 'Sıralama güncellendi');
        dispatchRefresh();
    },
    onError: (error) => {
        notify('error', resolveErrorMessage(error, 'Sıralama güncellenemedi.'));
    },
});

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
    const success = await performBulkAction('/admin/api/brands/bulk', action, ids, {
        rows,
        getRowId: (row: RowData) => row.id,
        onSuccessLocalUpdate: (nextAction, nextIds, currentRows) => {
            const idSet = new Set(nextIds.map((id) => String(id)));

            if (nextAction === 'activate') {
                currentRows.forEach((currentRow) => {
                    const rowItem = currentRow as RowData;
                    if (idSet.has(String(rowItem.id))) {
                        rowItem.status_value = 1;
                        rowItem.status = 'Aktif';
                    }
                });
            }

            if (nextAction === 'deactivate') {
                currentRows.forEach((currentRow) => {
                    const rowItem = currentRow as RowData;
                    if (idSet.has(String(rowItem.id))) {
                        rowItem.status_value = 0;
                        rowItem.status = 'Pasif';
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



</script>

<style scoped>
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




