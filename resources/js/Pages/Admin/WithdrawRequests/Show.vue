<template>
    <AdminLayout title="Withdraw Request">
        <div class="mx-auto max-w-lg">
            <div class="mb-6">
                <Link
                    :href="route('admin.withdraw.index')"
                    class="mb-2 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ← Back to List
                </Link>
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Withdraw Request #{{ request.id }}
                </h3>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <!-- Amount + status -->
                <div class="mb-6 flex items-center justify-between">
                    <p
                        class="text-3xl font-bold text-gray-800 dark:text-white/90"
                    >
                        ৳{{ money(request.amount) }}
                    </p>
                    <span
                        class="rounded-full px-4 py-1.5 text-sm font-semibold capitalize"
                        :class="{
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400':
                                request.status === 'pending',
                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400':
                                request.status === 'approved',
                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400':
                                request.status === 'rejected',
                        }"
                        >{{ request.status }}</span
                    >
                </div>

                <!-- Seller info -->
                <div
                    class="mb-5 rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]"
                >
                    <p
                        class="mb-2 text-xs font-semibold tracking-wide text-gray-400 uppercase"
                    >
                        Seller Info
                    </p>
                    <p
                        class="text-sm font-medium text-gray-800 dark:text-white/90"
                    >
                        {{ request.seller_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ request.seller_email }}
                    </p>
                </div>

                <!-- Payout details -->
                <div
                    class="mb-5 space-y-3 border-t border-gray-100 pt-5 dark:border-gray-800"
                >
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Method</span>
                        <span
                            class="text-sm font-medium text-gray-800 capitalize dark:text-white/90"
                            >{{ request.method }}</span
                        >
                    </div>
                    <div
                        v-if="request.method === 'bkash'"
                        class="flex justify-between"
                    >
                        <span class="text-sm text-gray-500">bKash Number</span>
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-white/90"
                            >{{ request.bkash_number ?? '—' }}</span
                        >
                    </div>
                    <template v-else>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Bank Name</span>
                            <span
                                class="text-sm font-medium text-gray-800 dark:text-white/90"
                                >{{ request.bank_name ?? '—' }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500"
                                >Account No</span
                            >
                            <span
                                class="text-sm font-medium text-gray-800 dark:text-white/90"
                                >{{ request.bank_account ?? '—' }}</span
                            >
                        </div>
                    </template>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Requested</span>
                        <span
                            class="text-sm text-gray-800 dark:text-white/90"
                            >{{ request.created_at }}</span
                        >
                    </div>
                    <div
                        v-if="request.processed_at"
                        class="flex justify-between"
                    >
                        <span class="text-sm text-gray-500">Processed</span>
                        <span
                            class="text-sm text-gray-800 dark:text-white/90"
                            >{{ request.processed_at }}</span
                        >
                    </div>
                    <div v-if="request.note" class="flex justify-between gap-4">
                        <span class="text-sm text-gray-500">Note</span>
                        <span
                            class="text-right text-sm text-gray-600 italic dark:text-gray-400"
                            >"{{ request.note }}"</span
                        >
                    </div>
                </div>

                <!-- Actions -->
                <!-- Actions -->
                <div
                    v-if="request.status === 'pending'"
                    class="space-y-3 border-t border-gray-100 pt-5 dark:border-gray-800"
                >
                    <!-- Amount options -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Pay Amount</label
                        >
                        <div class="mb-3 flex gap-2">
                            <button
                                @click="setAmount('full')"
                                :class="
                                    amountMode === 'full'
                                        ? 'bg-brand-500 text-white'
                                        : 'border border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-400'
                                "
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                            >
                                Full (৳{{ money(request.amount) }})
                            </button>
                            <button
                                @click="setAmount('half')"
                                :class="
                                    amountMode === 'half'
                                        ? 'bg-brand-500 text-white'
                                        : 'border border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-400'
                                "
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                            >
                                Half (৳{{ money(request.amount / 2) }})
                            </button>
                            <button
                                @click="setAmount('custom')"
                                :class="
                                    amountMode === 'custom'
                                        ? 'bg-brand-500 text-white'
                                        : 'border border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-400'
                                "
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                            >
                                Custom
                            </button>
                        </div>

                        <div class="relative">
                            <span
                                class="absolute top-1/2 left-4 -translate-y-1/2 text-sm text-gray-400"
                                >৳</span
                            >
                            <input
                                v-model="approveForm.paid_amount"
                                type="number"
                                :max="request.amount"
                                min="1"
                                :readonly="amountMode !== 'custom'"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pr-4 pl-8 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                :class="
                                    amountMode !== 'custom' ? 'opacity-70' : ''
                                "
                            />
                        </div>

                        <!-- remaining refund notice -->
                        <p
                            v-if="remaining > 0"
                            class="mt-1.5 text-xs text-yellow-600 dark:text-yellow-400"
                        >
                            ৳{{ money(remaining) }} will be refunded to seller's
                            available balance.
                        </p>
                    </div>

                    <!-- Admin note -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Note (optional)</label
                        >
                        <input
                            v-model="approveForm.note"
                            type="text"
                            placeholder="Payment reference, transaction ID..."
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                    </div>

                    <button
                        @click="approve"
                        :disabled="
                            approveForm.processing || !approveForm.paid_amount
                        "
                        class="w-full rounded-xl bg-green-500 py-3 text-sm font-semibold text-white transition hover:bg-green-600 disabled:opacity-50"
                    >
                        {{
                            approveForm.processing
                                ? 'Processing...'
                                : `Approve & Pay ৳${money(approveForm.paid_amount || 0)}`
                        }}
                    </button>

                    <!-- Reject -->
                    <div v-if="!showReject">
                        <button
                            @click="showReject = true"
                            class="w-full rounded-xl border border-red-300 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            Reject Request
                        </button>
                    </div>

                    <div v-else class="space-y-3">
                        <input
                            v-model="rejectForm.reason"
                            type="text"
                            placeholder="Rejection reason (optional)"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <div class="flex gap-2">
                            <button
                                @click="showReject = false"
                                class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                Cancel
                            </button>
                            <button
                                @click="reject"
                                :disabled="rejectForm.processing"
                                class="flex-1 rounded-xl bg-red-500 py-2.5 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-50"
                            >
                                {{
                                    rejectForm.processing
                                        ? 'Rejecting...'
                                        : 'Confirm Reject'
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Approved: show paid info -->
                <div
                    v-if="request.status === 'approved'"
                    class="border-t border-gray-100 pt-5 dark:border-gray-800"
                >
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Paid Amount</span>
                        <span
                            class="text-sm font-bold text-green-600 dark:text-green-400"
                            >৳{{ money(request.paid_amount) }}</span
                        >
                    </div>
                    <div
                        v-if="request.paid_amount < request.amount"
                        class="mt-2 flex justify-between"
                    >
                        <span class="text-sm text-red-500">Refunded</span>
                        <span
                            class="text-sm font-medium text-red-800 dark:text-white/90"
                            >৳{{
                                money(request.amount - request.paid_amount)
                            }}</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ request: Object });

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const showReject = ref(false);
const amountMode = ref('full');

const approveForm = useForm({
    paid_amount: props.request.amount,
    note: '',
});

const rejectForm = useForm({ reason: '' });

const remaining = computed(() => {
    const paid = parseFloat(approveForm.paid_amount) || 0;
    return Math.max(0, props.request.amount - paid);
});

const setAmount = (mode) => {
    amountMode.value = mode;
    if (mode === 'full') approveForm.paid_amount = props.request.amount;
    if (mode === 'half') approveForm.paid_amount = props.request.amount / 2;
    if (mode === 'custom') approveForm.paid_amount = '';
};

const approve = () => {
    approveForm.post(route('admin.withdraw.approve', props.request.id), {
        preserveScroll: true,
    });
};

const reject = () => {
    rejectForm.post(route('admin.withdraw.reject', props.request.id), {
        preserveScroll: true,
        onSuccess: () => (showReject.value = false),
    });
};
</script>
