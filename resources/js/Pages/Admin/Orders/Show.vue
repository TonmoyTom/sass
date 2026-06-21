<template>
    <AdminLayout title="Order Details">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a
                        href="/admin/orders"
                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                d="M19 12H5M12 19l-7-7 7-7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </a>
                    <div>
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-white/90"
                        >
                            INV-{{ String(order.id).padStart(6, '0') }}
                        </h3>
                        <p
                            class="mt-0.5 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ order.sold_at }}
                        </p>
                    </div>
                </div>
                <a
                    :href="`/admin/orders/${order.id}/invoice`"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"
                        />
                        <path d="M14 2v6h6" />
                    </svg>
                    View Invoice
                </a>
            </div>

            <!-- status banner -->
            <div
                class="mb-5 flex items-center gap-3 rounded-2xl border p-4"
                :class="
                    order.status === 'completed'
                        ? 'border-green-200 bg-green-50 dark:border-green-900/40 dark:bg-green-900/10'
                        : 'border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-900/10'
                "
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full"
                    :class="
                        order.status === 'completed'
                            ? 'bg-green-100 text-green-600 dark:bg-green-900/30'
                            : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30'
                    "
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M20 6L9 17l-5-5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <div>
                    <p
                        class="font-medium text-gray-800 capitalize dark:text-white/90"
                    >
                        {{ order.status }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ order.sale_type }} purchase
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- module info -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Module
                    </h5>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400"
                                >Module</span
                            >
                            <span
                                class="font-medium text-gray-800 dark:text-white/90"
                                >{{ order.module_name }}</span
                            >
                        </div>
                        <div
                            v-if="order.tier_name"
                            class="flex justify-between"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Tier</span
                            >
                            <span
                                class="font-medium text-gray-800 dark:text-white/90"
                                >{{ order.tier_name }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400"
                                >Type</span
                            >
                            <span
                                class="font-medium text-gray-800 capitalize dark:text-white/90"
                                >{{ order.sale_type }}</span
                            >
                        </div>
                    </div>
                </section>

                <!-- customer info -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Customer
                    </h5>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400"
                                >Tenant</span
                            >
                            <span
                                class="font-medium text-gray-800 dark:text-white/90"
                                >{{ order.tenant_name }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400"
                                >Email</span
                            >
                            <span
                                class="font-medium text-gray-800 dark:text-white/90"
                                >{{ order.tenant_email ?? '—' }}</span
                            >
                        </div>
                        <div
                            v-if="order.seller_name"
                            class="flex justify-between"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Via Seller</span
                            >
                            <span
                                class="font-medium text-gray-800 dark:text-white/90"
                                >{{ order.seller_name }}</span
                            >
                        </div>
                    </div>
                </section>
            </div>

            <!-- amount breakdown -->
            <section
                class="mt-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">
                    Amount Breakdown
                </h5>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400"
                            >Total Amount</span
                        >
                        <span
                            class="font-semibold text-gray-800 dark:text-white/90"
                            >৳{{ money(order.amount) }}</span
                        >
                    </div>
                    <div v-if="order.commission" class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400"
                            >Seller Commission</span
                        >
                        <span
                            class="font-medium text-amber-600 dark:text-amber-400"
                            >৳{{ money(order.commission) }}</span
                        >
                    </div>
                    <div
                        class="flex justify-between border-t border-gray-100 pt-3 dark:border-gray-800"
                    >
                        <span
                            class="font-medium text-gray-700 dark:text-gray-300"
                            >Platform Earning</span
                        >
                        <span
                            class="font-semibold text-green-600 dark:text-green-400"
                            >৳{{
                                money(order.admin_amount ?? order.amount)
                            }}</span
                        >
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    order: Object,
});

const money = (v) =>
    Number(v ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
</script>
