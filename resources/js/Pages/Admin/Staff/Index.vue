<template>
    <AdminLayout title="Staff">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Staff
                </h3>
                <Link
                    :href="route('admin.staff.create')"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                >
                    <span class="text-base leading-none">+</span>
                    New Staff
                </Link>
            </div>

            <DataTable
                :data="staff"
                :filters="filters"
                route-name="admin.staff.index"
                :columns="[
                    { key: 'name', label: 'Name', sortable: true },
                    { key: 'email', label: 'Email' },
                    { key: 'status', label: 'Status' },
                    { key: 'created_at', label: 'Created', sortable: true },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #filters="{ filters: f, apply }">
                    <select
                        v-model="f.status"
                        @change="apply"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </template>

                <template #row="{ row: user }">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                        {{ user.name }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ user.email }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            :class="statusClass(user.status)"
                            class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                        >
                            {{ user.status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ user.created_at }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link
                                :href="route('admin.staff.edit', user.id)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroy(user)"
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
import { Link, router } from '@inertiajs/vue3';

defineProps({
    staff: Object,
    filters: { type: Object, default: () => ({}) },
});

const statusClass = (status) =>
    ({
        active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        inactive: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
    })[status] ?? 'bg-gray-100 text-gray-700';

const destroy = (user) => {
    if (!confirm(`"${user.name}" staff account delete korte chao?`)) return;
    router.delete(route('admin.staff.destroy', user.id), {
        preserveScroll: true,
    });
};
</script>
