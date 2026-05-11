<template>
    <Teleport to="body">
        <div v-if="show" class="vue-form-modal-root" @keydown.esc="handleEsc">
            <div class="vue-form-modal-backdrop" @click="handleBackdropClick"></div>
            <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" :aria-hidden="!show">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ title }}</h5>
                            <button type="button" class="close" aria-label="Close" :disabled="loading" @click="closeModal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <slot />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" :disabled="loading" @click="closeModal">
                                Kapat
                            </button>
                            <button type="button" class="btn btn-primary" :disabled="loading" @click="submitModal">
                                <span
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm mr-1"
                                    role="status"
                                    aria-hidden="true"
                                ></span>
                                {{ loading ? 'Kaydediliyor...' : 'Kaydet' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { watch } from 'vue';

const props = withDefaults(defineProps<{
    show: boolean;
    title?: string;
    loading?: boolean;
    closeOnBackdrop?: boolean;
    closeOnEsc?: boolean;
}>(), {
    title: 'Form',
    loading: false,
    closeOnBackdrop: true,
    closeOnEsc: true,
});

const emit = defineEmits<{
    (event: 'update:show', value: boolean): void;
    (event: 'submit'): void;
    (event: 'close'): void;
}>();

const closeModal = () => {
    if (props.loading) {
        return;
    }

    emit('update:show', false);
    emit('close');
};

const submitModal = () => {
    emit('submit');
};

const handleBackdropClick = () => {
    if (!props.closeOnBackdrop) {
        return;
    }

    closeModal();
};

const handleEsc = () => {
    if (!props.closeOnEsc) {
        return;
    }

    closeModal();
};

watch(() => props.show, (value) => {
    document.body.classList.toggle('modal-open', value);
});
</script>

<style scoped>
.vue-form-modal-root {
    position: fixed;
    inset: 0;
    z-index: 9999;
}

.vue-form-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
}

.modal {
    position: relative;
    z-index: 2;
}
</style>
