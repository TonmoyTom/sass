<template>
    <WorkspaceLayout title="Courses">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Courses
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage your courses, lessons, and content.
                    </p>
                </div>
                <Link
                    href="/lms/courses/create"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition"
                >
                    <Plus class="h-4 w-4" />
                    New Course
                </Link>
            </div>

            <DataTable
                :data="courses"
                :filters="filters"
                :columns="[
                    { key: 'course', label: 'Course', sortable: true },
                    { key: 'category', label: 'Category' },
                    { key: 'price', label: 'Price', sortable: true },
                    { key: 'enrollments', label: 'Enrollments' },
                    { key: 'status', label: 'Status' },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #filters="{ filters: f, apply }">
                    <select
                        v-model="f.status"
                        @change="apply"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                    >
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </template>

                <template #row="{ row: c }">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="c.thumbnail"
                                :src="c.thumbnail"
                                class="h-10 w-14 shrink-0 rounded-lg object-cover"
                            />
                            <div
                                v-else
                                class="flex h-10 w-14 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800"
                            >
                                <BookOpen class="h-4 w-4 text-gray-400" />
                            </div>
                            <div>
                                <p
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ c.title }}
                                </p>
                                <p
                                    v-if="c.subcategory_name"
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ c.subcategory_name }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td
                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ c.category_name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span
                            v-if="c.is_free"
                            class="font-medium text-green-600 dark:text-green-400"
                            >Free</span
                        >
                        <span
                            v-else
                            class="font-medium text-gray-800 dark:text-white/90"
                            >৳{{ money(c.price) }}</span
                        >
                    </td>
                    <td
                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ c.enrollments_count }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                            :class="statusClass(c.status)"
                        >
                            {{ c.status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <Link
                            :href="`/lms/courses/${c.id}/edit`"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                        >
                            Manage
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
import { BookOpen, Plus } from 'lucide-vue-next';

defineProps({
    courses: Object,
    filters: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const statusClass = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
        published:
            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        archived:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};
</script>
