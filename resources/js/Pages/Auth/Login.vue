<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    Check,
    GraduationCap,
    Loader2,
    Lock,
    Mail,
} from 'lucide-vue-next';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'));
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen bg-white">
        <div class="grid min-h-screen lg:grid-cols-2">
            <!-- Left Side: Branding (Hidden on mobile) -->
            <div
                class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-700 p-12 text-white lg:flex lg:flex-col lg:justify-between"
            >
                <!-- Decorative shapes -->
                <div class="absolute inset-0 overflow-hidden">
                    <div
                        class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"
                    ></div>
                    <div
                        class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"
                    ></div>
                    <div
                        class="absolute top-1/2 left-1/2 h-64 w-64 rounded-full bg-white/5 blur-2xl"
                    ></div>
                </div>

                <!-- Logo -->
                <div class="relative z-10 flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-white/20 bg-white/15 backdrop-blur-sm"
                    >
                        <GraduationCap class="h-7 w-7 text-white" />
                    </div>
                    <span class="text-2xl font-bold tracking-tight"
                        >EduSaaS</span
                    >
                </div>

                <!-- Middle Content -->
                <div class="relative z-10 space-y-6">
                    <h1 class="text-4xl leading-tight font-bold tracking-tight">
                        Manage your school<br />
                        <span
                            class="bg-gradient-to-r from-yellow-200 to-pink-200 bg-clip-text text-transparent"
                        >
                            digitally & efficiently.
                        </span>
                    </h1>
                    <p class="max-w-md text-lg leading-relaxed text-white/80">
                        Join 100+ schools across Bangladesh using EduSaaS to
                        streamline operations, track attendance, and manage
                        finances.
                    </p>

                    <!-- Features list -->
                    <div class="space-y-3 pt-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20"
                            >
                                <Check class="h-4 w-4 text-white" />
                            </div>
                            <span class="text-white/90"
                                >Student & Teacher Management</span
                            >
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20"
                            >
                                <Check class="h-4 w-4 text-white" />
                            </div>
                            <span class="text-white/90"
                                >Attendance & Fee Tracking</span
                            >
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20"
                            >
                                <Check class="h-4 w-4 text-white" />
                            </div>
                            <span class="text-white/90"
                                >Modular & Scalable</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Bottom Stats -->
                <div
                    class="relative z-10 grid grid-cols-3 gap-6 border-t border-white/20 pt-8"
                >
                    <div>
                        <div class="text-3xl font-bold">100+</div>
                        <div class="mt-1 text-sm text-white/70">
                            Active Schools
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold">50K+</div>
                        <div class="mt-1 text-sm text-white/70">Students</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold">99.9%</div>
                        <div class="mt-1 text-sm text-white/70">Uptime</div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div
                class="flex flex-col justify-center px-6 py-12 lg:px-12 xl:px-24"
            >
                <div class="mx-auto w-full max-w-md">
                    <!-- Mobile Logo (only on mobile) -->
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600"
                        >
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900"
                            >EduSaaS</span
                        >
                    </div>

                    <!-- Header -->
                    <div class="mb-8 space-y-2">
                        <h2 class="text-3xl font-bold text-slate-900">
                            Welcome back 👋
                        </h2>
                        <p class="text-slate-600">
                            Please enter your credentials to access your
                            account.
                        </p>
                    </div>

                    <!-- Status Message -->
                    <div
                        v-if="status"
                        class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800"
                    >
                        {{ status }}
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email -->
                        <div class="space-y-2">
                            <label
                                for="email"
                                class="text-sm font-medium text-slate-700"
                            >
                                Email address
                            </label>
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                                >
                                    <Mail class="h-5 w-5 text-slate-400" />
                                </div>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="you@example.com"
                                    class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pr-4 pl-10 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                            form.errors.email,
                                    }"
                                />
                            </div>
                            <p
                                v-if="form.errors.email"
                                class="text-xs text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label
                                    for="password"
                                    class="text-sm font-medium text-slate-700"
                                >
                                    Password
                                </label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs font-medium text-indigo-600 transition-colors hover:text-indigo-700"
                                >
                                    Forgot password?
                                </Link>
                            </div>
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                                >
                                    <Lock class="h-5 w-5 text-slate-400" />
                                </div>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pr-4 pl-10 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                            form.errors.password,
                                    }"
                                />
                            </div>
                            <p
                                v-if="form.errors.password"
                                class="text-xs text-red-600"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center">
                            <label
                                class="group flex cursor-pointer items-center"
                            >
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    name="remember"
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 transition-colors focus:ring-2 focus:ring-indigo-500/20"
                                />
                                <span
                                    class="ml-2 text-sm text-slate-600 transition-colors group-hover:text-slate-900"
                                >
                                    Remember me for 30 days
                                </span>
                            </label>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            <span>{{
                                form.processing
                                    ? 'Signing in...'
                                    : 'Sign in to your account'
                            }}</span>
                            <ArrowRight
                                v-if="!form.processing"
                                class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                            />
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="bg-white px-3 text-slate-500"
                                >New to EduSaaS?</span
                            >
                        </div>
                    </div>

                    <!-- Sign Up Links -->
                    <div class="space-y-3">
                        <Link
                            :href="route('register')"
                            class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-50"
                        >
                            Register your school
                            <ArrowRight class="h-4 w-4" />
                        </Link>

                        <p class="text-center text-xs text-slate-500">
                            Want to earn by referring schools?
                            <Link
                                href="#"
                                class="font-medium text-indigo-600 hover:text-indigo-700"
                            >
                                Become a seller
                            </Link>
                        </p>
                    </div>

                    <!-- Footer -->
                    <p class="mt-8 text-center text-xs text-slate-500">
                        By signing in, you agree to our
                        <a
                            href="#"
                            class="text-slate-700 underline hover:text-indigo-600"
                            >Terms of Service</a
                        >
                        and
                        <a
                            href="#"
                            class="text-slate-700 underline hover:text-indigo-600"
                            >Privacy Policy</a
                        >.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
