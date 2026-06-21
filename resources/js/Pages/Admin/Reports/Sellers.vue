<template>
    <AdminLayout title="Seller Report">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center gap-3">
                <a href="/admin/reports" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Seller Report</h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ range.start }} — {{ range.end }}</p>
                </div>
            </div>

            <!-- summary -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Sellers</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ summary.total_sellers }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active</p>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ summary.active_sellers }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ summary.pending_sellers }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Commission Paid</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ money(summary.total_commission) }}</p>
                </div>
            </div>

            <!-- top sellers -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h5 class="font-semibold text-gray-800 dark:text-white/90">Top Sellers by Revenue</h5>
                </div>
                <table v-if="topSellers.length" class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">#</th>
                            <th class="px-5 py-3 font-medium">Seller</th>
                            <th class="px-5 py-3 font-medium">Orders</th>
                            <th class="px-5 py-3 text-right font-medium">Revenue</th>
                            <th class="px-5 py-3 text-right font-medium">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(s, i) in topSellers" :key="i" class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                            <td class="px-5 py-3.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">{{ i + 1 }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ s.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ s.email ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600 dark:text-gray-300">{{ s.orders }}</td>
                            <td class="px-5 py-3.5 text-right text-sm font-semibold text-gray-800 dark:text-white/90">৳{{ money(s.revenue) }}</td>
                            <td class="px-5 py-3.5 text-right text-sm font-medium text-amber-600 dark:text-amber-400">৳{{ money(s.commission) }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="py-8 text-center text-sm text-gray-400">No data</p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    summary: Object,
    topSellers: Array,
    range: Object,
});

const money = (v) => Number(v ?? 0).toLocaleString('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
</script>
