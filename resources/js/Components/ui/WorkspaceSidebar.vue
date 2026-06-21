<template>
    <aside
        :class="[
            'fixed top-0 left-0 z-[99999] mt-16 flex h-screen flex-col border-r border-gray-200 bg-white px-4 text-gray-900 transition-all duration-300 ease-in-out lg:mt-0 dark:border-gray-800 dark:bg-gray-900',
            {
                'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
                'lg:w-[90px]': !isExpanded && !isHovered,
                'w-[290px] translate-x-0': isMobileOpen,
                '-translate-x-full': !isMobileOpen,
                'lg:translate-x-0': true,
            },
        ]"
        @mouseenter="!isExpanded && (isHovered = true)"
        @mouseleave="isHovered = false"
    >
        <!-- logo -->
        <div
            class="flex py-7"
            :class="collapsed ? 'lg:justify-center' : 'justify-start'"
        >
            <Link href="/dashboard" class="flex items-center gap-2.5">
                <!-- logo image (thakle) -->
                <img
                    v-if="companyLogo"
                    :src="companyLogo"
                    :alt="companyName"
                    class="h-9 w-9 flex-shrink-0 rounded-lg object-cover"
                />
                <!-- logo na thakle — initial box -->
                <div
                    v-else
                    class="bg-brand-500 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white"
                >
                    {{ companyInitial }}
                </div>
                <span
                    v-if="!collapsed"
                    class="text-brand-600 dark:text-brand-400 truncate text-xl font-bold"
                >
                    {{ companyName }}
                </span>
            </Link>
        </div>

        <div
            class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear"
        >
            <!-- MAIN MENU -->
            <nav v-if="!activeModule">
                <p
                    class="mb-3 px-3 text-xs tracking-wide text-gray-400 uppercase"
                    :class="collapsed ? 'lg:text-center' : ''"
                >
                    <span v-if="!collapsed">Main Menu</span>
                    <span v-else>···</span>
                </p>

                <div class="flex flex-col gap-1">
                    <!-- base items -->
                    <SidebarLink
                        v-for="item in baseItems"
                        :key="item.href"
                        :href="item.href"
                        :label="item.label"
                        :icon="item.icon"
                        :active="isActive(item.href)"
                        :collapsed="collapsed"
                    />

                    <!-- module groups (click → drill down) -->
                    <button
                        v-for="mod in moduleGroups"
                        :key="mod.key"
                        type="button"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                        :class="collapsed ? 'lg:justify-center' : ''"
                        @click="activeModule = mod.key"
                    >
                        <span
                            class="flex h-5 w-5 flex-shrink-0 items-center justify-center"
                            v-html="icons[mod.icon]"
                        />
                        <template v-if="!collapsed">
                            <span class="truncate">{{ mod.label }}</span>
                            <svg
                                class="ml-auto h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M9 18l6-6-6-6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </template>
                    </button>
                </div>
            </nav>

            <!-- MODULE SUB MENU (drill-down) -->
            <nav v-else>
                <!-- back -->
                <button
                    type="button"
                    class="text-brand-600 dark:text-brand-400 mb-2 flex w-full items-center gap-2 border-b border-gray-100 px-2 pb-3 text-sm font-medium dark:border-gray-800"
                    @click="activeModule = null"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M19 12H5M12 19l-7-7 7-7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <span v-if="!collapsed">Back</span>
                </button>

                <p
                    v-if="!collapsed"
                    class="mb-3 px-3 text-xs tracking-wide text-gray-400 uppercase"
                >
                    {{ currentModule.label }}
                </p>

                <div class="flex flex-col gap-1">
                    <SidebarLink
                        v-for="item in currentModule.items"
                        :key="item.href"
                        :href="item.href"
                        :label="item.label"
                        :icon="item.icon"
                        :active="isActive(item.href)"
                        :collapsed="collapsed"
                    />
                </div>
            </nav>
        </div>
    </aside>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useSidebar } from '../../composables/useSidebar.js';
import SidebarLink from './SidebarLink.vue';

const { isExpanded, isHovered, isMobileOpen } = useSidebar();
const page = usePage();

const collapsed = computed(
    () => !isExpanded.value && !isHovered.value && !isMobileOpen.value,
);

const companyName = computed(
    () => page.props.workspace?.company_name ?? 'Workspace',
);
const companyInitial = computed(() =>
    (companyName.value ?? 'W').charAt(0).toUpperCase(),
);
const enabledModules = computed(
    () => page.props.workspace?.enabled_modules ?? [],
);

const activeModule = ref(null);

const isActive = (href) => {
    const current = page.url;
    return current === href || current.startsWith(href + '/');
};

// base (always visible)
const baseItems = [
    { href: '/dashboard', label: 'Dashboard', icon: 'dashboard' },
    { href: '/users', label: 'Users', icon: 'user' },
    { href: '/roles', label: 'Roles', icon: 'shield' },
    { href: '/settings', label: 'Settings', icon: 'settings' },
];

const allModuleGroups = {
    eccomarce: {
        key: 'e-ccomarce',
        label: 'E-commerce',
        icon: 'cart',
        items: [
            {
                href: '/ecommerce/dashboard',
                label: 'Dashboard',
                icon: 'dashboard',
            },
            { href: '/ecommerce/products', label: 'Product', icon: 'box' },
            { href: '/ecommerce/orders', label: 'Orders', icon: 'bag' },
        ],
    },
    pos: {
        key: 'pos',
        label: 'POS',
        icon: 'register',
        items: [
            { href: '/pos/dashboard', label: 'Dashboard', icon: 'dashboard' },
            { href: '/pos/sales', label: 'Sales', icon: 'cart' },
        ],
    },
};

const moduleGroups = computed(() =>
    enabledModules.value.map((m) => allModuleGroups[m]).filter(Boolean),
);

const currentModule = computed(
    () => allModuleGroups[activeModule.value] ?? { label: '', items: [] },
);

const companyLogo = computed(() => page.props.workspace?.logo_url ?? null);
watch(
    () => page.url,
    (url) => {
        const match = Object.keys(allModuleGroups).find(
            (k) => url.startsWith('/' + k + '/') || url.startsWith('/' + k),
        );
        if (match && enabledModules.value.includes(match)) {
            activeModule.value = match;
        }
    },
    { immediate: true },
);

const icons = {
    dashboard:
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>',
    shield: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    settings:
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
    cart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>',
    box: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>',
    bag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/></svg>',
    register:
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h4M15 12h2M7 16h2"/></svg>',
};
</script>
