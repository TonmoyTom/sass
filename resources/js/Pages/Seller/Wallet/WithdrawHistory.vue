<template>
    <SellerLayout title="Withdraw History">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <Link
                        :href="route('seller.wallet.index')"
                        class="mb-2 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        ← Back to Wallet
                    </Link>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Withdraw History
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        All your withdrawal requests.
                    </p>
                </div>
                <Link
                    :href="route('seller.wallet.withdraw.page')"
                    class="bg-brand-500 hover:bg-brand-600 rounded-xl px-4 py-2 text-sm font-medium text-white transition"
                >
                    New Request
                </Link>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    v-if="requests.data.length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <div
                        v-for="w in requests.data"
                        :key="w.id"
                        class="flex cursor-pointer items-center justify-between p-4 transition hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        @click="
                            router.visit(
                                route('seller.wallet.withdraw.show', w.id),
                            )
                        "
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                                :class="{
                                    'bg-yellow-100 dark:bg-yellow-900/30':
                                        w.status === 'pending',
                                    'bg-green-100 dark:bg-green-900/30':
                                        w.status === 'approved',
                                    'bg-red-100 dark:bg-red-900/30':
                                        w.status === 'rejected',
                                }"
                            >
                                <span class="text-base">
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
                                    Requested: {{ w.created_at }}
                                </p>
                                <p
                                    v-if="w.processed_at"
                                    class="text-xs text-gray-400"
                                >
                                    Processed: {{ w.processed_at }}
                                </p>
                                <p
                                    v-if="w.note"
                                    class="mt-0.5 text-xs text-gray-400 italic"
                                >
                                    "{{ w.note }}"
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
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
                            <svg
                                class="h-4 w-4 text-gray-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M9 18l6-6-6-6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div v-else class="py-16 text-center">
                    <p class="text-sm text-gray-400">
                        No withdraw requests yet.
                    </p>
                    <Link
                        :href="route('seller.wallet.withdraw.page')"
                        class="bg-brand-500 hover:bg-brand-600 mt-3 inline-block rounded-xl px-5 py-2 text-sm font-medium text-white"
                    >
                        Request Withdrawal
                    </Link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="requests.links.length > 3"
                    class="flex flex-wrap gap-1 border-t border-gray-100 p-4 dark:border-gray-800"
                >
                    <template v-for="(link, i) in requests.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-sm"
                            :class="
                                link.active
                                    ? 'bg-brand-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'
                            "
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="cursor-default rounded-lg px-3 py-1.5 text-sm text-gray-400 opacity-50"
                        />
                    </template>
                </div>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    requests: { type: Object, default: () => ({ data: [], links: [] }) },
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
</script>
