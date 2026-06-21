<template>
    <AdminLayout title="Reports">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Reports & Analytics</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ range.start }} — {{ range.end }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="/admin/reports/revenue" class="rounded-lg border border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Revenue</a>
                    <a href="/admin/reports/tenants" class="rounded-lg border border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Tenants</a>
                    <a href="/admin/reports/sellers" class="rounded-lg border border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Sellers</a>
                </div>
            </div>

            <!-- stats -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Revenue (30d)</p>
                    <p class="mt-1 text-xl font-bold text-green-600 dark:text-green-400">৳{{ money(stats.month_revenue) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Orders (30d)</p>
                    <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.month_orders }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Revenue</p>
                    <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">৳{{ money(stats.total_revenue) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Orders</p>
                    <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.total_orders }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tenants</p>
                    <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.total_tenants }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active Sellers</p>
                    <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.total_sellers }}</p>
                </div>
            </div>

            <!-- chart -->
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h5 class="mb-5 font-semibold text-gray-800 dark:text-white/90">Revenue (Last 30 Days)</h5>
                <div class="h-72">
                    <canvas ref="chartRef"></canvas>
                </div>
            </div>

            <!-- top sellers + modules -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">Top Sellers</h5>
                    <div v-if="topSellers.length" class="space-y-3">
                        <div v-for="(s, i) in topSellers" :key="i" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">{{ i + 1 }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ s.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ s.orders }} orders</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white/90">৳{{ money(s.revenue) }}</span>
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">No data</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">Top Modules</h5>
                    <div v-if="topModules.length" class="space-y-3">
                        <div v-for="(m, i) in topModules" :key="i" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">{{ i + 1 }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ m.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ m.orders }} orders</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white/90">৳{{ money(m.revenue) }}</span>
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">No data</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Chart, registerables } from 'chart.js';
import { onMounted, ref } from 'vue';

Chart.register(...registerables);

const props = defineProps({
    stats: Object,
    chart: Array,
    topSellers: Array,
    topModules: Array,
    range: Object,
});

const chartRef = ref(null);

const money = (v) => Number(v ?? 0).toLocaleString('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

onMounted(() => {
    new Chart(chartRef.value, {
        type: 'line',
        data: {
            labels: props.chart.map((d) => d.date),
            datasets: [
                {
                    label: 'Revenue',
                    data: props.chart.map((d) => d.revenue),
                    borderColor: '#465fff',
                    backgroundColor: 'rgba(70, 95, 255, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => '৳' + Number(ctx.parsed.y).toLocaleString('en-BD'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => '৳' + v },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 10 },
                },
            },
        },
    });
});
</script>
