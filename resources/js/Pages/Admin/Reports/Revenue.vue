<template>
    <AdminLayout title="Revenue Report">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center gap-3">
                <a href="/admin/reports" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Revenue Report</h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ range.start }} — {{ range.end }}</p>
                </div>
            </div>

            <!-- summary -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Revenue</p>
                    <p class="mt-1 text-xl font-bold text-green-600 dark:text-green-400">৳{{ money(summary.total_revenue) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Orders</p>
                    <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ summary.total_orders }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Commission Paid</p>
                    <p class="mt-1 text-xl font-bold text-amber-600 dark:text-amber-400">৳{{ money(summary.commission_paid) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Platform Earning</p>
                    <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">৳{{ money(summary.platform_earning) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Avg Order</p>
                    <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">৳{{ money(summary.avg_order) }}</p>
                </div>
            </div>

            <!-- chart -->
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h5 class="mb-5 font-semibold text-gray-800 dark:text-white/90">Daily Revenue & Orders</h5>
                <div class="h-72">
                    <canvas ref="chartRef"></canvas>
                </div>
            </div>

            <!-- breakdowns -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- by type -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">By Sale Type</h5>
                    <div v-if="byType.length" class="space-y-3">
                        <div v-for="(t, i) in byType" :key="i">
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium capitalize text-gray-700 dark:text-gray-300">{{ t.type }}</span>
                                <span class="text-gray-800 dark:text-white/90">৳{{ money(t.revenue) }} <span class="text-xs text-gray-400">({{ t.orders }})</span></span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full bg-brand-500" :style="{ width: barWidth(t.revenue, byType) }"></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">No data</p>
                </div>

                <!-- by module -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">By Module</h5>
                    <div v-if="byModule.length" class="space-y-3">
                        <div v-for="(m, i) in byModule" :key="i">
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ m.module }}</span>
                                <span class="text-gray-800 dark:text-white/90">৳{{ money(m.revenue) }} <span class="text-xs text-gray-400">({{ m.orders }})</span></span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full bg-green-500" :style="{ width: barWidth(m.revenue, byModule) }"></div>
                            </div>
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
    summary: Object,
    chart: Array,
    byType: Array,
    byModule: Array,
    range: Object,
});

const chartRef = ref(null);

const money = (v) => Number(v ?? 0).toLocaleString('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

const barWidth = (value, list) => {
    const max = Math.max(...list.map((x) => x.revenue), 1);
    return Math.round((value / max) * 100) + '%';
};

onMounted(() => {
    new Chart(chartRef.value, {
        type: 'bar',
        data: {
            labels: props.chart.map((d) => d.date),
            datasets: [
                {
                    type: 'line',
                    label: 'Revenue',
                    data: props.chart.map((d) => d.revenue),
                    borderColor: '#465fff',
                    backgroundColor: 'rgba(70, 95, 255, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                    borderWidth: 2,
                    yAxisID: 'y',
                    order: 0,
                },
                {
                    type: 'bar',
                    label: 'Orders',
                    data: props.chart.map((d) => d.orders),
                    backgroundColor: 'rgba(16, 185, 129, 0.25)',
                    borderRadius: 4,
                    yAxisID: 'y1',
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.dataset.label === 'Revenue'
                            ? '৳' + Number(ctx.parsed.y).toLocaleString('en-BD')
                            : ctx.parsed.y + ' orders',
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: { callback: (v) => '৳' + v },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    ticks: { stepSize: 1 },
                    grid: { display: false },
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
