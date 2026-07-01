<script setup>
import SeoHead from '@/Components/seo/SeoHead.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    seo: Object,
});

const page = usePage();
const tenant = computed(() => page.props.tenant ?? {});
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- Header -->
        <header
            class="border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4"
            >
                <div class="flex items-center gap-2">
                    <img
                        v-if="tenant.logo"
                        :src="tenant.logo"
                        :alt="tenant.name"
                        class="h-8 w-8 rounded-lg object-cover"
                    />
                    <span
                        class="text-lg font-bold text-gray-900 dark:text-white"
                    >
                        {{ tenant.name || 'Welcome' }}
                    </span>
                </div>

                <Link
                    href="/tenant-login"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2 text-sm font-medium text-white"
                >
                    Login
                </Link>
            </div>
        </header>

        <!-- Hero -->
        <section class="mx-auto max-w-4xl px-6 py-24 text-center">
            <h1
                class="text-4xl font-bold text-gray-900 sm:text-5xl dark:text-white"
            >
                {{ seo.title || tenant.name }}
            </h1>
            <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                {{ seo.description }}
            </p>

            <div class="mt-8 flex items-center justify-center gap-4">
                <Link
                    href="/tenant-login"
                    class="bg-brand-500 hover:bg-brand-600 rounded-xl px-6 py-3 text-sm font-semibold text-white"
                >
                    Get Started
                </Link>
            </div>
        </section>
    </div>
</template>
