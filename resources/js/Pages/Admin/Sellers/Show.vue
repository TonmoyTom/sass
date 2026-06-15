<template>
    <AdminLayout title="Seller Details">
        <div class="mx-auto">
            <!-- Header -->
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.sellers.index')"
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
                    <div>
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ seller.name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ seller.email }}
                        </p>
                    </div>
                    <span
                        :class="statusClass(seller.status)"
                        class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                    >
                        {{ seller.status }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <!-- pending: Approve + Reject -->
                    <button
                        v-if="seller.status === 'pending'"
                        @click="approve"
                        class="rounded-lg border border-green-300 px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50 dark:border-green-800"
                    >
                        Approve
                    </button>
                    <button
                        v-if="seller.status === 'pending'"
                        @click="reject"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                    >
                        Reject
                    </button>

                    <!-- active: Suspend -->
                    <button
                        v-if="seller.status === 'active'"
                        @click="suspend"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                    >
                        Suspend
                    </button>

                    <!-- suspended: Reactivate -->
                    <button
                        v-if="seller.status === 'suspended'"
                        @click="approve"
                        class="rounded-lg border border-green-300 px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50 dark:border-green-800"
                    >
                        Reactivate
                    </button>

                    <Link
                        :href="route('admin.sellers.edit', seller.id)"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Edit
                    </Link>
                    <button
                        @click="destroy"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <!-- Wallet stat cards -->
            <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Available
                    </p>
                    <p
                        class="mt-1 text-xl font-bold text-green-600 dark:text-green-400"
                    >
                        ৳{{ money(seller.wallet.available_balance) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pending
                    </p>
                    <p
                        class="mt-1 text-xl font-bold text-yellow-600 dark:text-yellow-400"
                    >
                        ৳{{ money(seller.wallet.pending_balance) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Earned
                    </p>
                    <p
                        class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90"
                    >
                        ৳{{ money(seller.wallet.total_earned) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Withdrawn
                    </p>
                    <p
                        class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90"
                    >
                        ৳{{ money(seller.wallet.total_withdrawn) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- Seller info -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Seller Details
                    </h5>
                    <dl class="space-y-3">
                        <Row
                            label="Referral Code"
                            :value="seller.referral_code"
                            mono
                        />
                        <Row
                            label="Commission Rate"
                            :value="seller.commission_rate + '%'"
                        />
                        <Row
                            label="Total Sales"
                            :value="String(seller.total_sales)"
                        />
                        <Row label="Phone" :value="seller.phone" />
                        <Row label="Joined" :value="seller.joined_at" />
                    </dl>
                </section>
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Address
                    </h5>
                    <dl class="space-y-3">
                        <Row label="Country" :value="seller.country" />
                        <Row label="City/State" :value="seller.city" />
                        <Row label="Postal Code" :value="seller.postal_code" />
                    </dl>
                </section>

                <!-- Payout info -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Payout Details
                    </h5>
                    <dl class="space-y-3">
                        <Row label="bKash" :value="seller.bkash_number" />
                        <Row label="Bank Name" :value="seller.bank_name" />
                        <Row
                            label="Bank Account"
                            :value="seller.bank_account"
                        />
                        <div class="flex justify-between gap-4">
                            <dt
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                NID
                            </dt>
                            <dd
                                class="flex items-center gap-2 text-right text-sm font-medium text-gray-800 dark:text-white/90"
                            >
                                {{ seller.nid_number || '—' }}
                                <span
                                    v-if="seller.nid_number"
                                    :class="
                                        seller.nid_verified
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                                    "
                                    class="rounded-full px-2 py-0.5 text-xs"
                                >
                                    {{
                                        seller.nid_verified
                                            ? 'Verified'
                                            : 'Unverified'
                                    }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { h } from 'vue';

const props = defineProps({
    seller: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

// inline Row
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
        active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        pending:
            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        suspended:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[status] ?? 'bg-gray-100 text-gray-700';

const destroy = () => {
    if (
        !confirm(`Delete seller "${props.seller.name}"? Eta undo kora jabe na.`)
    )
        return;
    router.delete(route('admin.sellers.destroy', props.seller.id));
};

const approve = () => {
    router.post(
        route('admin.sellers.approve', props.seller.id),
        {},
        { preserveScroll: true },
    );
};

const suspend = () => {
    if (!confirm(`Suspend "${props.seller.name}"?`)) return;
    router.post(
        route('admin.sellers.suspend', props.seller.id),
        {},
        { preserveScroll: true },
    );
};

const reject = () => {
    if (!confirm(`Reject "${props.seller.name}"?`)) return;
    router.post(
        route('admin.sellers.reject', props.seller.id),
        {},
        { preserveScroll: true },
    );
};
</script>
