<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import InstructorModal from '@modules/LMS/resources/js/Components/Learn/InstructorModal.vue';
import LeaderboardModal from '@modules/LMS/resources/js/Components/Learn/LeaderboardModal.vue';
import ReviewForm from '@modules/LMS/resources/js/Components/Learn/ReviewForm.vue';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { stripHtml } from '@/composables/text.js';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronDown,
    CircleCheck,
    Clock,
    FileDown,
    GraduationCap,
    Lock,
    PlayCircle,
    Trophy,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    course: Object,
    enrollment: Object,
    own_review: {
        type: Object,
        default: null,
    },
    seo: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

// ── curriculum accordion — current/first module open by default ──
const openModuleId = ref(props.course.modules?.[0]?.id ?? null);
const toggleModule = (id) => {
    openModuleId.value = openModuleId.value === id ? null : id;
};

const goToLesson = (lesson) => {
    if (!lesson.is_unlocked) return;
    router.visit(learnRoutes.lessonShow(lesson.id));
};

// ── FAQ accordion ──
const openFaqIds = ref(props.course.faqs?.[0] ? [props.course.faqs[0].id] : []);
const toggleFaq = (id) => {
    openFaqIds.value = openFaqIds.value.includes(id)
        ? openFaqIds.value.filter((i) => i !== id)
        : [...openFaqIds.value, id];
};

// ── instructor detail popover (hover) ──
const selectedInstructor = ref(null);
const leaderboardOpen = ref(false);
const popoverPos = ref({ top: 0, left: 0 });

const showInstructor = (event, instructor) => {
    const rect = event.currentTarget.getBoundingClientRect();
    const popoverWidth = 288; // w-72

    let left = rect.left;
    if (left + popoverWidth > window.innerWidth - 16) {
        left = window.innerWidth - popoverWidth - 16;
    }

    popoverPos.value = { top: rect.bottom + 8, left };
    selectedInstructor.value = instructor;
};

const hideInstructor = () => {
    selectedInstructor.value = null;
};
</script>

<template>
    <WorkspaceLayout :title="course.title">
        <div class="mx-auto">
            <!-- flash messages -->
            <div
                v-if="$page.props.flash?.error"
                class="bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400 mb-5 rounded-xl px-4 py-3 text-sm font-medium"
            >
                {{ $page.props.flash.error }}
            </div>

            <!-- compact header -->
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <Link
                    :href="learnRoutes.myCourses"
                    class="mb-3 inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    My courses
                </Link>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p
                            v-if="course.category_name"
                            class="text-brand-500 dark:text-brand-400 text-[11px] font-semibold tracking-wide uppercase"
                        >
                            {{ course.category_name }}
                        </p>
                        <h1 class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90">
                            {{ course.title }}
                        </h1>
                        <p
                            v-if="course.short_description"
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ stripHtml(course.short_description) }}
                        </p>
                        <p
                            v-if="course.instructors?.length"
                            class="mt-2 text-xs text-gray-400"
                        >
                            By {{ course.instructors.map((i) => i.name).join(', ') }}
                        </p>
                    </div>

                    <div class="w-full shrink-0 sm:w-48">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400">Progress</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-200">
                                {{ enrollment.progress }}%
                            </span>
                        </div>
                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div
                                class="bg-brand-500 h-full rounded-full transition-all"
                                :style="{ width: `${enrollment.progress}%` }"
                            />
                        </div>
                        <p class="mt-1.5 text-[11px] text-gray-400">
                            Enrolled {{ enrollment.enrolled_at }}
                        </p>
                        <a
                            v-if="enrollment.status === 'completed'"
                            :href="learnRoutes.downloadCertificate(course.id)"
                            class="bg-success-500 hover:bg-success-600 mt-2.5 flex w-full items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white transition"
                        >
                            <GraduationCap class="h-3.5 w-3.5" />
                            Download certificate
                        </a>
                        <button
                            type="button"
                            class="mt-2.5 flex w-full items-center justify-center gap-1.5 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20"
                            @click="leaderboardOpen = true"
                        >
                            <Trophy class="h-3.5 w-3.5" />
                            Leaderboard
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_300px]">
                <!-- ── main: curriculum ── -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white/90">
                        Course content
                    </h2>

                    <div class="space-y-3">
                        <div
                            v-for="courseModule in course.modules"
                            :key="courseModule.id"
                            class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-3 bg-gray-50/70 px-4 py-3 text-left dark:bg-gray-900/60"
                                @click="toggleModule(courseModule.id)"
                            >
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ courseModule.title }}
                                </span>
                                <span class="flex items-center gap-3">
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ courseModule.lessons.length }}
                                        {{ courseModule.lessons.length === 1 ? 'lesson' : 'lessons' }}
                                    </span>
                                    <ChevronDown
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': openModuleId === courseModule.id }"
                                    />
                                </span>
                            </button>

                            <ul
                                v-show="openModuleId === courseModule.id"
                                class="divide-y divide-gray-100 dark:divide-gray-800"
                            >
                                <li
                                    v-for="lesson in courseModule.lessons"
                                    :key="lesson.id"
                                    class="group flex items-center justify-between gap-3 px-4 py-3 transition-colors"
                                    :class="lesson.is_unlocked ? 'cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-500/5' : 'opacity-60'"
                                    @click="goToLesson(lesson)"
                                >
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full transition"
                                            :class="
                                                lesson.is_complete
                                                    ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400'
                                                    : lesson.is_unlocked
                                                      ? 'bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 group-hover:bg-brand-500 group-hover:text-white'
                                                      : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500'
                                            "
                                        >
                                            <CircleCheck v-if="lesson.is_complete" class="h-3.5 w-3.5" />
                                            <Lock v-else-if="!lesson.is_unlocked" class="h-3.5 w-3.5" />
                                            <PlayCircle v-else class="h-3.5 w-3.5" />
                                        </span>
                                        <span class="truncate text-sm text-gray-700 dark:text-gray-300">
                                            {{ lesson.title }}
                                        </span>
                                    </span>

                                    <span class="flex shrink-0 items-center gap-2.5 text-gray-400 dark:text-gray-500">
                                        <FileDown v-if="lesson.has_ebook" class="h-3.5 w-3.5" />
                                        <span
                                            v-if="lesson.video_duration_minutes"
                                            class="flex items-center gap-1 text-xs"
                                        >
                                            <Clock class="h-3.5 w-3.5" />
                                            {{ lesson.video_duration_minutes }}m
                                        </span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ── sidebar ── -->
                <div class="space-y-6">
                    <!-- instructors -->
                    <div
                        v-if="course.instructors?.length"
                        class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                    >
                        <p class="mb-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">
                            Instructor{{ course.instructors.length > 1 ? 's' : '' }}
                        </p>
                        <div class="space-y-3">
                            <button
                                v-for="instructor in course.instructors"
                                :key="instructor.id"
                                type="button"
                                class="-m-1.5 flex w-full items-center gap-2.5 rounded-lg p-1.5 text-left transition hover:bg-gray-50 dark:hover:bg-white/5"
                                @mouseenter="showInstructor($event, instructor)"
                                @mouseleave="hideInstructor"
                            >
                                <img
                                    :src="instructor.avatar"
                                    :alt="instructor.name"
                                    class="h-9 w-9 shrink-0 rounded-full object-cover"
                                />
                                <p class="flex min-w-0 items-center gap-1.5 truncate text-sm font-medium text-gray-800 dark:text-gray-100">
                                    <GraduationCap class="text-brand-500 h-3.5 w-3.5 shrink-0" />
                                    {{ instructor.name }}
                                </p>
                            </button>
                        </div>
                    </div>

                    <!-- faq -->
                    <div
                        v-if="course.faqs?.length"
                        class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                    >
                        <p class="mb-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">
                            FAQ
                        </p>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            <div v-for="faq in course.faqs" :key="faq.id">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-2 py-2.5 text-left"
                                    @click="toggleFaq(faq.id)"
                                >
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                                        {{ faq.question }}
                                    </span>
                                    <ChevronDown
                                        class="h-3.5 w-3.5 shrink-0 text-gray-400 transition-transform"
                                        :class="{ 'rotate-180': openFaqIds.includes(faq.id) }"
                                    />
                                </button>
                                <div
                                    v-show="openFaqIds.includes(faq.id)"
                                    class="prose prose-xs dark:prose-invert prose-p:text-gray-500 dark:prose-p:text-gray-400 max-w-none pb-3"
                                    v-html="faq.answer"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- review -->
                    <ReviewForm :course-id="course.id" :own-review="own_review" />
                </div>
            </div>
        </div>

        <InstructorModal :instructor="selectedInstructor" :top="popoverPos.top" :left="popoverPos.left" />
        <LeaderboardModal
            :open="leaderboardOpen"
            :course-id="course.id"
            @close="leaderboardOpen = false"
        />
    </WorkspaceLayout>
</template>
