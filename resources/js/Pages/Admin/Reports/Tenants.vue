<template>
    <AdminLayout title="Tenant Report">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center gap-3">
                <a href="/admin/reports" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Tenant Report</h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ range.start }} — {{ range.end }}</p>
                </div>
            </div>

            <!-- summary -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Tenants</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ summary.total_tenants }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active</p>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ summary.active_tenants }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">New (30d)</p>
                    <p class="mt-1 text-2xl font-bold text-brand-600 dark:text-brand-400">{{ summary.new_this_month }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Paying Tenants</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ summary.paying_tenants }}</p>
                </div>
            </div>

            <!-- top tenants -->
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h5 class="font-semibold text-gray-800 dark:text-white/90">Top Tenants by Revenue</h5>
                </div>
                <table v-if="topTenants.length" class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">#</th>
                            <th class="px-5 py-3 font-medium">Tenant</th>
                            <th class="px-5 py-3 font-medium">Orders</th>
                            <th class="px-5 py-3 text-right font-medium">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(t, i) in topTenants" :key="i" class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                            <td class="px-5 py-3.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">{{ i + 1 }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ t.tenant }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ t.email ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600 dark:text-gray-300">{{ t.orders }}</td>
                            <td class="px-5 py-3.5 text-right text-sm font-semibold text-gray-800 dark:text-white/90">৳{{ money(t.revenue) }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="py-8 text-center text-sm text-gray-400">No data</p>
            </div>

            <!-- recent tenants -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h5 class="font-semibold text-gray-800 dark:text-white/90">Recent Tenants</h5>
                </div>
                <table v-if="recent.length" class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Tenant</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(t, i) in recent" :key="i" class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ t.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ t.email ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium capitalize" :class="statusClass(t.status)">
                                    {{ t.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm text-gray-500 dark:text-gray-400">{{ t.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="py-8 text-center text-sm text-gray-400">No tenants</p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    summary: Object,
    topTenants: Array,
    recent: Array,
    range: Object,
});

const money = (v) => Number(v ?? 0).toLocaleString('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

const statusClass = (status) => {
    const map = {
        active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        suspended: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};
</script>
