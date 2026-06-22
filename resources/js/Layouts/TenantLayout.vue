<template>
    <div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-950">
        <div class="mx-auto max-w-5xl space-y-6 px-4">
            <section
                class="relative rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <!-- theme toggle (top-right) -->
                <div class="absolute top-6 right-6">
                    <ThemeToggler />
                </div>

                <Link
                    href="/dashboard"
                    class="hover:border-brand-300 hover:bg-brand-50 dark:hover:border-brand-700 dark:hover:bg-brand-900/20 flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 text-gray-400 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500"
                >
                    <img
                        v-if="tenant.logo"
                        :src="tenant.logo"
                        :alt="tenant.name"
                        class="h-full w-full object-cover"
                    />
                    <Image v-else class="h-6 w-6" />
                </Link>
                <div class="mt-4 flex items-center gap-3">
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ tenant.name }}
                    </h1>
                    <span
                        :class="statusPill(tenant.status)"
                        class="flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-current" />
                        {{ tenant.status }}
                    </span>
                </div>

                <p
                    class="mt-1 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
                >
                    <Globe class="h-4 w-4" /> {{ tenant.domain }}
                </p>

                <div class="mt-5 flex gap-3">
                    <Link
                        href="/workspace"
                        class="bg-brand-500 hover:bg-brand-600 flex flex-1 items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold text-white"
                    >
                        <ExternalLink class="h-4 w-4" /> Login as tenant
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-200 py-3 text-sm font-medium text-gray-600 hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-red-900/50 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                    >
                        <LogOut class="h-4 w-4" /> Logout
                    </Link>
                </div>
            </section>

            <!-- ── Page content ── -->
            <slot />
        </div>
    </div>
</template>

<script setup>
import ThemeToggler from '@/components/common/ThemeToggler.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ExternalLink, Globe, Image, LogOut } from 'lucide-vue-next';
import { computed } from 'vue';
import { useSidebar } from '../composables/useSidebar.js';
const { toggleSidebar, toggleMobileSidebar } = useSidebar();

const page = usePage();
const tenant = computed(
    () => page.props.tenant ?? page.props.auth?.tenant ?? {},
);

const statusPill = (s) =>
    ({
        active: 'text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400',
        trial: 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400',
        suspended:
            'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400',
        expired:
            'text-gray-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400',
    })[String(s).toLowerCase()] ??
    'text-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-gray-400';
</script>
