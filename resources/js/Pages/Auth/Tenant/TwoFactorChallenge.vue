<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-gray-900"
    >
        <div
            class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Two-Factor Authentication
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Enter the code from your authenticator app, or a recovery code.
            </p>

            <form @submit.prevent="submit" class="mt-5 space-y-3">
                <input
                    v-model="form.code"
                    type="text"
                    autofocus
                    placeholder="6-digit code or recovery code"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-center text-lg tracking-widest dark:border-gray-700 dark:text-white/90"
                />
                <p v-if="form.errors.code" class="text-sm text-red-500">
                    {{ form.errors.code }}
                </p>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-brand-500 hover:bg-brand-600 w-full rounded-lg py-2.5 text-sm font-medium text-white disabled:opacity-50"
                >
                    Verify
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({ code: '' });

const submit = () => {
    form.post('/two-factor-challenge');
};
</script>
