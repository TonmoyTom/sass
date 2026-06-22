<template>
    <TenantLayout title="Cart">
        <div class="space-y-6">
            <!-- ── Header ── -->
            <div
                class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <Link
                    :href="route('tenant.modules.index')"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div
                    class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                >
                    <ShoppingCart class="h-5 w-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3
                            class="text-xl font-bold tracking-tight text-gray-900 dark:text-white"
                        >
                            Your Cart
                        </h3>
                        <span
                            v-if="items.length"
                            class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                        >
                            {{ items.length }}
                            {{ items.length === 1 ? 'item' : 'items' }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Review your modules before checkout.
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
                    <ShoppingCart
                        class="h-8 w-8 text-gray-400 dark:text-gray-500"
                    />
                </div>
                <p class="font-semibold text-gray-900 dark:text-white">
                    Your cart is empty
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Browse modules and add the ones you need.
                </p>
                <Link
                    :href="route('tenant.modules.index')"
                    class="bg-brand-500 hover:bg-brand-600 mt-5 inline-block rounded-xl px-5 py-2.5 text-sm font-semibold text-white"
                >
                    Browse Modules
                </Link>
            </div>

            <!-- ── Cart items ── -->
            <template v-else>
                <div
                    class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div
                        v-for="(item, i) in items"
                        :key="item.id"
                        class="p-5"
                        :class="
                            i < items.length - 1
                                ? 'border-b border-gray-100 dark:border-gray-800'
                                : ''
                        "
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                                >
                                    <LayoutGrid class="h-6 w-6" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p
                                            class="font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ item.module_name }}
                                        </p>
                                        <span
                                            v-if="item.is_upgrade"
                                            class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 rounded-full px-2 py-0.5 text-[10px] font-medium"
                                        >
                                            Upgrade
                                        </span>
                                    </div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ item.tier_name }} ·
                                        <span class="capitalize">{{
                                            item.billing_cycle
                                        }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
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
                                    <p
                                        v-if="item.credit > 0"
                                        class="text-xs text-gray-400 line-through dark:text-gray-500"
                                    >
                                        ৳{{ money(item.price) }}
                                    </p>
                                </div>
                                <button
                                    @click="remove(item)"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 dark:text-gray-500 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                    title="Remove"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- credit info -->
                        <div
                            v-if="item.credit > 0"
                            class="mt-2 flex items-center gap-1.5 pl-16 text-xs text-green-600 dark:text-green-400"
                        >
                            <Check class="h-3.5 w-3.5 shrink-0" />
                            ৳{{ money(item.credit) }} credit from your current
                            plan's remaining days
                        </div>
                    </div>
                </div>

                <!-- ── Summary ── -->
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div
                        v-if="has_credit"
                        class="flex items-center justify-between pb-2"
                    >
                        <span class="text-sm text-gray-500 dark:text-gray-400"
                            >Subtotal (full price)</span
                        >
                        <span
                            class="text-sm text-gray-500 line-through dark:text-gray-400"
                            >৳{{ money(full_total) }}</span
                        >
                    </div>
                    <div
                        v-if="has_credit"
                        class="flex items-center justify-between border-b border-gray-100 pb-3 text-green-600 dark:border-gray-800 dark:text-green-400"
                    >
                        <span class="text-sm">Total credit</span>
                        <span class="text-sm font-medium"
                            >−৳{{ money(full_total - total) }}</span
                        >
                    </div>
                    <div
                        v-else
                        class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800"
                    >
                        <span class="text-sm text-gray-500 dark:text-gray-400"
                            >Subtotal</span
                        >
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white"
                            >৳{{ money(total) }}</span
                        >
                    </div>

                    <div class="flex items-center justify-between pt-3">
                        <span
                            class="font-semibold text-gray-900 dark:text-white"
                            >Total</span
                        >
                        <span
                            class="text-xl font-bold text-gray-900 dark:text-white"
                            >৳{{ money(total) }}</span
                        >
                    </div>

                    <p
                        v-if="has_credit"
                        class="mt-3 rounded-xl bg-green-50 p-3 text-xs text-green-700 dark:bg-green-900/20 dark:text-green-400"
                    >
                        You're getting credit for your current plans' remaining
                        days. Your new plan starts today.
                    </p>

                    <div
                        class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-between"
                    >
                        <button
                            @click="clearCart"
                            class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Clear Cart
                        </button>
                        <Link
                            :href="route('tenant.checkout.index')"
                            class="bg-brand-500 hover:bg-brand-600 rounded-xl px-6 py-2.5 text-center text-sm font-semibold text-white"
                        >
                            Proceed to Checkout
                        </Link>
                    </div>
                </div>
            </template>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    LayoutGrid,
    ShoppingCart,
    Trash2,
} from 'lucide-vue-next';

defineProps({
    items: Array,
    total: Number,
    full_total: Number,
    has_credit: Boolean,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const remove = (item) => {
    router.delete(route('tenant.cart.remove', item.id), {
        preserveScroll: true,
    });
};

const clearCart = () => {
    if (!confirm('Clear all items from cart?')) return;
    router.post(route('tenant.cart.clear'), {}, { preserveScroll: true });
};
</script>
