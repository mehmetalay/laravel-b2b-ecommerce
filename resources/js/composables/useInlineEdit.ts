import { ref } from 'vue';
import http, { type NormalizedHttpError } from '../lib/http';
import { normalizeValidationErrors } from '../lib/validation';

type InlineField = 'name' | 'status' | 'sort_order';

const notify = (type: 'success' | 'error', message: string): void => {
    const maybeNotify = (window as typeof window & {
        notify?: (level: string, text: string) => void;
    }).notify;

    if (typeof maybeNotify === 'function') {
        maybeNotify(type, message);
    }
};

export const useInlineEdit = () => {
    const loadingKeys = ref<Record<string, boolean>>({});

    const isLoading = (key: string): boolean => Boolean(loadingKeys.value[key]);

    const setLoading = (key: string, value: boolean): void => {
        loadingKeys.value = {
            ...loadingKeys.value,
            [key]: value,
        };
    };

    const updateInline = async (
        url: string,
        field: InlineField,
        value: string | number,
        key?: string
    ): Promise<unknown> => {
        const loadingKey = key || `${url}:${field}`;

        if (isLoading(loadingKey)) {
            return;
        }

        setLoading(loadingKey, true);

        try {
            const response = await http.patch(url, { field, value }, {
                suppressValidationToast: true,
            });
            notify('success', response.data?.message || 'Güncellendi.');
            return response.data;
        } catch (error) {
            const normalized = (error as NormalizedHttpError).normalizedValidation
                ?? normalizeValidationErrors((error as NormalizedHttpError).response?.data);

            if (Object.keys(normalized.errors).length > 0) {
                const firstField = Object.keys(normalized.errors)[0];
                const firstMessage = normalized.errors[firstField]?.[0];
                notify('error', firstMessage || normalized.message || 'İşlem başarısız.');
            } else {
                notify('error', normalized.message || 'İşlem başarısız.');
            }

            throw error;
        } finally {
            setLoading(loadingKey, false);
        }
    };

    return {
        loadingKeys,
        isLoading,
        updateInline,
    };
};
