<template>
    <div
        class="min-h-screen bg-gray-100 px-4 py-10 dark:bg-gray-950 print:bg-white print:p-0"
    >
        <div class="mx-auto max-w-2xl">
            <!-- toolbar -->
            <div class="mb-6 flex items-center justify-between print:hidden">
                <a
                    href="/admin/orders"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-300"
                >
                    <svg
                        class="h-4 w-4"
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
                    Back to Orders
                </a>
                <button
                    type="button"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                    @click="print"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"
                        />
                    </svg>
                    Print / Save PDF
                </button>
            </div>

            <!-- invoice -->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm print:rounded-none print:shadow-none"
            >
                <!-- body -->
                <div style="padding: 3rem" class="print:!p-12">
                    <!-- top -->
                    <div class="flex items-start justify-between">
                        <div>
                            <h1
                                class="text-2xl font-bold tracking-tight text-gray-900"
                            >
                                Invoice
                            </h1>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ order.invoice_no }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-brand-600 text-lg font-bold">
                                EduSaaS
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                platform.com
                            </p>
                        </div>
                    </div>

                    <!-- meta -->
                    <div class="mt-10 grid grid-cols-2 gap-8">
                        <div>
                            <p
                                class="text-xs font-medium tracking-wider text-gray-400 uppercase"
                            >
                                Billed To
                            </p>
                            <p class="mt-2 font-semibold text-gray-900">
                                {{ order.tenant_name }}
                            </p>
                            <p class="mt-0.5 text-sm text-gray-600">
                                {{ order.tenant_email ?? '' }}
                            </p>
                            <p
                                v-if="order.tenant_address?.city"
                                class="text-sm text-gray-600"
                            >
                                {{ order.tenant_address.city
                                }}<span v-if="order.tenant_address.country"
                                    >, {{ order.tenant_address.country }}</span
                                >
                            </p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-xs font-medium tracking-wider text-gray-400 uppercase"
                            >
                                Invoice Date
                            </p>
                            <p class="mt-2 font-medium text-gray-900">
                                {{ order.sold_at }}
                            </p>
                            <p
                                class="mt-4 text-xs font-medium tracking-wider text-gray-400 uppercase"
                            >
                                Status
                            </p>
                            <span
                                class="mt-1.5 inline-block rounded-full px-3 py-1 text-xs font-medium capitalize"
                                :class="
                                    order.status === 'completed'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-amber-100 text-amber-700'
                                "
                            >
                                {{ order.status }}
                            </span>
                        </div>
                    </div>

                    <!-- items -->
                    <div class="mt-12">
                        <table class="w-full">
                            <thead>
                                <tr
                                    class="border-b border-gray-200 text-left text-xs tracking-wider text-gray-400 uppercase"
                                >
                                    <th class="pb-3 font-medium">
                                        Description
                                    </th>
                                    <th class="pb-3 text-center font-medium">
                                        Type
                                    </th>
                                    <th class="pb-3 text-right font-medium">
                                        Amount
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100">
                                    <td class="py-5">
                                        <p class="font-medium text-gray-900">
                                            {{ order.module_name }}
                                        </p>
                                        <p
                                            v-if="order.tier_name"
                                            class="mt-0.5 text-sm text-gray-500"
                                        >
                                            {{ order.tier_name }} plan
                                        </p>
                                    </td>
                                    <td
                                        class="py-5 text-center text-sm text-gray-600 capitalize"
                                    >
                                        {{ order.sale_type }}
                                    </td>
                                    <td
                                        class="py-5 text-right font-medium text-gray-900"
                                    >
                                        ৳{{ money(order.amount) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- total -->
                    <div class="mt-8 flex justify-end">
                        <div class="w-full max-w-[240px] space-y-2.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="text-gray-900"
                                    >৳{{ money(order.amount) }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-t border-gray-200 pt-2.5 text-base font-bold"
                            >
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900"
                                    >৳{{ money(order.amount) }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- footer -->
                <div
                    style="padding: 1.5rem 3rem"
                    class="border-t border-gray-200 text-center"
                >
                    <p class="text-sm text-gray-500">
                        Thank you for your purchase.
                    </p>
                    <p class="mt-0.5 text-xs text-gray-400">
                        This is a computer-generated invoice.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    order: Object,
});

const money = (v) =>
    Number(v ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const print = () => {
    window.print();
};
</script>
