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

            <!-- Balance hero + stats -->
            <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
                <!-- available balance (big) -->
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
                                <span class="text-white/60">Pending: </span>
                                <span class="font-semibold"
                                    >৳{{ money(stats.pending_balance) }}</span
                                >
                            </div>
                            <div>
                                <span class="text-white/60"
                                    >Total earned:
                                </span>
                                <span class="font-semibold"
                                    >৳{{ money(stats.total_earned) }}</span
                                >
                            </div>
                        </div>
                        <Link
                            :href="route('seller.wallet.index')"
                            class="text-brand-600 mt-5 inline-block rounded-xl bg-white px-5 py-2.5 text-sm font-semibold transition hover:bg-white/90"
                        >
                            View Wallet
                        </Link>
                    </div>
                </div>

                <!-- commission rate card -->
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
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Sales
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ stats.total_sales }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Approved Modules
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ stats.approved_modules }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending Requests
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400"
                    >
                        {{ stats.pending_requests }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending Earnings
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        ৳{{ money(stats.pending_balance) }}
                    </p>
                </div>
            </div>

            <!-- Quick actions + Recent -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <!-- quick actions -->
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

                <!-- recent commissions -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h5
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            Recent Earnings
                        </h5>
                        <Link
                            :href="route('seller.commissions.index')"
                            class="text-brand-600 dark:text-brand-400 text-xs font-medium hover:underline"
                        >
                            View all
                        </Link>
                    </div>

                    <div
                        v-if="recent_commissions.length"
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <div
                            v-for="(c, i) in recent_commissions"
                            :key="i"
                            class="flex items-center justify-between py-3"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ c.module_name }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
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
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    seller: Object,
    stats: Object,
    recent_commissions: Array,
});

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
</script>
