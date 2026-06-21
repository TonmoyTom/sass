<template>
    <TenantLayout title="Cart">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center gap-4">
                <Link
                    :href="route('tenant.modules.index')"
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

            <!-- empty -->
            <div
                v-if="!items.length"
                class="rounded-2xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800"
                >
                    <svg
                        class="h-8 w-8 text-gray-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path
                            d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <p class="font-medium text-gray-800 dark:text-white/90">
                    Your cart is empty
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Browse modules and add the ones you need.
                </p>
                <Link
                    :href="route('tenant.modules.index')"
                    class="bg-brand-500 hover:bg-brand-600 mt-5 inline-block rounded-lg px-5 py-2.5 text-sm font-medium text-white"
                >
                    Browse Modules
                </Link>
            </div>

            <!-- cart items -->
            <!-- cart items -->
            <template v-else>
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
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
                                    class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 flex h-12 w-12 items-center justify-center rounded-xl"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <rect
                                            x="3"
                                            y="3"
                                            width="7"
                                            height="7"
                                            rx="1"
                                        />
                                        <rect
                                            x="14"
                                            y="3"
                                            width="7"
                                            height="7"
                                            rx="1"
                                        />
                                        <rect
                                            x="3"
                                            y="14"
                                            width="7"
                                            height="7"
                                            rx="1"
                                        />
                                        <rect
                                            x="14"
                                            y="14"
                                            width="7"
                                            height="7"
                                            rx="1"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
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
                                        class="font-semibold text-gray-800 dark:text-white/90"
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
                                    <p
                                        v-if="item.credit > 0"
                                        class="text-xs text-gray-400 line-through"
                                    >
                                        ৳{{ money(item.price) }}
                                    </p>
                                </div>
                                <button
                                    @click="remove(item)"
                                    class="text-gray-400 hover:text-red-500"
                                    title="Remove"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- credit info -->
                        <div
                            v-if="item.credit > 0"
                            class="mt-2 flex items-center gap-1.5 pl-16 text-xs text-green-600 dark:text-green-400"
                        >
                            <svg
                                class="h-3.5 w-3.5"
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
                            ৳{{ money(item.credit) }} credit from your current
                            plan's remaining days
                        </div>
                    </div>
                </div>

                <!-- summary -->
                <div
                    class="mt-5 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
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
                            class="text-sm font-medium text-gray-800 dark:text-white/90"
                            >৳{{ money(total) }}</span
                        >
                    </div>

                    <div class="flex items-center justify-between pt-3">
                        <span
                            class="font-semibold text-gray-800 dark:text-white/90"
                            >Total</span
                        >
                        <span
                            class="text-xl font-bold text-gray-800 dark:text-white/90"
                            >৳{{ money(total) }}</span
                        >
                    </div>

                    <p
                        v-if="has_credit"
                        class="mt-3 rounded-lg bg-green-50 p-3 text-xs text-green-700 dark:bg-green-900/20 dark:text-green-400"
                    >
                        You're getting credit for your current plans' remaining
                        days. Your new plan starts today.
                    </p>

                    <div
                        class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-between"
                    >
                        <button
                            @click="clearCart"
                            class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                        >
                            Clear Cart
                        </button>
                        <Link
                            :href="route('tenant.checkout.index')"
                            class="bg-brand-500 hover:bg-brand-600 rounded-lg px-6 py-2.5 text-center text-sm font-medium text-white"
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
