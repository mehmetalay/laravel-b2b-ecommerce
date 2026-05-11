<template>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead class="thead-light">
                <tr>
                    <th v-for="column in columns" :key="column.key">
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="rows.length === 0">
                    <td :colspan="columns.length" class="text-center text-muted">
                        No data
                    </td>
                </tr>
                <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
                    <td v-for="column in columns" :key="column.key">
                        {{ row[column.key] }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        columns: Array<{ key: string; label: string }>;
        rows: Array<Record<string, unknown>>;
    }>(),
    {
        columns: () => [],
        rows: () => [],
    }
);
</script>
