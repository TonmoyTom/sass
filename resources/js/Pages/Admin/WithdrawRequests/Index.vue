<template>
    <AdminLayout title="Withdraw Requests">
        <div class="mx-auto">
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Withdraw Requests
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Review and process seller withdrawal requests.
                </p>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending Requests
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400"
                    >
                        {{ stats.pending_count }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending Amount
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        ৳{{ money(stats.pending_amount) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Paid Out
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        ৳{{ money(stats.approved_amount) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Refunded
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400"
                    >
                        ৳{{ money(stats.refunded_amount) }}
                    </p>
                </div>
            </div>

            <!-- Filter -->
            <div class="mb-4 flex gap-2">
                <button
                    v-for="s in ['all', 'pending', 'approved', 'rejected']"
                    :key="s"
                    @click="filterStatus(s)"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium capitalize transition"
                    :class="
                        currentStatus === s
                            ? 'bg-brand-500 text-white'
                            : 'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03]'
                    "
                >
                    {{ s }}
                </button>
            </div>

            <!-- List -->
            <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    v-if="requests.data.length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <div
                        v-for="w in requests.data"
                        :key="w.id"
                        class="flex cursor-pointer items-center justify-between p-4 transition hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        @click="
                            router.visit(route('admin.withdraw.show', w.id))
                        "
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                                :class="{
                                    'bg-yellow-100 dark:bg-yellow-900/30':
                                        w.status === 'pending',
                                    'bg-green-100 dark:bg-green-900/30':
                                        w.status === 'approved',
                                    'bg-red-100 dark:bg-red-900/30':
                                        w.status === 'rejected',
                                }"
                            >
                                <span class="text-sm">
                                    {{
                                        w.status === 'pending'
                                            ? '⏳'
                                            : w.status === 'approved'
                                              ? '✓'
                                              : '✕'
                                    }}
                                </span>
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ w.seller_name }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ w.seller_email }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ w.created_at }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <p class="text-xs text-gray-400">Method</p>
                                <p
                                    class="text-sm font-medium text-gray-700 capitalize dark:text-gray-300"
                                >
                                    {{ w.method }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-base font-bold text-gray-800 dark:text-white/90"
                                >
                                    ৳{{ money(w.amount) }}
                                </p>

                                <template v-if="w.status === 'approved'">
                                    <p
                                        class="text-xs text-green-600 dark:text-green-400"
                                    >
                                        Paid ৳{{ money(w.paid_amount) }}
                                    </p>
                                    <p
                                        v-if="w.amount - w.paid_amount > 0"
                                        class="text-xs text-yellow-600 dark:text-yellow-400"
                                    >
                                        Refunded ৳{{
                                            money(w.amount - w.paid_amount)
                                        }}
                                    </p>
                                </template>

                                <span
                                    class="text-xs font-semibold capitalize"
                                    :class="{
                                        'text-yellow-600 dark:text-yellow-400':
                                            w.status === 'pending',
                                        'text-green-600 dark:text-green-400':
                                            w.status === 'approved',
                                        'text-red-600 dark:text-red-400':
                                            w.status === 'rejected',
                                    }"
                                    >{{ w.status }}</span
                                >
                            </div>
                            <svg
                                class="h-4 w-4 text-gray-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M9 18l6-6-6-6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div v-else class="py-16 text-center">
                    <p class="text-sm text-gray-400">
                        No withdraw requests found.
                    </p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="requests.links.length > 3"
                    class="flex flex-wrap gap-1 border-t border-gray-100 p-4 dark:border-gray-800"
                >
                    <template v-for="(link, i) in requests.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-sm"
                            :class="
                                link.active
                                    ? 'bg-brand-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'
                            "
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="cursor-default rounded-lg px-3 py-1.5 text-sm text-gray-400 opacity-50"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    requests: { type: Object, default: () => ({ data: [], links: [] }) },
    filters: { type: Object, default: () => ({}) },
    stats: {
        type: Object,
        default: () => ({
            pending_count: 0,
            pending_amount: 0,
            approved_amount: 0,
        }),
    },
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const currentStatus = computed(() => props.filters.status ?? 'all');

const filterStatus = (status) => {
    router.get(
        route('admin.withdraw.index'),
        { status: status === 'all' ? undefined : status },
        { preserveScroll: true, replace: true },
    );
};
</script>
