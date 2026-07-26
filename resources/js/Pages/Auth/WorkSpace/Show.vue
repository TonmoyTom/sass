<template>
    <WorkspaceLayout title="Security Settings">
        <div class="mx-auto space-y-6">
            <!-- Two-Factor Authentication card -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="flex items-start gap-4 border-b border-gray-100 p-6 dark:border-gray-800"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                        :class="
                            enabled
                                ? 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400'
                                : 'bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400'
                        "
                    >
                        <ShieldCheck class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3
                                class="text-base font-semibold text-gray-900 dark:text-white"
                            >
                                Two-Factor Authentication
                            </h3>
                            <span
                                v-if="enabled"
                                class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400"
                            >
                                <Check class="h-3 w-3" /> Active
                            </span>
                        </div>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Add an extra layer of security using an
                            authenticator app like Google Authenticator or
                            Authy.
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Already enabled -->
                    <div v-if="enabled" class="space-y-4">
                        <div
                            v-if="recoveryCodes.length"
                            class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-900/10"
                        >
                            <div class="flex items-start gap-2">
                                <TriangleAlert
                                    class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400"
                                />
                                <div class="min-w-0">
                                    <p
                                        class="text-sm font-medium text-amber-800 dark:text-amber-400"
                                    >
                                        Save your recovery codes
                                    </p>
                                    <p
                                        class="mt-0.5 text-xs text-amber-700/80 dark:text-amber-400/70"
                                    >
                                        Each code can only be used once if you
                                        lose access to your device.
                                    </p>
                                    <div
                                        class="mt-3 grid grid-cols-2 gap-2 rounded-lg bg-white/60 p-3 font-mono text-xs text-amber-900 dark:bg-black/20 dark:text-amber-300"
                                    >
                                        <span
                                            v-for="code in recoveryCodes"
                                            :key="code"
                                            >{{ code }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-gray-200 p-4 dark:border-gray-800"
                        >
                            <p
                                class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Disable two-factor authentication
                            </p>
                            <form @submit.prevent="disable" class="space-y-3">
                                <input
                                    v-model="disableForm.password"
                                    type="password"
                                    placeholder="Enter your password to confirm"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-red-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                />
                                <p
                                    v-if="disableForm.errors.password"
                                    class="text-sm text-red-500"
                                >
                                    {{ disableForm.errors.password }}
                                </p>
                                <button
                                    type="submit"
                                    :disabled="disableForm.processing"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:opacity-50"
                                >
                                    <ShieldOff class="h-4 w-4" />
                                    {{
                                        disableForm.processing
                                            ? 'Disabling...'
                                            : 'Disable 2FA'
                                    }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Setup in progress (QR scan + confirm) -->
                    <div v-else-if="qr_code_url" class="space-y-4">
                        <div
                            class="rounded-xl border border-gray-200 p-5 text-center dark:border-gray-800"
                        >
                            <p
                                class="mb-4 text-sm text-gray-600 dark:text-gray-400"
                            >
                                Scan this QR code with your authenticator app
                            </p>
                            <div
                                class="mx-auto mb-4 inline-flex rounded-xl border border-gray-100 bg-white p-3 dark:border-gray-800"
                                v-html="qrSvg"
                            ></div>

                            <form
                                @submit.prevent="confirm"
                                class="mx-auto max-w-xs space-y-3"
                            >
                                <input
                                    v-model="confirmForm.code"
                                    type="text"
                                    autofocus
                                    placeholder="000000"
                                    maxlength="6"
                                    inputmode="numeric"
                                    class="focus:border-brand-300 h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-center text-xl font-semibold tracking-[0.3em] focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                />
                                <p
                                    v-if="confirmForm.errors.code"
                                    class="text-sm text-red-500"
                                >
                                    {{ confirmForm.errors.code }}
                                </p>
                                <button
                                    type="submit"
                                    :disabled="confirmForm.processing"
                                    class="bg-brand-500 hover:bg-brand-600 w-full rounded-lg py-2.5 text-sm font-medium text-white transition disabled:opacity-50"
                                >
                                    {{
                                        confirmForm.processing
                                            ? 'Verifying...'
                                            : 'Verify & Enable'
                                    }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Not enabled -->
                    <div
                        v-else
                        class="flex items-center justify-between gap-4 rounded-xl border border-dashed border-gray-200 p-5 dark:border-gray-700"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800"
                            >
                                <ShieldAlert
                                    class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                />
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Two-factor authentication is not enabled yet.
                            </p>
                        </div>
                        <button
                            @click="enable"
                            class="bg-brand-500 hover:bg-brand-600 shrink-0 rounded-lg px-4 py-2 text-sm font-medium whitespace-nowrap text-white transition"
                        >
                            Enable 2FA
                        </button>
                    </div>
                </div>
            </div>

            <!-- Session & Cookies card -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="flex items-start gap-4 border-b border-gray-100 p-6 dark:border-gray-800"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                    >
                        <Cookie class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-base font-semibold text-gray-900 dark:text-white"
                        >
                            Session & Cookies
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Troubleshooting tools for clearing stored browser
                            data.
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <ClearSession />
                </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import ClearSession from '@/Components/common/ClearSession.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import {
    Check,
    Cookie,
    ShieldAlert,
    ShieldCheck,
    ShieldOff,
    TriangleAlert,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    enabled: Boolean,
    qr_code_url: String,
});

const page = usePage();
const recoveryCodes = computed(() => page.props.flash?.recovery_codes ?? []);

// qr_code_url ekta otpauth:// URI — QR image render korar jonno external API use kortesi
// (offline/self-hosted chaile bacon/bacon-qr-code diye server-side SVG generate kore pathate hobe)
const qrSvg = computed(() => {
    if (!props.qr_code_url) return '';
    const encoded = encodeURIComponent(props.qr_code_url);
    return `<img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encoded}" alt="QR Code" class="h-[180px] w-[180px]" />`;
});

const confirmForm = useForm({ code: '' });
const disableForm = useForm({ password: '' });

const enable = () => {
    router.post('/settings/two-factor/enable', {}, { preserveScroll: true });
};

const confirm = () => {
    confirmForm.post('/settings/two-factor/confirm', {
        preserveScroll: true,
        onSuccess: () => confirmForm.reset(),
    });
};

const disable = () => {
    disableForm.delete('/settings/two-factor', {
        preserveScroll: true,
        onSuccess: () => disableForm.reset(),
    });
};
</script>
