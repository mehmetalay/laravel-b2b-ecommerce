<template>
    <div>
        <ServerDataTable
            title="Özellik Değerleri"
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
            storage-key="attribute-values-table"
            loaded-event-component="attribute-values"
            empty-text="Özellik değeri bulunamadı"
            @selection-change="onSelectionChange"
            @rows-change="onRowsChange"
        >
            <template #header-actions>
                <button type="button" class="btn btn-primary btn-sm" @click="openCreateModal">
                    <i class="las la-plus"></i> Yeni
                </button>
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
                        :id="`attribute-value-status-${row.id}`"
                        type="checkbox"
                        class="custom-control-input"
                        :checked="Number(row.status_value || 0) === 1"
                        :disabled="inlineEdit.isLoading(statusLoadingKey(row))"
                        @change="onStatusToggle(row, $event)"
                    >
                    <label class="custom-control-label" :for="`attribute-value-status-${row.id}`"></label>
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
                    class="btn btn-secondary font-15 p-2 mr-1 attribute-value-sort-handle"
                    title="Sürükle bırak"
                >
                    <i class="las la-arrows-alt"></i>
                </a>
                <button
                    v-if="typeof row.edit_url === 'string' && row.edit_url"
                    type="button"
                    class="btn btn-info font-15 p-2 mr-1"
                    title="Düzenle"
                    @click="openEditModal(row)"
                >
                    <i class="las la-edit"></i>
                </button>
                <a
                    v-if="typeof row.delete_url === 'string' && row.delete_url"
                    href="javascript:;"
                    class="btn btn-danger font-15 p-2"
                    title="Sil"
                    data-selector="row-delete"
                    :data-url="String(row.delete_url)"
                >
                    <i class="las la-trash"></i>
                </a>
            </template>
        </ServerDataTable>

        <FormModal
            v-model:show="show"
            :title="mode.value === 'edit' ? 'Özellik Değeri Düzenle' : 'Özellik Değeri Yeni'"
            :loading="loading"
            @close="onModalClose"
            @submit="submitForm"
        >
            <div class="form-group">
                <label for="attribute-value-name">Adı</label>
                <input
                    id="attribute-value-name"
                    v-model="data.name"
                    name="name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': !!fieldError('name') }"
                >
                <small v-if="fieldError('name')" class="invalid-feedback d-block">{{ fieldError('name') }}</small>
            </div>

            <div class="form-group mt-3">
                <label for="attribute-value-name-en">Adı (EN)</label>
                <input
                    id="attribute-value-name-en"
                    v-model="data.name_en"
                    name="name_en"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': !!fieldError('name_en') }"
                >
                <small v-if="fieldError('name_en')" class="invalid-feedback d-block">{{ fieldError('name_en') }}</small>
            </div>

            <div class="form-group mt-3">
                <div class="custom-control custom-checkbox">
                    <input id="attribute-value-status" v-model="data.status" type="checkbox" class="custom-control-input">
                    <label class="custom-control-label" for="attribute-value-status">Aktif</label>
                </div>
            </div>

            <div class="form-group mt-2">
                <div class="custom-control custom-checkbox">
                    <input id="attribute-value-show-filter" v-model="data.show_in_filter" type="checkbox" class="custom-control-input">
                    <label class="custom-control-label" for="attribute-value-show-filter">Filtrede göster</label>
                </div>
            </div>
        </FormModal>
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import ServerDataTable from './ServerDataTable.vue';
import FormModal from './modals/FormModal.vue';
import { useModalForm } from '../composables/useModalForm';
import { useBulkActions } from '../composables/useBulkActions';
import { useServerDatatableSortable } from '../composables/useServerDatatableSortable';
import { useServerDatatableInlineEdit } from '../composables/useServerDatatableInlineEdit';
import { dispatchServerDatatableRefresh } from '../shared/serverDatatableEvents';

const props = defineProps<{
    attributeId: number | string;
}>();

interface AttributeValueFormData {
    id: number | null;
    name: string;
    name_en: string;
    status: boolean;
    show_in_filter: boolean;
}

interface RowData {
    id: number;
    name?: string;
    status?: string;
    edit_url?: string;
    inline_update_url?: string;
    status_value?: number;
    sort_order?: number;
    sort_order_url?: string;
}

const endpoint = computed(() => (
    `/admin/api/attributes/${encodeURIComponent(String(props.attributeId))}/attribute-values`
));

const form = useModalForm<AttributeValueFormData>({
    id: null,
    name: '',
    name_en: '',
    status: true,
    show_in_filter: true,
});

const { show, data, errors, loading, mode, openCreate, openEdit, submit, reset } = form;
const { loading: bulkLoading, performBulkAction } = useBulkActions('attribute-values');
const selectedIds = ref<Array<string | number>>([]);
const rows = ref<RowData[]>([]);

const columns = [
    { key: 'id', label: 'ID', className: 'text-left' },
    { key: 'name', label: 'Adı' },
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

const fieldError = (field: string): string => errors.value[field]?.[0] || '';

const notifySuccess = (message: string): void => {
    const maybeNotify = (window as typeof window & { notify?: (type: string, text: string) => void }).notify;
    if (typeof maybeNotify === 'function') {
        maybeNotify('success', message);
    }
};

const notifyError = (message: string): void => {
    const maybeNotify = (window as typeof window & { notify?: (type: string, text: string) => void }).notify;
    if (typeof maybeNotify === 'function') {
        maybeNotify('error', message);
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
        component: 'attribute-values',
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
    notify: (type, message) => {
        if (type === 'success') {
            notifySuccess(message);
            return;
        }

        notifyError(message);
    },
    dispatchRefresh,
    getCurrentPage,
    getPerPage,
    loadingKeyPrefix: 'attribute-value',
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
    componentKey: 'attribute-values',
    rootSelector: '[data-vue="attribute-values-index"]',
    handleSelector: '.attribute-value-sort-handle',
    endpoint: () => `/aka/catalog/product-attributes/attributes/${props.attributeId}/attribute-values/sort`,
    buildPayload: (order) => ({ order }),
    onSuccess: () => {
        notifySuccess('Sıralama güncellendi');
        dispatchRefresh();
    },
    onError: (error) => {
        notifyError(resolveErrorMessage(error, 'Sıralama güncellenemedi.'));
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
    const success = await performBulkAction('/admin/api/attribute-values/bulk', action, ids, {
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

const openCreateModal = () => {
    openCreate(`/aka/catalog/product-attributes/attributes/${props.attributeId}/attribute-values`, {
        id: null,
        name: '',
        name_en: '',
        status: true,
        show_in_filter: true,
    }, 'post');
};

const openEditModal = async (row: RowData) => {
    if (!row.edit_url) {
        return;
    }

    await openEdit(String(row.edit_url), {
        submitEndpoint: `/aka/catalog/product-attributes/attributes/${props.attributeId}/attribute-values/${row.id}`,
        submitMethod: 'patch',
        mapData: (payload) => {
            const item = payload as Partial<AttributeValueFormData>;
            return {
                id: row.id,
                name: String(item.name || ''),
                name_en: String(item.name_en || ''),
                status: Boolean(item.status),
                show_in_filter: Boolean(item.show_in_filter),
            };
        },
    });
};

const submitForm = async () => {
    const payload = {
        name: data.value.name,
        name_en: data.value.name_en,
        status: data.value.status ? 1 : 0,
        show_in_filter: data.value.show_in_filter ? 1 : 0,
    };

    try {
        const response = await submit(payload) as { message?: string };
        notifySuccess(response?.message || 'Kaydedildi');
        show.value = false;
        dispatchRefresh();
    } catch (_error) {
        // handled by composable
    }
};

const onModalClose = () => {
    reset({
        id: null,
        name: '',
        name_en: '',
        status: true,
        show_in_filter: true,
    });
};

watch(errors, async (nextErrors) => {
    const firstField = Object.keys(nextErrors)[0];
    if (!firstField || !show.value) {
        return;
    }

    await nextTick();
    const target = document.querySelector<HTMLElement>(`.vue-form-modal-root [name="${firstField}"]`);
    target?.focus();
});

watch(show, (isOpen) => {
    if (!isOpen) {
        onModalClose();
    }
});

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


