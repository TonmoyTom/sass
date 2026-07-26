<template>
    <AdminLayout title="Sellers">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-5 flex items-center justify-between">
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Sellers
                </h3>
                <Link
                    :href="route('admin.sellers.create')"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                >
                    + Create Seller
                </Link>
            </div>

            <DataTable
                :data="sellers"
                :filters="filters"
                route-name="admin.sellers.index"
                :columns="[
                    { key: 'name', label: 'Seller' },
                    { key: 'referral_code', label: 'Referral Code' },
                    {
                        key: 'commission_rate',
                        label: 'Commission',
                        sortable: true,
                    },
                    { key: 'total_sales', label: 'Sales', sortable: true },
                    { key: 'balance', label: 'Balance' },
                    { key: 'status', label: 'Status' },
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
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </template>

                <template #row="{ row: seller }">
                    <td class="px-4 py-3">
                        <div
                            class="font-medium text-gray-800 dark:text-white/90"
                        >
                            {{ seller.name }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ seller.email }}
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        >
                            {{ seller.referral_code }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ seller.commission_rate }}%
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ seller.total_sales }}
                    </td>
                    <td
                        class="px-4 py-3 font-medium text-gray-800 dark:text-white/90"
                    >
                        ৳{{ formatMoney(seller.balance) }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            :class="statusClass(seller.status)"
                            class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                        >
                            {{ seller.status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link
                                :href="route('admin.sellers.show', seller.id)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                View
                            </Link>
                            <Link
                                :href="route('admin.sellers.edit', seller.id)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                Edit
                            </Link>
                            <a
                                target="_blank"
                                v-if="
                                    $page.props.auth?.user?.user_type ===
                                        'super_admin' && seller.user_id
                                "
                                @click="loginAsSeller(seller)"
                                class="bg-brand-500 hover:bg-brand-600 cursor-pointer rounded-lg px-3 py-1.5 text-xs font-medium text-white"
                            >
                                Login
                            </a>
                            <button
                                @click="destroy(seller)"
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
import DataTable from '@/Components/ui/DataTable.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    sellers: Object,
    filters: { type: Object, default: () => ({}) },
});

const formatMoney = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const statusClass = (status) =>
    ({
        active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        pending:
            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        suspended:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[status] ?? 'bg-gray-100 text-gray-700';

const destroy = (seller) => {
    if (!confirm(`Delete seller "${seller.name}"? Eta undo kora jabe na.`))
        return;
    router.delete(route('admin.sellers.destroy', seller.id), {
        preserveScroll: true,
    });
};

const loginAsSeller = (seller) => {
    router.post(
        route('admin.users.impersonate', seller.user_id),
        {},
        { preserveScroll: true },
    );
};
</script>
