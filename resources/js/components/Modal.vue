<template>
    <div v-if="modelValue" class="vue-modal-backdrop" @click.self="close">
        <div class="vue-modal-card">
            <header class="vue-modal-header">
                <h6 class="mb-0">{{ title }}</h6>
                <button type="button" class="close" @click="close">&times;</button>
            </header>
            <div class="vue-modal-body">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        modelValue?: boolean;
        title?: string;
    }>(),
    {
        modelValue: false,
        title: 'Modal',
    }
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: boolean): void;
}>();

const close = () => {
    emit('update:modelValue', false);
};
</script>

<style scoped>
.vue-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.vue-modal-card {
    background: #fff;
    width: min(600px, 95vw);
    border-radius: 8px;
    overflow: hidden;
}

.vue-modal-header {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.vue-modal-body {
    padding: 16px;
}

.close {
    border: 0;
    background: transparent;
    font-size: 24px;
    line-height: 1;
}
</style>
