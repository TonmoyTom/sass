<template>
    <WorkspaceLayout title="Assignments">
        <div class="mx-auto">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    Assignments
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Every assignment across your courses. Add or edit from a course's Edit page.
                </p>
            </div>

            <DataTable
                :data="assignments"
                :filters="filters"
                :columns="[
                    { key: 'title', label: 'Assignment', sortable: true },
                    { key: 'course', label: 'Course' },
                    { key: 'due_date', label: 'Due', sortable: true },
                    { key: 'submissions', label: 'Submissions' },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #row="{ row: a }">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800 dark:text-white/90">
                            {{ a.title }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ a.max_score }} points
                        </p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                        <Link
                            v-if="a.course_id"
                            :href="`/lms/courses/${a.course_id}/edit`"
                            class="hover:underline"
                        >
                            {{ a.course_title }}
                        </Link>
                        <span v-else>—</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                        {{ a.due_date ?? 'No deadline' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                        {{ a.graded_count }}/{{ a.submissions_count }} graded
                    </td>
                    <td class="px-4 py-3 text-right">
                        <Link
                            :href="`/lms/assignments/${a.id}/submissions`"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                        >
                            View submissions
                        </Link>
                    </td>
                </template>
            </DataTable>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import DataTable from '@/Components/ui/DataTable.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    assignments: Object,
    filters: Object,
});
</script>