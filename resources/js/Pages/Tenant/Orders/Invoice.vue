<template>
    <TenantLayout title="Invoice">
        <div class="space-y-6">
            <!-- ── Toolbar ── -->
            <div
                class="flex items-center justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 print:hidden"
            >
                <Link
                    :href="route('tenant.orders.show', order.id)"
                    class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                    <ArrowLeft class="h-4 w-4" /> Back to order
                </Link>

                <div class="flex items-center gap-2">
                    <button
                        @click="print"
                        class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <Download class="h-4 w-4" /> Download
                    </button>
                    <button
                        @click="print"
                        class="bg-brand-500 hover:bg-brand-600 flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white"
                    >
                        <Printer class="h-4 w-4" /> Print
                    </button>
                </div>
            </div>

            <!-- ── Invoice card ── -->
            <section
                class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 print:border-0 print:shadow-none"
            >
                <!-- header -->
                <div
                    class="flex items-start justify-between border-b border-gray-100 p-6 dark:border-gray-800"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-9 w-9 items-center justify-center rounded-lg"
                            >
                                <FileText class="h-5 w-5" />
                            </span>
                            <h1
                                class="text-lg font-bold text-gray-900 dark:text-white"
                            >
                                Invoice
                            </h1>
                        </div>
                        <p
                            class="mt-2 font-mono text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ order.invoice_no }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p
                            class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                        >
                            Status
                        </p>
                        <span
                            :class="statusPill(order.status)"
                            class="mt-1 inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium capitalize"
                        >
                            <component
                                :is="isPaid ? CheckCircle2 : Clock"
                                class="h-3.5 w-3.5"
                            />
                            {{ order.status }}
                        </span>
                    </div>
                </div>

                <!-- billed to / details -->
                <div
                    class="grid grid-cols-2 gap-6 border-b border-gray-100 p-6 dark:border-gray-800"
                >
                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                        >
                            Billed to
                        </p>
                        <p
                            class="mt-1.5 font-semibold text-gray-900 dark:text-white"
                        >
                            {{ order.tenant_name }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ order.tenant_email }}
                        </p>
                        <p
                            v-if="
                                order.tenant_address?.city ||
                                order.tenant_address?.country
                            "
                            class="text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{
                                [
                                    order.tenant_address?.city,
                                    order.tenant_address?.country,
                                ]
                                    .filter(Boolean)
                                    .join(', ')
                            }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="mb-3">
                            <p
                                class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                            >
                                Issued
                            </p>
                            <p
                                class="mt-1 font-medium text-gray-900 dark:text-white"
                            >
                                {{ order.sold_at ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                            >
                                Type
                            </p>
                            <p
                                class="mt-1 font-medium text-gray-900 capitalize dark:text-white"
                            >
                                {{ order.sale_type ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- line item -->
                <div class="p-6">
                    <div
                        class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800"
                    >
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b border-gray-100 bg-gray-50 text-left text-xs tracking-wide text-gray-400 uppercase dark:border-gray-800 dark:bg-gray-800 dark:text-gray-500"
                                >
                                    <th class="px-4 py-3 font-medium">
                                        Description
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Amount
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-4 py-4">
                                        <p
                                            class="font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ order.module_name ?? '—' }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-400 dark:text-gray-500"
                                        >
                                            {{ order.tier_name }} plan
                                        </p>
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-medium text-gray-900 dark:text-white"
                                    >
                                        ৳{{ money(order.amount) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- total -->
                    <div class="mt-6 flex justify-end">
                        <div class="w-full max-w-xs space-y-2">
                            <div
                                class="flex justify-between text-sm text-gray-500 dark:text-gray-400"
                            >
                                <span>Subtotal</span
                                ><span>৳{{ money(order.amount) }}</span>
                            </div>
                            <div
                                class="flex justify-between border-t border-gray-100 pt-3 text-base font-bold text-gray-900 dark:border-gray-800 dark:text-white"
                            >
                                <span>Total</span
                                ><span>৳{{ money(order.amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- footer -->
                <div
                    class="border-t border-gray-100 bg-gray-50/60 px-6 py-4 text-center text-xs text-gray-400 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-500 print:bg-white"
                >
                    Thank you for your business · AllSphere
                </div>
            </section>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Clock,
    FileText,
    Printer,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    order: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const statusPill = (s) =>
    ({
        completed: 'text-green-600 bg-green-50',
        paid: 'text-green-600 bg-green-50',
        pending: 'text-amber-600 bg-amber-50',
        failed: 'text-red-600 bg-red-50',
        refunded: 'text-gray-500 bg-gray-100',
    })[String(s).toLowerCase()] ?? 'text-gray-600 bg-gray-100';

const isPaid = computed(() =>
    ['completed', 'paid'].includes(String(props.order.status).toLowerCase()),
);

const print = () => window.print();
</script>

<style>
@media print {
    @page {
        margin: 16mm;
    }
    body {
        background: #fff !important;
    }
}
</style>
