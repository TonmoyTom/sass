<template>
    <SellerLayout title="Withdraw Details">
        <div class="mx-auto max-w-lg">
            <div class="mb-6">
                <Link
                    :href="route('seller.wallet.index')"
                    class="mb-2 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ← Back to Wallet
                </Link>
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Withdraw Details
                </h3>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <!-- Status badge + amount -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Request #{{ request.id }}
                        </p>
                        <p
                            class="mt-1 text-3xl font-bold text-gray-800 dark:text-white/90"
                        >
                            ৳{{ money(request.amount) }}
                        </p>
                    </div>
                    <span
                        class="rounded-full px-4 py-1.5 text-sm font-semibold capitalize"
                        :class="{
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400':
                                request.status === 'pending',
                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400':
                                request.status === 'approved',
                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400':
                                request.status === 'rejected',
                        }"
                    >
                        {{ request.status }}
                    </span>
                </div>

                <!-- Timeline -->
                <div class="mb-6 space-y-1">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                        >
                            <span class="text-xs text-green-600">✓</span>
                        </div>
                        <div class="flex-1">
                            <p
                                class="text-sm font-medium text-gray-800 dark:text-white/90"
                            >
                                Request Submitted
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ request.created_at }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="ml-3.5 h-5 w-px bg-gray-200 dark:bg-gray-700"
                    ></div>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                            :class="
                                request.status === 'rejected'
                                    ? 'bg-red-100 dark:bg-red-900/30'
                                    : request.processed_at
                                      ? 'bg-green-100 dark:bg-green-900/30'
                                      : 'bg-gray-100 dark:bg-gray-800'
                            "
                        >
                            <span
                                class="text-xs"
                                :class="
                                    request.status === 'rejected'
                                        ? 'text-red-600'
                                        : request.processed_at
                                          ? 'text-green-600'
                                          : 'text-gray-400'
                                "
                            >
                                {{
                                    request.status === 'rejected'
                                        ? '✕'
                                        : request.processed_at
                                          ? '✓'
                                          : '○'
                                }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <p
                                class="text-sm font-medium text-gray-800 dark:text-white/90"
                            >
                                {{
                                    request.status === 'rejected'
                                        ? 'Request Rejected'
                                        : 'Payment Processed'
                                }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{
                                    request.processed_at ??
                                    'Pending admin review'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div
                    class="space-y-3 border-t border-gray-100 pt-5 dark:border-gray-800"
                >
                    <!-- Paid / Refunded (approved only) -->
                    <template v-if="request.status === 'approved'">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-sm text-gray-500 dark:text-gray-400"
                                >Paid Amount</span
                            >
                            <span
                                class="text-sm font-bold text-green-600 dark:text-green-400"
                                >৳{{ money(request.paid_amount) }}</span
                            >
                        </div>
                        <div
                            v-if="
                                Number(request.paid_amount) <
                                Number(request.amount)
                            "
                            class="-mx-3 flex items-center justify-between rounded-lg bg-yellow-50 px-3 py-2 dark:bg-yellow-900/20"
                        >
                            <span
                                class="text-sm font-medium text-yellow-700 dark:text-yellow-400"
                                >Refunded to Balance</span
                            >
                            <span
                                class="text-sm font-bold text-yellow-700 dark:text-yellow-400"
                                >৳{{
                                    money(request.amount - request.paid_amount)
                                }}</span
                            >
                        </div>
                    </template>

                    <!-- Method -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400"
                            >Payment Method</span
                        >
                        <span
                            class="text-sm font-medium text-gray-800 capitalize dark:text-white/90"
                        >
                            {{ request.method }}
                        </span>
                    </div>

                    <!-- Account info -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400"
                            >Account Name</span
                        >
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-white/90"
                        >
                            {{ request.account_name ?? '—' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400"
                            >Account Number</span
                        >
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-white/90"
                        >
                            {{ request.account_number ?? '—' }}
                        </span>
                    </div>

                    <!-- Processed by -->
                    <div
                        v-if="request.approved_by"
                        class="flex items-center justify-between"
                    >
                        <span class="text-sm text-gray-500 dark:text-gray-400"
                            >Processed By</span
                        >
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-white/90"
                        >
                            {{ request.approved_by }}
                        </span>
                    </div>

                    <!-- Note -->
                    <div
                        v-if="request.note"
                        class="flex items-start justify-between gap-4"
                    >
                        <span class="text-sm text-gray-500 dark:text-gray-400"
                            >Note</span
                        >
                        <span
                            class="text-right text-sm text-gray-600 italic dark:text-gray-400"
                        >
                            "{{ request.note }}"
                        </span>
                    </div>
                </div>

                <!-- Pending notice -->
                <div
                    v-if="request.status === 'pending'"
                    class="mt-5 rounded-xl bg-yellow-50 p-3 text-sm text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400"
                >
                    Your request is under review. Admin will process it shortly.
                </div>

                <!-- Rejected notice -->
                <div
                    v-if="request.status === 'rejected'"
                    class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
                >
                    This request was rejected and the amount was returned to
                    your available balance.
                </div>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    request: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
</script>
