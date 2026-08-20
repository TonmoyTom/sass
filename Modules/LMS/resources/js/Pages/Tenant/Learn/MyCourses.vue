<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { Link } from '@inertiajs/vue3';
import { BookOpen, CheckCircle2, PlayCircle, Search, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    enrollments: Array,
});

const stats = computed(() => ({
    total: props.enrollments.length,
    active: props.enrollments.filter((e) => e.status === 'active').length,
    completed: props.enrollments.filter((e) => e.status === 'completed').length,
    avgProgress: props.enrollments.length
        ? Math.round(
              props.enrollments.reduce((sum, e) => sum + e.progress, 0) / props.enrollments.length,
          )
        : 0,
}));

const activeFilter = ref('all');

const filters = computed(() => [
    { key: 'all', label: 'All', count: props.enrollments.length },
    {
        key: 'active',
        label: 'In progress',
        count: props.enrollments.filter((e) => e.status === 'active').length,
    },
    {
        key: 'completed',
        label: 'Completed',
        count: props.enrollments.filter((e) => e.status === 'completed').length,
    },
]);

const filteredEnrollments = computed(() => {
    if (activeFilter.value === 'all') return props.enrollments;
    return props.enrollments.filter((e) => e.status === activeFilter.value);
});

const statusBadge = (status) => {
    if (status === 'completed') {
        return {
            label: 'Completed',
            class: 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
        };
    }
    if (status === 'cancelled') {
        return {
            label: 'Cancelled',
            class: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
        };
    }
    return {
        label: 'In progress',
        class: 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
    };
};
</script>

<template>
    <WorkspaceLayout title="My Courses">
        <div class="mx-auto">
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">My Courses</h1>
                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                    Track your progress across every course you're enrolled in.
                </p>
            </div>

            <!-- stat cards -->
            <div v-if="enrollments.length" class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 flex h-9 w-9 items-center justify-center rounded-lg">
                        <BookOpen class="h-4.5 w-4.5" />
                    </div>
                    <p class="mt-3 text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.total }}</p>
                    <p class="text-xs text-gray-400">Total enrolled</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-500 dark:bg-amber-500/10 dark:text-amber-400">
                        <PlayCircle class="h-4.5 w-4.5" />
                    </div>
                    <p class="mt-3 text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.active }}</p>
                    <p class="text-xs text-gray-400">In progress</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 flex h-9 w-9 items-center justify-center rounded-lg">
                        <CheckCircle2 class="h-4.5 w-4.5" />
                    </div>
                    <p class="mt-3 text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.completed }}</p>
                    <p class="text-xs text-gray-400">Completed</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 text-purple-500 dark:bg-purple-500/10 dark:text-purple-400">
                        <TrendingUp class="h-4.5 w-4.5" />
                    </div>
                    <p class="mt-3 text-xl font-bold text-gray-800 dark:text-white/90">{{ stats.avgProgress }}%</p>
                    <p class="text-xs text-gray-400">Avg. progress</p>
                </div>
            </div>

            <!-- main card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <!-- filters -->
                <div v-if="enrollments.length" class="flex gap-2 border-b border-gray-100 dark:border-gray-800">
                    <button
                        v-for="f in filters"
                        :key="f.key"
                        type="button"
                        class="border-b-2 px-1 py-3 text-sm font-semibold whitespace-nowrap"
                        :class="
                            activeFilter === f.key
                                ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                        "
                        @click="activeFilter = f.key"
                    >
                        {{ f.label }}
                        <span class="ml-1 text-xs text-gray-400">({{ f.count }})</span>
                    </button>
                </div>

            <!-- enrolled course grid -->
                <div
                    v-if="filteredEnrollments.length"
                    class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Link
                        v-for="enrollment in filteredEnrollments"
                        :key="enrollment.id"
                        :href="learnRoutes.myCourseShow(enrollment.course_id)"
                        class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-transparent hover:shadow-[0_20px_40px_-16px_rgba(70,95,255,0.3)] dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <img
                                v-if="enrollment.course_thumbnail"
                                :src="enrollment.course_thumbnail"
                                :alt="enrollment.course_title"
                                class="h-full w-full object-cover object-top transition duration-300 group-hover:scale-105"
                            />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <BookOpen class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                        </div>

                        <span
                            class="absolute top-3 left-3 rounded-full px-2.5 py-1 text-[11px] font-semibold"
                            :class="statusBadge(enrollment.status).class"
                        >
                            {{ statusBadge(enrollment.status).label }}
                        </span>
                    </div>

                    <div class="p-4">
                        <p
                            v-if="enrollment.category_name"
                            class="text-brand-500 dark:text-brand-400 text-[11px] font-semibold tracking-wide uppercase"
                        >
                            {{ enrollment.category_name }}
                        </p>
                        <h3 class="mt-1 line-clamp-2 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ enrollment.course_title }}
                        </h3>

                        <!-- progress -->
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Progress</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-200">
                                    {{ enrollment.progress }}%
                                </span>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div
                                    class="h-full rounded-full transition-all"
                                    :class="enrollment.status === 'completed' ? 'bg-success-500' : 'bg-brand-500'"
                                    :style="{ width: `${enrollment.progress}%` }"
                                />
                            </div>
                        </div>

                        <div
                            class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800"
                        >
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                Enrolled {{ enrollment.enrolled_at }}
                            </span>
                            <span
                                class="text-brand-500 flex items-center gap-1 text-xs font-semibold"
                            >
                                <CheckCircle2 v-if="enrollment.status === 'completed'" class="h-3.5 w-3.5" />
                                <PlayCircle v-else class="h-3.5 w-3.5" />
                                {{ enrollment.status === 'completed' ? 'Review' : 'Continue' }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- empty state (no enrollments at all) -->
            <div
                v-else-if="!enrollments.length"
                class="mt-10 rounded-2xl border border-dashed border-gray-200 py-20 text-center dark:border-gray-800"
            >
                <BookOpen class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    You haven't enrolled in any courses yet
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Browse the catalog and find something to learn.
                </p>
                <Link
                    :href="learnRoutes.browse"
                    class="bg-brand-500 hover:bg-brand-600 mt-5 inline-flex items-center gap-1.5 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition"
                >
                    <Search class="h-4 w-4" />
                    Browse courses
                </Link>
            </div>

            <!-- empty state (filter has no matches) -->
            <div
                v-else
                class="mt-10 rounded-2xl border border-dashed border-gray-200 py-16 text-center dark:border-gray-800"
            >
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No courses match this filter.
                </p>
            </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>