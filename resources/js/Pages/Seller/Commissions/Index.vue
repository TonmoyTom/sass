<template>
    <SellerLayout title="Commissions">
        <div class="mx-auto">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Commissions</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Track your earnings from every module sale made with your code.
                </p>
            </div>

            <!-- summary cards -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Earned</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ money(summary.total_earned) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Available</p>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">৳{{ money(summary.available) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">৳{{ money(summary.pending) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Paid Out</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ money(summary.paid) }}</p>
                </div>
            </div>

            <!-- table -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h5 class="mb-5 font-semibold text-gray-800 dark:text-white/90">Commission History</h5>

                <DataTable
                    :data="commissions"
                    :filters="filters"
                    route-name="seller.commissions.index"
                    :columns="[
                        { key: 'module', label: 'Module' },
                        { key: 'customer', label: 'Customer' },
                        { key: 'sale', label: 'Sale' },
                        { key: 'rate', label: 'Rate' },
                        { key: 'commission', label: 'Commission', sortable: true },
                        { key: 'status', label: 'Status' },
                        { key: 'date', label: 'Date', sortable: true },
                    ]"
                >
                    <template #filters="{ filters: f, apply }">
                        <div class="flex gap-1">
                            <button
                                v-for="sf in statusFilters"
                                :key="sf.value"
                                @click="f.status = sf.value; apply()"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium"
                                :class="
                                    (f.status ?? '') === sf.value
                                        ? 'bg-brand-500 text-white'
                                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'
                                "
                            >
                                {{ sf.label }}
                            </button>
                        </div>
                    </template>

                    <template #row="{ row: c }">
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">{{ c.module_name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ c.tenant_name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">৳{{ money(c.sale_amount) }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ c.rate }}%</td>
                        <td class="px-4 py-3 font-medium text-green-600 dark:text-green-400">৳{{ money(c.amount) }}</td>
                        <td class="px-4 py-3">
                            <span :class="statusClass(c.status)" class="rounded-full px-2.5 py-1 text-xs font-medium capitalize">
                                {{ c.status }}
                            </span>
                            <div v-if="c.status === 'pending' && c.hold_until" class="mt-1 text-[10px] text-gray-400">
                                Available {{ c.hold_until }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ c.created_at }}</td>
                    </template>
                </DataTable>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import DataTable from '@/Components/ui/DataTable.vue';

defineProps({
    commissions: Object,
    summary: Object,
    filters: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

const statusFilters = [
    { value: '', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Available' },
    { value: 'paid', label: 'Paid' },
];

const statusClass = (status) =>
    ({
        pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        approved: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        paid: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[status] ?? 'bg-gray-100 text-gray-700';
</script>
