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
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h5 class="font-semibold text-gray-800 dark:text-white/90">Commission History</h5>

                    <!-- status filter -->
                    <div class="flex gap-1">
                        <Link
                            v-for="f in statusFilters"
                            :key="f.value"
                            :href="route('seller.commissions.index', f.value ? { status: f.value } : {})"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="(filters.status ?? '') === f.value
                                ? 'bg-brand-500 text-white'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'"
                        >
                            {{ f.label }}
                        </Link>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 font-medium">Module</th>
                                <th class="px-4 py-3 font-medium">Customer</th>
                                <th class="px-4 py-3 font-medium">Sale</th>
                                <th class="px-4 py-3 font-medium">Rate</th>
                                <th class="px-4 py-3 font-medium">Commission</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="c in commissions.data" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
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
                            </tr>

                            <tr v-if="!commissions.data.length">
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No commissions yet. Start referring modules to earn!
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- pagination -->
                <div v-if="commissions.links.length > 3" class="mt-5 flex flex-wrap gap-1">
                    <template v-for="(link, i) in commissions.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-sm"
                            :class="link.active ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'"
                        />
                        <span v-else v-html="link.label" class="cursor-default rounded-lg px-3 py-1.5 text-sm text-gray-400 opacity-50" />
                    </template>
                </div>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link } from '@inertiajs/vue3';

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
