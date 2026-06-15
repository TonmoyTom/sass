<template>
    <AdminLayout title="Request Details">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.module-requests.index')"
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
                        Module Request
                    </h3>
                    <span
                        :class="statusClass(request.status)"
                        class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                    >
                        {{ request.status }}
                    </span>
                </div>

                <div
                    v-if="request.status === 'pending'"
                    class="flex items-center gap-2"
                >
                    <button
                        @click="approve"
                        class="rounded-lg border border-green-300 px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50 dark:border-green-800"
                    >
                        Approve
                    </button>
                    <button
                        @click="openReject"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                    >
                        Reject
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- Seller -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Seller
                    </h5>
                    <dl class="space-y-3">
                        <Row label="Name" :value="request.seller.name" />
                        <Row label="Email" :value="request.seller.email" />
                        <Row label="Phone" :value="request.seller.phone" />
                        <Row
                            label="Referral Code"
                            :value="request.seller.referral_code"
                            mono
                        />
                    </dl>
                </section>

                <!-- Module -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Module
                    </h5>
                    <dl class="space-y-3">
                        <Row label="Name" :value="request.module.name" />
                        <Row label="Alias" :value="request.module.alias" mono />
                        <Row
                            label="Category"
                            :value="request.module.category"
                        />
                        <Row
                            label="Commission"
                            :value="request.module.commission_rate + '%'"
                        />
                    </dl>
                </section>

                <!-- Request info -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Request Info
                    </h5>
                    <dl class="space-y-3">
                        <Row label="Submitted" :value="request.created_at" />
                        <div v-if="request.note">
                            <dt
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Seller Note
                            </dt>
                            <dd
                                class="mt-1 text-sm text-gray-800 dark:text-white/90"
                            >
                                {{ request.note }}
                            </dd>
                        </div>
                        <Row
                            v-if="request.reviewed_at"
                            label="Reviewed By"
                            :value="request.reviewed_by"
                        />
                        <Row
                            v-if="request.reviewed_at"
                            label="Reviewed At"
                            :value="request.reviewed_at"
                        />
                        <div v-if="request.admin_note">
                            <dt
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Admin Note
                            </dt>
                            <dd
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ request.admin_note }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>

        <!-- Reject modal -->
        <div
            v-if="rejecting"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="rejecting = false"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900"
            >
                <h4
                    class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Reject Request
                </h4>
                <textarea
                    v-model="rejectForm.admin_note"
                    rows="3"
                    placeholder="Reject korar karon (optional)"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                ></textarea>
                <div class="mt-4 flex justify-end gap-3">
                    <button
                        @click="rejecting = false"
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
import { h, ref } from 'vue';

const props = defineProps({
    request: Object,
});

const Row = (p) =>
    h('div', { class: 'flex justify-between gap-4' }, [
        h('dt', { class: 'text-sm text-gray-500 dark:text-gray-400' }, p.label),
        h(
            'dd',
            {
                class: [
                    'text-sm font-medium text-gray-800 dark:text-white/90 text-right',
                    p.mono ? 'font-mono text-xs' : '',
                ],
            },
            p.value || '—',
        ),
    ]);

const statusClass = (status) =>
    ({
        pending:
            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        approved:
            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        rejected:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[status] ?? 'bg-gray-100 text-gray-700';

const approve = () => {
    router.post(
        route('admin.module-requests.approve', props.request.id),
        {},
        { preserveScroll: true },
    );
};

const rejecting = ref(false);
const rejectForm = useForm({ admin_note: '' });

const openReject = () => {
    rejecting.value = true;
    rejectForm.admin_note = '';
};

const confirmReject = () => {
    rejectForm.post(route('admin.module-requests.reject', props.request.id), {
        preserveScroll: true,
        onSuccess: () => (rejecting.value = false),
    });
};
</script>
