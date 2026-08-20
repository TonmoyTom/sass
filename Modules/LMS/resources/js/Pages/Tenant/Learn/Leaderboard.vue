<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Award, Medal, Search, Trophy } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    course: Object,
    leaderboard: Array,
    current_student_id: [String, Number],
    filters: Object,
});

const searchTerm = ref(props.filters?.search ?? '');
let searchTimer = null;

const onSearchInput = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            window.location.pathname,
            { search: searchTerm.value },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 350);
};

const medal = (rank) => {
    if (rank === 1) return { icon: Trophy, class: 'text-amber-400' };
    if (rank === 2) return { icon: Medal, class: 'text-gray-400' };
    if (rank === 3) return { icon: Award, class: 'text-amber-700' };
    return null;
};
</script>

<template>
    <WorkspaceLayout title="Leaderboard">
        <div class="mx-auto ">
            <Link
                :href="learnRoutes.myCourseShow(course.id)"
                class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <ArrowLeft class="h-4 w-4" /> Back to course
            </Link>

            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    Leaderboard
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ course.title }} — ranked by quiz + assignment scores
                </p>
            </div>

            <div class="relative mb-4">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input
                    v-model="searchTerm"
                    type="text"
                    placeholder="Search by name..."
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white pr-3 pl-9 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    @input="onSearchInput"
                />
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div v-if="!leaderboard.length" class="p-10 text-center text-sm text-gray-400">
                    {{ searchTerm ? 'No students match your search.' : 'No enrolled students yet.' }}
                </div>

                <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="row in leaderboard"
                        :key="row.student_id"
                        class="flex items-center gap-4 px-4 py-3.5"
                        :class="
                            row.student_id === current_student_id
                                ? 'bg-brand-50 dark:bg-brand-500/10'
                                : ''
                        "
                    >
                        <div class="flex w-8 shrink-0 items-center justify-center">
                            <component
                                :is="medal(row.rank).icon"
                                v-if="medal(row.rank)"
                                class="h-5 w-5"
                                :class="medal(row.rank).class"
                            />
                            <span v-else class="text-sm font-semibold text-gray-400">
                                {{ row.rank }}
                            </span>
                        </div>

                        <img
                            :src="row.avatar"
                            :alt="row.name"
                            class="h-9 w-9 shrink-0 rounded-full object-cover"
                        />

                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-semibold"
                                :class="
                                    row.student_id === current_student_id
                                        ? 'text-brand-700 dark:text-brand-400'
                                        : 'text-gray-800 dark:text-gray-100'
                                "
                            >
                                {{ row.name }}
                                <span v-if="row.student_id === current_student_id" class="text-brand-500 text-xs font-normal">
                                    (you)
                                </span>
                            </p>
                            <p class="text-xs text-gray-400">
                                Quiz {{ row.quiz_score }} + Assignment {{ row.assignment_score }}
                            </p>
                        </div>

                        <p class="shrink-0 text-sm font-bold text-gray-900 dark:text-white">
                            {{ row.total_score }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>