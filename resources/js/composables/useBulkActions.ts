import { ref, type Ref } from 'vue';
import http, { type NormalizedHttpError } from '../lib/http';
import { normalizeValidationErrors } from '../lib/validation';
import { dispatchServerDatatableRefresh } from '../shared/serverDatatableEvents';

type BulkAction = 'activate' | 'deactivate' | 'delete';
type RowId = string | number;

interface BulkActionOptions<T = Record<string, unknown>> {
    rows?: Ref<T[]>;
    getRowId?: (row: T) => RowId;
    onSuccessLocalUpdate?: (action: BulkAction, ids: RowId[], rows: T[]) => void;
}

const notify = (type: 'success' | 'error', message: string): void => {
    const maybeNotify = (window as typeof window & { notify?: (level: string, text: string) => void }).notify;
    if (typeof maybeNotify === 'function') {
        maybeNotify(type, message);
    }
};

export const useBulkActions = (component: string) => {
    const loading = ref(false);
    const dispatchRefresh = (): void => {
        dispatchServerDatatableRefresh({
            component,
            reason: 'bulk',
            source: 'useBulkActions',
        });
    };

    const performBulkAction = async (
        endpoint: string,
        action: BulkAction,
        ids: Array<string | number>,
        options?: BulkActionOptions
    ): Promise<boolean> => {
        if (loading.value || ids.length === 0) {
            return false;
        }

        if (action === 'delete') {
            const maybeSwal = (window as typeof window & {
                Swal?: {
                    fire: (options: Record<string, unknown>) => Promise<{ isConfirmed?: boolean }>;
                };
            }).Swal;

            let confirmed = false;
            if (maybeSwal && typeof maybeSwal.fire === 'function') {
                const result = await maybeSwal.fire({
                    title: 'Emin misiniz?',
                    text: `${ids.length} kayıt silinecek`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, sil',
                    cancelButtonText: 'İptal',
                });
                confirmed = Boolean(result?.isConfirmed);
            } else {
                const maybeCustomConfirm = (window as typeof window & {
                    customConfirm?: (message: string) => Promise<boolean>;
                }).customConfirm;
                confirmed = typeof maybeCustomConfirm === 'function'
                    ? await maybeCustomConfirm('Seçili kayıtları silmek istediğinize emin misiniz?')
                    : false;
            }

            if (!confirmed) {
                return false;
            }
        }

        loading.value = true;

        try {
            const response = await http.patch(endpoint, { action, ids }, {
                suppressValidationToast: true,
            });

            if (options?.onSuccessLocalUpdate && options?.rows) {
                options.onSuccessLocalUpdate(action, ids, options.rows.value);
            }

            notify('success', response.data?.message || 'İşlem başarıyla tamamlandı.');
            dispatchRefresh();
            if (action === 'delete' && options?.rows?.value.length === 0) {
                dispatchRefresh();
            }

            return true;
        } catch (error) {
            const normalized = (error as NormalizedHttpError).normalizedValidation
                ?? normalizeValidationErrors((error as NormalizedHttpError).response?.data);
            notify('error', normalized.message || 'İşlem başarısız.');
            return false;
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        performBulkAction,
    };
};
