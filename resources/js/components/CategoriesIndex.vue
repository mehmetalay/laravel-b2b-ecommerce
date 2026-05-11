<template>
    <div>
        <ServerDataTable
            title="Kategoriler"
            :endpoint="endpoint"
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
            storage-key="categories-table"
            loaded-event-component="categories"
            empty-text="Kategori bulunamadı"
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
                    :alt="typeof row.name === 'string' ? row.name : 'Kategori Görseli'"
                    width="50"
                    height="50"
                    loading="lazy"
                    class="table-product-image"
                >
                <i v-else class="las la-photo-video category-image-placeholder" aria-label="Görsel Yok"></i>
            </template>

            <template #cell-subcategories_label="{ row }">
                <a
                    v-if="Number(row.subcategories_count || 0) > 0 && typeof row.subcategories_url === 'string' && row.subcategories_url"
                    :href="String(row.subcategories_url)"
                    class="badge outline-badge-info"
                >
                    {{ row.subcategories_label || `${row.subcategories_count} alt kategori var` }}
                    <i class="las la-level-down-alt"></i>
                </a>
                <span v-else class="text-muted">Alt kategori yok</span>
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
                        :id="`category-status-${row.id}`"
                        type="checkbox"
                        class="custom-control-input"
                        :checked="Number(row.status_value || 0) === 1"
                        :disabled="inlineEdit.isLoading(statusLoadingKey(row))"
                        @change="onStatusToggle(row, $event)"
                    >
                    <label class="custom-control-label" :for="`category-status-${row.id}`"></label>
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
                    class="btn btn-secondary font-15 p-2 mr-1 category-sort-handle"
                    title="Sürükle bırak"
                >
                    <i class="las la-arrows-alt"></i>
                </a>

                <a
                    :href="String(row.edit_url)"
                    class="btn btn-info font-15 p-2"
                    title="Düzenle"
                >
                    <i class="las la-edit"></i>
                </a>
            </template>
        </ServerDataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import ServerDataTable from './ServerDataTable.vue';
import { useBulkActions } from '../composables/useBulkActions';
import { useServerDatatableSortable } from '../composables/useServerDatatableSortable';
import { useServerDatatableInlineEdit } from '../composables/useServerDatatableInlineEdit';
import { dispatchServerDatatableRefresh } from '../shared/serverDatatableEvents';

const props = defineProps<{
    parentId?: number | string | null;
    createUrl?: string;
}>();

const createUrl = computed(() => props.createUrl || '/aka/catalog/categories/create');

interface RowData {
    id: number;
    name?: string;
    status?: string;
    status_value?: number;
    sort_order?: number;
    sort_order_url?: string;
    inline_update_url?: string;
}

const endpoint = computed(() => {
    if (props.parentId === undefined || props.parentId === null || String(props.parentId).trim() === '') {
        return '/admin/api/categories';
    }

    return `/admin/api/categories?parent_id=${encodeURIComponent(String(props.parentId))}`;
});

const { loading: bulkLoading, performBulkAction } = useBulkActions('categories');
const selectedIds = ref<Array<string | number>>([]);
const rows = ref<RowData[]>([]);

const columns = [
    { key: 'image_url', label: 'Resim', type: 'image', className: 'text-left' },
    { key: 'name', label: 'Kategori Adı' },
    { key: 'subcategories_label', label: 'Alt Kategori', className: 'text-center' },
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
        component: 'categories',
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

const resolveSortScopeValue = (): number | string | null => {
    if (props.parentId === undefined || props.parentId === null) {
        return null;
    }

    if (typeof props.parentId === 'string' && props.parentId.trim() === '') {
        return null;
    }

    return props.parentId;
};

const inlineDatatableEdit = useServerDatatableInlineEdit({
    resolveErrorMessage,
    notify: (type, message) => notify(type, message),
    dispatchRefresh,
    getCurrentPage,
    getPerPage,
    loadingKeyPrefix: 'category',
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
    componentKey: 'categories',
    rootSelector: '[data-vue="categories-index"]',
    handleSelector: '.category-sort-handle',
    endpoint: '/aka/catalog/categories/sort',
    buildPayload: (order) => ({
        value: resolveSortScopeValue(),
        order,
    }),
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
    const success = await performBulkAction('/admin/api/categories/bulk', action, ids, {
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





