<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Clock, Download, FileText, Search } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    assignment: Object,
    submissions: Object, // paginated: { data, links, ... }
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

const gradingId = ref(null);

const gradeForm = useForm({
    grade: '',
    feedback: '',
});

const openGrading = (submission) => {
    gradingId.value = submission.id;
    gradeForm.grade = submission.grade ?? '';
    gradeForm.feedback = submission.feedback ?? '';
    gradeForm.clearErrors();
};

const closeGrading = () => {
    gradingId.value = null;
    gradeForm.reset();
};

const submitGrade = (submissionId) => {
    gradeForm.put(`/lms/assignment-submissions/${submissionId}/grade`, {
        preserveScroll: true,
        onSuccess: closeGrading,
    });
};
</script>

<template>
    <WorkspaceLayout title="Submissions">
        <div class="mx-auto">
            <Link
                href="/lms/assignments"
                class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <ArrowLeft class="h-4 w-4" /> All assignments
            </Link>

            <div class="mb-4">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    {{ assignment.title }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ assignment.max_score }} points
                    <span v-if="assignment.due_date"> · Due {{ assignment.due_date }}</span>
                    · {{ submissions.total }} submission{{ submissions.total === 1 ? '' : 's' }}
                </p>
            </div>

            <div class="relative mb-6 max-w-sm">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input
                    v-model="searchTerm"
                    type="text"
                    placeholder="Search by student name..."
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white pr-3 pl-9 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    @input="onSearchInput"
                />
            </div>

            <div v-if="!submissions.data.length" class="rounded-2xl border border-gray-200 bg-white p-10 text-center dark:border-gray-800 dark:bg-white/[0.03]">
                <FileText class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ searchTerm ? 'No submissions match your search.' : 'No submissions yet.' }}
                </p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="submission in submissions.data"
                    :key="submission.id"
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <img
                                :src="submission.student_avatar"
                                :alt="submission.student_name"
                                class="h-10 w-10 shrink-0 rounded-full object-cover"
                            />
                            <div>
                                <p class="font-medium text-gray-800 dark:text-white/90">
                                    {{ submission.student_name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ submission.submitted_at }}
                                    <span v-if="submission.is_late" class="text-amber-500">(late)</span>
                                </p>
                            </div>
                        </div>

                        <span
                            v-if="submission.grade !== null"
                            class="bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400 flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            {{ submission.grade }}/{{ assignment.max_score }}
                        </span>
                        <span
                            v-else
                            class="flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400"
                        >
                            <Clock class="h-3.5 w-3.5" />
                            Pending review
                        </span>
                    </div>

                    <p
                        v-if="submission.submitted_text"
                        class="mt-4 rounded-xl bg-gray-50 p-3 text-sm whitespace-pre-line text-gray-700 dark:bg-white/5 dark:text-gray-300"
                    >
                        {{ submission.submitted_text }}
                    </p>

                    <a
                        v-if="submission.file_path"
                        :href="submission.file_path"
                        target="_blank"
                        rel="noopener"
                        class="text-brand-500 mt-3 inline-flex items-center gap-1.5 text-xs font-medium hover:underline"
                    >
                        <Download class="h-3.5 w-3.5" />
                        {{ submission.file_name ?? 'Download file' }}
                    </a>

                    <div v-if="submission.grade !== null && gradingId !== submission.id" class="mt-4">
                        <p v-if="submission.feedback" class="text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-medium">Feedback:</span> {{ submission.feedback }}
                        </p>
                        <button
                            type="button"
                            class="text-brand-500 mt-2 text-xs font-medium hover:underline"
                            @click="openGrading(submission)"
                        >
                            Edit grade
                        </button>
                    </div>

                    <!-- grading form -->
                    <form
                        v-if="gradingId === submission.id"
                        class="mt-4 space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800"
                        @submit.prevent="submitGrade(submission.id)"
                    >
                        <div class="flex items-center gap-3">
                            <input
                                v-model.number="gradeForm.grade"
                                type="number"
                                min="0"
                                :max="assignment.max_score"
                                placeholder="Grade"
                                class="h-10 w-28 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
                            />
                            <span class="text-sm text-gray-400">/ {{ assignment.max_score }}</span>
                        </div>
                        <p v-if="gradeForm.errors.grade" class="text-error-500 text-xs">
                            {{ gradeForm.errors.grade }}
                        </p>

                        <textarea
                            v-model="gradeForm.feedback"
                            rows="3"
                            placeholder="Feedback for the student (optional)"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:text-white/90"
                        />

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="gradeForm.processing"
                                class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                {{ gradeForm.processing ? 'Saving...' : 'Save grade' }}
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                @click="closeGrading"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                    <button
                        v-else-if="submission.grade === null"
                        type="button"
                        class="bg-brand-500 hover:bg-brand-600 mt-4 rounded-lg px-4 py-2 text-sm font-medium text-white"
                        @click="openGrading(submission)"
                    >
                        Grade this submission
                    </button>
                </div>
            </div>

            <!-- pagination -->
            <div v-if="submissions.links?.length > 3" class="mt-4 flex flex-wrap gap-1">
                <template v-for="(link, i) in submissions.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        preserve-scroll
                        preserve-state
                        class="rounded-lg px-3 py-1.5 text-sm"
                        :class="
                            link.active
                                ? 'bg-brand-500 text-white'
                                : 'border border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.05]'
                        "
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="cursor-default rounded-lg px-3 py-1.5 text-sm text-gray-400 opacity-50"
                    />
                </template>
            </div>
        </div>
    </WorkspaceLayout>
</template>