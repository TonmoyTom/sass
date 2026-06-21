<template>
    <TenantLayout title="Dashboard">
        <div class="mx-auto">
            <!-- greeting -->
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    {{ tenant.name ?? 'Your Workspace' }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage your subscription and open your workspace.
                </p>
            </div>

            <!-- Open Workspace hero -->
            <div
                class="from-brand-500 to-brand-600 relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br p-6 text-white lg:p-7"
            >
                <div
                    class="pointer-events-none absolute -top-12 -right-12 h-48 w-48 rounded-full bg-white/10"
                ></div>
                <div
                    class="pointer-events-none absolute -bottom-16 -left-8 h-56 w-56 rounded-full bg-white/[0.06]"
                ></div>

                <div
                    class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 backdrop-blur"
                        >
                            <svg
                                class="h-7 w-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium tracking-wide text-white/60 uppercase"
                            >
                                Your workspace
                            </p>
                            <p class="mt-0.5 font-mono text-base font-semibold">
                                {{ tenant.subdomain ?? tenant.name }}
                            </p>
                            <p class="mt-1.5 text-sm text-white/80">
                                Manage staff, products, orders and more.
                            </p>
                        </div>
                    </div>
                    <a
                        :href="route('tenant.workspace.open')"
                        class="text-brand-600 inline-flex flex-shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold shadow-sm transition hover:bg-white/90"
                    >
                        Open Workspace
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                d="M5 12h14M12 5l7 7-7 7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- stats -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Modules
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ stats.total_modules }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Active
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        {{ stats.active_modules }}
                    </p>
                </div>
                <Link
                    :href="route('tenant.modules.index')"
                    class="text-brand-600 dark:text-brand-400 flex items-center justify-center gap-1.5 rounded-2xl border border-dashed border-gray-300 bg-white p-4 text-sm font-medium transition hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03]"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                    </svg>
                    Browse Modules
                </Link>
            </div>

            <!-- modules -->
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-5 font-semibold text-gray-800 dark:text-white/90">
                    Your Modules
                </h5>

                <div
                    v-if="modules.length"
                    class="grid grid-cols-1 gap-3 md:grid-cols-2"
                >
                    <div
                        v-for="m in modules"
                        :key="m.id"
                        class="flex items-center justify-between rounded-xl border border-gray-200 p-3.5 transition hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <rect
                                        x="3"
                                        y="3"
                                        width="7"
                                        height="7"
                                        rx="1.5"
                                    />
                                    <rect
                                        x="14"
                                        y="3"
                                        width="7"
                                        height="7"
                                        rx="1.5"
                                    />
                                    <rect
                                        x="3"
                                        y="14"
                                        width="7"
                                        height="7"
                                        rx="1.5"
                                    />
                                    <rect
                                        x="14"
                                        y="14"
                                        width="7"
                                        height="7"
                                        rx="1.5"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ m.name }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ m.tier_name
                                    }}<span v-if="m.expires_at">
                                        · until {{ m.expires_at }}</span
                                    >
                                </p>
                            </div>
                        </div>

                        <span
                            :class="
                                m.is_expired
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                    : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                            "
                            class="rounded-full px-2.5 py-1 text-xs font-medium"
                        >
                            {{ m.is_expired ? 'Expired' : 'Active' }}
                        </span>
                    </div>
                </div>

                <div v-else class="py-12 text-center">
                    <div
                        class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800"
                    >
                        <svg
                            class="h-7 w-7 text-gray-400"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                            <rect x="14" y="3" width="7" height="7" rx="1.5" />
                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No modules yet.
                    </p>
                    <Link
                        :href="route('tenant.modules.index')"
                        class="bg-brand-500 hover:bg-brand-600 mt-4 inline-block rounded-lg px-5 py-2.5 text-sm font-medium text-white"
                    >
                        Browse Modules
                    </Link>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    tenant: Object,
    modules: Array,
    stats: Object,
});
</script>
