<script setup>
import SeoHead from '@/Components/seo/SeoHead.vue';
import LearnFooter from '@modules/LMS/resources/js/Components/Learn/LearnFooter.vue';
import LearnHeader from '@modules/LMS/resources/js/Components/Learn/LearnHeader.vue';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { Link, router } from '@inertiajs/vue3';
import { BookOpen, Search, SlidersHorizontal, Users, X } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

const props = defineProps({
    courses: Object,
    categories: Array,
    filters: Object,
    seo: {
        type: Object,
        default: () => ({}),
    },
});

const localFilters = reactive({
    search: props.filters.search ?? '',
    category_id: props.filters.category_id ?? '',
    is_free: props.filters.is_free ?? '',
    sort: `${props.filters.sort_by ?? 'created_at'}:${props.filters.sort_dir ?? 'desc'}`,
});

const filtersOpen = ref(false);

const hasActiveFilters = () =>
    !!(localFilters.search || localFilters.category_id || localFilters.is_free);

let debounceTimer = null;

const apply = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const [sort_by, sort_dir] = localFilters.sort.split(':');

        // window.location.pathname used (not route()) so this keeps working
        // regardless of the tenant subdomain wildcard route param.
        router.get(
            window.location.pathname,
            {
                search: localFilters.search || undefined,
                category_id: localFilters.category_id || undefined,
                is_free: localFilters.is_free,
                sort_by,
                sort_dir,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
};

const clearFilters = () => {
    localFilters.search = '';
    localFilters.category_id = '';
    localFilters.is_free = '';
    localFilters.sort = 'created_at:desc';
    apply();
};

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="min-h-screen bg-white dark:bg-gray-950">
        <LearnHeader />

        <!-- ── Page header ── -->
        <section class="border-b border-gray-100 py-10 dark:border-gray-800">
            <div class="mx-auto max-w-6xl px-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">
                    Browse courses
                </h1>
                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                    {{ courses.total }}
                    {{ courses.total === 1 ? 'course' : 'courses' }} available
                </p>
            </div>
        </section>

        <!-- ── Filter bar ── -->
        <section
            class="sticky top-16 z-30 border-b border-gray-100 bg-white/90 py-4 backdrop-blur-md dark:border-gray-800 dark:bg-gray-950/90"
        >
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- search -->
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 px-3">
                                <Search class="h-4 w-4 text-gray-400" />
                        </div>
                        <input
                            v-model="localFilters.search"
                            type="text"
                            placeholder="Search courses..."
                            class="focus:border-brand-300 dark:focus:border-brand-500 h-11 w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm text-gray-800 transition outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            @input="apply"
                        />
                    </div>

                    <!-- mobile filter toggle -->
                    <button
                        type="button"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 text-sm font-medium text-gray-700 sm:hidden dark:border-gray-700 dark:text-gray-200"
                        @click="filtersOpen = !filtersOpen"
                    >
                        <SlidersHorizontal class="h-4 w-4 px-2" />
                        Filters
                    </button>

                    <!-- desktop filters -->
                    <div class="hidden items-center gap-3 sm:flex">
                        <select
                            v-model="localFilters.category_id"
                            class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                            @change="apply"
                        >
                            <option value="">All categories</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>

                        <select
                            v-model="localFilters.is_free"
                            class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                            @change="apply"
                        >
                            <option value="">Free & paid</option>
                            <option value="1">Free only</option>
                            <option value="0">Paid only</option>
                        </select>

                        <select
                            v-model="localFilters.sort"
                            class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                            @change="apply"
                        >
                            <option value="created_at:desc">Newest first</option>
                            <option value="title:asc">Title A–Z</option>
                            <option value="price:asc">Price: low to high</option>
                            <option value="price:desc">Price: high to low</option>
                        </select>

                        <button
                            v-if="hasActiveFilters()"
                            type="button"
                            class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            @click="clearFilters"
                        >
                            <X class="h-3.5 w-3.5" />
                            Clear
                        </button>
                    </div>
                </div>

                <!-- mobile filters (collapsible) -->
                <div v-if="filtersOpen" class="mt-3 grid grid-cols-2 gap-3 sm:hidden">
                    <select
                        v-model="localFilters.category_id"
                        class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        @change="apply"
                    >
                        <option value="">All categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>

                    <select
                        v-model="localFilters.is_free"
                        class="h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        @change="apply"
                    >
                        <option value="">Free & paid</option>
                        <option value="1">Free only</option>
                        <option value="0">Paid only</option>
                    </select>

                    <select
                        v-model="localFilters.sort"
                        class="col-span-2 h-11 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        @change="apply"
                    >
                        <option value="created_at:desc">Newest first</option>
                        <option value="title:asc">Title A–Z</option>
                        <option value="price:asc">Price: low to high</option>
                        <option value="price:desc">Price: high to low</option>
                    </select>

                    <button
                        v-if="hasActiveFilters()"
                        type="button"
                        class="col-span-2 flex items-center justify-center gap-1 rounded-xl border border-gray-200 py-2 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400"
                        @click="clearFilters"
                    >
                        <X class="h-3.5 w-3.5" />
                        Clear filters
                    </button>
                </div>
            </div>
        </section>

        <!-- ── Results ── -->
        <section class="py-10">
            <div class="mx-auto max-w-6xl px-6">
                <div
                    v-if="courses.data.length"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <Link
                        v-for="course in courses.data"
                        :key="course.id"
                        :href="learnRoutes.courseShow(course.id)"
                        class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-transparent hover:shadow-[0_20px_40px_-16px_rgba(70,95,255,0.3)] dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800"
                        >
                            <img
                                v-if="course.thumbnail"
                                :src="course.thumbnail"
                                :alt="course.title"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center"
                            >
                                <BookOpen class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                            </div>

                            <span
                                v-if="course.is_enrolled"
                                class="bg-success-500 absolute top-3 left-3 rounded-full px-2.5 py-1 text-[11px] font-semibold text-white"
                            >
                                Enrolled
                            </span>
                            <span
                                v-else-if="course.is_free"
                                class="absolute top-3 left-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-gray-700"
                            >
                                Free
                            </span>
                        </div>

                        <div class="p-4">
                            <p
                                v-if="course.category_name"
                                class="text-brand-500 dark:text-brand-400 text-[11px] font-semibold tracking-wide uppercase"
                            >
                                {{ course.category_name }}
                            </p>
                            <h3
                                class="mt-1 line-clamp-2 text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                {{ course.title }}
                            </h3>
                            <p
                                v-if="course.instructor_name"
                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ course.instructor_name }}
                            </p>

                            <div
                                class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800"
                            >
                                <span
                                    v-if="course.is_free"
                                    class="text-success-600 dark:text-success-400 text-sm font-bold"
                                >
                                    Free
                                </span>
                                <span
                                    v-else
                                    class="text-sm font-bold text-gray-900 dark:text-white"
                                >
                                    ৳{{ money(course.price) }}
                                </span>
                                <span
                                    class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500"
                                >
                                    <Users class="h-3.5 w-3.5" />
                                    {{ course.enrollments_count }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- empty state -->
                <div
                    v-else
                    class="rounded-2xl border border-dashed border-gray-200 py-20 text-center dark:border-gray-800"
                >
                    <BookOpen class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                    <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        No courses match your filters
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Try a different search term or clear your filters.
                    </p>
                    <button
                        v-if="hasActiveFilters()"
                        type="button"
                        class="bg-brand-500 hover:bg-brand-600 mt-5 inline-flex items-center gap-1.5 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" />
                        Clear filters
                    </button>
                </div>

                <!-- pagination -->
                <div
                    v-if="courses.links?.length > 3"
                    class="mt-10 flex flex-wrap justify-center gap-1"
                >
                    <template v-for="(link, i) in courses.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            preserve-scroll
                            preserve-state
                            class="rounded-lg px-3 py-1.5 text-sm font-medium"
                            :class="
                                link.active
                                    ? 'bg-brand-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'
                            "
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="cursor-default rounded-lg px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600"
                        />
                    </template>
                </div>
            </div>
        </section>

        <LearnFooter />
    </div>
</template>
