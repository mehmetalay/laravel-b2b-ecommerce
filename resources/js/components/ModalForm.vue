<template>
    <section class="vue-modal-form">
        <button v-if="showInternalTrigger" type="button" class="btn btn-info" @click="openModal">
            {{ triggerText }}
        </button>

        <Teleport to="body">
            <div v-if="isOpen" class="vue-modal-overlay" @click.self="closeModal">
                <div
                    class="modal fade show vue-modal-shell"
                    tabindex="-1"
                    role="dialog"
                    aria-modal="true"
                    aria-hidden="false"
                    style="display: block;"
                >
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ title }}</h5>
                                <button type="button" class="close" aria-label="Close" @click="closeModal">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <div v-if="successMessage" class="alert alert-success py-2 mb-3">
                                    {{ successMessage }}
                                </div>

                                <div v-if="formError" class="alert alert-danger py-2 mb-3">
                                    {{ formError }}
                                </div>

                                <div v-for="field in fields" :key="field.name" class="form-group">
                                    <label v-if="field.type !== 'hidden'" :for="fieldId(field)">{{ field.label }}</label>

                                    <input
                                        v-if="field.type === 'text' || field.type === 'email' || field.type === 'number' || field.type === 'hidden'"
                                        :id="fieldId(field)"
                                        v-model="formState[field.name]"
                                        :name="field.name"
                                        :type="field.type"
                                        class="form-control"
                                        :class="{ 'is-invalid': hasError(validationErrors, field.name) }"
                                        :placeholder="field.placeholder || ''"
                                    >

                                    <textarea
                                        v-else-if="field.type === 'textarea'"
                                        :id="fieldId(field)"
                                        v-model="formState[field.name]"
                                        :name="field.name"
                                        class="form-control"
                                        :class="{ 'is-invalid': hasError(validationErrors, field.name) }"
                                        :placeholder="field.placeholder || ''"
                                        rows="3"
                                    />

                                    <select
                                        v-else-if="field.type === 'select'"
                                        :id="fieldId(field)"
                                        v-model="formState[field.name]"
                                        :name="field.name"
                                        class="form-control"
                                        :class="{ 'is-invalid': hasError(validationErrors, field.name) }"
                                    >
                                        <option v-for="option in field.options || []" :key="String(option.value)" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>

                                    <div v-else-if="field.type === 'checkbox'" class="custom-control custom-checkbox mt-2">
                                        <input
                                            :id="fieldId(field)"
                                            v-model="formState[field.name]"
                                            :name="field.name"
                                            type="checkbox"
                                            class="custom-control-input"
                                        >
                                        <label class="custom-control-label" :for="fieldId(field)">
                                            {{ field.placeholder || field.label }}
                                        </label>
                                    </div>

                                    <small v-if="getFieldError(validationErrors, field.name)" class="text-danger d-block mt-1">
                                        {{ getFieldError(validationErrors, field.name) }}
                                    </small>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" :disabled="submitting" @click="closeModal">
                                    İptal
                                </button>
                                <button type="button" class="btn btn-primary" :disabled="submitting" @click="submitForm">
                                    <span v-if="submitting" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                                    {{ submitText }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </section>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import http, { getFieldError, type NormalizedHttpError } from '../lib/http';
import { hasError, normalizeValidationErrors, type ValidationErrors } from '../lib/validation';

interface FieldOption {
    label: string;
    value: string | number;
}

interface FormField {
    name: string;
    label: string;
    type: 'text' | 'email' | 'number' | 'textarea' | 'select' | 'checkbox' | 'hidden';
    placeholder?: string;
    value?: string | number | boolean;
    options?: FieldOption[];
}

const props = withDefaults(
    defineProps<{
        title?: string;
        triggerText?: string;
        submitText?: string;
        endpoint: string;
        method?: 'POST' | 'PUT' | 'PATCH';
        triggerSelector?: string;
        reloadOnSuccess?: boolean;
        fields?: FormField[];
    }>(),
    {
        title: 'Modal Form',
        triggerText: 'Open Form',
        submitText: 'Kaydet',
        method: 'POST',
        triggerSelector: '',
        reloadOnSuccess: false,
        fields: () => [],
    }
);

const isOpen = ref(false);
const submitting = ref(false);
const successMessage = ref('');
const formError = ref('');
const validationErrors = ref<ValidationErrors>({});
const formState = reactive<Record<string, string | number | boolean>>({});
const externalTriggerElements: Element[] = [];

const showInternalTrigger = computed(() => {
    return !props.triggerSelector && !!props.triggerText;
});

const initState = () => {
    Object.keys(formState).forEach((key) => {
        delete formState[key];
    });

    props.fields.forEach((field) => {
        if (field.type === 'checkbox') {
            formState[field.name] = Boolean(field.value);
            return;
        }

        formState[field.name] = field.value ?? '';
    });
};

const fieldId = (field: FormField): string => {
    return `modal-form-${field.name}`;
};

const openModal = () => {
    successMessage.value = '';
    formError.value = '';
    validationErrors.value = {};
    initState();
    isOpen.value = true;
};

const closeModal = (force = false) => {
    if (submitting.value && !force) {
        return;
    }

    isOpen.value = false;
};

const onExternalTriggerClick = (event: Event) => {
    event.preventDefault();
    openModal();
};

const buildPayload = (): Record<string, unknown> => {
    const payload: Record<string, unknown> = {};

    props.fields.forEach((field) => {
        const value = formState[field.name];

        if (field.type === 'checkbox') {
            if (value === true) {
                payload[field.name] = 1;
            }

            return;
        }

        payload[field.name] = value;
    });

    return payload;
};

const submitForm = async () => {
    submitting.value = true;
    successMessage.value = '';
    formError.value = '';
    validationErrors.value = {};

    try {
        const response = await http.request({
            url: props.endpoint,
            method: props.method,
            data: buildPayload(),
        });

        const data = response.data as { message?: string };
        successMessage.value = data?.message || 'Kaydedildi.';
        closeModal(true);

        if (props.reloadOnSuccess) {
            window.location.reload();
        }
    } catch (error) {
        const normalized = (error as NormalizedHttpError).normalizedValidation
            ?? normalizeValidationErrors((error as NormalizedHttpError).response?.data);

        if (Object.keys(normalized.errors).length > 0) {
            validationErrors.value = normalized.errors;
        } else {
            formError.value = normalized.message || 'Request failed.';
        }
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    if (!props.triggerSelector) {
        return;
    }

    externalTriggerElements.push(...Array.from(document.querySelectorAll(props.triggerSelector)));

    externalTriggerElements.forEach((element) => {
        element.addEventListener('click', onExternalTriggerClick);
    });
});

onBeforeUnmount(() => {
    externalTriggerElements.forEach((element) => {
        element.removeEventListener('click', onExternalTriggerClick);
    });
});

watch(isOpen, (value) => {
    document.body.classList.toggle('modal-open', value);
});

onBeforeUnmount(() => {
    document.body.classList.remove('modal-open');
});
</script>

<style scoped>
.vue-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.vue-modal-shell {
    width: 100%;
}

.modal-dialog {
    max-width: 520px;
    width: 100%;
    margin: 0 auto;
}

.modal-content {
    border-radius: 10px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}
</style>
