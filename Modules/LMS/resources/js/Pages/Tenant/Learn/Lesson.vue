<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import AssignmentWidget from '@modules/LMS/resources/js/Components/Learn/AssignmentWidget.vue';
import CalculatorWidget from '@modules/LMS/resources/js/Components/Learn/CalculatorWidget.vue';
import LeaderboardModal from '@modules/LMS/resources/js/Components/Learn/LeaderboardModal.vue';
import LessonVideoPlayer from '@modules/LMS/resources/js/Components/Learn/LessonVideoPlayer.vue';
import NotepadWidget from '@modules/LMS/resources/js/Components/Learn/NotepadWidget.vue';
import QuizWidget from '@modules/LMS/resources/js/Components/Learn/QuizWidget.vue';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Calculator,
    CheckCircle2,
    ChevronDown,
    ClipboardList,
    FileText,
    HelpCircle,
    Lock,
    NotebookPen,
    PlayCircle,
    Trophy,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    lesson: Object,
    progress: Object,
    course: Object,
    navigation: Object,
    note: {
        type: String,
        default: '',
    },
});

// ── content tab (video / ebook / quiz / assignment) ──
const activeTab = ref(
    props.lesson.has_video
        ? 'video'
        : props.lesson.has_ebook
          ? 'ebook'
          : props.lesson.quizzes?.length
            ? 'quiz'
            : 'assignment',
);

const openModuleId = ref(
    props.course.modules.find((m) => m.lessons.some((l) => l.is_current))?.id ?? null,
);
const toggleModule = (id) => {
    openModuleId.value = openModuleId.value === id ? null : id;
};

// ── progress ──
const videoCompleted = ref(props.progress?.video_completed ?? false);
const ebookOpened = ref(props.progress?.ebook_opened_at ?? false);

const completeThresholdLabel = computed(() => {
    const seconds = props.lesson.video_complete_threshold_seconds ?? 120;
    if (seconds < 60) return `${seconds} seconds`;
    const minutes = Math.round(seconds / 60);
    return `${minutes} minute${minutes === 1 ? '' : 's'}`;
});

const onVideoProgress = async (watchedSeconds, durationSeconds) => {
    try {
        const { data } = await window.axios.post(learnRoutes.trackVideo(props.lesson.id), {
            watched_seconds: watchedSeconds,
            duration_seconds: durationSeconds || undefined,
        });
        if (data.completed && !videoCompleted.value) {
            videoCompleted.value = true;
            refreshProgress();
        }
    } catch {
        // network hiccup — next progress tick will retry, no need to surface an error
    }
};

const openEbook = async () => {
    activeTab.value = 'ebook';
    if (!ebookOpened.value) {
        ebookOpened.value = true;
        try {
            await window.axios.post(learnRoutes.markEbookRead(props.lesson.id));
            refreshProgress();
        } catch {
            ebookOpened.value = false;
        }
    }
};

// re-fetch just the course (lock/complete state) and progress props from
// the server — the sidebar checkmarks and progress bar are plain snapshot
// props from initial page load, so they won't reflect a completion that
// just happened in this same session without an explicit refetch.
const refreshProgress = () => {
    router.reload({ only: ['course', 'progress'], preserveScroll: true });
};

const onQuizCompleted = () => {
    refreshProgress();
};

// ── floating tools ──
const calculatorOpen = ref(false);
const notepadOpen = ref(false);
const leaderboardOpen = ref(false);
const noteContent = ref(props.note);

// ── flat progress across the course ──
const totalLessons = computed(() =>
    props.course.modules.reduce((sum, m) => sum + m.lessons.length, 0),
);
const completedLessons = computed(() =>
    props.course.modules.reduce(
        (sum, m) => sum + m.lessons.filter((l) => l.is_complete).length,
        0,
    ),
);
const courseProgressPercent = computed(() =>
    totalLessons.value ? Math.round((completedLessons.value / totalLessons.value) * 100) : 0,
);
</script>

<template>
    <WorkspaceLayout :title="lesson.title">
        <div class="mx-auto">
            <!-- header row -->
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <Link
                        :href="learnRoutes.myCourseShow(course.id)"
                        class="hover:text-brand-500 mb-1.5 inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400"
                    >
                        <ArrowLeft class="h-3.5 w-3.5" />
                        {{ course.title }}
                    </Link>
                    <h1 class="truncate text-xl font-semibold text-gray-800 dark:text-white/90">
                        {{ lesson.title }}
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    <div class="w-32">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div
                                class="bg-brand-500 h-full rounded-full transition-all"
                                :style="{ width: `${courseProgressPercent}%` }"
                            />
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                        {{ courseProgressPercent }}%
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
                <!-- main content -->
                <div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <!-- content tabs -->
                        <div
                            v-if="[lesson.has_video, lesson.has_ebook, lesson.quizzes?.length > 0, lesson.assignments?.length > 0].filter(Boolean).length > 1"
                            class="mb-4 flex gap-2"
                        >
                            <button
                                v-if="lesson.has_video"
                                type="button"
                                class="rounded-lg px-3.5 py-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'video'
                                        ? 'bg-brand-500 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                                "
                                @click="activeTab = 'video'"
                            >
                                <PlayCircle class="mr-1.5 inline h-4 w-4 -translate-y-px" />
                                Video
                            </button>
                            <button
                                v-if="lesson.has_ebook"
                                type="button"
                                class="rounded-lg px-3.5 py-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'ebook'
                                        ? 'bg-brand-500 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                                "
                                @click="openEbook"
                            >
                                <FileText class="mr-1.5 inline h-4 w-4 -translate-y-px" />
                                Ebook
                            </button>
                            <button
                                v-if="lesson.quizzes?.length"
                                type="button"
                                class="rounded-lg px-3.5 py-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'quiz'
                                        ? 'bg-brand-500 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                                "
                                @click="activeTab = 'quiz'"
                            >
                                <HelpCircle class="mr-1.5 inline h-4 w-4 -translate-y-px" />
                                Quiz
                            </button>
                            <button
                                v-if="lesson.assignments?.length"
                                type="button"
                                class="rounded-lg px-3.5 py-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'assignment'
                                        ? 'bg-brand-500 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                                "
                                @click="activeTab = 'assignment'"
                            >
                                <ClipboardList class="mr-1.5 inline h-4 w-4 -translate-y-px" />
                                Assignment
                            </button>
                        </div>

                        <!-- video -->
                        <div v-if="activeTab === 'video' && lesson.has_video">
                            <LessonVideoPlayer
                                :video-url="lesson.video_url"
                                :video-source="lesson.video_source"
                                @progress="onVideoProgress"
                            />
                            <div
                                v-if="videoCompleted"
                                class="bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400 mt-4 flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium"
                            >
                                <CheckCircle2 class="h-4 w-4 shrink-0" />
                                Video watched — nice work!
                            </div>
                            <p v-else class="mt-3 text-xs text-gray-400">
                                Watch at least {{ completeThresholdLabel }} of this video and your progress will be marked done — that's all.
                            </p>
                        </div>

                        <!-- ebook -->
                        <div v-else-if="activeTab === 'ebook' && lesson.has_ebook">
                            <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                                <iframe
                                    :src="lesson.ebook_url"
                                    class="h-[75vh] w-full"
                                    title="Lesson ebook"
                                />
                            </div>
                            <a
                                :href="lesson.ebook_url"
                                target="_blank"
                                rel="noopener"
                                class="text-brand-500 mt-3 inline-block text-sm font-medium hover:underline"
                            >
                                Open in a new tab ↗
                            </a>
                        </div>

                        <!-- quiz -->
                        <div v-else-if="activeTab === 'quiz' && lesson.quizzes?.length" class="space-y-4">
                            <QuizWidget
                                v-for="quiz in lesson.quizzes"
                                :key="quiz.id"
                                :quiz="quiz"
                                @submitted="onQuizCompleted"
                            />
                        </div>

                        <!-- assignment -->
                        <div v-else-if="activeTab === 'assignment' && lesson.assignments?.length" class="space-y-4">
                            <AssignmentWidget
                                v-for="assignment in lesson.assignments"
                                :key="assignment.id"
                                :assignment="assignment"
                            />
                        </div>

                        <div
                            v-else
                            class="flex aspect-video items-center justify-center rounded-2xl bg-gray-50 text-sm text-gray-400 dark:bg-gray-900"
                        >
                            No content attached to this lesson yet.
                        </div>
                    </div>

                    <!-- prev/next -->
                    <div class="mt-5 flex items-center justify-between">
                        <Link
                            v-if="navigation.prev_lesson_id"
                            :href="learnRoutes.lessonShow(navigation.prev_lesson_id)"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Previous
                        </Link>
                        <span v-else />

                        <Link
                            v-if="navigation.next_lesson_id"
                            :href="learnRoutes.lessonShow(navigation.next_lesson_id)"
                            class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition"
                        >
                            Next lesson
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                        <Link
                            v-else
                            :href="learnRoutes.myCourseShow(course.id)"
                            class="bg-success-500 hover:bg-success-600 inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition"
                        >
                            Finish course
                            <CheckCircle2 class="h-4 w-4" />
                        </Link>
                    </div>
                </div>

                <!-- sidebar -->
                <aside class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <button
                        type="button"
                        class="text-brand-600 dark:text-brand-400 mb-4 flex w-full items-center gap-2 rounded-xl bg-amber-50 px-3 py-2.5 text-sm font-semibold transition hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20"
                        @click="leaderboardOpen = true"
                    >
                        <Trophy class="h-4 w-4 text-amber-500" />
                        Leaderboard
                    </button>

                    <p class="mb-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">
                        Course content
                    </p>
                    <div class="space-y-2">
                        <div
                            v-for="module in course.modules"
                            :key="module.id"
                            class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-2 bg-gray-50/70 px-3 py-2.5 text-left dark:bg-gray-900/60"
                                @click="toggleModule(module.id)"
                            >
                                <span class="truncate text-xs font-semibold text-gray-700 dark:text-gray-200">
                                    {{ module.title }}
                                </span>
                                <ChevronDown
                                    class="h-3.5 w-3.5 shrink-0 text-gray-400 transition-transform"
                                    :class="{ 'rotate-180': openModuleId === module.id }"
                                />
                            </button>
                            <ul v-show="openModuleId === module.id" class="divide-y divide-gray-100 dark:divide-gray-800">
                                <li v-for="l in module.lessons" :key="l.id">
                                    <component
                                        :is="l.is_unlocked ? Link : 'div'"
                                        :href="l.is_unlocked ? learnRoutes.lessonShow(l.id) : undefined"
                                        class="flex items-center gap-2.5 px-3 py-2.5 text-xs"
                                        :class="[
                                            l.is_current
                                                ? 'bg-brand-50 dark:bg-brand-500/10'
                                                : l.is_unlocked
                                                  ? 'hover:bg-gray-50 dark:hover:bg-white/5'
                                                  : 'cursor-not-allowed opacity-50',
                                        ]"
                                    >
                                        <CheckCircle2
                                            v-if="l.is_complete"
                                            class="text-success-500 h-3.5 w-3.5 shrink-0"
                                        />
                                        <Lock
                                            v-else-if="!l.is_unlocked"
                                            class="h-3.5 w-3.5 shrink-0 text-gray-300 dark:text-gray-600"
                                        />
                                        <PlayCircle
                                            v-else
                                            class="h-3.5 w-3.5 shrink-0"
                                            :class="l.is_current ? 'text-brand-500' : 'text-gray-400'"
                                        />
                                        <span
                                            class="truncate"
                                            :class="
                                                l.is_current
                                                    ? 'text-brand-700 dark:text-brand-400 font-semibold'
                                                    : 'text-gray-600 dark:text-gray-300'
                                            "
                                        >
                                            {{ l.title }}
                                        </span>
                                    </component>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <!-- floating tool launcher -->
        <div class="fixed right-5 bottom-5 z-[9997] flex flex-col gap-2.5">
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-600 shadow-lg ring-1 ring-gray-200 transition hover:scale-105 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700"
                title="Notepad"
                @click="notepadOpen = !notepadOpen"
            >
                <NotebookPen class="h-4.5 w-4.5" />
            </button>
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-600 shadow-lg ring-1 ring-gray-200 transition hover:scale-105 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700"
                title="Calculator"
                @click="calculatorOpen = !calculatorOpen"
            >
                <Calculator class="h-4.5 w-4.5" />
            </button>
        </div>

        <CalculatorWidget v-if="calculatorOpen" @close="calculatorOpen = false" />
        <NotepadWidget
            v-if="notepadOpen"
            :lesson-id="lesson.id"
            v-model:note="noteContent"
            @close="notepadOpen = false"
        />
        <LeaderboardModal
            :open="leaderboardOpen"
            :course-id="course.id"
            @close="leaderboardOpen = false"
        />
    </WorkspaceLayout>
</template>