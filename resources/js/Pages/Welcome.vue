<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Check, GraduationCap, Star } from 'lucide-vue-next';
</script>

<template>
    <!-- <Head title="EduSaaS - School Management Made Simple" /> -->
    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b bg-white/80 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-6 py-4">
                <div class="flex items-center justify-between">
                    <Link href="/" class="flex items-center gap-2">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-purple-600"
                        >
                            <GraduationCap class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900"
                            >Test</span
                        >
                    </Link>
                    <div class="hidden items-center gap-8 md:flex">
                        <Link
                            href="/features"
                            class="text-sm font-medium text-slate-600 hover:text-slate-900"
                            >Features</Link
                        >
                        <Link
                            href="/pricing"
                            class="text-sm font-medium text-slate-600 hover:text-slate-900"
                            >Pricing</Link
                        >
                        <Link
                            href="/become-seller"
                            class="text-sm font-medium text-slate-600 hover:text-slate-900"
                            >Become a Seller</Link
                        >
                        <Link
                            href="/about"
                            class="text-sm font-medium text-slate-600 hover:text-slate-900"
                            >About</Link
                        >
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            href="/login"
                            class="text-sm font-medium text-slate-700 hover:text-slate-900"
                            >Sign in</Link
                        >
                        <Link
                            href="/register"
                            class="rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2 text-sm font-medium text-white transition-shadow hover:shadow-md"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <!-- Background decoration -->
            <div class="absolute inset-0 -z-10">
                <div
                    class="absolute top-0 left-1/2 h-[600px] w-[600px] -translate-x-1/2 rounded-full bg-gradient-to-br from-indigo-200/40 via-purple-200/40 to-blue-200/40 blur-3xl"
                ></div>
            </div>

            <div class="mx-auto max-w-7xl px-6 py-20 lg:py-32">
                <div class="mx-auto max-w-4xl text-center">
                    <!-- Badge -->
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-1.5 text-sm font-medium text-indigo-700 ring-1 ring-indigo-200"
                    >
                        <Star class="h-4 w-4" />
                        Built for Bangladeshi schools
                    </div>

                    <h1
                        class="mb-6 text-5xl font-bold tracking-tight text-slate-900 lg:text-7xl"
                    >
                        Your School,<br />
                        <span
                            class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 bg-clip-text text-transparent"
                        >
                            Digitally Transformed
                        </span>
                    </h1>

                    <p
                        class="mx-auto mb-10 max-w-2xl text-xl leading-relaxed text-slate-600"
                    >
                        Manage students, attendance, fees, and more — all in one
                        platform built for Bangladeshi schools, tuition centers,
                        and coaching institutes.
                    </p>

                    <div
                        class="mb-8 flex flex-col justify-center gap-4 sm:flex-row"
                    >
                        <Link
                            href="/register"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg transition-all hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl"
                        >
                            Start 14-Day Free Trial
                            <ArrowRight class="h-5 w-5" />
                        </Link>
                        <Link
                            href="/features"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border-2 border-slate-200 bg-white px-8 py-3.5 text-base font-semibold text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-50"
                        >
                            See Features
                        </Link>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-center gap-6 text-sm text-slate-600"
                    >
                        <div class="flex items-center gap-2">
                            <Check class="h-4 w-4 text-green-600" />
                            No credit card required
                        </div>
                        <div class="flex items-center gap-2">
                            <Check class="h-4 w-4 text-green-600" />
                            Setup in 5 minutes
                        </div>
                        <div class="flex items-center gap-2">
                            <Check class="h-4 w-4 text-green-600" />
                            Cancel anytime
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="border-y bg-slate-50">
            <div class="mx-auto max-w-7xl px-6 py-16">
                <div class="grid grid-cols-2 gap-8 text-center md:grid-cols-4">
                    <div>
                        <div class="text-4xl font-bold text-slate-900">
                            100+
                        </div>
                        <div class="mt-1 text-sm text-slate-600">
                            Active Schools
                        </div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-slate-900">
                            50K+
                        </div>
                        <div class="mt-1 text-sm text-slate-600">
                            Students Managed
                        </div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-slate-900">
                            99.9%
                        </div>
                        <div class="mt-1 text-sm text-slate-600">Uptime</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-slate-900">
                            24/7
                        </div>
                        <div class="mt-1 text-sm text-slate-600">
                            Bangla Support
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t bg-white">
            <div class="mx-auto max-w-7xl px-6 py-12">
                <div
                    class="flex flex-col items-center justify-between gap-4 md:flex-row"
                >
                    <p class="text-sm text-slate-600">
                        © 2026 EduSaaS. Made with ❤️ in Bangladesh.
                    </p>
                    <div class="flex items-center gap-6 text-sm text-slate-600">
                        <a href="#" class="hover:text-slate-900">Terms</a>
                        <a href="#" class="hover:text-slate-900">Privacy</a>
                        <a href="#" class="hover:text-slate-900">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
