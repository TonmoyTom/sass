<template>
    <SellerLayout title="Withdraw Funds">
        <div class="mx-auto ">
            <div class="mb-6">
                <Link
                    :href="route('seller.wallet.index')"
                    class="mb-3 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ← Back to Wallet
                </Link>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Withdraw Funds</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Available: <span class="font-semibold text-gray-800 dark:text-white/90">৳{{ money(wallet.available_balance) }}</span>
                </p>
            </div>

            <!-- Pending request notice -->
            <div
                v-if="pending_request"
                class="mb-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800/40 dark:bg-yellow-900/20"
            >
                <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-400">Pending Withdraw Request</p>
                <p class="mt-1 text-sm text-yellow-600 dark:text-yellow-500">
                    ৳{{ money(pending_request.amount) }} via {{ pending_request.method }} — submitted {{ pending_request.created_at }}
                </p>
                <p class="mt-1 text-xs text-yellow-500">You cannot submit another request until this is processed.</p>
            </div>

            <div class="space-y-5">
                <!-- Step 1: Payout Details -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="mb-4 flex items-center justify-between">
                        <h5 class="font-semibold text-gray-800 dark:text-white/90">Payout Details</h5>
                        <button
                            @click="editingPayout = !editingPayout"
                            class="text-brand-500 text-sm hover:underline"
                        >
                            {{ editingPayout ? 'Cancel' : 'Edit' }}
                        </button>
                    </div>

                    <!-- View mode -->
                    <div v-if="!editingPayout" class="space-y-3">
                        <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-pink-100 dark:bg-pink-900/30">
                                <span class="text-sm font-bold text-pink-600">৳</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">bKash</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ payout.bkash_number || '— not set' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                <span class="text-sm font-bold text-blue-600">🏦</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Bank</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ payout.bank_name && payout.bank_account ? `${payout.bank_name} — ${payout.bank_account}` : '— not set' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit mode -->
                    <div v-else class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">bKash Number</label>
                            <input
                                v-model="payoutForm.bkash_number"
                                type="text"
                                placeholder="01XXXXXXXXX"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bank Name</label>
                            <input
                                v-model="payoutForm.bank_name"
                                type="text"
                                placeholder="e.g. Dutch-Bangla Bank"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bank Account Number</label>
                            <input
                                v-model="payoutForm.bank_account"
                                type="text"
                                placeholder="Account number"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div class="flex justify-end">
                            <button
                                @click="savePayout"
                                :disabled="payoutForm.processing"
                                class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                {{ payoutForm.processing ? 'Saving...' : 'Save Details' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Withdraw Form -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">Request Withdrawal</h5>

                    <!-- Method select -->
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                @click="withdrawForm.method = 'bkash'"
                                :disabled="!payout.bkash_number"
                                :class="[
                                    'flex items-center gap-3 rounded-xl border-2 p-3 text-left transition',
                                    withdrawForm.method === 'bkash'
                                        ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20'
                                        : 'border-gray-200 hover:border-gray-300 dark:border-gray-700',
                                    !payout.bkash_number ? 'cursor-not-allowed opacity-40' : ''
                                ]"
                            >
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-pink-100 dark:bg-pink-900/30">
                                    <span class="text-sm font-bold text-pink-600">৳</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">bKash</p>
                                    <p class="text-xs text-gray-500">{{ payout.bkash_number || 'Not set' }}</p>
                                </div>
                            </button>

                            <button
                                @click="withdrawForm.method = 'bank'"
                                :disabled="!payout.bank_account"
                                :class="[
                                    'flex items-center gap-3 rounded-xl border-2 p-3 text-left transition',
                                    withdrawForm.method === 'bank'
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                        : 'border-gray-200 hover:border-gray-300 dark:border-gray-700',
                                    !payout.bank_account ? 'cursor-not-allowed opacity-40' : ''
                                ]"
                            >
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                    <span class="text-sm font-bold text-blue-600">🏦</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Bank</p>
                                    <p class="text-xs text-gray-500">{{ payout.bank_name || 'Not set' }}</p>
                                </div>
                            </button>
                        </div>
                        <p v-if="withdrawForm.errors.method" class="mt-1.5 text-sm text-red-500">{{ withdrawForm.errors.method }}</p>
                    </div>

                    <!-- Amount -->
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount (min ৳500)</label>
                        <div class="relative">
                            <span class="absolute top-1/2 left-4 -translate-y-1/2 text-sm text-gray-400">৳</span>
                            <input
                                v-model="withdrawForm.amount"
                                type="number"
                                min="10"
                                :max="wallet.available_balance"
                                placeholder="0"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-8 pr-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <p v-if="withdrawForm.errors.amount" class="mt-1.5 text-sm text-red-500">{{ withdrawForm.errors.amount }}</p>
                    </div>

                    <!-- Note -->
                    <div class="mb-5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Note (optional)</label>
                        <input
                            v-model="withdrawForm.note"
                            type="text"
                            placeholder="Any note for admin..."
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                    </div>

                    <button
                        @click="submitWithdraw"
                        :disabled="withdrawForm.processing || !!pending_request || wallet.available_balance <= 0"
                        class="bg-brand-500 hover:bg-brand-600 w-full rounded-xl py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ withdrawForm.processing ? 'Submitting...' : 'Request Withdrawal' }}
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
    payout: Object,
    pending_request: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

// payout edit
const editingPayout = ref(false);
const payoutForm = useForm({
    bkash_number: props.payout.bkash_number ?? '',
    bank_name:    props.payout.bank_name ?? '',
    bank_account: props.payout.bank_account ?? '',
});

const savePayout = () => {
    payoutForm.post(route('seller.wallet.payout.update'), {
        preserveScroll: true,
        onSuccess: () => (editingPayout.value = false),
    });
};

// withdraw
const withdrawForm = useForm({
    amount: '',
    method: props.payout.bkash_number ? 'bkash' : (props.payout.bank_account ? 'bank' : ''),
    note:   '',
});

const submitWithdraw = () => {
    withdrawForm.post(route('seller.wallet.withdraw'), {
        preserveScroll: true,
        onSuccess: () => {
            withdrawForm.reset();
        },
    });
};
</script>
