<template>
    <AdminLayout title="Module Requests">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div
                class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Module Requests
                </h3>

                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between lg:justify-end"
                >
                    <!-- status filter -->
                    <div
                        class="flex flex-wrap gap-1 rounded-lg bg-gray-100 p-1 dark:bg-white/[0.03]"
                    >
                        <Link
                            v-for="f in statusFilters"
                            :key="f.value"
                            :href="
                                route(
                                    'admin.module-requests.index',
                                    f.value ? { status: f.value } : {},
                                )
                            "
                            class="rounded-md px-3 py-1.5 text-sm font-medium whitespace-nowrap"
                            :class="
                                (filters.status ?? '') === f.value
                                    ? 'bg-white text-brand-600 shadow-sm dark:bg-white/10 dark:text-brand-400'
                                    : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'
                            "
                        >
                            {{ f.label }}
                        </Link>
                    </div>

                    <Link
                        :href="route('admin.module-requests.create')"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                    >
                        <span class="text-base leading-none">+</span>
                        New Request
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-200 text-xs text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Seller</th>
                            <th class="px-4 py-3 font-medium">Module</th>
                            <th class="px-4 py-3 font-medium">Commission</th>
                            <th class="px-4 py-3 font-medium">Note</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Reviewed</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr
                            v-for="req in requests.data"
                            :key="req.id"
                            class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        >
                            <td class="px-4 py-3">
                                <div
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ req.seller_name }}
                                </div>
                                <div
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ req.seller_email }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ req.module_name }}
                                </div>
                                <span
                                    class="font-mono text-xs text-gray-500 dark:text-gray-400"
                                    >{{ req.module_alias }}</span
                                >
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ req.commission }}%
                            </td>
                            <td
                                class="max-w-[200px] px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                <span class="line-clamp-2 text-xs">{{
                                    req.note || '—'
                                }}</span>
                                <span
                                    v-if="req.admin_note"
                                    class="mt-1 block text-xs text-red-500"
                                    >Admin: {{ req.admin_note }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="statusClass(req.status)"
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                >
                                    {{ req.status }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400"
                            >
                                <template v-if="req.reviewed_at">
                                    {{ req.reviewed_by }}<br />{{
                                        req.reviewed_at
                                    }}
                                </template>
                                <span v-else>—</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'admin.module-requests.show',
                                                req.id,
                                            )
                                        "
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                                    >
                                        View
                                    </Link>
                                    <select
                                        :value="req.status"
                                        @change="changeStatus(req, $event.target.value)"
                                        class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                        :class="statusSelectClass(req.status)"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!requests.data.length">
                            <td
                                colspan="7"
                                class="px-4 py-10 text-center text-gray-500 dark:text-gray-400"
                            >
                                No requests found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="requests.links.length > 3"
                class="mt-5 flex flex-wrap gap-1"
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

        <!-- Reject modal -->
        <div
            v-if="rejecting"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="rejecting = null"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900"
            >
                <h4
                    class="mb-2 text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Reject Request
                </h4>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ rejecting.seller_name }} — {{ rejecting.module_name }}
                </p>
                <textarea
                    v-model="rejectForm.admin_note"
                    rows="3"
                    placeholder="Reject korar karon (optional)"
                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                ></textarea>
                <div class="mt-4 flex justify-end gap-3">
                    <button
                        @click="rejecting = null"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmReject"
                        :disabled="rejectForm.processing"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
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
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    requests: Object,
    filters: Object,
});

const statusFilters = [
    { value: '', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
];

const statusClass = (status) =>
    ({
        pending:
            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        approved:
            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        rejected:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[status] ?? 'bg-gray-100 text-gray-700';

const statusSelectClass = (status) =>
    ({
        pending:
            'border-yellow-300 text-yellow-700 dark:border-yellow-800 dark:text-yellow-400',
        approved:
            'border-green-300 text-green-700 dark:border-green-800 dark:text-green-400',
        rejected:
            'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
    })[status] ?? '';

const changeStatus = (req, newStatus) => {
    if (newStatus === req.status) return;

    if (newStatus === 'rejected') {
        openReject(req);
        return;
    }

    router.patch(
        route('admin.module-requests.update-status', req.id),
        { status: newStatus },
        { preserveScroll: true },
    );
};

// reject modal
const rejecting = ref(null);
const rejectForm = useForm({ admin_note: '' });

const openReject = (req) => {
    rejecting.value = req;
    rejectForm.admin_note = '';
};

const confirmReject = () => {
    rejectForm.post(route('admin.module-requests.reject', rejecting.value.id), {
        preserveScroll: true,
        onSuccess: () => (rejecting.value = null),
    });
};
</script>
