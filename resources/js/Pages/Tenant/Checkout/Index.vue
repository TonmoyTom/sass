<template>
    <TenantLayout title="Checkout">
        <div class="space-y-6">
            <!-- ── Header ── -->
            <div
                class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <Link
                    :href="route('tenant.cart.index')"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div
                    class="bg-brand-50 text-brand-500 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl dark:bg-brand-900/20 dark:text-brand-400"
                >
                    <CreditCard class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Checkout
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Confirm your order and complete the purchase.
                    </p>
                </div>
            </div>

            <!-- ── Empty ── -->
            <div
                v-if="!items.length"
                class="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800"
                >
                    <ShoppingCart class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <p class="font-semibold text-gray-900 dark:text-white">Your cart is empty</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Add some modules before checking out.
                </p>
                <Link
                    :href="route('tenant.modules.index')"
                    class="bg-brand-500 hover:bg-brand-600 mt-5 inline-block rounded-xl px-5 py-2.5 text-sm font-semibold text-white"
                >
                    Browse Modules
                </Link>
            </div>

            <template v-else>
                <!-- ── Order summary ── -->
                <section
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="mb-4 flex items-center gap-2 font-semibold text-gray-900 dark:text-white"
                    >
                        <span
                            class="bg-brand-50 text-brand-500 flex h-7 w-7 items-center justify-center rounded-lg dark:bg-brand-900/20 dark:text-brand-400"
                        >
                            <Receipt class="h-4 w-4" />
                        </span>
                        Order Summary
                    </h5>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div
                            v-for="item in items"
                            :key="item.id"
                            class="py-3 first:pt-0"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p
                                            class="text-sm font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ item.module_name }}
                                        </p>
                                        <span
                                            v-if="item.is_upgrade"
                                            class="bg-brand-50 text-brand-600 rounded-full px-2 py-0.5 text-[10px] font-medium dark:bg-brand-900/20 dark:text-brand-400"
                                        >
                                            Upgrade
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ item.tier_name }} ·
                                        <span class="capitalize">{{
                                            item.billing_cycle
                                        }}</span>
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    ৳{{ money(item.charge)
                                    }}<span
                                        class="text-xs font-normal text-gray-400 dark:text-gray-500"
                                        >/{{
                                            item.billing_cycle === 'yearly'
                                                ? 'yr'
                                                : 'mo'
                                        }}</span
                                    >
                                </p>
                            </div>

                            <!-- proration breakdown -->
                            <div
                                v-if="item.credit > 0"
                                class="mt-2 space-y-1 rounded-xl bg-gray-50 p-3 text-xs dark:bg-gray-800/50"
                            >
                                <div class="flex justify-between text-gray-500 dark:text-gray-400">
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
                        class="mt-3 flex items-start gap-2 rounded-xl bg-green-50 p-3 text-xs text-green-700 dark:bg-green-900/20 dark:text-green-400"
                    >
                        <Check class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        You're getting credit for your current plans' remaining
                        days. Your new plan starts today.
                    </div>

                    <!-- total -->
                    <div
                        class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800"
                    >
                        <div>
                            <span class="font-semibold text-gray-900 dark:text-white"
                                >Total</span
                            >
                            <span
                                v-if="full_total && full_total !== total"
                                class="ml-2 text-sm text-gray-400 line-through dark:text-gray-500"
                                >৳{{ money(full_total) }}</span
                            >
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white"
                            >৳{{ money(total) }}</span
                        >
                    </div>
                </section>

                <!-- ── Referral code ── -->
                <section
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white"
                    >
                        <span
                            class="bg-brand-50 text-brand-500 flex h-7 w-7 items-center justify-center rounded-lg dark:bg-brand-900/20 dark:text-brand-400"
                        >
                            <Tag class="h-4 w-4" />
                        </span>
                        Referral Code
                    </h5>
                    <p class="mt-1 mb-3 text-sm text-gray-500 dark:text-gray-400">
                        Have a referral code? Enter it below.
                    </p>
                    <input
                        v-model="form.referral_code"
                        type="text"
                        placeholder="e.g. ABC123"
                        class="focus:border-brand-400 focus:ring-brand-100 h-11 w-full rounded-xl border border-gray-200 bg-white px-4 font-mono text-sm text-gray-800 uppercase focus:ring-2 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500 dark:focus:border-brand-600 dark:focus:ring-brand-900/40"
                    />
                    <p
                        v-if="form.errors.referral_code"
                        class="mt-1.5 text-sm text-red-500 dark:text-red-400"
                    >
                        {{ form.errors.referral_code }}
                    </p>
                </section>

                <!-- ── Payment ── -->
                <section
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="mb-3 flex items-center gap-2 font-semibold text-gray-900 dark:text-white"
                    >
                        <span
                            class="bg-brand-50 text-brand-500 flex h-7 w-7 items-center justify-center rounded-lg dark:bg-brand-900/20 dark:text-brand-400"
                        >
                            <CreditCard class="h-4 w-4" />
                        </span>
                        Payment
                    </h5>
                    <p
                        class="flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-sm text-amber-700 dark:bg-amber-900/20 dark:text-amber-400"
                    >
                        <Info class="mt-0.5 h-4 w-4 shrink-0" />
                        Payment gateway coming soon. For now, your order will be
                        confirmed directly.
                    </p>
                </section>

                <!-- ── Confirm ── -->
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
import {
    ArrowLeft,
    Check,
    CreditCard,
    Info,
    Receipt,
    ShoppingCart,
    Tag,
} from 'lucide-vue-next';

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
