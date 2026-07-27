<template>
    <AdminLayout title="Orders">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Orders
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    All module purchases across tenants.
                </p>
            </div>

            <!-- totals -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Revenue
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        ৳{{ money(totals.revenue) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Orders
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ counts.all }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Completed
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ counts.completed }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400"
                    >
                        {{ counts.pending }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">Free</p>
                    <p
                        class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400"
                    >
                        {{ counts.free }}
                    </p>
                </div>
            </div>

            <DataTable
                :data="orders"
                :filters="filters"
                route-name="admin.orders.index"
                :columns="[
                    { key: 'order', label: 'Order', sortable: true },
                    { key: 'tenant', label: 'Tenant' },
                    { key: 'module', label: 'Module' },
                    { key: 'amount', label: 'Amount', sortable: true },
                    { key: 'status', label: 'Status' },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #filters="{ filters: f, apply }">
                    <div
                        class="flex gap-2 border-b border-gray-200 dark:border-gray-800"
                    >
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="
                                f.status = tab.value;
                                apply();
                            "
                            class="border-b-2 px-4 py-2.5 text-sm font-medium transition"
                            :class="
                                (f.status ?? '') === tab.value
                                    ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                            "
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </template>

                <template #row="{ row: order }">
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800 dark:text-white/90">
                            {{ order.invoice_number }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ order.sold_at ?? order.created_at }}
                        </p>
                    </td>
                    <td
                        class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300"
                    >
                        {{ order.tenant_name }}
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm text-gray-800 dark:text-white/90">
                            {{ order.module_name }}
                        </p>
                        <p
                            v-if="order.tier_name"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ order.tier_name }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <p
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            ৳{{ money(order.amount) }}
                        </p>
                        <p
                            class="text-xs text-gray-500 capitalize dark:text-gray-400"
                        >
                            {{ order.sale_type }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                            :class="statusClass(order.status)"
                        >
                            {{ order.status }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a
                                :href="`/admin/orders/${order.id}`"
                                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800"
                                title="View"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                    />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </a>
                            <a
                                :href="`/admin/orders/${order.id}/invoice`"
                                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800"
                                title="Invoice"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"
                                    />
                                    <path
                                        d="M14 2v6h6M16 13H8M16 17H8M10 9H8"
                                    />
                                </svg>
                            </a>
                            <Link
                                :href="`/admin/renewal-reminders/send/${order.id}`"
                                method="post"
                                as="button"
                                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800"
                                title="Send Renewal Reminder"
                                preserve-scroll
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="M13.73 21a2 2 0 01-3.46 0"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </Link>
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
import { Link } from '@inertiajs/vue3';

defineProps({
    orders: Object,
    filters: Object,
    counts: Object,
    totals: Object,
});

const money = (v) =>
    Number(v ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const tabs = [
    { label: 'All', value: '' },
    { label: 'Completed', value: 'completed' },
    { label: 'Pending', value: 'pending' },
];

const statusClass = (status) => {
    const map = {
        completed:
            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        pending:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        refunded:
            'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};
</script>
