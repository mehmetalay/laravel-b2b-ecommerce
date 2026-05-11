import { ref } from 'vue';

export type FormErrors = Record<string, string[]>;

export const useFormErrors = () => {
    const errors = ref<FormErrors>({});

    const setErrors = (payload: FormErrors): void => {
        errors.value = payload || {};
    };

    const clearErrors = (): void => {
        errors.value = {};
    };

    const getError = (field: string): string => {
        const fieldErrors = errors.value[field];
        return Array.isArray(fieldErrors) && fieldErrors.length > 0 ? fieldErrors[0] : '';
    };

    const hasError = (field: string): boolean => getError(field) !== '';

    return {
        errors,
        setErrors,
        clearErrors,
        getError,
        hasError,
    };
};
