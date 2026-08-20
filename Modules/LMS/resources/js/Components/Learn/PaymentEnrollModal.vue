<script setup>
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { useForm } from '@inertiajs/vue3';
import { Building2, Check, Copy, Smartphone, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    course: {
        type: Object,
        required: true,
    },
    paymentMethods: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const methodLabels = {
    bkash: 'Bkash',
    nagad: 'Nagad',
    bank: 'Bank Transfer',
};

const selectedMethod = ref(props.paymentMethods[0]?.method ?? null);
const copiedField = ref(null);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            selectedMethod.value = props.paymentMethods[0]?.method ?? null;
            copiedField.value = null;
            form.reset();
        }
    },
);

const activeMethod = computed(() =>
    props.paymentMethods.find((m) => m.method === selectedMethod.value),
);

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const amountDue = computed(() =>
    props.course.has_discount ? props.course.discount_price : props.course.price,
);

const copy = async (text, field) => {
    if (!text) return;
    try {
        await navigator.clipboard.writeText(text);
        copiedField.value = field;
        setTimeout(() => {
            if (copiedField.value === field) copiedField.value = null;
        }, 1500);
    } catch {
        // clipboard not available — silently ignore, number is still visible to copy manually
    }
};

const form = useForm({
    payment_method: methodLabels[selectedMethod.value] ?? '',
    transaction_id: '',
});

watch(selectedMethod, (val) => {
    form.payment_method = methodLabels[val] ?? '';
});

const submit = () => {
    form.post(learnRoutes.enrollCourse(props.course.id), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-950/60 p-4 backdrop-blur-sm"
                @click.self="emit('close')"
            >
                <div
                    class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900"
                >
                    <!-- header -->
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Complete payment</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ course.title }}</p>
                        </div>
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                            @click="emit('close')"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="px-6 py-5">
                        <!-- amount -->
                        <div class="bg-brand-50 dark:bg-brand-500/10 flex items-center justify-between rounded-xl px-4 py-3">
                            <span class="text-sm text-gray-600 dark:text-gray-300">Amount to pay</span>
                            <span class="text-brand-600 dark:text-brand-400 text-lg font-extrabold">
                                ৳{{ money(amountDue) }}
                            </span>
                        </div>

                        <!-- no methods configured -->
                        <p
                            v-if="!paymentMethods.length"
                            class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-500/10 dark:text-amber-400"
                        >
                            No payment method is set up yet. Please contact support to enroll.
                        </p>

                        <template v-else>
                            <!-- method tabs -->
                            <div class="mt-5 flex gap-2">
                                <button
                                    v-for="m in paymentMethods"
                                    :key="m.method"
                                    type="button"
                                    class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border px-3 py-2.5 text-sm font-semibold transition"
                                    :class="
                                        selectedMethod === m.method
                                            ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
                                            : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5'
                                    "
                                    @click="selectedMethod = m.method"
                                >
                                    <Smartphone v-if="m.method !== 'bank'" class="h-3.5 w-3.5" />
                                    <Building2 v-else class="h-3.5 w-3.5" />
                                    {{ methodLabels[m.method] ?? m.method }}
                                </button>
                            </div>

                            <!-- method details -->
                            <div v-if="activeMethod" class="mt-4 space-y-3">
                                <template v-if="activeMethod.method !== 'bank'">
                                    <div
                                        v-if="activeMethod.merchant_number"
                                        class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-700"
                                    >
                                        <div>
                                            <p class="text-xs text-gray-400">Send Money to this number</p>
                                            <p class="font-mono text-base font-bold text-gray-900 dark:text-white">
                                                {{ activeMethod.merchant_number }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                            @click="copy(activeMethod.merchant_number, 'merchant')"
                                        >
                                            <Check v-if="copiedField === 'merchant'" class="text-success-500 h-3.5 w-3.5" />
                                            <Copy v-else class="h-3.5 w-3.5" />
                                            {{ copiedField === 'merchant' ? 'Copied' : 'Copy' }}
                                        </button>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="rounded-xl border border-gray-200 p-4 text-sm dark:border-gray-700">
                                        <dl class="space-y-2">
                                            <div v-if="activeMethod.bank_name" class="flex justify-between gap-3">
                                                <dt class="text-gray-400">Bank</dt>
                                                <dd class="font-medium text-gray-800 dark:text-gray-100">{{ activeMethod.bank_name }}</dd>
                                            </div>
                                            <div v-if="activeMethod.account_name" class="flex justify-between gap-3">
                                                <dt class="text-gray-400">Account name</dt>
                                                <dd class="font-medium text-gray-800 dark:text-gray-100">{{ activeMethod.account_name }}</dd>
                                            </div>
                                            <div v-if="activeMethod.account_number" class="flex items-center justify-between gap-3">
                                                <dt class="text-gray-400">Account number</dt>
                                                <dd class="flex items-center gap-1.5 font-mono font-medium text-gray-800 dark:text-gray-100">
                                                    {{ activeMethod.account_number }}
                                                    <button type="button" @click="copy(activeMethod.account_number, 'account')">
                                                        <Check v-if="copiedField === 'account'" class="text-success-500 h-3.5 w-3.5" />
                                                        <Copy v-else class="h-3.5 w-3.5 text-gray-400" />
                                                    </button>
                                                </dd>
                                            </div>
                                            <div v-if="activeMethod.routing_number" class="flex justify-between gap-3">
                                                <dt class="text-gray-400">Routing number</dt>
                                                <dd class="font-medium text-gray-800 dark:text-gray-100">{{ activeMethod.routing_number }}</dd>
                                            </div>
                                            <div v-if="activeMethod.branch" class="flex justify-between gap-3">
                                                <dt class="text-gray-400">Branch</dt>
                                                <dd class="font-medium text-gray-800 dark:text-gray-100">{{ activeMethod.branch }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </template>

                                <p
                                    v-if="activeMethod.instructions"
                                    class="rounded-xl bg-gray-50 px-4 py-3 text-xs leading-relaxed text-gray-600 dark:bg-white/5 dark:text-gray-300"
                                >
                                    {{ activeMethod.instructions }}
                                </p>
                            </div>

                            <!-- transaction id form -->
                            <form class="mt-5 space-y-3" @submit.prevent="submit">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Transaction ID
                                    </label>
                                    <input
                                        v-model="form.transaction_id"
                                        type="text"
                                        placeholder="e.g. 8N7X2K1P0Q"
                                        class="focus:border-brand-300 dark:focus:border-brand-500 h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                    <p v-if="form.errors.transaction_id" class="text-error-500 mt-1 text-xs">
                                        {{ form.errors.transaction_id }}
                                    </p>
                                    <p v-if="form.errors.payment_method" class="text-error-500 mt-1 text-xs">
                                        {{ form.errors.payment_method }}
                                    </p>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-success-500 hover:bg-success-600 flex w-full items-center justify-center rounded-xl py-3 text-sm font-semibold text-white transition disabled:opacity-60"
                                >
                                    {{ form.processing ? 'Submitting...' : "I've paid — Complete enrollment" }}
                                </button>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>