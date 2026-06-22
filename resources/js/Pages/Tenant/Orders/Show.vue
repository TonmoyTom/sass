<template>
    <TenantLayout title="Order Details">
        <div class="space-y-6">
            <!-- ── Header ── -->
            <div
                class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <Link
                    :href="route('tenant.orders.index')"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div
                    class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                >
                    <Receipt class="h-5 w-5" />
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h3
                            class="text-xl font-bold tracking-tight text-gray-900 dark:text-white"
                        >
                            Order #{{ order.id }}
                        </h3>
                        <span
                            :class="statusPill(order.status)"
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-current" />
                            {{ order.status }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        {{ order.sold_at ?? '—' }}
                    </p>
                </div>
            </div>

            <!-- ── Module summary ── -->
            <section
                class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-14 w-14 shrink-0 items-center justify-center rounded-xl"
                        >
                            <LayoutGrid class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                {{ order.module_name ?? '—' }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span v-if="order.tier_name"
                                    >{{ order.tier_name }} plan</span
                                >
                                <span v-if="order.tier_name && order.sale_type">
                                    ·
                                </span>
                                <span class="capitalize">{{
                                    order.sale_type
                                }}</span>
                            </p>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        ৳{{ money(order.amount) }}
                    </p>
                </div>
            </section>

            <!-- ── Details ── -->
            <section
                class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <h5
                    class="mb-4 flex items-center gap-2 font-semibold text-gray-900 dark:text-white"
                >
                    <span
                        class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-7 w-7 items-center justify-center rounded-lg"
                    >
                        <Info class="h-4 w-4" />
                    </span>
                    Order details
                </h5>
                <dl
                    class="divide-y divide-gray-100 text-sm dark:divide-gray-800"
                >
                    <div
                        class="flex items-center justify-between py-3 first:pt-0"
                    >
                        <dt class="text-gray-500 dark:text-gray-400">
                            Order ID
                        </dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            #{{ order.id }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500 dark:text-gray-400">Module</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ order.module_name ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500 dark:text-gray-400">Plan</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ order.tier_name ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500 dark:text-gray-400">Type</dt>
                        <dd
                            class="font-medium text-gray-900 capitalize dark:text-white"
                        >
                            {{ order.sale_type ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                        <dd>
                            <span
                                :class="statusPill(order.status)"
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-current"
                                />
                                {{ order.status }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500 dark:text-gray-400">Date</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ order.sold_at ?? '—' }}
                        </dd>
                    </div>
                    <div
                        class="flex items-center justify-between py-3 last:pb-0"
                    >
                        <dt class="font-semibold text-gray-900 dark:text-white">
                            Amount paid
                        </dt>
                        <dd
                            class="text-lg font-bold text-gray-900 dark:text-white"
                        >
                            ৳{{ money(order.amount) }}
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- ── Actions ── -->
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <Link
                    :href="route('tenant.orders.index')"
                    class="rounded-xl border border-gray-200 px-5 py-2.5 text-center text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Back to orders
                </Link>
                <Link
                    :href="route('tenant.orders.invoice', order.id)"
                    class="bg-brand-500 hover:bg-brand-600 flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold text-white"
                >
                    <FileText class="h-4 w-4" /> View invoice
                </Link>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    FileText,
    Info,
    LayoutGrid,
    Receipt,
} from 'lucide-vue-next';

defineProps({
    order: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const statusPill = (s) =>
    ({
        completed:
            'text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400',
        pending:
            'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400',
        failed: 'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400',
        refunded:
            'text-gray-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400',
    })[String(s).toLowerCase()] ??
    'text-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-gray-400';
</script>
