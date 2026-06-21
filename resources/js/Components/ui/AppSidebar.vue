<template>
    <aside
        :class="[
            'fixed top-0 left-0 z-99999 mt-16 flex h-screen flex-col border-r border-gray-200 bg-white px-5 text-gray-900 transition-all duration-300 ease-in-out lg:mt-0 dark:border-gray-800 dark:bg-gray-900',
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
        <div
            :class="[
                'flex py-8',
                !isExpanded && !isHovered
                    ? 'lg:justify-center'
                    : 'justify-start',
            ]"
        >
            <IconLink
                href="/"
                text="ALLSPHERE"
                tagline="All-in-One Business Ecosystem Platform"
                :icon="FullLogo"
                :show-text="showFullLogo"
            />
        </div>
        <div
            class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear"
        >
            <nav class="mb-6">
                <div class="flex flex-col gap-4">
                    <div
                        v-for="(menuGroup, groupIndex) in menuGroups"
                        :key="groupIndex"
                    >
                        <h2
                            :class="[
                                'mb-4 flex text-xs leading-[20px] text-gray-400 uppercase',
                                !isExpanded && !isHovered
                                    ? 'lg:justify-center'
                                    : 'justify-start',
                            ]"
                        >
                            <template
                                v-if="isExpanded || isHovered || isMobileOpen"
                            >
                                {{ menuGroup.title }}
                            </template>
                            <HorizontalDots v-else />
                        </h2>
                        <ul class="flex flex-col gap-4">
                            <li
                                v-for="(item, index) in menuGroup.items"
                                :key="item.name"
                            >
                                <button
                                    v-if="item.subItems"
                                    @click="toggleSubmenu(groupIndex, index)"
                                    :class="[
                                        'menu-item group w-full',
                                        {
                                            'menu-item-active': isSubmenuOpen(
                                                groupIndex,
                                                index,
                                            ),
                                            'menu-item-inactive':
                                                !isSubmenuOpen(
                                                    groupIndex,
                                                    index,
                                                ),
                                        },
                                        !isExpanded && !isHovered
                                            ? 'lg:justify-center'
                                            : 'lg:justify-start',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            isSubmenuOpen(groupIndex, index)
                                                ? 'menu-item-icon-active'
                                                : 'menu-item-icon-inactive',
                                        ]"
                                    >
                                        <component :is="item.icon" />
                                    </span>
                                    <span
                                        v-if="
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen
                                        "
                                        class="menu-item-text"
                                        >{{ item.name }}</span
                                    >
                                    <ChevronDownIcon
                                        v-if="
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen
                                        "
                                        :class="[
                                            'ml-auto h-5 w-5 transition-transform duration-200',
                                            {
                                                'text-brand-500 rotate-180':
                                                    isSubmenuOpen(
                                                        groupIndex,
                                                        index,
                                                    ),
                                            },
                                        ]"
                                    />
                                </button>
                                <Link
                                    v-else-if="item.path"
                                    :href="item.path"
                                    :class="[
                                        'menu-item group',
                                        {
                                            'menu-item-active': isActive(
                                                item.path,
                                            ),
                                            'menu-item-inactive': !isActive(
                                                item.path,
                                            ),
                                        },
                                    ]"
                                >
                                    <span
                                        :class="[
                                            isActive(item.path)
                                                ? 'menu-item-icon-active'
                                                : 'menu-item-icon-inactive',
                                        ]"
                                    >
                                        <component :is="item.icon" />
                                    </span>
                                    <span
                                        v-if="
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen
                                        "
                                        class="menu-item-text"
                                        >{{ item.name }}</span
                                    >
                                </Link>
                                <transition
                                    @enter="startTransition"
                                    @after-enter="endTransition"
                                    @before-leave="startTransition"
                                    @after-leave="endTransition"
                                >
                                    <div
                                        v-show="
                                            isSubmenuOpen(groupIndex, index) &&
                                            (isExpanded ||
                                                isHovered ||
                                                isMobileOpen)
                                        "
                                    >
                                        <ul class="mt-2 ml-9 space-y-1">
                                            <li
                                                v-for="subItem in item.subItems"
                                                :key="subItem.name"
                                            >
                                                <Link
                                                    :href="subItem.path"
                                                    :class="[
                                                        'menu-dropdown-item',
                                                        {
                                                            'menu-dropdown-item-active':
                                                                isActive(
                                                                    subItem.path,
                                                                ),
                                                            'menu-dropdown-item-inactive':
                                                                !isActive(
                                                                    subItem.path,
                                                                ),
                                                        },
                                                    ]"
                                                >
                                                    {{ subItem.name }}
                                                    <span
                                                        class="ml-auto flex items-center gap-1"
                                                    >
                                                        <span
                                                            v-if="subItem.new"
                                                            :class="[
                                                                'menu-dropdown-badge',
                                                                {
                                                                    'menu-dropdown-badge-active':
                                                                        isActive(
                                                                            subItem.path,
                                                                        ),
                                                                    'menu-dropdown-badge-inactive':
                                                                        !isActive(
                                                                            subItem.path,
                                                                        ),
                                                                },
                                                            ]"
                                                        >
                                                            new
                                                        </span>
                                                        <span
                                                            v-if="subItem.pro"
                                                            :class="[
                                                                'menu-dropdown-badge',
                                                                {
                                                                    'menu-dropdown-badge-active':
                                                                        isActive(
                                                                            subItem.path,
                                                                        ),
                                                                    'menu-dropdown-badge-inactive':
                                                                        !isActive(
                                                                            subItem.path,
                                                                        ),
                                                                },
                                                            ]"
                                                        >
                                                            pro
                                                        </span>
                                                    </span>
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </transition>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </aside>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, h } from 'vue';

import {
    ChevronDown as ChevronDownIcon,
    LayoutGrid as GridIcon,
    MoreHorizontal as HorizontalDots,
} from 'lucide-vue-next';

import { useSidebar } from '@/composables/useSidebar';
import IconLink from '../ui/IconLink.vue';

const page = usePage();
const currentPath = computed(() => new URL(page.url, 'http://x').pathname);

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();

const showFullLogo = computed(
    () => isExpanded.value || isHovered.value || isMobileOpen.value,
);

const FullLogo = {
    render() {
        return [
            h('img', {
                class: 'dark:hidden',
                src: '/logo/allsphare_logo.png',
                alt: 'Logo',
                width: 32,
                height: 32,
            }),
            h('img', {
                class: 'hidden dark:block',
                src: '/logo/allsphare_logo.png',
                alt: 'Logo',
                width: 32,
                height: 32,
            }),
        ];
    },
};

const IconLogo = {
    render() {
        return h('img', {
            src: '/logo/allsphare_logo.png',
            alt: 'Logo',
            width: 32,
            height: 32,
        });
    },
};

const menuGroups = [
    {
        title: 'Menu',
        items: [
            { icon: GridIcon, name: 'Dashboard', path: '/dashboard' },
            {
                name: 'Tenants',
                icon: GridIcon,
                subItems: [
                    {
                        name: 'Create',
                        path: '/admin/tenants/create',
                        pro: false,
                    },
                    { name: 'List', path: '/admin/tenants', pro: false },
                ],
            },
            {
                name: 'Sellers',
                icon: GridIcon,
                subItems: [
                    {
                        name: 'Create',
                        path: '/admin/sellers/create',
                        pro: false,
                    },
                    { name: 'List', path: '/admin/sellers', pro: false },
                    {
                        name: 'Request',
                        path: '/admin/module-requests',
                        pro: false,
                    },
                    {
                        name: 'Commission',
                        path: '/admin/commissions',
                        pro: false,
                    },
                ],
            },
            {
                name: 'Modules',
                icon: GridIcon,
                subItems: [
                    {
                        name: 'Create',
                        path: '/admin/modules/create',
                        pro: false,
                    },
                    { name: 'List', path: '/admin/modules', pro: false },
                ],
            },
            {
                name: 'Orders',
                icon: GridIcon,
                subItems: [
                    {
                        name: 'List',
                        path: '/admin/orders',
                        pro: false,
                    },
                ],
            },
            {
                name: 'Reports',
                icon: GridIcon,
                subItems: [
                    {
                        name: 'List',
                        path: '/admin/reports',
                        pro: false,
                    },
                ],
            },
        ],
    },
];

const isActive = (path) => currentPath.value === path;

const toggleSubmenu = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`;
    openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isAnySubmenuRouteActive = computed(() => {
    return menuGroups.some((group) =>
        group.items.some(
            (item) =>
                item.subItems &&
                item.subItems.some((subItem) => isActive(subItem.path)),
        ),
    );
});

const isSubmenuOpen = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`;
    return (
        openSubmenu.value === key ||
        (isAnySubmenuRouteActive.value &&
            menuGroups[groupIndex].items[itemIndex].subItems?.some((subItem) =>
                isActive(subItem.path),
            ))
    );
};

const startTransition = (el) => {
    el.style.height = 'auto';
    const height = el.scrollHeight;
    el.style.height = '0px';
    el.offsetHeight;
    el.style.height = height + 'px';
};

const endTransition = (el) => {
    el.style.height = '';
};
</script>
