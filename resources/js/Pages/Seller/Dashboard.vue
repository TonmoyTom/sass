<template>
    <SellerLayout title="Dashboard">
        <div class="mx-auto">
            <!-- Greeting -->
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Welcome back, {{ seller.name }} 👋
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Here's how your sales are doing.
                    </p>
                </div>
                <div
                    class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <span class="text-xs text-gray-500 dark:text-gray-400"
                        >Your code:</span
                    >
                    <span
                        class="text-brand-600 dark:text-brand-400 font-mono text-sm font-bold"
                        >{{ seller.referral_code }}</span
                    >
                </div>
            </div>

            <!-- Balance hero + commission rate -->
            <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div
                    class="from-brand-500 to-brand-600 relative overflow-hidden rounded-2xl bg-gradient-to-br p-6 text-white lg:col-span-2"
                >
                    <div
                        class="pointer-events-none absolute -top-8 -right-8 h-40 w-40 rounded-full bg-white/10"
                    ></div>
                    <div class="relative">
                        <p class="text-sm font-medium text-white/70">
                            Available Balance
                        </p>
                        <p class="mt-1 text-4xl font-bold">
                            ৳{{ money(stats.available_balance) }}
                        </p>
                        <div class="mt-4 flex gap-6 text-sm">
                            <div>
                                <span class="text-white/60">Pending: </span
                                ><span class="font-semibold"
                                    >৳{{ money(stats.pending_balance) }}</span
                                >
                            </div>
                            <div>
                                <span class="text-white/60">Total earned: </span
                                ><span class="font-semibold"
                                    >৳{{ money(stats.total_earned) }}</span
                                >
                            </div>
                        </div>
                        <Link
                            :href="route('seller.wallet.index')"
                            class="text-brand-600 mt-5 inline-block rounded-xl bg-white px-5 py-2.5 text-sm font-semibold transition hover:bg-white/90"
                            >View Wallet</Link
                        >
                    </div>
                </div>

                <div
                    class="flex flex-col justify-center rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Your Commission Rate
                    </p>
                    <p
                        class="text-brand-600 dark:text-brand-400 mt-2 text-5xl font-bold"
                    >
                        {{ seller.commission_rate }}%
                    </p>
                    <p class="mt-2 text-xs text-gray-400">
                        on every module sale
                    </p>
                </div>
            </div>

            <!-- small stats -->
            <!-- withdraw stats row -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending Withdraw
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400"
                    >
                        ৳{{ money(stats.pending_withdraw) }}
                    </p>
                    <p class="mt-0.5 text-xs text-gray-400">
                        {{ stats.pending_withdraw_count }} request(s)
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Paid Out
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        ৳{{ money(stats.paid_out) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Refunded
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400"
                    >
                        ৳{{ money(stats.refunded) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Requested
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        ৳{{ money(stats.total_requested) }}
                    </p>
                </div>
            </div>

            <!-- charts -->
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Earnings Trend (30 Days)
                    </h5>
                    <div class="h-64"><canvas ref="earningsRef"></canvas></div>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Commission Status
                    </h5>
                    <div class="h-64"><canvas ref="statusRef"></canvas></div>
                </div>
            </div>

            <!-- earning by module + quick actions -->
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Earnings by Module
                    </h5>
                    <div v-if="byModule.length" class="space-y-3">
                        <div v-for="(m, i) in byModule" :key="i">
                            <div
                                class="mb-1 flex items-center justify-between text-sm"
                            >
                                <span
                                    class="font-medium text-gray-700 dark:text-gray-300"
                                    >{{ m.module }}</span
                                >
                                <span class="text-gray-800 dark:text-white/90"
                                    >৳{{ money(m.amount) }}
                                    <span class="text-xs text-gray-400"
                                        >({{ m.count }})</span
                                    ></span
                                >
                            </div>
                            <div
                                class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="bg-brand-500 h-full rounded-full"
                                    :style="{ width: barWidth(m.amount) }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">
                        No earnings yet
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Quick Actions
                    </h5>
                    <div class="flex flex-col gap-2">
                        <Link
                            :href="route('seller.modules.index')"
                            class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.02]"
                        >
                            <span
                                class="bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 flex h-9 w-9 items-center justify-center rounded-lg"
                                >📦</span
                            >
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Browse Modules</span
                            >
                        </Link>
                        <Link
                            :href="route('seller.referrals.index')"
                            class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.02]"
                        >
                            <span
                                class="bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 flex h-9 w-9 items-center justify-center rounded-lg"
                                >🔗</span
                            >
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Share Referral Code</span
                            >
                        </Link>
                        <Link
                            :href="route('seller.commissions.index')"
                            class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.02]"
                        >
                            <span
                                class="bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 flex h-9 w-9 items-center justify-center rounded-lg"
                                >💰</span
                            >
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >View Commissions</span
                            >
                        </Link>
                    </div>
                </div>
            </div>

            <!-- recent earnings -->
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h5 class="font-semibold text-gray-800 dark:text-white/90">
                        Recent Earnings
                    </h5>
                    <Link
                        :href="route('seller.commissions.index')"
                        class="text-brand-600 dark:text-brand-400 text-xs font-medium hover:underline"
                        >View all</Link
                    >
                </div>
                <div
                    v-if="recentCommissions.length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <div
                        v-for="(c, i) in recentCommissions"
                        :key="i"
                        class="flex items-center justify-between py-3"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-gray-800 dark:text-white/90"
                            >
                                {{ c.module_name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ c.tenant_name }} · {{ c.created_at }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-sm font-semibold text-green-600 dark:text-green-400"
                            >
                                +৳{{ money(c.amount) }}
                            </p>
                            <span
                                :class="statusClass(c.status)"
                                class="text-xs capitalize"
                                >{{ c.status }}</span
                            >
                        </div>
                    </div>
                </div>
                <p v-else class="py-10 text-center text-sm text-gray-400">
                    No earnings yet. Share your code to start earning!
                </p>
            </div>
            <!-- Withdraw Requests -->
            <div
                class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h5 class="font-semibold text-gray-800 dark:text-white/90">
                        Withdraw History
                    </h5>
                    <Link
                        :href="route('seller.wallet.withdraw.history')"
                        class="text-brand-600 dark:text-brand-400 text-xs font-medium hover:underline"
                    >
                        View All
                    </Link>
                </div>

                <div
                    v-if="withdrawRequests.length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <div
                        v-for="w in withdrawRequests"
                        :key="w.id"
                        class="flex cursor-pointer items-center justify-between py-3 transition hover:opacity-80"
                        @click="
                            router.visit(
                                route('seller.wallet.withdraw.show', w.id),
                            )
                        "
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full"
                                :class="{
                                    'bg-yellow-100 dark:bg-yellow-900/30':
                                        w.status === 'pending',
                                    'bg-green-100 dark:bg-green-900/30':
                                        w.status === 'approved',
                                    'bg-red-100 dark:bg-red-900/30':
                                        w.status === 'rejected',
                                }"
                            >
                                <span
                                    class="text-sm"
                                    :class="{
                                        'text-yellow-600 dark:text-yellow-400':
                                            w.status === 'pending',
                                        'text-green-600 dark:text-green-400':
                                            w.status === 'approved',
                                        'text-red-600 dark:text-red-400':
                                            w.status === 'rejected',
                                    }"
                                >
                                    {{
                                        w.status === 'pending'
                                            ? '⏳'
                                            : w.status === 'approved'
                                              ? '✓'
                                              : '✕'
                                    }}
                                </span>
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-800 capitalize dark:text-white/90"
                                >
                                    via {{ w.method }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ w.created_at }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-base font-bold text-gray-800 dark:text-white/90"
                            >
                                ৳{{ money(w.amount) }}
                            </p>

                            <template v-if="w.status === 'approved'">
                                <p
                                    class="text-xs text-green-600 dark:text-green-400"
                                >
                                    Paid ৳{{ money(w.paid_amount) }}
                                </p>
                                <p
                                    v-if="w.amount - w.paid_amount > 0"
                                    class="text-xs text-yellow-600 dark:text-yellow-400"
                                >
                                    Refunded ৳{{
                                        money(w.amount - w.paid_amount)
                                    }}
                                </p>
                            </template>

                            <span
                                class="text-xs font-semibold capitalize"
                                :class="{
                                    'text-yellow-600 dark:text-yellow-400':
                                        w.status === 'pending',
                                    'text-green-600 dark:text-green-400':
                                        w.status === 'approved',
                                    'text-red-600 dark:text-red-400':
                                        w.status === 'rejected',
                                }"
                                >{{ w.status }}</span
                            >
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center py-8 text-center">
                    <p class="text-sm text-gray-400">
                        No withdraw history yet.
                    </p>
                    <Link
                        :href="route('seller.wallet.withdraw.page')"
                        class="bg-brand-500 hover:bg-brand-600 mt-3 rounded-xl px-5 py-2 text-sm font-medium text-white transition"
                    >
                        Request Withdrawal
                    </Link>
                </div>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import { onMounted, ref } from 'vue';

Chart.register(...registerables);

const props = defineProps({
    seller: Object,
    stats: Object,
    earningsChart: Array,
    statusBreakdown: Object,
    byModule: Array,
    recentCommissions: { type: Array, default: () => [] },
    withdrawRequests: { type: Array, default: () => [] }, //
    hasPendingWithdraw: Boolean, // ← add
});

const earningsRef = ref(null);
const statusRef = ref(null);

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const statusClass = (status) =>
    ({
        pending: 'text-yellow-600 dark:text-yellow-400',
        approved: 'text-green-600 dark:text-green-400',
        paid: 'text-blue-600 dark:text-blue-400',
    })[status] ?? 'text-gray-500';

const barWidth = (value) => {
    const max = Math.max(...props.byModule.map((m) => m.amount), 1);
    return Math.round((value / max) * 100) + '%';
};

onMounted(() => {
    new Chart(earningsRef.value, {
        type: 'line',
        data: {
            labels: props.earningsChart.map((d) => d.date),
            datasets: [
                {
                    data: props.earningsChart.map((d) => d.amount),
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

    new Chart(statusRef.value, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Paid'],
            datasets: [
                {
                    data: [
                        props.statusBreakdown.pending,
                        props.statusBreakdown.approved,
                        props.statusBreakdown.paid,
                    ],
                    backgroundColor: ['#f59e0b', '#10b981', '#3b82f6'],
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
});
</script>
