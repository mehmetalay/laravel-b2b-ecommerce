import { ref } from 'vue';
import http, { type NormalizedHttpError } from '../lib/http';
import { normalizeValidationErrors, type ValidationErrors } from '../lib/validation';

type FormMode = 'create' | 'edit';
type HttpMethod = 'post' | 'put' | 'patch';

interface OpenEditOptions<T> {
    submitEndpoint?: string;
    submitMethod?: HttpMethod;
    mapData?: (payload: unknown) => Partial<T>;
}

const notifyError = (message: string): void => {
    const maybeNotify = (window as typeof window & {
        notify?: (type: string, text: string) => void;
    }).notify;

    if (typeof maybeNotify === 'function') {
        maybeNotify('error', message);
    }
};

export const useModalForm = <T extends Record<string, unknown>>(initialState: T) => {
    const show = ref(false);
    const data = ref<T>({ ...initialState });
    const errors = ref<ValidationErrors>({});
    const loading = ref(false);
    const mode = ref<FormMode>('create');
    const endpoint = ref('');
    const method = ref<HttpMethod>('post');

    const reset = (nextState?: Partial<T>): void => {
        data.value = { ...initialState, ...(nextState || {}) } as T;
        errors.value = {};
        loading.value = false;
    };

    const setErrors = (nextErrors: ValidationErrors): void => {
        errors.value = nextErrors;
    };

    const openCreate = (submitEndpoint: string, initialData?: Partial<T>, submitMethod: HttpMethod = 'post'): void => {
        mode.value = 'create';
        endpoint.value = submitEndpoint;
        method.value = submitMethod;
        reset(initialData);
        show.value = true;
    };

    const openEdit = async (fetchEndpoint: string, options?: OpenEditOptions<T>): Promise<void> => {
        mode.value = 'edit';
        method.value = options?.submitMethod ?? 'patch';
        endpoint.value = options?.submitEndpoint ?? fetchEndpoint;
        reset();
        show.value = true;
        loading.value = true;

        try {
            const response = await http.get(fetchEndpoint);
            const payload = response.data?.data ?? response.data;
            const mapped = options?.mapData ? options.mapData(payload) : (payload as Partial<T>);
            data.value = { ...initialState, ...(mapped || {}) } as T;
        } catch (_error) {
            show.value = false;
            notifyError('Kayit bilgisi alinirken bir hata olustu.');
        } finally {
            loading.value = false;
        }
    };

    const submit = async (payload?: Record<string, unknown>): Promise<unknown> => {
        if (loading.value) {
            return;
        }

        errors.value = {};
        loading.value = true;

        try {
            const response = await http.request({
                method: method.value,
                url: endpoint.value,
                data: payload ?? data.value,
                suppressValidationToast: true,
            });

            return response.data;
        } catch (error) {
            const normalized = (error as NormalizedHttpError).normalizedValidation
                ?? normalizeValidationErrors((error as NormalizedHttpError).response?.data);

            if (Object.keys(normalized.errors).length > 0) {
                setErrors(normalized.errors);
            } else {
                notifyError(normalized.message || 'Islem sirasinda bir hata olustu.');
            }

            throw error;
        } finally {
            loading.value = false;
        }
    };

    return {
        show,
        data,
        errors,
        loading,
        mode,
        endpoint,
        method,
        openCreate,
        openEdit,
        submit,
        reset,
        setErrors,
    };
};

export type { ValidationErrors };
