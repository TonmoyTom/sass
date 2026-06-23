<template>
    <AdminLayout title="Commissions">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Commissions
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Review and approve seller commissions.
                    </p>
                </div>
                <button
                    type="button"
                    :disabled="processing"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    @click="approveDue"
                >
                    <svg
                        class="h-4 w-4"
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
                    Approve Due
                </button>
            </div>

            <!-- status -->
            <div
                v-if="$page.props.flash?.status"
                class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ $page.props.flash.status }}
            </div>
            <div
                v-if="$page.props.flash?.error"
                class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
            >
                {{ $page.props.flash.error }}
            </div>

            <!-- totals -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending Amount
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400"
                    >
                        ৳{{ money(totals.pending) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Approved Amount
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        ৳{{ money(totals.approved) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending Count
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ counts.pending }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Paid
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ counts.paid }}
                    </p>
                </div>
            </div>

            <!-- tabs -->
            <div
                class="mb-5 flex gap-2 border-b border-gray-200 dark:border-gray-800"
            >
                <Link
                    v-for="tab in tabs"
                    :key="tab.value"
                    :href="
                        tab.value
                            ? `/admin/commissions?status=${tab.value}`
                            : '/admin/commissions'
                    "
                    class="border-b-2 px-4 py-2.5 text-sm font-medium transition"
                    :class="
                        filters.status === tab.value ||
                        (!filters.status && !tab.value)
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                    "
                >
                    {{ tab.label }}
                    <span
                        v-if="tab.count !== null"
                        class="ml-1 text-xs text-gray-400"
                        >({{ tab.count }})</span
                    >
                </Link>
            </div>

            <!-- table -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <table class="w-full">
                    <thead>
                        <tr
                            class="border-b border-gray-200 text-left text-xs tracking-wide text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400"
                        >
                            <th class="px-5 py-3.5 font-medium">Seller</th>
                            <th class="px-5 py-3.5 font-medium">
                                Module / Tenant
                            </th>
                            <th class="px-5 py-3.5 font-medium">Commission</th>
                            <th class="px-5 py-3.5 font-medium">Status</th>
                            <th class="px-5 py-3.5 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="c in commissions.data"
                            :key="c.id"
                            class="border-b border-gray-100 last:border-0 dark:border-gray-800"
                        >
                            <td class="px-5 py-4">
                                <p
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ c.seller_name }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ c.seller_email }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ c.created_at }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <p
                                    class="text-sm text-gray-800 dark:text-white/90"
                                >
                                    {{ c.module_name }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ c.tenant_name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ c.tenant_email }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <p
                                    class="font-semibold text-gray-800 dark:text-white/90"
                                >
                                    ৳{{ money(c.amount) }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ c.rate }}% · {{ c.type }}
                                </p>
                                <p
                                    v-if="c.is_held"
                                    class="mt-0.5 text-xs text-amber-600 dark:text-amber-400"
                                >
                                    Hold until {{ c.hold_until }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="statusClass(c.status)"
                                >
                                    {{ c.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <template v-if="c.status === 'pending'">
                                        <button
                                            type="button"
                                            class="rounded-lg bg-green-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-600"
                                            @click="approve(c)"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                            @click="rejecting = c"
                                        >
                                            Reject
                                        </button>
                                    </template>
                                    <span v-else class="text-xs text-gray-400"
                                        >—</span
                                    >
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!commissions.data.length">
                            <td
                                colspan="5"
                                class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400"
                            >
                                No commissions found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- pagination -->
            <div
                v-if="commissions.links?.length > 3"
                class="mt-5 flex flex-wrap gap-1.5"
            >
                <component
                    :is="link.url ? 'a' : 'span'"
                    v-for="link in commissions.links"
                    :key="link.label"
                    :href="link.url"
                    class="rounded-lg px-3.5 py-2 text-sm"
                    :class="
                        link.active
                            ? 'bg-brand-500 text-white'
                            : link.url
                              ? 'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300'
                              : 'text-gray-300'
                    "
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- reject modal -->
        <div
            v-if="rejecting"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="rejecting = null"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900"
            >
                <h4
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Reject Commission
                </h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ rejecting.seller_name }} · ৳{{ money(rejecting.amount) }}
                </p>
                <textarea
                    v-model="rejectNote"
                    rows="3"
                    class="focus:border-brand-300 mt-4 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:text-white/90"
                    placeholder="Reason (optional)"
                />
                <div class="mt-5 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                        @click="rejecting = null"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600"
                        @click="performReject"
                    >
                        Reject
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router ,Link} from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    commissions: Object,
    filters: Object,
    counts: Object,
    totals: Object,
});

const processing = ref(false);
const rejecting = ref(null);
const rejectNote = ref('');

const money = (v) =>
    Number(v ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const tabs = computed(() => [
    { label: 'All', value: '', count: props.counts.all },
    { label: 'Pending', value: 'pending', count: props.counts.pending },
    { label: 'Approved', value: 'approved', count: props.counts.approved },
    { label: 'Paid', value: 'paid', count: props.counts.paid },
]);

const statusClass = (status) => {
    const map = {
        pending:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        approved:
            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        paid: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        cancelled:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};

const approve = (c) => {
    router.post(
        route('admin.commissions.approve', c.id),
        {},
        { preserveScroll: true },
    );
};

const performReject = () => {
    router.post(
        route('admin.commissions.reject', rejecting.value.id),
        { note: rejectNote.value },
        {
            preserveScroll: true,
            onFinish: () => {
                rejecting.value = null;
                rejectNote.value = '';
            },
        },
    );
};

const approveDue = () => {
    processing.value = true;
    router.post(
        route('admin.commissions.approve-due'),
        {},
        {
            preserveScroll: true,
            onFinish: () => (processing.value = false),
        },
    );
};
</script>
