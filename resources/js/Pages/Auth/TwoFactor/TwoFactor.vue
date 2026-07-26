<template>
    <AdminLayout title="Two-Factor Authentication">
        <div class="mx-auto max-w-lg">
            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Two-Factor Authentication
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Add an extra layer of security to your account using an
                    authenticator app.
                </p>

                <!-- Already enabled -->
                <div v-if="enabled" class="mt-5">
                    <div
                        class="mb-4 flex items-center gap-2 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400"
                    >
                        <Check class="h-4 w-4" /> Two-factor authentication is
                        enabled.
                    </div>

                    <div
                        v-if="recoveryCodes.length"
                        class="mb-4 rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20"
                    >
                        <p
                            class="mb-2 text-sm font-medium text-yellow-800 dark:text-yellow-400"
                        >
                            Save these recovery codes — each can be used once if
                            you lose access to your device:
                        </p>
                        <div
                            class="grid grid-cols-2 gap-2 font-mono text-xs text-yellow-900 dark:text-yellow-300"
                        >
                            <span v-for="code in recoveryCodes" :key="code">{{
                                code
                            }}</span>
                        </div>
                    </div>

                    <form @submit.prevent="disable" class="space-y-3">
                        <input
                            v-model="disableForm.password"
                            type="password"
                            placeholder="Enter your password to disable"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
                        />
                        <p
                            v-if="disableForm.errors.password"
                            class="text-sm text-red-500"
                        >
                            {{ disableForm.errors.password }}
                        </p>
                        <button
                            type="submit"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                        >
                            Disable 2FA
                        </button>
                    </form>
                </div>

                <!-- Setup in progress -->
                <div v-else-if="qr_code_url" class="mt-5">
                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                        Scan this QR code with Google Authenticator, Authy, or
                        any TOTP app:
                    </p>
                    <div
                        class="mb-4 flex justify-center rounded-lg bg-white p-4"
                        v-html="qrSvg"
                    ></div>

                    <form @submit.prevent="confirm" class="space-y-3">
                        <input
                            v-model="confirmForm.code"
                            type="text"
                            placeholder="Enter 6-digit code"
                            maxlength="6"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-center text-lg tracking-widest dark:border-gray-700 dark:text-white/90"
                        />
                        <p
                            v-if="confirmForm.errors.code"
                            class="text-sm text-red-500"
                        >
                            {{ confirmForm.errors.code }}
                        </p>
                        <button
                            type="submit"
                            class="bg-brand-500 hover:bg-brand-600 w-full rounded-lg py-2.5 text-sm font-medium text-white"
                        >
                            Verify & Enable
                        </button>
                    </form>
                </div>

                <!-- Not enabled -->
                <div v-else class="mt-5">
                    <button
                        @click="enable"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white"
                    >
                        Enable Two-Factor Authentication
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    enabled: Boolean,
    qr_code_url: String,
});

const page = usePage();
const recoveryCodes = computed(() => page.props.flash?.recovery_codes ?? []);

// qr_code_url ekta otpauth:// URI — SVG banate hole external QR lib lagbe,
// simple approach: Google Chart API diye render (offline hole client-side lib use koro)
const qrSvg = computed(() => {
    if (!props.qr_code_url) return '';
    const encoded = encodeURIComponent(props.qr_code_url);
    return `<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encoded}" alt="QR Code" />`;
});

const confirmForm = useForm({ code: '' });
const disableForm = useForm({ password: '' });

const enable = () => {
    router.post(route('two-factor.enable'), {}, { preserveScroll: true });
};

const confirm = () => {
    confirmForm.post(route('two-factor.confirm'), {
        preserveScroll: true,
        onSuccess: () => confirmForm.reset(),
    });
};

const disable = () => {
    disableForm.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => disableForm.reset(),
    });
};
</script>
