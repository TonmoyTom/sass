<script setup>
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    FileText,
    Loader2,
    Paperclip,
    Upload,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    assignment: {
        type: Object,
        required: true,
    },
});

const submittedText = ref(props.assignment.submission?.submitted_text ?? '');
const selectedFile = ref(null);
const fileInputRef = ref(null);
const submitting = ref(false);
const errorMessage = ref(null);

const submission = ref(props.assignment.submission);

const dueDateLabel = computed(() => {
    if (!props.assignment.due_date) return null;
    return new Date(props.assignment.due_date).toLocaleString([], {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
});

const canSubmit = computed(() => {
    if (!props.assignment.is_past_due) return true;
    return props.assignment.allow_late_submission;
});

const onFileChange = (event) => {
    selectedFile.value = event.target.files[0] ?? null;
};

const submit = async () => {
    if (!submittedText.value.trim() && !selectedFile.value) {
        errorMessage.value = 'Add some text or attach a file before submitting.';
        return;
    }

    submitting.value = true;
    errorMessage.value = null;

    try {
        const formData = new FormData();
        formData.append('submitted_text', submittedText.value);
        if (selectedFile.value) formData.append('file', selectedFile.value);

        const { data } = await window.axios.post(
            learnRoutes.submitAssignment(props.assignment.id),
            formData,
        );

        submission.value = {
            submitted_text: submittedText.value,
            file_name: data.file_name,
            file_url: data.file_url,
            submitted_at: data.submitted_at,
            is_late: data.is_late,
            grade: null,
            feedback: null,
            is_graded: false,
        };
        selectedFile.value = null;
        if (fileInputRef.value) fileInputRef.value.value = '';
    } catch (err) {
        errorMessage.value = err.response?.data?.error ?? 'Could not submit. Please try again.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                    <FileText class="text-brand-500 h-4 w-4" />
                    {{ assignment.title }}
                </p>
                <div
                    v-if="assignment.instructions"
                    class="prose prose-sm dark:prose-invert prose-p:text-gray-500 dark:prose-p:text-gray-400 mt-2 max-w-none"
                    v-html="assignment.instructions"
                />
                <a
                    v-if="assignment.file_url"
                    :href="assignment.file_url"
                    target="_blank"
                    rel="noopener"
                    class="mt-2 flex items-center gap-1.5 text-xs"
                >
                    <Paperclip class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                    <span class="text-gray-400">Reference file:</span>
                    <span class="text-brand-500 font-medium hover:underline">
                        {{ assignment.file_name ?? 'Download' }}
                    </span>
                </a>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
            <span>{{ assignment.max_score }} points</span>
            <span v-if="dueDateLabel" class="flex items-center gap-1">
                <Clock class="h-3.5 w-3.5" />
                Due {{ dueDateLabel }}
            </span>
        </div>

        <div
            v-if="assignment.is_past_due && !submission"
            class="mt-3 flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-2.5 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400"
        >
            <AlertTriangle class="h-3.5 w-3.5 shrink-0" />
            {{
                assignment.allow_late_submission
                    ? 'The deadline has passed — late submissions are still accepted.'
                    : 'The deadline has passed and late submissions are not accepted.'
            }}
        </div>

        <!-- already graded — show result -->
        <div v-if="submission?.is_graded" class="mt-4">
            <div class="bg-success-50 dark:bg-success-500/10 flex items-center gap-3 rounded-xl px-4 py-3">
                <CheckCircle2 class="text-success-600 dark:text-success-400 h-5 w-5 shrink-0" />
                <div>
                    <p class="text-success-700 dark:text-success-400 text-sm font-bold">
                        Graded: {{ submission.grade }}/{{ assignment.max_score }}
                    </p>
                    <p v-if="submission.feedback" class="mt-0.5 text-xs text-gray-600 dark:text-gray-300">
                        {{ submission.feedback }}
                    </p>
                </div>
            </div>
        </div>

        <!-- submitted, awaiting grade -->
        <div
            v-else-if="submission"
            class="bg-brand-50 dark:bg-brand-500/10 mt-4 flex items-center gap-3 rounded-xl px-4 py-3"
        >
            <Clock class="text-brand-500 h-5 w-5 shrink-0" />
            <p class="text-brand-700 dark:text-brand-400 text-sm font-semibold">
                Submitted — waiting for review
            </p>
        </div>

        <!-- "your submission" — always visible once submitted, clearly separate from the instructor's reference file above -->
        <div
            v-if="submission"
            class="mt-3 rounded-xl border border-gray-100 p-3.5 dark:border-gray-800"
        >
            <p class="mb-1.5 text-xs font-semibold text-gray-400 uppercase">
                Your submission
                <span v-if="submission.is_late" class="ml-1 normal-case text-amber-500">(late)</span>
            </p>
            <p v-if="submission.submitted_text" class="text-sm whitespace-pre-line text-gray-700 dark:text-gray-300">
                {{ submission.submitted_text }}
            </p>
            <a
                v-if="submission.file_url"
                :href="submission.file_url"
                target="_blank"
                rel="noopener"
                class="text-brand-500 mt-1.5 flex items-center gap-1.5 text-xs font-medium hover:underline"
            >
                <Paperclip class="h-3.5 w-3.5" />
                {{ submission.file_name ?? 'Your attached file' }}
            </a>
            <span v-else-if="submission.file_name" class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-400">
                <Paperclip class="h-3.5 w-3.5" />
                {{ submission.file_name }}
            </span>
            <p class="mt-1.5 text-[11px] text-gray-400">Submitted {{ submission.submitted_at }}</p>
        </div>

        <!-- submission form (also shown for resubmission before grading) -->
        <form v-if="canSubmit && !submission?.is_graded" class="mt-4 space-y-3" @submit.prevent="submit">
            <p v-if="submission" class="text-xs text-gray-400">
                You can resubmit below — this replaces your current submission.
            </p>

            <textarea
                v-model="submittedText"
                rows="4"
                placeholder="Write your answer here..."
                class="focus:border-brand-300 dark:focus:border-brand-500 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />

            <div>
                <input ref="fileInputRef" type="file" class="hidden" @change="onFileChange" />
                <button
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                    @click="fileInputRef.click()"
                >
                    <Paperclip class="h-3.5 w-3.5" />
                    {{ selectedFile ? selectedFile.name : 'Attach a file' }}
                </button>
                <span v-if="submission?.file_name && !selectedFile" class="ml-2 text-xs text-gray-400">
                    Current file: {{ submission.file_name }}
                </span>
            </div>

            <p v-if="errorMessage" class="text-error-500 text-xs">{{ errorMessage }}</p>

            <button
                type="submit"
                :disabled="submitting"
                class="bg-brand-500 hover:bg-brand-600 flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
            >
                <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                <Upload v-else class="h-4 w-4" />
                {{ submitting ? 'Submitting...' : submission ? 'Resubmit' : 'Submit assignment' }}
            </button>
        </form>
    </div>
</template>