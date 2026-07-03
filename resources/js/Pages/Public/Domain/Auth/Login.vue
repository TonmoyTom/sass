<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-gray-950"
    >
        <div
            class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <h1
                class="mb-1 text-xl font-semibold text-gray-900 dark:text-white"
            >
                Sign in
            </h1>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                Enter your credentials to access your workspace.
            </p>

            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Password</label
                    >
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white"
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <label
                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                >
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300"
                    />
                    Remember me
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-brand-500 hover:bg-brand-600 mt-2 h-11 rounded-lg text-sm font-medium text-white disabled:opacity-50"
                >
                    {{ form.processing ? 'Signing in...' : 'Sign in' }}
                </button>
            </form>
        </div>
    </div>
</template>
