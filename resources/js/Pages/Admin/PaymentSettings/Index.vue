<template>
    <AdminLayout title="Payment Settings">
        <div class="mx-auto">
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Payment Settings
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure payment methods available to tenants at checkout.
                </p>
            </div>

            <div class="space-y-5">
                <div
                    v-for="setting in localSettings"
                    :key="setting.method"
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full"
                                :class="methodStyle(setting.method).bg"
                            >
                                <span
                                    class="text-sm font-bold"
                                    :class="methodStyle(setting.method).text"
                                >
                                    {{ methodStyle(setting.method).symbol }}
                                </span>
                            </div>
                            <h5
                                class="font-semibold text-gray-800 capitalize dark:text-white/90"
                            >
                                {{ setting.method }}
                            </h5>
                        </div>

                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                type="checkbox"
                                v-model="setting.is_active"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-green-500 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-5 dark:bg-gray-700"
                            ></div>
                        </label>
                    </div>

                    <!-- bKash / Nagad fields -->
                    <div
                        v-if="
                            setting.method === 'bkash' ||
                            setting.method === 'nagad'
                        "
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Merchant Number
                            </label>
                            <input
                                v-model="setting.merchant_number"
                                type="text"
                                placeholder="01XXXXXXXXX"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                API Key
                            </label>
                            <input
                                v-model="setting.api_key"
                                type="text"
                                placeholder="API key"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                API Secret
                                <span
                                    v-if="setting.has_api_secret"
                                    class="text-xs text-green-600 dark:text-green-400"
                                    >(saved — leave blank to keep)</span
                                >
                            </label>
                            <input
                                v-model="setting.api_secret"
                                type="password"
                                :placeholder="
                                    setting.has_api_secret
                                        ? '••••••••'
                                        : 'API secret'
                                "
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Username
                            </label>
                            <input
                                v-model="setting.username"
                                type="text"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Password
                                <span
                                    v-if="setting.has_password"
                                    class="text-xs text-green-600 dark:text-green-400"
                                    >(saved — leave blank to keep)</span
                                >
                            </label>
                            <input
                                v-model="setting.password"
                                type="password"
                                :placeholder="
                                    setting.has_password
                                        ? '••••••••'
                                        : 'Password'
                                "
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                    </div>

                    <!-- Bank fields -->
                    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Bank Name
                            </label>
                            <input
                                v-model="setting.bank_name"
                                type="text"
                                placeholder="e.g. Dutch-Bangla Bank"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Account Name
                            </label>
                            <input
                                v-model="setting.account_name"
                                type="text"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Account Number
                            </label>
                            <input
                                v-model="setting.account_number"
                                type="text"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Routing Number
                            </label>
                            <input
                                v-model="setting.routing_number"
                                type="text"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Branch
                            </label>
                            <input
                                v-model="setting.branch"
                                type="text"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                    </div>

                    <div class="mt-4">
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Instructions for customers (optional)
                        </label>
                        <textarea
                            v-model="setting.instructions"
                            rows="2"
                            placeholder="e.g. Send money and enter the transaction ID at checkout."
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        ></textarea>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button
                            @click="saveMethod(setting)"
                            :disabled="saving === setting.method"
                            class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {{
                                saving === setting.method ? 'Saving...' : 'Save'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps({
    settings: Array,
});

const localSettings = reactive(JSON.parse(JSON.stringify(props.settings)));
const saving = ref(null);

const methodStyle = (method) => {
    const styles = {
        bkash: {
            bg: 'bg-pink-100 dark:bg-pink-900/30',
            text: 'text-pink-600',
            symbol: '৳',
        },
        nagad: {
            bg: 'bg-orange-100 dark:bg-orange-900/30',
            text: 'text-orange-600',
            symbol: '৳',
        },
        bank: {
            bg: 'bg-blue-100 dark:bg-blue-900/30',
            text: 'text-blue-600',
            symbol: '🏦',
        },
    };
    return (
        styles[method] ?? {
            bg: 'bg-gray-100',
            text: 'text-gray-600',
            symbol: '?',
        }
    );
};

const saveMethod = (setting) => {
    saving.value = setting.method;
    router.patch(`/admin/payment-settings/${setting.method}`, setting, {
        preserveScroll: true,
        onFinish: () => (saving.value = null),
    });
};
</script>
