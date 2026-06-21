<template>
    <TenantLayout title="Checkout">
        <div class="mx-auto">
            <div class="mb-6 flex items-center gap-4">
                <Link
                    :href="route('tenant.cart.index')"
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
                    Checkout
                </h3>
            </div>

            <!-- empty -->
            <div
                v-if="!items.length"
                class="rounded-2xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <p class="text-gray-500 dark:text-gray-400">
                    Your cart is empty.
                </p>
                <Link
                    :href="route('tenant.modules.index')"
                    class="bg-brand-500 hover:bg-brand-600 mt-4 inline-block rounded-lg px-5 py-2.5 text-sm font-medium text-white"
                >
                    Browse Modules
                </Link>
            </div>

            <template v-else>
                <!-- order summary -->
                <!-- order summary -->
                <div
                    class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Order Summary
                    </h5>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="item in items" :key="item.id" class="py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p
                                            class="text-sm font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ item.module_name }}
                                        </p>
                                        <span
                                            v-if="item.is_upgrade"
                                            class="bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 rounded-full px-2 py-0.5 text-[10px] font-medium"
                                        >
                                            Upgrade
                                        </span>
                                    </div>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ item.tier_name }} ·
                                        <span class="capitalize">{{
                                            item.billing_cycle
                                        }}</span>
                                    </p>
                                </div>
                                <p
                                    class="text-sm font-semibold text-gray-800 dark:text-white/90"
                                >
                                    ৳{{ money(item.charge)
                                    }}<span
                                        class="text-xs font-normal text-gray-400"
                                        >/{{
                                            item.billing_cycle === 'yearly'
                                                ? 'yr'
                                                : 'mo'
                                        }}</span
                                    >
                                </p>
                            </div>

                            <!-- proration breakdown (credit thakle) -->
                            <div
                                v-if="item.credit > 0"
                                class="mt-2 space-y-1 rounded-lg bg-gray-50 p-2.5 text-xs dark:bg-white/[0.02]"
                            >
                                <div
                                    class="flex justify-between text-gray-500 dark:text-gray-400"
                                >
                                    <span>Plan price</span>
                                    <span>৳{{ money(item.price) }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-green-600 dark:text-green-400"
                                >
                                    <span
                                        >Credit (current plan's remaining
                                        days)</span
                                    >
                                    <span>−৳{{ money(item.credit) }}</span>
                                </div>
                                <div
                                    class="flex justify-between border-t border-gray-200 pt-1 font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300"
                                >
                                    <span>You pay now</span>
                                    <span>৳{{ money(item.charge) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- credit note -->
                    <div
                        v-if="has_credit"
                        class="mt-3 rounded-lg bg-green-50 p-3 text-xs text-green-700 dark:bg-green-900/20 dark:text-green-400"
                    >
                        You're getting credit for your current plans' remaining
                        days. Your new plan starts today.
                    </div>

                    <div
                        class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800"
                    >
                        <div>
                            <span
                                class="font-semibold text-gray-800 dark:text-white/90"
                                >Total</span
                            >
                            <span
                                v-if="full_total && full_total !== total"
                                class="ml-2 text-sm text-gray-400 line-through"
                                >৳{{ money(full_total) }}</span
                            >
                        </div>
                        <span
                            class="text-xl font-bold text-gray-800 dark:text-white/90"
                            >৳{{ money(total) }}</span
                        >
                    </div>
                </div>
                <!-- referral code -->
                <div
                    class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Referral Code
                    </h5>
                    <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                        Have a referral code? Enter it below.
                    </p>
                    <input
                        v-model="form.referral_code"
                        type="text"
                        placeholder="e.g. ABC123"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 font-mono text-sm text-gray-800 uppercase focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                    <p
                        v-if="form.errors.referral_code"
                        class="mt-1.5 text-sm text-red-500"
                    >
                        {{ form.errors.referral_code }}
                    </p>
                </div>

                <!-- payment placeholder -->
                <div
                    class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Payment
                    </h5>
                    <p
                        class="rounded-lg bg-yellow-50 p-3 text-sm text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400"
                    >
                        Payment gateway coming soon. For now, your order will be
                        confirmed directly.
                    </p>
                </div>

                <!-- confirm -->
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="bg-brand-500 hover:bg-brand-600 w-full rounded-xl py-3.5 text-sm font-semibold text-white disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? 'Processing...'
                            : `Confirm Purchase · ৳${money(total)}`
                    }}
                </button>
            </template>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    items: Array,
    total: Number,
    full_total: Number,
    has_credit: Boolean,
    referral_code: String,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const form = useForm({
    referral_code: props.referral_code ?? '',
});

const submit = () => {
    form.post(route('tenant.checkout.store'));
};
</script>
