<script setup>
import { router, usePage } from '@inertiajs/vue3';
import {
    Book,
    Box,
    LayoutGrid,
    Search,
    Settings,
    Shield,
    ShoppingBag,
    ShoppingCart,
    User,
} from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();

// user-er permission — HandleInertiaRequests theke ashe (WorkspaceSidebar-er
// shathe consistent pattern)
const userPermissions = computed(() => page.props.auth?.permissions ?? []);
const hasPermission = (permission) => {
    if (!permission) return true;
    return userPermissions.value.includes(permission);
};

// ── searchable command list — mirrors WorkspaceSidebar's menu structure ──
const allCommands = [
    { label: 'Dashboard', href: '/dashboard', icon: LayoutGrid, group: 'Main' },
    { label: 'Users', href: '/users', icon: User, group: 'Main', permission: 'users.view' },
    { label: 'Create User', href: '/users/create', icon: User, group: 'Main', permission: 'users.create' },
    { label: 'Roles', href: '/roles', icon: Shield, group: 'Main', permission: 'roles.view' },
    { label: 'Add Role', href: '/roles/create', icon: Shield, group: 'Main', permission: 'roles.create' },
    { label: 'Permissions', href: '/permissions', icon: Shield, group: 'Main', permission: 'roles.view' },
    { label: 'Settings', href: '/settings', icon: Settings, group: 'Main', permission: 'settings.view' },
    { label: 'SEO Settings', href: '/settings/seo', icon: Settings, group: 'Main', permission: 'settings.view' },
    { label: 'Payment Settings', href: '/settings/payment', icon: Settings, group: 'Main', permission: 'settings.view' },
    { label: 'Two-Factor Settings', href: '/settings/two-factor', icon: Settings, group: 'Main' },
    { label: 'My Modules', href: '/my-modules', icon: Box, group: 'Main', permission: 'my-modules.view' },

    { label: 'Instructors', href: '/lms/instructors', icon: User, group: 'LMS', permission: 'instructors.view' },
    { label: 'Category', href: '/lms/categories', icon: Book, group: 'LMS', permission: 'categories.view' },
    { label: 'Sub-Category', href: '/lms/subcategories', icon: Book, group: 'LMS', permission: 'subcategories.view' },
    { label: 'Courses', href: '/lms/courses', icon: Book, group: 'LMS', permission: 'courses.view' },
    { label: 'Quizzes', href: '/lms/quizzes', icon: Box, group: 'LMS', permission: 'quizzes.view' },
    { label: 'Assignments', href: '/lms/assignments', icon: ShoppingBag, group: 'LMS', permission: 'assignments.view' },
    { label: 'My Courses', href: '/lms/my-courses', icon: Book, group: 'LMS', permission: 'lms.my-courses.view' },
    { label: 'My Orders', href: '/lms/my-orders', icon: ShoppingCart, group: 'LMS', permission: 'lms.my-orders.view' },
];

const availableCommands = computed(() => allCommands.filter((c) => hasPermission(c.permission)));

// ── palette state ──
const open = ref(false);
const query = ref('');
const activeIndex = ref(0);
const inputRef = ref(null);

const results = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return availableCommands.value;
    return availableCommands.value.filter((c) => c.label.toLowerCase().includes(q));
});

const openPalette = () => {
    open.value = true;
    query.value = '';
    activeIndex.value = 0;
    nextTick(() => inputRef.value?.focus());
};

const closePalette = () => {
    open.value = false;
};

const go = (command) => {
    closePalette();
    router.visit(command.href);
};

const onGlobalKeydown = (event) => {
    const isCmdK = (event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k';
    if (isCmdK) {
        event.preventDefault();
        open.value ? closePalette() : openPalette();
        return;
    }
    if (!open.value) return;

    if (event.key === 'Escape') {
        closePalette();
    } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        activeIndex.value = Math.min(activeIndex.value + 1, results.value.length - 1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const command = results.value[activeIndex.value];
        if (command) go(command);
    }
};

onMounted(() => window.addEventListener('keydown', onGlobalKeydown));
onUnmounted(() => window.removeEventListener('keydown', onGlobalKeydown));
</script>

<template>
    <div class="hidden lg:block">
        <button
            type="button"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 relative flex h-11 w-full items-center rounded-lg border border-gray-200 bg-transparent py-2.5 pr-14 pl-12 text-left text-sm text-gray-400 xl:w-[430px] dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/30"
            @click="openPalette"
        >
            <span class="absolute top-1/2 left-4 -translate-y-1/2">
                <Search class="h-5 w-5 text-gray-500 dark:text-gray-400" />
            </span>
            <span>Search or type command...</span>
            <span
                class="absolute top-1/2 right-2.5 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-lg border border-gray-200 bg-gray-50 px-[7px] py-[4.5px] text-xs -tracking-[0.2px] text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400"
            >
                <span>⌘</span><span>K</span>
            </span>
        </button>
    </div>

    <!-- command palette -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[99999] flex items-start justify-center bg-gray-950/50 px-4 pt-[12vh] backdrop-blur-sm"
                @click.self="closePalette"
            >
                <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                    <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-3.5 dark:border-gray-800">
                        <Search class="h-4.5 w-4.5 shrink-0 text-gray-400" />
                        <input
                            ref="inputRef"
                            v-model="query"
                            type="text"
                            placeholder="Search pages, settings, courses..."
                            class="w-full bg-transparent text-sm text-gray-800 outline-none placeholder:text-gray-400 dark:text-white"
                            @input="activeIndex = 0"
                        />
                        <kbd class="shrink-0 rounded-md border border-gray-200 px-1.5 py-0.5 text-[10px] text-gray-400 dark:border-gray-700">
                            Esc
                        </kbd>
                    </div>

                    <div class="max-h-80 overflow-y-auto p-2">
                        <div v-if="!results.length" class="py-8 text-center text-sm text-gray-400">
                            No matching pages.
                        </div>

                        <button
                            v-for="(command, i) in results"
                            :key="command.href"
                            type="button"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition"
                            :class="
                                i === activeIndex
                                    ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
                                    : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5'
                            "
                            @mouseenter="activeIndex = i"
                            @click="go(command)"
                        >
                            <component :is="command.icon" class="h-4 w-4 shrink-0" />
                            <span class="flex-1 truncate">{{ command.label }}</span>
                            <span class="shrink-0 text-xs text-gray-400">{{ command.group }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
