<template>
    <div
        class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <!-- Filters bar -->
        <div
            class="flex flex-wrap items-center gap-3 border-b border-gray-100 p-4 dark:border-gray-800"
        >
            <div class="relative min-w-[200px] flex-1">
                <input
                    v-model="localFilters.search"
                    type="text"
                    placeholder="Search..."
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                />
            </div>

            <slot name="filters" :filters="localFilters" :apply="apply" />

            <button
                v-if="hasActiveFilters"
                @click="reset"
                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"
            >
                Clear
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-4 py-3 text-xs font-medium text-gray-500 uppercase dark:text-gray-400"
                            :class="
                                col.sortable
                                    ? 'cursor-pointer select-none hover:text-gray-700'
                                    : ''
                            "
                            @click="col.sortable && toggleSort(col.key)"
                        >
                            <span class="flex items-center gap-1">
                                {{ col.label }}
                                <span
                                    v-if="
                                        col.sortable &&
                                        localFilters.sort_by === col.key
                                    "
                                    class="text-brand-500"
                                >
                                    {{
                                        localFilters.sort_dir === 'asc'
                                            ? '↑'
                                            : '↓'
                                    }}
                                </span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-if="!data.data.length">
                        <td
                            :colspan="columns.length"
                            class="py-10 text-center text-sm text-gray-400"
                        >
                            No records found.
                        </td>
                    </tr>
                    <tr
                        v-for="row in data.data"
                        :key="row.id"
                        class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                    >
                        <slot name="row" :row="row" />
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="data.links?.length > 3"
            class="flex flex-wrap gap-1 border-t border-gray-100 p-4 dark:border-gray-800"
        >
            <template v-for="(link, i) in data.links" :key="i">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
                    preserve-state
                    class="rounded-lg px-3 py-1.5 text-sm"
                    :class="
                        link.active
                            ? 'bg-brand-500 text-white'
                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'
                    "
                />
                <span
                    v-else
                    v-html="link.label"
                    class="cursor-default rounded-lg px-3 py-1.5 text-sm text-gray-400 opacity-50"
                />
            </template>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';

const props = defineProps({
    data: { type: Object, required: true }, // paginated response
    columns: { type: Array, required: true }, // [{ key, label, sortable }]
    filters: { type: Object, default: () => ({}) }, // current filter values from server
    routeName: { type: String, default: '' }, // ekhon r use hocche na (backward-compat er jonno rakha holo)
});

const localFilters = reactive({
    search: props.filters.search ?? '',
    sort_by: props.filters.sort_by ?? '',
    sort_dir: props.filters.sort_dir ?? 'desc',
    ...props.filters,
});

const hasActiveFilters = computed(() =>
    Object.entries(localFilters).some(
        ([key, val]) => key !== 'sort_by' && key !== 'sort_dir' && val,
    ),
);

let debounceTimer = null;

const apply = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        // Ziggy route() er bodole current path use kora hocche, karon
        // tenant subdomain wildcard route-e {tenant} parameter lagbe na
        router.get(
            window.location.pathname,
            { ...localFilters },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 300);
};

watch(() => localFilters.search, apply);

const toggleSort = (key) => {
    if (localFilters.sort_by === key) {
        localFilters.sort_dir =
            localFilters.sort_dir === 'asc' ? 'desc' : 'asc';
    } else {
        localFilters.sort_by = key;
        localFilters.sort_dir = 'asc';
    }
    apply();
};

const reset = () => {
    Object.keys(localFilters).forEach((k) => (localFilters[k] = ''));
    localFilters.sort_dir = 'desc';
    apply();
};

defineExpose({ apply });
</script>
