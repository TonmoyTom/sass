<template>
    <WorkspaceLayout title="Quizzes">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Quizzes
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Create quizzes and attach them to lessons.
                    </p>
                </div>
                <Link
                    href="/lms/quizzes/create"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition"
                >
                    <Plus class="h-4 w-4" />
                    New Quiz
                </Link>
            </div>

            <DataTable
                :data="quizzes"
                :filters="filters"
                :columns="[
                    { key: 'title', label: 'Quiz', sortable: true },
                    { key: 'questions', label: 'Questions' },
                    { key: 'passing', label: 'Passing Score' },
                    { key: 'attempts', label: 'Max Attempts' },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #row="{ row: q }">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800 dark:text-white/90">
                            {{ q.title }}
                        </p>
                        <p
                            v-if="q.time_limit_minutes"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ q.time_limit_minutes }} min limit
                        </p>
                    </td>
                    <td
                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ q.questions_count }} question{{
                            q.questions_count === 1 ? '' : 's'
                        }}
                    </td>
                    <td
                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ q.passing_score }}%
                    </td>
                    <td
                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{
                            q.max_attempts === 0 ? 'Unlimited' : q.max_attempts
                        }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <Link
                            :href="`/lms/quizzes/${q.id}/edit`"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                        >
                            Edit
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
import { Plus } from 'lucide-vue-next';

defineProps({
    quizzes: Object,
    filters: Object,
});
</script>
