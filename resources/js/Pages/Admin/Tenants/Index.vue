<template>
    <AdminLayout title="Tenants">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-5 flex items-center justify-between">
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Tenants
                </h3>
                <Link
                    :href="route('admin.tenants.create')"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                >
                    + Create Tenant
                </Link>
            </div>

            <DataTable
                :data="tenants"
                :filters="filters"
                route-name="admin.tenants.index"
                :columns="[
                    { key: 'name', label: 'Name', sortable: true },
                    { key: 'domain', label: 'Domain' },
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
                        <option value="trial">Trial</option>
                        <option value="suspended">Suspended</option>
                        <option value="expired">Expired</option>
                    </select>
                </template>

                <template #row="{ row: tenant }">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                        {{ tenant.name }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        <a
                            v-if="tenant.domain"
                            :href="`http://${tenant.domain}`"
                            target="_blank"
                            class="text-brand-500 hover:underline"
                        >
                            {{ tenant.domain }}
                        </a>
                        <span v-else>{{ tenant.subdomain }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ tenant.email }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            :class="statusClass(tenant.status)"
                            class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                        >
                            {{ tenant.status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ tenant.created_at }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link
                                :href="route('admin.tenants.show', tenant.id)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                View
                            </Link>
                            <Link
                                :href="route('admin.tenants.edit', tenant.id)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="
                                    $page.props.auth?.user?.user_type === 'super_admin' &&
                                    tenant.user_id
                                "
                                @click="loginAsTenant(tenant)"
                                class="bg-brand-500 hover:bg-brand-600 cursor-pointer rounded-lg px-3 py-1.5 text-xs font-medium text-white"
                            >
                                Login
                            </button>
                            <button
                                v-if="tenant.status !== 'suspended'"
                                @click="suspend(tenant)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                Suspend
                            </button>
                            <button
                                v-else
                                @click="reactivate(tenant)"
                                class="rounded-lg border border-green-300 px-3 py-1.5 text-xs font-medium text-green-600 hover:bg-green-50 dark:border-green-800"
                            >
                                Reactivate
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
    tenants: Object,
    filters: { type: Object, default: () => ({}) },
});

const statusClass = (status) => {
    return (
        {
            active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            trial: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            suspended:
                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            expired:
                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        }[status] ?? 'bg-gray-100 text-gray-700'
    );
};

const suspend = (tenant) => {
    if (!confirm(`Suspend "${tenant.name}"?`)) return;
    router.post(
        route('admin.tenants.suspend', tenant.id),
        {},
        { preserveScroll: true },
    );
};

const reactivate = (tenant) => {
    router.post(
        route('admin.tenants.reactivate', tenant.id),
        {},
        { preserveScroll: true },
    );
};

const loginAsTenant = (tenant) => {
    router.post(
        route('admin.users.impersonate', tenant.user_id),
        {},
        { preserveScroll: true },
    );
};
</script>
