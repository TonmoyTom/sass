<template>
    <WorkspaceLayout :title="isEditing ? 'Edit Quiz' : 'New Quiz'">
        <div class="mx-auto ">
            <div class="mb-6">
                <Link
                    href="/lms/quizzes"
                    class="mb-3 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"
                >
                    ← Back to Quizzes
                </Link>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    {{ isEditing ? 'Edit Quiz' : 'Create Quiz' }}
                </h3>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Quiz settings -->
                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">Quiz Settings</h5>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Title</label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="e.g. Chapter 1 Quiz"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                            <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-500">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description (optional)</label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Time Limit (min)
                                </label>
                                <input
                                    v-model.number="form.time_limit_minutes"
                                    type="number"
                                    min="1"
                                    placeholder="No limit"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Passing Score (%)
                                </label>
                                <input
                                    v-model.number="form.passing_score"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Max Attempts
                                </label>
                                <input
                                    v-model.number="form.max_attempts"
                                    type="number"
                                    min="0"
                                    placeholder="0 = unlimited"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h5 class="font-semibold text-gray-800 dark:text-white/90">
                            Questions ({{ form.questions.length }})
                        </h5>
                        <p v-if="form.errors.questions" class="text-sm text-red-500">{{ form.errors.questions }}</p>
                    </div>

                    <div
                        v-for="(question, qIndex) in form.questions"
                        :key="qIndex"
                        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                    >
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <span class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold">
                                {{ qIndex + 1 }}
                            </span>
                            <div class="flex-1">
                                <textarea
                                    v-model="question.question_text"
                                    rows="2"
                                    placeholder="Enter question text..."
                                    class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                ></textarea>
                            </div>
                            <button
                                type="button"
                                @click="removeQuestion(qIndex)"
                                class="shrink-0 rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>

                        <div class="mb-4 grid grid-cols-2 gap-3 pl-10">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Type</label>
                                <select
                                    v-model="question.type"
                                    @change="onTypeChange(question)"
                                    class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
                                >
                                    <option value="mcq">Multiple Choice</option>
                                    <option value="true_false">True / False</option>
                                    <option value="short_answer">Short Answer</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Points</label>
                                <input
                                    v-model.number="question.points"
                                    type="number"
                                    min="1"
                                    class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
                                />
                            </div>
                        </div>

                        <!-- MCQ / True-False options -->
                        <div v-if="question.type !== 'short_answer'" class="space-y-2 pl-10">
                            <div
                                v-for="(option, oIndex) in question.options"
                                :key="oIndex"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="radio"
                                    :name="`correct-${qIndex}`"
                                    :checked="option.is_correct"
                                    @change="setCorrectOption(question, oIndex)"
                                    class="shrink-0"
                                />
                                <input
                                    v-model="option.option_text"
                                    type="text"
                                    :placeholder="`Option ${oIndex + 1}`"
                                    :disabled="question.type === 'true_false'"
                                    class="h-9 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm disabled:bg-gray-50 dark:border-gray-700 dark:text-white/90 dark:disabled:bg-gray-800"
                                />
                                <button
                                    v-if="question.type === 'mcq' && question.options.length > 2"
                                    type="button"
                                    @click="removeOption(question, oIndex)"
                                    class="shrink-0 text-gray-400 hover:text-red-500"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <button
                                v-if="question.type === 'mcq'"
                                type="button"
                                @click="addOption(question)"
                                class="text-brand-500 text-xs font-medium hover:underline"
                            >
                                + Add option
                            </button>
                        </div>

                        <p v-if="question.type === 'short_answer'" class="pl-10 text-xs text-gray-400">
                            This question will require manual grading by the instructor.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="addQuestion"
                        class="border-brand-300 text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 w-full rounded-xl border-2 border-dashed py-3 text-sm font-medium"
                    >
                        + Add Question
                    </button>
                </div>

                <div class="flex justify-end gap-3">
                    <Link
                        href="/lms/quizzes"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update Quiz' : 'Create Quiz') }}
                    </button>
                </div>
            </form>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Trash2, X } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    quiz: Object,
});

const isEditing = computed(() => !!props.quiz);

const buildQuestionsFromQuiz = () => {
    if (!props.quiz) return [];

    return props.quiz.questions.map((q) => ({
        question_text: q.question_text,
        type: q.type,
        points: q.points,
        options: q.options.map((o) => ({
            option_text: o.option_text,
            is_correct: o.is_correct,
        })),
    }));
};

const form = useForm({
    title: props.quiz?.title ?? '',
    description: props.quiz?.description ?? '',
    time_limit_minutes: props.quiz?.time_limit_minutes ?? '',
    passing_score: props.quiz?.passing_score ?? 60,
    max_attempts: props.quiz?.max_attempts ?? 1,
    questions: buildQuestionsFromQuiz(),
});

const newQuestion = (type = 'mcq') => ({
    question_text: '',
    type,
    points: 1,
    options: type === 'true_false'
        ? [{ option_text: 'True', is_correct: true }, { option_text: 'False', is_correct: false }]
        : [{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }],
});

const addQuestion = () => {
    form.questions.push(newQuestion());
};

const removeQuestion = (index) => {
    form.questions.splice(index, 1);
};

const onTypeChange = (question) => {
    if (question.type === 'true_false') {
        question.options = [
            { option_text: 'True', is_correct: true },
            { option_text: 'False', is_correct: false },
        ];
    } else if (question.type === 'mcq' && question.options.length < 2) {
        question.options = [
            { option_text: '', is_correct: true },
            { option_text: '', is_correct: false },
        ];
    } else if (question.type === 'short_answer') {
        question.options = [];
    }
};

const addOption = (question) => {
    question.options.push({ option_text: '', is_correct: false });
};

const removeOption = (question, index) => {
    question.options.splice(index, 1);
};

const setCorrectOption = (question, index) => {
    question.options.forEach((opt, i) => {
        opt.is_correct = i === index;
    });
};

// notun quiz-e default 1 ta question soho shuru
if (!isEditing.value && form.questions.length === 0) {
    form.questions.push(newQuestion());
}

const submit = () => {
    if (isEditing.value) {
        form.put(`/lms/quizzes/${props.quiz.id}`);
    } else {
        form.post('/lms/quizzes');
    }
};
</script>
