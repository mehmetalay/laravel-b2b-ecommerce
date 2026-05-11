<template>
    <section class="vue-product-search card-box border rounded p-3 mb-3">
        <h6 class="mb-2">{{ title }}</h6>

        <input
            v-model="query"
            :placeholder="placeholder"
            class="form-control"
            type="text"
            autocomplete="off"
        >

        <div v-if="loading" class="mt-2 text-muted small">
            Searching...
        </div>

        <div v-else-if="errorMessage" class="mt-2 text-danger small">
            {{ errorMessage }}
        </div>

        <ul v-else-if="results.length > 0" class="list-group mt-2">
            <li
                v-for="product in results"
                :key="product.id"
                class="list-group-item d-flex justify-content-between align-items-center"
            >
                <span>
                    <strong>{{ product.name }}</strong>
                </span>
                <small class="text-muted">
                    {{ product.code || product.code_group || product.barcode || '-' }}
                </small>
            </li>
        </ul>

        <div v-else-if="query.trim().length >= minChars" class="mt-2 text-muted small">
            No results found.
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import http from '../lib/http';

interface Product {
    id: number;
    name: string;
    code?: string | null;
    code_group?: string | null;
    barcode?: string | null;
}

const props = withDefaults(
    defineProps<{
        endpoint?: string;
        placeholder?: string;
        minChars?: number;
        debounceMs?: number;
        title?: string;
    }>(),
    {
        endpoint: '/aka/catalog/products/search',
        placeholder: 'Search product by name, code, barcode',
        minChars: 2,
        debounceMs: 350,
        title: 'Quick Product Search',
    }
);

const query = ref('');
const results = ref<Product[]>([]);
const loading = ref(false);
const errorMessage = ref('');

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const fetchProducts = async (searchText: string) => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await http.get<Product[]>(props.endpoint, {
            params: { q: searchText },
        });

        results.value = Array.isArray(response.data) ? response.data : [];
    } catch (_error) {
        results.value = [];
        errorMessage.value = 'Search request failed.';
    } finally {
        loading.value = false;
    }
};

watch(query, (value) => {
    const text = value.trim();

    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    if (text.length < props.minChars) {
        results.value = [];
        errorMessage.value = '';
        loading.value = false;
        return;
    }

    debounceTimer = setTimeout(() => {
        void fetchProducts(text);
    }, props.debounceMs);
});
</script>

<style scoped>
.vue-product-search {
    background: #fff;
}
</style>
