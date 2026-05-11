export type ValidationErrors = Record<string, string[]>;

export interface NormalizedValidation {
    message: string;
    errors: ValidationErrors;
}

const DEFAULT_MESSAGE = 'Validation failed.';

const toStringArray = (value: unknown): string[] => {
    if (!Array.isArray(value)) {
        if (typeof value === 'string') {
            return [value];
        }

        return [];
    }

    return value
        .map((item) => (typeof item === 'string' ? item : String(item)))
        .filter((item) => item.length > 0);
};

export const normalizeValidationErrors = (payload: unknown): NormalizedValidation => {
    const response = payload && typeof payload === 'object' ? (payload as Record<string, unknown>) : {};
    const rawErrors = response.errors && typeof response.errors === 'object'
        ? (response.errors as Record<string, unknown>)
        : {};

    const errors = Object.entries(rawErrors).reduce<ValidationErrors>((carry, [field, messages]) => {
        const normalizedMessages = toStringArray(messages);

        if (normalizedMessages.length > 0) {
            carry[field] = normalizedMessages;
        }

        return carry;
    }, {});

    const message = typeof response.message === 'string' && response.message.length > 0
        ? response.message
        : DEFAULT_MESSAGE;

    return { message, errors };
};

export const firstError = (errors: ValidationErrors, field: string): string | null => {
    if (!errors[field] || errors[field].length === 0) {
        return null;
    }

    return errors[field][0] ?? null;
};

export const hasError = (errors: ValidationErrors, field: string): boolean => {
    return !!firstError(errors, field);
};
