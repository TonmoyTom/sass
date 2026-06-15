<template>
    <SellerLayout title="Request Details">
        <div class="mx-auto">
            <!-- Header -->
            <div class="mb-6 flex items-center gap-4">
                <Link
                    :href="route('seller.modules.index')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                >
                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M15 18l-6-6 6-6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </Link>
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    {{ request.module.name }}
                </h3>
                <span
                    :class="statusClass(request.status)"
                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                >
                    {{ request.status }}
                </span>
            </div>

            <!-- status banner -->
            <div
                v-if="request.status === 'approved'"
                class="mb-5 flex items-start gap-3 rounded-2xl border border-green-200 bg-green-50 p-4 dark:border-green-900/40 dark:bg-green-900/20"
            >
                <div
                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40"
                >
                    <svg
                        class="h-5 w-5 text-green-600 dark:text-green-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path
                            d="M20 6L9 17l-5-5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-green-800 dark:text-green-300">
                        You're approved to sell this module!
                    </p>
                    <p
                        class="mt-0.5 text-sm text-green-700 dark:text-green-400"
                    >
                        You can sell all tiers below and earn
                        <strong>{{ request.module.seller_commission }}%</strong>
                        commission on every sale — your personal rate set by
                        admin.
                    </p>
                </div>
            </div>

            <div
                v-else-if="request.status === 'pending'"
                class="mb-5 flex items-start gap-3 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900/40 dark:bg-yellow-900/20"
            >
                <div
                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/40"
                >
                    <svg
                        class="h-5 w-5 text-yellow-600 dark:text-yellow-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <circle cx="12" cy="12" r="9" />
                        <path
                            d="M12 7v5l3 2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <div>
                    <p
                        class="font-semibold text-yellow-800 dark:text-yellow-300"
                    >
                        Under review
                    </p>
                    <p
                        class="mt-0.5 text-sm text-yellow-700 dark:text-yellow-400"
                    >
                        Admin is reviewing your request. You'll be notified once
                        it's approved.
                    </p>
                </div>
            </div>

            <div
                v-else-if="request.status === 'rejected'"
                class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-900/20"
            >
                <div
                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40"
                >
                    <svg
                        class="h-5 w-5 text-red-600 dark:text-red-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path
                            d="M18 6L6 18M6 6l12 12"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-red-800 dark:text-red-300">
                        Request rejected
                    </p>
                    <p class="mt-0.5 text-sm text-red-700 dark:text-red-400">
                        {{
                            request.admin_note ||
                            'No reason provided. Contact admin for details.'
                        }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- module info -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-3 font-semibold text-gray-800 dark:text-white/90"
                    >
                        About this module
                    </h5>
                    <p
                        class="mb-4 text-sm leading-relaxed text-gray-600 dark:text-gray-400"
                    >
                        {{
                            request.module.description ||
                            'No description provided.'
                        }}
                    </p>
                    <dl
                        class="space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800"
                    >
                        <Row
                            label="Category"
                            :value="request.module.category"
                        />
                        <Row label="Requested On" :value="request.created_at" />
                        <Row
                            v-if="request.reviewed_at"
                            label="Reviewed On"
                            :value="request.reviewed_at"
                        />
                    </dl>
                </section>

                <!-- commission breakdown -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Your Commission
                    </h5>
                    <div
                        class="bg-brand-50 dark:bg-brand-900/20 rounded-xl p-4 text-center"
                    >
                        <p
                            class="text-brand-600 dark:text-brand-400 text-3xl font-bold"
                        >
                            {{ request.module.seller_commission }}%
                        </p>
                        <p
                            class="text-brand-600/80 dark:text-brand-400/80 mt-1 text-xs"
                        >
                            on every sale you make
                        </p>
                    </div>
                    <dl class="mt-4 space-y-2">
                        <div class="flex justify-between gap-4">
                            <dt
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                Module default rate
                            </dt>
                            <dd
                                class="text-right text-xs font-medium text-gray-400 line-through"
                            >
                                {{ request.module.module_commission }}%
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt
                                class="text-xs font-medium text-gray-600 dark:text-gray-300"
                            >
                                Your rate (set by admin)
                            </dt>
                            <dd
                                class="text-right text-xs font-bold text-green-600 dark:text-green-400"
                            >
                                {{ request.module.seller_commission }}%
                            </dd>
                        </div>
                    </dl>
                    <p
                        class="mt-3 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400"
                    >
                        You earn your personal rate, not the module's default.
                    </p>
                </section>

                <!-- seller note -->
                <section
                    v-if="request.note"
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-2 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Your Note
                    </h5>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ request.note }}
                    </p>
                </section>

                <!-- tiers -->
                <section
                    v-if="
                        request.status === 'approved' &&
                        request.module.tiers.length
                    "
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Tiers you can sell
                    </h5>
                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                        Each tier you sell earns you
                        {{ request.module.seller_commission }}% commission.
                    </p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div
                            v-for="(tier, i) in request.module.tiers"
                            :key="i"
                            class="rounded-xl border border-gray-200 p-4 dark:border-gray-700"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-medium text-gray-800 dark:text-white/90"
                                    >{{ tier.name }}</span
                                >
                                <span class="text-xs text-gray-400"
                                    >Tier {{ i + 1 }}</span
                                >
                            </div>
                            <div
                                class="mt-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                ৳{{ money(tier.monthly_price)
                                }}<span class="text-xs">/mo</span>
                                <span
                                    v-if="tier.yearly_price"
                                    class="ml-2 text-xs text-gray-400"
                                >
                                    · ৳{{ money(tier.yearly_price) }}/yr
                                </span>
                            </div>
                            <div
                                class="mt-2 text-xs text-green-600 dark:text-green-400"
                            >
                                You earn ৳{{
                                    money(
                                        (tier.monthly_price *
                                            request.module.seller_commission) /
                                            100,
                                    )
                                }}/mo per sale
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link } from '@inertiajs/vue3';
import { h } from 'vue';

defineProps({
    request: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const Row = (p) =>
    h('div', { class: 'flex justify-between gap-4' }, [
        h('dt', { class: 'text-sm text-gray-500 dark:text-gray-400' }, p.label),
        h(
            'dd',
            {
                class: 'text-sm font-medium text-gray-800 dark:text-white/90 text-right capitalize',
            },
            p.value || '—',
        ),
    ]);

const statusClass = (status) =>
    ({
        pending:
            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        approved:
            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        rejected:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[status] ?? 'bg-gray-100 text-gray-700';
</script>
