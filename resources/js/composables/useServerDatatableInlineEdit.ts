import { nextTick, ref, type Ref } from 'vue';
import { useInlineEdit } from './useInlineEdit';

type InlineNotifyType = 'success' | 'error';

type DatatableInlineRow = {
    id: number;
    name?: string;
    status?: string;
    status_value?: number;
    sort_order?: number;
    inline_update_url?: string;
};

type UseServerDatatableInlineEditOptions = {
    resolveErrorMessage: (error: unknown, fallback: string) => string;
    notify: (type: InlineNotifyType, message: string) => void;
    dispatchRefresh: () => void;
    getCurrentPage: () => number;
    getPerPage: () => number;
    loadingKeyPrefix?: string;
};

type UseServerDatatableInlineEditResult = {
    inlineEdit: ReturnType<typeof useInlineEdit>;
    editingNameValue: Ref<string>;
    editingSortOrderValue: Ref<string>;
    isEditingName: (row: DatatableInlineRow) => boolean;
    isEditingSortOrder: (row: DatatableInlineRow) => boolean;
    statusLoadingKey: (row: DatatableInlineRow) => string;
    nameLoadingKey: (row: DatatableInlineRow) => string;
    sortOrderLoadingKey: (row: DatatableInlineRow) => string;
    startNameEdit: (row: DatatableInlineRow) => Promise<void>;
    cancelNameEdit: (row: DatatableInlineRow) => void;
    saveNameEdit: (row: DatatableInlineRow) => Promise<void>;
    onNameBlur: (row: DatatableInlineRow) => Promise<void>;
    onStatusToggle: (row: DatatableInlineRow, event: Event) => Promise<void>;
    startSortOrderEdit: (row: DatatableInlineRow) => Promise<void>;
    cancelSortOrderEdit: (row: DatatableInlineRow) => void;
    saveSortOrderEdit: (row: DatatableInlineRow) => Promise<void>;
    onSortOrderBlur: (row: DatatableInlineRow) => Promise<void>;
};

export const useServerDatatableInlineEdit = (
    options: UseServerDatatableInlineEditOptions
): UseServerDatatableInlineEditResult => {
    const inlineEdit = useInlineEdit();
    const loadingKeyPrefix = options.loadingKeyPrefix || 'brand';

    const editingNameRowId = ref<number | null>(null);
    const editingNameValue = ref('');
    const editingOriginalName = ref('');
    const editingSortOrderRowId = ref<number | null>(null);
    const editingSortOrderValue = ref('');
    const editingOriginalSortOrder = ref<number | null>(null);
    const skipBlurRowId = ref<number | null>(null);
    const skipSortBlurRowId = ref<number | null>(null);

    const statusLoadingKey = (row: DatatableInlineRow): string => `${loadingKeyPrefix}-status-${row.id}`;
    const nameLoadingKey = (row: DatatableInlineRow): string => `${loadingKeyPrefix}-name-${row.id}`;
    const sortOrderLoadingKey = (row: DatatableInlineRow): string => `${loadingKeyPrefix}-sort-order-${row.id}`;

    const isEditingName = (row: DatatableInlineRow): boolean => Number(row.id) === editingNameRowId.value;

    const startNameEdit = async (row: DatatableInlineRow) => {
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

    const cancelNameEdit = (row: DatatableInlineRow) => {
        skipBlurRowId.value = Number(row.id);
        clearNameEditState();
    };

    const saveNameEdit = async (row: DatatableInlineRow) => {
        if (!isEditingName(row)) {
            return;
        }

        const nextName = editingNameValue.value.trim();
        const prevName = editingOriginalName.value;

        if (!nextName) {
            options.notify('error', 'Ad alanı boş olamaz');
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
            const res = await inlineEdit.updateInline(
                String(row.inline_update_url),
                'name',
                nextName,
                nameLoadingKey(row)
            ) as { data?: Record<string, unknown> };

            if (res?.data) {
                const responseData = res.data as Partial<DatatableInlineRow>;
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
            options.dispatchRefresh();
        } finally {
            clearNameEditState();
        }
    };

    const onNameBlur = async (row: DatatableInlineRow) => {
        if (skipBlurRowId.value === Number(row.id)) {
            skipBlurRowId.value = null;
            return;
        }

        await saveNameEdit(row);
    };

    const onStatusToggle = async (row: DatatableInlineRow, event: Event) => {
        const input = event.target as HTMLInputElement;
        const prevValue = Number(row.status_value || 0) === 1 ? 1 : 0;
        const nextValue = input.checked ? 1 : 0;

        if (!row.inline_update_url) {
            input.checked = !input.checked;
            return;
        }

        try {
            const res = await inlineEdit.updateInline(
                String(row.inline_update_url),
                'status',
                nextValue,
                statusLoadingKey(row)
            ) as { data?: Record<string, unknown> };

            if (res?.data) {
                const responseData = res.data as Partial<DatatableInlineRow>;
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
            options.dispatchRefresh();
        }
    };

    const isEditingSortOrder = (row: DatatableInlineRow): boolean => Number(row.id) === editingSortOrderRowId.value;

    const startSortOrderEdit = async (row: DatatableInlineRow) => {
        editingSortOrderRowId.value = Number(row.id);
        editingOriginalSortOrder.value = Math.max(1, Number(row.sort_order || 1));
        editingSortOrderValue.value = String(editingOriginalSortOrder.value);

        await nextTick();
        const target = document.querySelector<HTMLInputElement>(`[data-inline-sort-order-input="${row.id}"]`);
        target?.focus();
        target?.select();
    };

    const clearSortOrderEditState = () => {
        editingSortOrderRowId.value = null;
        editingSortOrderValue.value = '';
        editingOriginalSortOrder.value = null;
    };

    const cancelSortOrderEdit = (row: DatatableInlineRow) => {
        skipSortBlurRowId.value = Number(row.id);
        clearSortOrderEditState();
    };

    const saveSortOrderEdit = async (row: DatatableInlineRow) => {
        if (!isEditingSortOrder(row)) {
            return;
        }

        const parsed = Number(editingSortOrderValue.value);
        const prevSortOrder = Math.max(1, Number(editingOriginalSortOrder.value || row.sort_order || 1));
        const nextSortOrder = Number.isFinite(parsed) ? Math.trunc(parsed) : NaN;

        if (!Number.isFinite(nextSortOrder) || nextSortOrder <= 0) {
            options.notify('error', 'Sıra değeri 1 veya daha büyük olmalıdır');
            clearSortOrderEditState();
            return;
        }

        if (nextSortOrder === prevSortOrder) {
            clearSortOrderEditState();
            return;
        }

        if (!row.inline_update_url) {
            clearSortOrderEditState();
            return;
        }

        try {
            const res = await inlineEdit.updateInline(
                String(row.inline_update_url),
                'sort_order',
                nextSortOrder,
                sortOrderLoadingKey(row)
            ) as { data?: Record<string, unknown> };

            if (res?.data) {
                const responseData = res.data as Partial<DatatableInlineRow>;
                row.sort_order = Number(responseData.sort_order ?? nextSortOrder);
            } else {
                row.sort_order = nextSortOrder;
            }

            options.notify('success', 'Sıralama güncellendi');

            const finalSortOrder = Math.max(1, Number(row.sort_order || nextSortOrder));
            const currentPage = options.getCurrentPage();
            const perPage = options.getPerPage();
            const targetPage = Math.max(1, Math.ceil(finalSortOrder / perPage));

            if (targetPage !== currentPage) {
                options.notify('success', 'Kayıt başka sayfaya taşındı');
            }

            options.dispatchRefresh();
        } catch (_error) {
            row.sort_order = prevSortOrder;
            options.dispatchRefresh();
        } finally {
            clearSortOrderEditState();
        }
    };

    const onSortOrderBlur = async (row: DatatableInlineRow) => {
        if (skipSortBlurRowId.value === Number(row.id)) {
            skipSortBlurRowId.value = null;
            return;
        }

        await saveSortOrderEdit(row);
    };

    return {
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
    };
};
