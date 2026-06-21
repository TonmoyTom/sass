<template>
    <WorkspaceLayout title="Dashboard">
        <!-- greeting -->
        <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ greeting }}, {{ user.name }} 👋
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Welcome back to {{ company.name }}.
            </p>
        </div>
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"
                        />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ stats.products }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Products</p>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path
                            d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"
                        />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ stats.orders }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Orders</p>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path
                            d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                        />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ stats.customers }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Customers
                </p>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 mb-3 flex h-11 w-11 items-center justify-center rounded-xl"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="12" y1="1" x2="12" y2="23" />
                        <path
                            d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"
                        />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ stats.revenue }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Revenue</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- quick actions -->
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">
                    Quick Actions
                </h5>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <a
                        v-for="action in quickActions"
                        :key="action.href"
                        :href="action.href"
                        class="hover:border-brand-300 hover:bg-brand-50/30 dark:hover:border-brand-700 flex flex-col items-center gap-2 rounded-xl border border-gray-200 p-4 text-center transition dark:border-gray-700"
                    >
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            v-html="action.icon"
                        />
                        <span
                            class="text-xs font-medium text-gray-700 dark:text-gray-300"
                            >{{ action.label }}</span
                        >
                    </a>
                </div>
            </div>

            <!-- workspace info -->
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">
                    Workspace
                </h5>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400"
                            >Company</span
                        >
                        <span
                            class="font-medium text-gray-800 dark:text-white/90"
                            >{{ company.name }}</span
                        >
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400"
                            >Modules</span
                        >
                        <span
                            class="font-medium text-gray-800 dark:text-white/90"
                            >{{ enabledModules.length }} active</span
                        >
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span
                            v-for="m in enabledModules"
                            :key="m"
                            class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                        >
                            {{ m }}
                        </span>
                    </div>
                </div>
                <a
                    href="/settings"
                    class="mt-5 block rounded-lg border border-gray-200 py-2.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                >
                    Workspace Settings
                </a>
            </div>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    company: Object,
    user: Object,
});

const page = usePage();
const enabledModules = computed(
    () => page.props.workspace?.enabled_modules ?? [],
);

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 17) return 'Good afternoon';
    return 'Good evening';
});
const stats = {
    products: 0,
    orders: 0,
    customers: 0,
    revenue: '৳0',
};

const quickActions = [
    {
        href: '/ecommerce/products',
        label: 'Products',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>',
    },
    {
        href: '/ecommerce/orders',
        label: 'Orders',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>',
    },
    {
        href: '/roles',
        label: 'Roles',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    },
    {
        href: '/staff',
        label: 'Staff',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
    },
    {
        href: '/settings',
        label: 'Settings',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
    },
];
</script>
