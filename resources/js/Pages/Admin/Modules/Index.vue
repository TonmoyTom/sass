<template>
    <AdminLayout title="Modules">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Modules
                </h3>
                <Link
                    :href="route('admin.modules.create')"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                >
                    + Create Module
                </Link>
            </div>

            <DataTable
                :data="modules"
                :filters="filters"
                route-name="admin.modules.index"
                :columns="[
                    { key: 'name', label: 'Module', sortable: true },
                    { key: 'category', label: 'Category' },
                    { key: 'pricing', label: 'Pricing' },
                    { key: 'tiers', label: 'Tiers' },
                    { key: 'starting', label: 'Starting' },
                    { key: 'commission', label: 'Commission', sortable: true },
                    { key: 'status', label: 'Status' },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #filters="{ filters: f, apply }">
                    <select
                        v-model="f.module_category"
                        @change="apply"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                    >
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>

                    <select
                        v-model="f.pricing_type"
                        @change="apply"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                    >
                        <option value="">All Pricing</option>
                        <option value="one_time">One-time</option>
                        <option value="subscription">Subscription</option>
                    </select>

                    <select
                        v-model="f.is_active"
                        @change="apply"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                    >
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </template>

                <template #row="{ row: m }">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800 dark:text-white/90">
                            {{ m.name }}
                            <span
                                v-if="m.is_core"
                                class="ml-1 rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                                >CORE</span
                            >
                        </div>
                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ m.alias }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 capitalize dark:text-gray-400">
                        {{ m.category }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ m.pricing_type === 'one_time' ? 'One-time' : 'Subscription' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ m.tiers_count }}
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                        {{ m.starting_price != null ? '৳' + money(m.starting_price) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ m.commission_rate }}%
                    </td>
                    <td class="px-4 py-3">
                        <span
                            :class="
                                m.is_active
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                            "
                            class="rounded-full px-2.5 py-1 text-xs font-medium"
                        >
                            {{ m.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link
                                :href="route('admin.modules.show', m.id)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                View
                            </Link>
                            <Link
                                :href="route('admin.modules.edit', m.id)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroy(m)"
                                class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    modules: Object,
    filters: { type: Object, default: () => ({}) },
});

// category dropdown er values — modules.data theke unique list, ba backend theke alada prop pathale sheta use koro
const page = usePage();
const categories = computed(() =>
    [...new Set((page.props.modules?.data ?? []).map((m) => m.category).filter(Boolean))],
);

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const destroy = (m) => {
    if (
        !confirm(
            `Delete module "${m.name}"? Tier gula-o delete hobe. Undo kora jabe na.`,
        )
    )
        return;
    router.delete(route('admin.modules.destroy', m.id), {
        preserveScroll: true,
    });
};
</script>
