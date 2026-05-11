<template>
    <div>
        <ServerDataTable
            title="Özellik Grupları"
            endpoint="/admin/api/attribute-groups"
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
            storage-key="attribute-groups-table"
            loaded-event-component="attribute-groups"
            empty-text="Özellik grubu bulunamadı"
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
                        :id="`attribute-group-status-${row.id}`"
                        type="checkbox"
                        class="custom-control-input"
                        :checked="Number(row.status_value || 0) === 1"
                        :disabled="inlineEdit.isLoading(statusLoadingKey(row))"
                        @change="onStatusToggle(row, $event)"
                    >
                    <label class="custom-control-label" :for="`attribute-group-status-${row.id}`"></label>
                </div>
            </template>

            <template #cell-actions="{ row }">
                <a
                    v-if="typeof row.attributes_url === 'string' && row.attributes_url"
                    :href="String(row.attributes_url)"
                    class="btn btn-secondary font-15 p-2 mr-1"
                    title="Özellikler"
                >
                    <i class="las la-level-down-alt"></i>
                </a>
                <button
                    v-if="typeof row.duplicate_url === 'string' && row.duplicate_url"
                    type="button"
                    class="btn btn-secondary font-15 p-2 mr-1"
                    title="Kopyala"
                    @click="openDuplicateModal(row)"
                >
                    <i class="las la-copy"></i>
                </button>
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
            :title="modalTitle"
            :loading="loading"
            @close="onModalClose"
            @submit="submitForm"
        >
            <div class="form-group">
                <label for="attribute-group-name">Adı</label>
                <input
                    id="attribute-group-name"
                    v-model="data.name"
                    name="name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': !!fieldError('name') }"
                    autocomplete="off"
                >
                <small v-if="fieldError('name')" class="invalid-feedback d-block">{{ fieldError('name') }}</small>
            </div>

            <div class="form-group mt-3">
                <div class="custom-control custom-checkbox">
                    <input id="attribute-group-status" v-model="data.status" type="checkbox" class="custom-control-input">
                    <label class="custom-control-label" for="attribute-group-status">Aktif</label>
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
import { useInlineEdit } from '../composables/useInlineEdit';
import { useBulkActions } from '../composables/useBulkActions';
import { dispatchServerDatatableRefresh } from '../shared/serverDatatableEvents';

interface GroupFormData {
    id: number | null;
    name: string;
    status: boolean;
}

interface RowData {
    id: number;
    name?: string;
    status?: string;
    edit_url?: string;
    duplicate_url?: string;
    inline_update_url?: string;
    status_value?: number;
}

const form = useModalForm<GroupFormData>({
    id: null,
    name: '',
    status: true,
});
const { show, data, errors, loading, mode, openCreate, openEdit, submit, reset } = form;
const inlineEdit = useInlineEdit();
const { loading: bulkLoading, performBulkAction } = useBulkActions('attribute-groups');

const modalType = ref<'create' | 'edit' | 'duplicate'>('create');
const editingNameRowId = ref<number | null>(null);
const editingNameValue = ref('');
const editingOriginalName = ref('');
const skipBlurRowId = ref<number | null>(null);
const selectedIds = ref<Array<string | number>>([]);
const rows = ref<RowData[]>([]);

const columns = [
    { key: 'id', label: 'ID', className: 'text-left' },
    { key: 'name', label: 'Adı' },
    { key: 'status', label: 'Durum', className: 'text-center' },
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

const modalTitle = computed(() => {
    if (modalType.value === 'duplicate') {
        return 'Özellik Grubu Kopyala';
    }

    return mode.value === 'edit' ? 'Özellik Grubu Düzenle' : 'Özellik Grubu Yeni';
});

const fieldError = (field: string): string => errors.value[field]?.[0] || '';

const notify = (type: 'success' | 'error', message: string): void => {
    const maybeNotify = (window as typeof window & { notify?: (level: string, text: string) => void }).notify;
    if (typeof maybeNotify === 'function') {
        maybeNotify(type, message);
    }
};

const dispatchRefresh = () => {
    dispatchServerDatatableRefresh({
        component: 'attribute-groups',
    });
};

const statusLoadingKey = (row: RowData): string => `ag-status-${row.id}`;
const nameLoadingKey = (row: RowData): string => `ag-name-${row.id}`;

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
    const success = await performBulkAction('/admin/api/attribute-groups/bulk', action, ids, {
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
    modalType.value = 'create';
    openCreate('/aka/catalog/product-attributes/attribute-groups', {
        id: null,
        name: '',
        status: true,
    }, 'post');
};

const openEditModal = async (row: RowData) => {
    if (!row.edit_url) {
        return;
    }

    modalType.value = 'edit';

    await openEdit(String(row.edit_url), {
        submitEndpoint: `/aka/catalog/product-attributes/attribute-groups/${row.id}`,
        submitMethod: 'patch',
        mapData: (payload) => {
            const item = payload as Partial<GroupFormData>;
            return {
                id: row.id,
                name: String(item.name || ''),
                status: Boolean(item.status),
            };
        },
    });
};

const openDuplicateModal = (row: RowData) => {
    if (!row.duplicate_url) {
        return;
    }

    modalType.value = 'duplicate';
    openCreate(String(row.duplicate_url), {
        id: row.id,
        name: row.name ? `${String(row.name)} Kopya` : '',
        status: true,
    }, 'post');
};

const submitForm = async () => {
    const payload = {
        name: data.value.name,
        status: data.value.status ? 1 : 0,
    };

    try {
        const response = await submit(payload) as { message?: string };
        notify('success', response?.message || 'Kaydedildi');
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
        status: true,
    });
};

const isEditingName = (row: RowData): boolean => Number(row.id) === editingNameRowId.value;

const startNameEdit = async (row: RowData) => {
    editingNameRowId.value = Number(row.id);
    editingNameValue.value = String(row.name || '');
    editingOriginalName.value = String(row.name || '');

    await nextTick();
    const target = document.querySelector<HTMLInputElement>(`[data-inline-name-input="${row.id}"]`);
    target?.focus();
    target?.select();
};

const clearNameEditState = () => {
    editingNameRowId.value = null;
    editingNameValue.value = '';
    editingOriginalName.value = '';
};

const cancelNameEdit = (row: RowData) => {
    skipBlurRowId.value = Number(row.id);
    clearNameEditState();
};

const saveNameEdit = async (row: RowData) => {
    if (!isEditingName(row)) {
        return;
    }

    const nextName = editingNameValue.value.trim();
    const prevName = editingOriginalName.value;

    if (!nextName) {
        notify('error', 'Ad alanı boş olamaz');
        clearNameEditState();
        return;
    }

    if (nextName === prevName) {
        clearNameEditState();
        return;
    }

    if (!row.inline_update_url) {
        clearNameEditState();
        return;
    }

    try {
        const res = await inlineEdit.updateInline(String(row.inline_update_url), 'name', nextName, nameLoadingKey(row)) as { data?: Record<string, unknown> };
        if (res?.data) {
            const responseData = res.data as Partial<RowData>;
            row.name = responseData.name ?? row.name;
            row.status_value = responseData.status_value ?? row.status_value;
            row.status = responseData.status
                ?? (Number(row.status_value || 0) === 1 ? 'Aktif' : 'Pasif');
        } else {
            row.name = nextName;
        }
    } catch (_error) {
        editingNameValue.value = editingOriginalName.value;
        row.name = prevName;
        dispatchRefresh();
    } finally {
        clearNameEditState();
    }
};

const onNameBlur = async (row: RowData) => {
    if (skipBlurRowId.value === Number(row.id)) {
        skipBlurRowId.value = null;
        return;
    }

    await saveNameEdit(row);
};

const onStatusToggle = async (row: RowData, event: Event) => {
    const input = event.target as HTMLInputElement;
    const prevValue = Number(row.status_value || 0) === 1 ? 1 : 0;
    const nextValue = input.checked ? 1 : 0;

    if (!row.inline_update_url) {
        input.checked = !input.checked;
        return;
    }

    try {
        const res = await inlineEdit.updateInline(String(row.inline_update_url), 'status', nextValue, statusLoadingKey(row)) as { data?: Record<string, unknown> };
        if (res?.data) {
            const responseData = res.data as Partial<RowData>;
            row.name = responseData.name ?? row.name;
            row.status_value = responseData.status_value ?? row.status_value;
            row.status = responseData.status
                ?? (Number(row.status_value || 0) === 1 ? 'Aktif' : 'Pasif');
        } else {
            row.status_value = nextValue;
            row.status = Number(row.status_value || 0) === 1 ? 'Aktif' : 'Pasif';
        }
    } catch (_error) {
        input.checked = !input.checked;
        row.status_value = prevValue;
        dispatchRefresh();
    }
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

.inline-edit-trigger .inline-edit-icon {
    opacity: 0;
    transition: opacity .15s ease;
    font-size: 14px;
}

.inline-edit-trigger:hover .inline-edit-icon {
    opacity: 1;
}
</style>


