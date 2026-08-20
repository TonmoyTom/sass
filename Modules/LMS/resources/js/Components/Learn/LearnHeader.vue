<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown, GraduationCap, LayoutGrid, LogOut } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { useAuthStudent } from '@modules/LMS/resources/js/composables/useAuthStudent.js';

const { isAuthenticated, student } = useAuthStudent();
const page = usePage();

const brand = computed(() => ({
    name:
        page.props.workspace?.company_name ||
        page.props.tenant?.name ||
        'Campus',
    logo: page.props.workspace?.logo_url || page.props.tenant?.logo || null,
}));

const initials = computed(() => {
    const name = student.value?.name ?? '';
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('') || 'U';
});

const scrolled = ref(false);
const onScroll = () => {
    scrolled.value = window.scrollY > 8;
};

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// ── user menu ──
const menuOpen = ref(false);
const menuWrapper = ref(null);

const closeOnClickOutside = (event) => {
    if (menuWrapper.value && !menuWrapper.value.contains(event.target)) {
        menuOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', closeOnClickOutside));
onUnmounted(() => document.removeEventListener('click', closeOnClickOutside));

const logout = () => {
    menuOpen.value = false;
    router.post('/logout');
};
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b bg-white/80 backdrop-blur-md transition-shadow duration-300 dark:bg-gray-950/80"
        :class="
            scrolled
                ? 'border-gray-100 shadow-[0_1px_0_0_rgba(16,24,40,0.04),0_8px_24px_-16px_rgba(16,24,40,0.15)] dark:border-gray-800'
                : 'border-transparent'
        "
    >
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
            <Link :href="learnRoutes.home" class="flex items-center gap-2.5">
                <img
                    v-if="brand.logo"
                    :src="brand.logo"
                    :alt="brand.name"
                    class="h-8 w-8 rounded-lg object-cover"
                />
                <span
                    v-else
                    class="bg-brand-500 flex h-8 w-8 items-center justify-center rounded-lg text-white"
                >
                    <GraduationCap class="h-4.5 w-4.5" />
                </span>
                <span class="text-base font-bold text-gray-900 dark:text-white">
                    {{ brand.name }}
                </span>
            </Link>

            <nav class="hidden items-center gap-8 sm:flex">
                <Link
                    :href="learnRoutes.browse"
                    class="hover:text-brand-600 dark:hover:text-brand-400 text-sm font-medium text-gray-600 transition-colors dark:text-gray-400"
                >
                    Browse courses
                </Link>
                <Link
                    v-if="isAuthenticated"
                    :href="learnRoutes.myCourses"
                    class="hover:text-brand-600 dark:hover:text-brand-400 text-sm font-medium text-gray-600 transition-colors dark:text-gray-400"
                >
                    My courses
                </Link>
            </nav>

            <!-- signed out -->
            <Link
                v-if="!isAuthenticated"
                :href="learnRoutes.login"
                class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                Sign in
            </Link>

            <!-- signed in — user menu -->
            <div v-else ref="menuWrapper" class="relative">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-full border border-gray-200 py-1 pr-3 pl-1 transition hover:border-gray-300 hover:shadow-sm dark:border-gray-700 dark:hover:border-gray-600"
                    @click="menuOpen = !menuOpen"
                >
                    <span
                        class="from-brand-500 to-brand-600 flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br text-xs font-bold text-white"
                    >
                        {{ initials }}
                    </span>
                    <span class="hidden text-sm font-medium text-gray-700 sm:inline dark:text-gray-200">
                        {{ student?.name?.split(' ')[0] ?? 'Account' }}
                    </span>
                    <ChevronDown
                        class="h-3.5 w-3.5 text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': menuOpen }"
                    />
                </button>

                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="menuOpen"
                        class="absolute top-full right-0 mt-2 w-56 rounded-xl border border-gray-100 bg-white py-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="border-b border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                            <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ student?.name }}
                            </p>
                            <p class="truncate text-xs text-gray-400">{{ student?.email }}</p>
                        </div>

                        <Link
                            :href="learnRoutes.myCourses"
                            class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                            @click="menuOpen = false"
                        >
                            <LayoutGrid class="h-4 w-4 text-gray-400" />
                            My courses
                        </Link>

                        <button
                            type="button"
                            class="text-error-600 dark:text-error-400 flex w-full items-center gap-2.5 px-3.5 py-2.5 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/5"
                            @click="logout"
                        >
                            <LogOut class="h-4 w-4" />
                            Sign out
                        </button>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>