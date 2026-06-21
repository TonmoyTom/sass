<template>
    <AdminLayout title="Dashboard">
        <div class="mx-auto">
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Dashboard
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Platform overview & analytics.
                </p>
            </div>

            <!-- alerts -->
            <div
                v-if="hasAlerts"
                class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3"
            >
                <a
                    v-if="alerts.pending_sellers"
                    href="/admin/sellers?status=pending"
                    class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3.5 dark:border-amber-900/40 dark:bg-amber-900/10"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                        </svg>
                    </span>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ alerts.pending_sellers }} pending seller{{
                                alerts.pending_sellers > 1 ? 's' : ''
                            }}
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-400">
                            Tap to review →
                        </p>
                    </div>
                </a>
                <a
                    v-if="alerts.pending_commission"
                    href="/admin/commissions?status=pending"
                    class="flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 p-3.5 dark:border-blue-900/40 dark:bg-blue-900/10"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path
                                d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"
                            />
                        </svg>
                    </span>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ alerts.pending_commission }} commission{{
                                alerts.pending_commission > 1 ? 's' : ''
                            }}
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">
                            Approve pending →
                        </p>
                    </div>
                </a>
                <a
                    v-if="alerts.pending_requests"
                    href="/admin/module-requests"
                    class="flex items-center gap-3 rounded-xl border border-purple-200 bg-purple-50 p-3.5 dark:border-purple-900/40 dark:bg-purple-900/10"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"
                            />
                        </svg>
                    </span>
                    <div>
                        <p
                            class="text-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ alerts.pending_requests }} module request{{
                                alerts.pending_requests > 1 ? 's' : ''
                            }}
                        </p>
                        <p class="text-xs text-purple-600 dark:text-purple-400">
                            Review now →
                        </p>
                    </div>
                </a>
            </div>

            <!-- stats with growth -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    v-for="card in statCards"
                    :key="card.key"
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="flex items-start justify-between">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl"
                            :class="card.iconBg"
                        >
                            <span v-html="card.icon" />
                        </div>
                        <span
                            class="flex items-center gap-0.5 text-xs font-medium"
                            :class="
                                card.growth >= 0
                                    ? 'text-green-600 dark:text-green-400'
                                    : 'text-red-600 dark:text-red-400'
                            "
                        >
                            <svg
                                v-if="card.growth >= 0"
                                class="h-3 w-3"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                            >
                                <path
                                    d="M7 17L17 7M17 7H7M17 7v10"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <svg
                                v-else
                                class="h-3 w-3"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                            >
                                <path
                                    d="M7 7l10 10M17 17H7M17 17V7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            {{ Math.abs(card.growth) }}%
                        </span>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        {{ card.label }}
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ card.prefix }}{{ card.formatted }}
                    </p>
                </div>
            </div>

            <!-- commission cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Commission Paid
                    </p>
                    <p
                        class="mt-1 text-xl font-bold text-green-600 dark:text-green-400"
                    >
                        ৳{{ money(commissionStats.total_paid) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Available to Withdraw
                    </p>
                    <p
                        class="mt-1 text-xl font-bold text-blue-600 dark:text-blue-400"
                    >
                        ৳{{ money(commissionStats.available) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending Commission
                    </p>
                    <p
                        class="mt-1 text-xl font-bold text-amber-600 dark:text-amber-400"
                    >
                        ৳{{ money(commissionStats.pending) }}
                    </p>
                </div>
            </div>

            <!-- chart row 1 -->
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Revenue Trend (30 Days)
                    </h5>
                    <div class="h-64"><canvas ref="revenueRef"></canvas></div>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Revenue by Module
                    </h5>
                    <div class="h-64"><canvas ref="moduleRef"></canvas></div>
                </div>
            </div>

            <!-- chart row 2 -->
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Monthly Comparison (6 Months)
                    </h5>
                    <div class="h-64"><canvas ref="monthlyRef"></canvas></div>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Tenant Status
                    </h5>
                    <div class="h-64"><canvas ref="tenantRef"></canvas></div>
                </div>
            </div>

            <!-- lists row 1 -->
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Top Sellers
                    </h5>
                    <div v-if="topSellers.length" class="space-y-3.5">
                        <div
                            v-for="(s, i) in topSellers"
                            :key="i"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold"
                                    >{{ i + 1 }}</span
                                >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        {{ s.name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ s.orders }} orders
                                    </p>
                                </div>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-800 dark:text-white/90"
                                >৳{{ money(s.revenue) }}</span
                            >
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">
                        No data
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Top Modules
                    </h5>
                    <div v-if="topModules.length" class="space-y-3.5">
                        <div
                            v-for="(m, i) in topModules"
                            :key="i"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-green-50 text-xs font-bold text-green-600 dark:bg-green-900/20 dark:text-green-400"
                                    >{{ i + 1 }}</span
                                >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        {{ m.name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ m.orders }} orders
                                    </p>
                                </div>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-800 dark:text-white/90"
                                >৳{{ money(m.revenue) }}</span
                            >
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">
                        No data
                    </p>
                </div>
            </div>

            <!-- lists row 2 -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h5
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            Recent Orders
                        </h5>
                        <a
                            href="/admin/orders"
                            class="text-brand-600 dark:text-brand-400 text-xs font-medium hover:underline"
                            >View all</a
                        >
                    </div>
                    <div v-if="recentOrders.length" class="space-y-3">
                        <div
                            v-for="o in recentOrders"
                            :key="o.id"
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ o.tenant }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ o.module }} · {{ o.date }}
                                </p>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-800 dark:text-white/90"
                                >৳{{ money(o.amount) }}</span
                            >
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">
                        No orders
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h5
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            Recent Tenants
                        </h5>
                        <a
                            href="/admin/tenants"
                            class="text-brand-600 dark:text-brand-400 text-xs font-medium hover:underline"
                            >View all</a
                        >
                    </div>
                    <div v-if="recentTenants.length" class="space-y-3">
                        <div
                            v-for="(t, i) in recentTenants"
                            :key="i"
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ t.name }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ t.email ?? t.date }}
                                </p>
                            </div>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                :class="statusClass(t.status)"
                                >{{ t.status }}</span
                            >
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">
                        No tenants
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Chart, registerables } from 'chart.js';
import { computed, onMounted, ref } from 'vue';

Chart.register(...registerables);

const props = defineProps({
    stats: Object,
    commissionStats: Object,
    alerts: Object,
    revenueChart: Array,
    monthly: Array,
    byModule: Array,
    tenantStatus: Object,
    topSellers: Array,
    topModules: Array,
    recentOrders: Array,
    recentTenants: Array,
});

const revenueRef = ref(null);
const moduleRef = ref(null);
const monthlyRef = ref(null);
const tenantRef = ref(null);

const money = (v) =>
    Number(v ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const hasAlerts = computed(
    () =>
        props.alerts.pending_sellers ||
        props.alerts.pending_commission ||
        props.alerts.pending_requests,
);

const statusClass = (status) => {
    const map = {
        active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        pending:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        suspended:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};

const statCards = computed(() => [
    {
        key: 'revenue',
        label: 'Total Revenue',
        prefix: '৳',
        formatted: money(props.stats.revenue.value),
        growth: props.stats.revenue.growth,
        iconBg: 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
        icon: '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
    },
    {
        key: 'orders',
        label: 'Total Orders',
        prefix: '',
        formatted: props.stats.orders.value,
        growth: props.stats.orders.growth,
        iconBg: 'bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400',
        icon: '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>',
    },
    {
        key: 'tenants',
        label: 'Tenants',
        prefix: '',
        formatted: props.stats.tenants.value,
        growth: props.stats.tenants.growth,
        iconBg: 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
        icon: '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>',
    },
    {
        key: 'sellers',
        label: 'Active Sellers',
        prefix: '',
        formatted: props.stats.sellers.value,
        growth: props.stats.sellers.growth,
        iconBg: 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
        icon: '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
    },
]);

const palette = [
    '#465fff',
    '#10b981',
    '#f59e0b',
    '#ef4444',
    '#8b5cf6',
    '#06b6d4',
];

onMounted(() => {
    new Chart(revenueRef.value, {
        type: 'line',
        data: {
            labels: props.revenueChart.map((d) => d.date),
            datasets: [
                {
                    data: props.revenueChart.map((d) => d.revenue),
                    borderColor: '#465fff',
                    backgroundColor: 'rgba(70,95,255,0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
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
                        label: (c) =>
                            '৳' + Number(c.parsed.y).toLocaleString('en-BD'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => '৳' + v },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
            },
        },
    });

    new Chart(moduleRef.value, {
        type: 'doughnut',
        data: {
            labels: props.byModule.map((m) => m.module),
            datasets: [
                {
                    data: props.byModule.map((m) => m.revenue),
                    backgroundColor: palette,
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, font: { size: 11 }, padding: 12 },
                },
                tooltip: {
                    callbacks: {
                        label: (c) =>
                            c.label +
                            ': ৳' +
                            Number(c.parsed).toLocaleString('en-BD'),
                    },
                },
            },
        },
    });

    new Chart(monthlyRef.value, {
        type: 'bar',
        data: {
            labels: props.monthly.map((m) => m.month),
            datasets: [
                {
                    label: 'Revenue',
                    data: props.monthly.map((m) => m.revenue),
                    backgroundColor: '#465fff',
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'Orders',
                    data: props.monthly.map((m) => m.orders),
                    backgroundColor: 'rgba(16,185,129,0.5)',
                    borderRadius: 6,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { boxWidth: 12, font: { size: 11 } },
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
                x: { grid: { display: false } },
            },
        },
    });

    new Chart(tenantRef.value, {
        type: 'bar',
        data: {
            labels: ['Active', 'Pending', 'Suspended'],
            datasets: [
                {
                    data: [
                        props.tenantStatus.active,
                        props.tenantStatus.pending,
                        props.tenantStatus.suspended,
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderRadius: 6,
                    barThickness: 44,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                x: { grid: { display: false } },
            },
        },
    });
});
</script>
