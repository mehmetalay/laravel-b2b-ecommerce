import axios, { type AxiosError, type AxiosInstance } from 'axios';
import { firstError, normalizeValidationErrors, type ValidationErrors, type NormalizedValidation } from './validation';

declare module 'axios' {
    interface AxiosRequestConfig {
        suppressValidationToast?: boolean;
    }
}

const csrfToken = document
    .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
    ?.getAttribute('content');

const http: AxiosInstance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
});

const notify = (level: 'warning' | 'error', message: string) => {
    const maybeNotify = (window as typeof window & { notify?: (type: string, text: string) => void }).notify;

    if (typeof maybeNotify === 'function') {
        maybeNotify(level, message);
    }
};

export interface NormalizedHttpError extends AxiosError {
    normalizedValidation?: NormalizedValidation;
}

export const getFieldError = (errors: ValidationErrors, field: string): string | null => {
    return firstError(errors, field);
};

http.interceptors.response.use(
    (response) => response,
    (error: AxiosError) => {
        const status = error.response?.status;

        if (status === 401) {
            notify('warning', 'Oturumunuz sona erdi. Lütfen tekrar giriş yapın.');
        }

        if (status === 419) {
            notify('warning', 'Oturum doğrulaması yenilenemedi. Lütfen sayfayı yenileyip tekrar deneyin.');
        }

        if (status === 422) {
            const normalized = normalizeValidationErrors(error.response?.data);
            (error as NormalizedHttpError).normalizedValidation = normalized;
            if (!error.config?.suppressValidationToast) {
                notify('warning', normalized.message || 'Lütfen formdaki hataları kontrol edin.');
            }
        }

        if (status === 500) {
            notify('error', 'Sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.');
        }

        return Promise.reject(error);
    }
);

export default http;
