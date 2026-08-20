<script setup>
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { CheckCircle2, Clock, HelpCircle, Loader2, RotateCcw, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    quiz: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['submitted']);

// 'idle' -> 'loading' -> 'in_progress' -> 'submitting' -> 'result'
const stage = ref('idle');
const attemptId = ref(null);
const questions = ref([]);
const answers = ref({}); // { [questionId]: selectedOptionId | text }
const result = ref(null); // { score, passed, answers }
const errorMessage = ref(null);

const canAttempt = ref(props.quiz.can_attempt);
const attemptsUsed = ref(props.quiz.attempts_used);
const bestScore = ref(props.quiz.best_score);

const allAnswered = computed(() =>
    questions.value.length > 0 && questions.value.every((q) => answers.value[q.id] !== undefined && answers.value[q.id] !== ''),
);

const startQuiz = async () => {
    stage.value = 'loading';
    errorMessage.value = null;
    try {
        const { data } = await window.axios.post(learnRoutes.quizStart(props.quiz.id));
        attemptId.value = data.attempt_id;
        questions.value = data.questions;
        answers.value = {};
        stage.value = 'in_progress';
    } catch (err) {
        errorMessage.value = err.response?.data?.error ?? 'Could not start the quiz. Try again.';
        stage.value = 'idle';
    }
};

const selectOption = (questionId, optionId) => {
    answers.value[questionId] = optionId;
};

const submitQuiz = async () => {
    stage.value = 'submitting';
    try {
        const payload = {
            answers: questions.value.map((q) => ({
                question_id: q.id,
                selected_option_id: q.type !== 'short_answer' ? answers.value[q.id] : null,
                answer_text: q.type === 'short_answer' ? answers.value[q.id] : null,
            })),
        };
        const { data } = await window.axios.post(learnRoutes.quizSubmit(attemptId.value), payload);
        result.value = data;
        attemptsUsed.value += 1;
        bestScore.value = bestScore.value === null ? data.score : Math.max(bestScore.value, data.score);
        canAttempt.value = props.quiz.max_attempts <= 0 || attemptsUsed.value < props.quiz.max_attempts;
        stage.value = 'result';
        emit('submitted', data);
    } catch {
        errorMessage.value = 'Could not submit the quiz. Please try again.';
        stage.value = 'in_progress';
    }
};

const retry = () => {
    stage.value = 'idle';
    result.value = null;
    questions.value = [];
    answers.value = {};
};

const answerFor = (questionId) => result.value?.answers.find((a) => a.question_id === questionId);
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                    <HelpCircle class="text-brand-500 h-4 w-4" />
                    {{ quiz.title }}
                </p>
                <p v-if="quiz.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ quiz.description }}
                </p>
            </div>
            <span
                v-if="quiz.has_passed || (result && result.passed)"
                class="bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400 shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold"
            >
                Passed
            </span>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
            <span>{{ quiz.question_count }} questions</span>
            <span>Pass mark: {{ quiz.passing_score }}%</span>
            <span v-if="quiz.time_limit_minutes" class="flex items-center gap-1">
                <Clock class="h-3.5 w-3.5" />
                {{ quiz.time_limit_minutes }}m
            </span>
            <span v-if="quiz.max_attempts > 0">
                {{ attemptsUsed }}/{{ quiz.max_attempts }} attempts used
            </span>
        </div>

        <p v-if="errorMessage" class="text-error-600 dark:text-error-400 mt-3 text-xs font-medium">
            {{ errorMessage }}
        </p>

        <!-- idle: not started yet -->
        <div v-if="stage === 'idle'" class="mt-4">
            <p v-if="bestScore !== null" class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                Best score so far: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ bestScore }}%</span>
            </p>
            <button
                type="button"
                :disabled="!canAttempt"
                class="bg-brand-500 hover:bg-brand-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
                @click="startQuiz"
            >
                {{ bestScore !== null ? 'Retake quiz' : 'Start quiz' }}
            </button>
            <p v-if="!canAttempt" class="mt-2 text-xs text-gray-400">
                No attempts remaining for this quiz.
            </p>
        </div>

        <!-- loading -->
        <div v-else-if="stage === 'loading'" class="mt-4 flex items-center gap-2 text-sm text-gray-500">
            <Loader2 class="h-4 w-4 animate-spin" />
            Starting quiz...
        </div>

        <!-- in progress -->
        <div v-else-if="stage === 'in_progress' || stage === 'submitting'" class="mt-4 space-y-5">
            <div v-for="(question, idx) in questions" :key="question.id">
                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                    {{ idx + 1 }}. {{ question.question_text }}
                    <span class="text-xs font-normal text-gray-400">({{ question.points }} pt)</span>
                </p>

                <div v-if="question.type !== 'short_answer'" class="mt-2 space-y-1.5">
                    <label
                        v-for="option in question.options"
                        :key="option.id"
                        class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-200 px-3 py-2 text-sm transition dark:border-gray-700"
                        :class="
                            answers[question.id] === option.id
                                ? 'border-brand-400 bg-brand-50 dark:bg-brand-500/10'
                                : 'hover:bg-gray-50 dark:hover:bg-white/5'
                        "
                    >
                        <input
                            type="radio"
                            :name="`q-${question.id}`"
                            class="text-brand-500"
                            :checked="answers[question.id] === option.id"
                            @change="selectOption(question.id, option.id)"
                        />
                        <span class="text-gray-700 dark:text-gray-300">{{ option.option_text }}</span>
                    </label>
                </div>

                <textarea
                    v-else
                    v-model="answers[question.id]"
                    rows="3"
                    placeholder="Type your answer..."
                    class="focus:border-brand-300 mt-2 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
            </div>

            <button
                type="button"
                :disabled="!allAnswered || stage === 'submitting'"
                class="bg-brand-500 hover:bg-brand-600 flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
                @click="submitQuiz"
            >
                <Loader2 v-if="stage === 'submitting'" class="h-4 w-4 animate-spin" />
                {{ stage === 'submitting' ? 'Submitting...' : 'Submit quiz' }}
            </button>
        </div>

        <!-- result -->
        <div v-else-if="stage === 'result'" class="mt-4">
            <div
                class="flex items-center gap-3 rounded-xl px-4 py-3"
                :class="
                    result.passed
                        ? 'bg-success-50 dark:bg-success-500/10'
                        : 'bg-error-50 dark:bg-error-500/10'
                "
            >
                <CheckCircle2 v-if="result.passed" class="text-success-600 dark:text-success-400 h-5 w-5 shrink-0" />
                <XCircle v-else class="text-error-600 dark:text-error-400 h-5 w-5 shrink-0" />
                <div>
                    <p
                        class="text-sm font-bold"
                        :class="result.passed ? 'text-success-700 dark:text-success-400' : 'text-error-700 dark:text-error-400'"
                    >
                        {{ result.passed ? 'You passed!' : 'Not quite — try again' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Score: {{ result.score }}% (pass mark {{ quiz.passing_score }}%)
                    </p>
                </div>
            </div>

            <!-- per-question breakdown -->
            <div class="mt-4 space-y-3">
                <div v-for="(question, idx) in questions" :key="question.id">
                    <p class="flex items-start gap-1.5 text-sm text-gray-800 dark:text-gray-100">
                        <CheckCircle2
                            v-if="answerFor(question.id)?.is_correct === true"
                            class="text-success-500 mt-0.5 h-3.5 w-3.5 shrink-0"
                        />
                        <XCircle
                            v-else-if="answerFor(question.id)?.is_correct === false"
                            class="text-error-500 mt-0.5 h-3.5 w-3.5 shrink-0"
                        />
                        <span>{{ idx + 1 }}. {{ question.question_text }}</span>
                    </p>
                    <div v-if="question.type !== 'short_answer'" class="mt-1 ml-5 space-y-1">
                        <p
                            v-for="option in question.options"
                            :key="option.id"
                            class="text-xs"
                            :class="{
                                'text-success-600 dark:text-success-400 font-semibold': option.id === answerFor(question.id)?.correct_option_id,
                                'text-error-500 line-through': option.id === answers[question.id] && option.id !== answerFor(question.id)?.correct_option_id,
                                'text-gray-400': option.id !== answers[question.id] && option.id !== answerFor(question.id)?.correct_option_id,
                            }"
                        >
                            {{ option.option_text }}
                        </p>
                    </div>
                    <p v-else class="mt-1 ml-5 text-xs text-gray-500 dark:text-gray-400">
                        Your answer: {{ answers[question.id] }} — pending review
                    </p>
                </div>
            </div>

            <button
                v-if="canAttempt"
                type="button"
                class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5"
                @click="retry"
            >
                <RotateCcw class="h-4 w-4" />
                Retry quiz
            </button>
            <p v-else class="mt-3 text-xs text-gray-400">No attempts remaining.</p>
        </div>
    </div>
</template>