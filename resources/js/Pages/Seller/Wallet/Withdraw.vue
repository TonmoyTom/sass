<template>
    <SellerLayout title="Withdraw Funds">
        <div class="mx-auto">
            <div class="mb-6">
                <Link
                    :href="route('seller.wallet.index')"
                    class="mb-3 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ← Back to Wallet
                </Link>
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Withdraw Funds
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Available:
                    <span class="font-semibold text-gray-800 dark:text-white/90"
                        >৳{{ money(wallet.available_balance) }}</span
                    >
                </p>
            </div>

            <!-- Pending request notice -->
            <div
                v-if="pending_request"
                class="mb-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800/40 dark:bg-yellow-900/20"
            >
                <p
                    class="text-sm font-semibold text-yellow-700 dark:text-yellow-400"
                >
                    Pending Withdraw Request
                </p>
                <p class="mt-1 text-sm text-yellow-600 dark:text-yellow-500">
                    ৳{{ money(pending_request.amount) }} via
                    {{ pending_request.method }} — submitted
                    {{ pending_request.created_at }}
                </p>
                <p class="mt-1 text-xs text-yellow-500">
                    You cannot submit another request until this is processed.
                </p>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">
                    Request Withdrawal
                </h5>

                <!-- Method select -->
                <div class="mb-4">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >Payment Method</label
                    >
                    <div class="grid grid-cols-3 gap-3">
                        <button
                            v-for="m in methods"
                            :key="m.key"
                            type="button"
                            @click="withdrawForm.method = m.key"
                            :class="[
                                'flex flex-col items-center gap-2 rounded-xl border-2 p-4 transition',
                                withdrawForm.method === m.key
                                    ? `${m.activeBorder} ${m.activeBg}`
                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700',
                            ]"
                        >
                            <div
                                :class="[
                                    'flex h-10 w-10 items-center justify-center rounded-full',
                                    m.iconBg,
                                ]"
                            >
                                <span :class="['text-sm font-bold', m.iconText]"
                                    >৳</span
                                >
                            </div>
                            <p
                                class="text-sm font-semibold text-gray-800 dark:text-white/90"
                            >
                                {{ m.label }}
                            </p>
                        </button>
                    </div>
                    <p
                        v-if="withdrawForm.errors.method"
                        class="mt-1.5 text-sm text-red-500"
                    >
                        {{ withdrawForm.errors.method }}
                    </p>
                </div>

                <!-- Account holder name -->
                <div class="mb-4">
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >Account Holder Name</label
                    >
                    <input
                        v-model="withdrawForm.account_name"
                        type="text"
                        placeholder="Full name"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />
                    <p
                        v-if="withdrawForm.errors.account_name"
                        class="mt-1.5 text-sm text-red-500"
                    >
                        {{ withdrawForm.errors.account_name }}
                    </p>
                </div>

                <!-- Mobile number -->
                <div class="mb-4">
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >
                        {{ selectedMethodLabel }} Number
                    </label>
                    <input
                        v-model="withdrawForm.account_number"
                        type="text"
                        placeholder="01XXXXXXXXX"
                        maxlength="11"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />
                    <p
                        v-if="withdrawForm.errors.account_number"
                        class="mt-1.5 text-sm text-red-500"
                    >
                        {{ withdrawForm.errors.account_number }}
                    </p>
                </div>

                <!-- Amount -->
                <div class="mb-4">
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >Amount (min ৳20)</label
                    >
                    <div class="relative">
                        <span
                            class="absolute top-1/2 left-4 -translate-y-1/2 text-sm text-gray-400"
                            >৳</span
                        >
                        <input
                            v-model="withdrawForm.amount"
                            type="number"
                            :max="wallet.available_balance"
                            placeholder="0"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pr-4 pl-8 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                    </div>
                    <p
                        v-if="withdrawForm.errors.amount"
                        class="mt-1.5 text-sm text-red-500"
                    >
                        {{ withdrawForm.errors.amount }}
                    </p>
                </div>

                <!-- Note -->
                <div class="mb-5">
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >Note (optional)</label
                    >
                    <input
                        v-model="withdrawForm.note"
                        type="text"
                        placeholder="Any note for admin..."
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />
                </div>

                <button
                    @click="submitWithdraw"
                    :disabled="
                        withdrawForm.processing ||
                        !!pending_request ||
                        wallet.available_balance <= 0
                    "
                    class="bg-brand-500 hover:bg-brand-600 w-full rounded-xl py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{
                        withdrawForm.processing
                            ? 'Submitting...'
                            : 'Request Withdrawal'
                    }}
                </button>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    wallet: Object,
    pending_request: Object,
    last_request: Object,
});

const methods = [
    {
        key: 'bkash',
        label: 'bKash',
        activeBorder: 'border-pink-500',
        activeBg: 'bg-pink-50 dark:bg-pink-900/20',
        iconBg: 'bg-pink-100 dark:bg-pink-900/30',
        iconText: 'text-pink-600',
    },
    {
        key: 'nagad',
        label: 'Nagad',
        activeBorder: 'border-orange-500',
        activeBg: 'bg-orange-50 dark:bg-orange-900/20',
        iconBg: 'bg-orange-100 dark:bg-orange-900/30',
        iconText: 'text-orange-600',
    },
    {
        key: 'rocket',
        label: 'Rocket',
        activeBorder: 'border-purple-500',
        activeBg: 'bg-purple-50 dark:bg-purple-900/20',
        iconBg: 'bg-purple-100 dark:bg-purple-900/30',
        iconText: 'text-purple-600',
    },
];

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const withdrawForm = useForm({
    method: props.last_request?.method ?? 'bkash',
    account_name: props.last_request?.account_name ?? '',
    account_number: props.last_request?.account_number ?? '',
    amount: '',
    note: '',
});

const selectedMethodLabel = computed(
    () => methods.find((m) => m.key === withdrawForm.method)?.label ?? '',
);

const submitWithdraw = () => {
    withdrawForm.post(route('seller.wallet.withdraw'), {
        preserveScroll: true,
        onSuccess: () => withdrawForm.reset('amount', 'note'),
    });
};
</script>
