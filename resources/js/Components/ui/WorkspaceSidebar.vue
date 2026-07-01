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
                    <template v-for="item in baseItems" :key="item.key">
                        <!-- item WITHOUT sub-items -->
                        <SidebarLink
                            v-if="!item.subItems"
                            :href="item.href"
                            :label="item.label"
                            :icon="item.icon"
                            :active="isActive(item.href)"
                            :collapsed="collapsed"
                        />

                        <!-- item WITH sub-items (accordion dropdown) -->
                        <div v-else>
                            <button
                                type="button"
                                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                                :class="[
                                    isSubmenuActive(item)
                                        ? 'text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10'
                                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]',
                                    collapsed ? 'lg:justify-center' : '',
                                ]"
                                @click="toggleSubmenu(item.key)"
                            >
                                <span
                                    class="flex h-5 w-5 flex-shrink-0 items-center justify-center"
                                >
                                    <component
                                        :is="icons[item.icon]"
                                        class="h-5 w-5"
                                    />
                                </span>
                                <template v-if="!collapsed">
                                    <span class="truncate">{{
                                        item.label
                                    }}</span>
                                    <ChevronRight
                                        class="ml-auto h-4 w-4 flex-shrink-0 transition-transform duration-200"
                                        :class="{
                                            'rotate-90':
                                                openSubmenu === item.key,
                                        }"
                                    />
                                </template>
                            </button>

                            <!-- sub items -->
                            <div
                                v-show="openSubmenu === item.key && !collapsed"
                                class="mt-1 flex flex-col gap-1 pl-8"
                            >
                                <Link
                                    v-for="sub in item.subItems"
                                    :key="sub.href"
                                    :href="sub.href"
                                    class="truncate rounded-lg px-3 py-2 text-sm transition"
                                    :class="
                                        isActive(sub.href)
                                            ? 'text-brand-600 dark:text-brand-400 font-medium'
                                            : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-100'
                                    "
                                >
                                    {{ sub.label }}
                                </Link>
                            </div>
                        </div>
                    </template>

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
                        >
                            <component :is="icons[mod.icon]" class="h-5 w-5" />
                        </span>
                        <template v-if="!collapsed">
                            <span class="truncate">{{ mod.label }}</span>
                            <ChevronRight class="ml-auto h-4 w-4" />
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
                    <ChevronLeft class="h-5 w-5" />
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
import {
    Box,
    ChevronLeft,
    ChevronRight,
    LayoutDashboard,
    Settings,
    Shield,
    ShoppingBag,
    ShoppingCart,
    Store,
    User,
} from 'lucide-vue-next';
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

// kon base item er dropdown open ache
const openSubmenu = ref(null);

const isActive = (href) => {
    const current = page.url;
    return current === href || current.startsWith(href + '/');
};

// base item er kono sub-item active kina check
const isSubmenuActive = (item) =>
    item.subItems?.some((sub) => isActive(sub.href)) ?? false;

const toggleSubmenu = (key) => {
    openSubmenu.value = openSubmenu.value === key ? null : key;
};

// base (always visible) — jegulo te subItems ache segulo dropdown hobe
const baseItems = [
    {
        key: 'dashboard',
        href: '/dashboard',
        label: 'Dashboard',
        icon: 'dashboard',
    },
    {
        key: 'users',
        label: 'Users',
        icon: 'user',
        subItems: [
            { href: '/users', label: 'Lists' },
            { href: '/users/create', label: 'Create User' },
        ],
    },
    {
        key: 'roles',
        label: 'Roles',
        icon: 'shield',
        subItems: [
            { href: '/roles', label: 'All Roles' },
            { href: '/roles/create', label: 'Add Role' },
            { href: '/permissions', label: 'Permissions' },
        ],
    },
    {
        key: 'settings',
        label: 'Settings',
        icon: 'settings',
        subItems: [
            { href: '/settings', label: 'General' },
            { href: '/settings/seo', label: 'Seo' },
        ],
    },
];

// URL match hole related dropdown auto open thakbe
watch(
    () => page.url,
    (url) => {
        const match = baseItems.find((item) =>
            item.subItems?.some(
                (sub) => url === sub.href || url.startsWith(sub.href + '/'),
            ),
        );
        if (match) openSubmenu.value = match.key;
    },
    { immediate: true },
);

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

// icon key -> lucide-vue-next component
const icons = {
    dashboard: LayoutDashboard,
    user: User,
    shield: Shield,
    settings: Settings,
    box: Box,
    bag: ShoppingBag,
    cart: ShoppingCart,
    register: Store,
};
</script>
