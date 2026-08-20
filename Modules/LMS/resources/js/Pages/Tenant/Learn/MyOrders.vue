<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { Link } from '@inertiajs/vue3';
import { FileText, Receipt } from 'lucide-vue-next';

defineProps({
    orders: Array,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const statusPill = (status) =>
    ({
        completed: 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
        pending: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        failed: 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400',
        refunded: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
    })[status] ?? 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400';
</script>

<template>
    <WorkspaceLayout title="My Orders">
        <div class="mx-auto">
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">My Orders</h1>
                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                    Every course you've purchased or enrolled in, with invoices.
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div v-if="!orders.length" class="p-10 text-center">
                    <Receipt class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        No orders yet — enroll in a course to see it here.
                    </p>
                </div>

                <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="flex flex-wrap items-center justify-between gap-4 p-4"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <img
                                v-if="order.course_thumbnail"
                                :src="order.course_thumbnail"
                                :alt="order.course_title"
                                class="h-12 w-16 shrink-0 rounded-lg object-cover"
                            />
                            <div
                                v-else
                                class="flex h-12 w-16 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800"
                            >
                                <FileText class="h-4 w-4 text-gray-300 dark:text-gray-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ order.course_title }}
                                </p>
                                <p class="font-mono text-xs text-gray-400">{{ order.invoice_number }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ order.amount > 0 ? `৳${money(order.amount)}` : 'Free' }}
                                </p>
                                <p class="text-xs text-gray-400">{{ order.purchased_at }}</p>
                            </div>

                            <span
                                class="rounded-full px-2.5 py-1 text-[11px] font-semibold capitalize"
                                :class="statusPill(order.status)"
                            >
                                {{ order.status }}
                            </span>

                            <Link
                                :href="learnRoutes.orderInvoice(order.id)"
                                class="text-brand-500 hover:text-brand-600 flex items-center gap-1 text-xs font-semibold whitespace-nowrap"
                            >
                                <FileText class="h-3.5 w-3.5" />
                                Invoice
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>