<template>
    <SellerLayout title="Wallet">
        <div class="mx-auto">
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Wallet
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Your earnings, available balance, and transaction history.
                </p>
            </div>

            <!-- balance hero -->
            <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
                <!-- available (big) -->
                <div
                    class="from-brand-500 to-brand-600 relative overflow-hidden rounded-2xl bg-gradient-to-br p-6 text-white lg:col-span-2"
                >
                    <div
                        class="pointer-events-none absolute -top-8 -right-8 h-40 w-40 rounded-full bg-white/10"
                    ></div>
                    <div class="relative">
                        <p class="text-sm font-medium text-white/70">
                            Available Balance
                        </p>
                        <p class="mt-1 text-4xl font-bold">
                            ৳{{ money(wallet.available_balance) }}
                        </p>
                        <button
                            @click="openWithdraw"
                            :disabled="wallet.available_balance <= 0"
                            class="text-brand-600 mt-5 rounded-xl bg-white px-6 py-2.5 text-sm font-semibold transition hover:bg-white/90 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Withdraw
                        </button>
                    </div>
                </div>

                <!-- pending + stats -->
                <div class="flex flex-col gap-4">
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                    >
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Pending (on hold)
                        </p>
                        <p
                            class="mt-1 text-xl font-bold text-yellow-600 dark:text-yellow-400"
                        >
                            ৳{{ money(wallet.pending_balance) }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                        >
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Total Earned
                            </p>
                            <p
                                class="mt-1 text-lg font-bold text-gray-800 dark:text-white/90"
                            >
                                ৳{{ money(wallet.total_earned) }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                        >
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Withdrawn
                            </p>
                            <p
                                class="mt-1 text-lg font-bold text-gray-800 dark:text-white/90"
                            >
                                ৳{{ money(wallet.total_withdrawn) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- transactions -->
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-5 font-semibold text-gray-800 dark:text-white/90">
                    Transaction History
                </h5>

                <div
                    v-if="transactions.data.length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <div
                        v-for="t in transactions.data"
                        :key="t.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                :class="typeIcon(t.type).bg"
                                class="flex h-10 w-10 items-center justify-center rounded-full"
                            >
                                <span
                                    :class="typeIcon(t.type).text"
                                    class="text-lg font-bold"
                                    >{{ typeIcon(t.type).symbol }}</span
                                >
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-800 capitalize dark:text-white/90"
                                >
                                    {{ t.description || t.type }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ t.created_at }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p
                                :class="
                                    isCredit(t.type)
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400'
                                "
                                class="text-sm font-semibold"
                            >
                                {{ isCredit(t.type) ? '+' : '−' }}৳{{
                                    money(t.amount)
                                }}
                            </p>
                            <p class="text-xs text-gray-400">
                                Balance: ৳{{ money(t.balance_after) }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="py-10 text-center text-sm text-gray-400">
                    No transactions yet. Your earnings will appear here.
                </p>

                <!-- pagination -->
                <div
                    v-if="transactions.links.length > 3"
                    class="mt-5 flex flex-wrap gap-1"
                >
                    <template v-for="(link, i) in transactions.links" :key="i">
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

        <!-- Withdraw modal -->
        <div
            v-if="withdrawing"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="withdrawing = false"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900"
            >
                <h4
                    class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Withdraw Funds
                </h4>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Available: ৳{{ money(wallet.available_balance) }}
                </p>

                <!-- payout info -->
                <div
                    v-if="payout.bkash_number || payout.bank_account"
                    class="mb-4 rounded-xl bg-gray-50 p-3 text-sm dark:bg-white/[0.03]"
                >
                    <p
                        v-if="payout.bkash_number"
                        class="text-gray-600 dark:text-gray-400"
                    >
                        bKash: {{ payout.bkash_number }}
                    </p>
                    <p
                        v-if="payout.bank_account"
                        class="text-gray-600 dark:text-gray-400"
                    >
                        {{ payout.bank_name }} — {{ payout.bank_account }}
                    </p>
                </div>
                <p
                    v-else
                    class="mb-4 rounded-xl bg-yellow-50 p-3 text-sm text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400"
                >
                    Add your payout details (bKash/bank) in profile before
                    withdrawing.
                </p>

                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >Amount</label
                >
                <input
                    v-model="form.amount"
                    type="number"
                    :max="wallet.available_balance"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    placeholder="0"
                />
                <p
                    v-if="form.errors.amount"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ form.errors.amount }}
                </p>

                <div class="mt-5 flex justify-end gap-3">
                    <button
                        @click="withdrawing = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitWithdraw"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Requesting...'
                                : 'Request Withdrawal'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    wallet: Object,
    transactions: Object,
    payout: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const isCredit = (type) => ['credit', 'release'].includes(type);

const typeIcon = (type) => {
    if (isCredit(type)) {
        return {
            symbol: '↓',
            bg: 'bg-green-100 dark:bg-green-900/30',
            text: 'text-green-600 dark:text-green-400',
        };
    }
    return {
        symbol: '↑',
        bg: 'bg-red-100 dark:bg-red-900/30',
        text: 'text-red-600 dark:text-red-400',
    };
};

// withdraw modal
const withdrawing = ref(false);
const form = useForm({ amount: '' });

const openWithdraw = () => {
    if (props.wallet.available_balance <= 0) return;
    withdrawing.value = true;
    form.amount = '';
};

const submitWithdraw = () => {
    form.post(route('seller.wallet.withdraw'), {
        preserveScroll: true,
        onSuccess: () => (withdrawing.value = false),
    });
};
</script>
