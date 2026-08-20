<script setup>
import SeoHead from '@/Components/seo/SeoHead.vue';
import CountdownBox from '@modules/LMS/resources/js/Components/Learn/CountdownBox.vue';
import CountdownTimer from '@modules/LMS/resources/js/Components/Learn/CountdownTimer.vue';
import LearnFooter from '@modules/LMS/resources/js/Components/Learn/LearnFooter.vue';
import LearnHeader from '@modules/LMS/resources/js/Components/Learn/LearnHeader.vue';
import LessonPreviewModal from '@modules/LMS/resources/js/Components/Learn/LessonPreviewModal.vue';
import PaymentEnrollModal from '@modules/LMS/resources/js/Components/Learn/PaymentEnrollModal.vue';
import ReviewsList from '@modules/LMS/resources/js/Components/Learn/ReviewsList.vue';
import { useAuthStudent } from '@modules/LMS/resources/js/composables/useAuthStudent.js';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { stripHtml } from '@/composables/text.js';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgeCheck,
    BookOpen,
    ChevronDown,
    CircleCheck,
    Clock,
    FileDown,
    GraduationCap,
    Layers,
    Lock,
    Play,
    PlayCircle,
    ShieldCheck,
    Zap,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    course: Object,
    enrollment: {
        type: Object,
        default: null,
    },
    payment_methods: {
        type: Array,
        default: () => [],
    },
    seo: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const { isAuthenticated } = useAuthStudent();

const isEnrolled = computed(() => !!props.enrollment);

const discountPercent = computed(() => {
    if (!props.course.has_discount) return 0;
    const price = Number(props.course.price) || 0;
    const discount = Number(props.course.discount_price) || 0;
    if (!price) return 0;
    return Math.round(((price - discount) / price) * 100);
});

// ── content / FAQ tabs ──
const activeTab = ref('content');

const openModuleId = ref(props.course.modules?.[0]?.id ?? null);
const toggleModule = (id) => {
    openModuleId.value = openModuleId.value === id ? null : id;
};

// ── FAQ accordion — first one open by default ──
const openFaqIds = ref(props.course.faqs?.[0] ? [props.course.faqs[0].id] : []);
const allFaqsOpen = computed(
    () => (props.course.faqs?.length ?? 0) > 0 && openFaqIds.value.length === props.course.faqs.length,
);

const toggleFaq = (id) => {
    openFaqIds.value = openFaqIds.value.includes(id)
        ? openFaqIds.value.filter((i) => i !== id)
        : [...openFaqIds.value, id];
};

const toggleAllFaqs = () => {
    openFaqIds.value = allFaqsOpen.value ? [] : (props.course.faqs ?? []).map((f) => f.id);
};

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const form = useForm({});

const submitEnroll = () => {
    form.post(learnRoutes.enrollCourse(props.course.id), {
        preserveScroll: true,
    });
};

// ── paid-course payment modal ──
const paymentModalOpen = ref(false);

// ── shared preview modal (lesson previews + course trailer) ──
const activePreview = ref(null); // { title, videoUrl } | null
const previewOpen = ref(false);

const openPreview = (title, videoUrl) => {
    if (!videoUrl) return;
    activePreview.value = { title, videoUrl };
    previewOpen.value = true;
};
const openLessonPreview = (lesson) => {
    // enrolled and this lesson is unlocked — go watch it for real
    if (isEnrolled.value && lesson.is_unlocked) {
        router.visit(learnRoutes.lessonShow(lesson.id));
        return;
    }

    // not enrolled (or locked) — a free-preview lesson can still be sampled
    if (lesson.is_free_preview) {
        openPreview(lesson.title, lesson.preview_video_url);
    }
};
const openCoursePreview = () => {
    openPreview(`${props.course.title} — Preview`, props.course.preview_video_url);
};
const closePreview = () => {
    previewOpen.value = false;
};
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="min-h-screen bg-white dark:bg-gray-950">
        <LearnHeader />

        <!-- ── Dark hero ── -->
        <section class="bg-gradient-to-br from-gray-950 via-[#150f2e] to-gray-950 pb-10">
            <div class="mx-auto max-w-6xl px-6 pt-10">
                <Link
                    :href="learnRoutes.browse"
                    class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-white/60 hover:text-white"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to courses
                </Link>

                <div class="grid grid-cols-1 gap-10 lg:grid-cols-[1fr_360px]">
                    <!-- copy -->
                    <div>
                        <p
                            v-if="course.category_name"
                            class="text-brand-300 text-xs font-semibold tracking-wide uppercase"
                        >
                            {{ course.category_name }}
                        </p>
                        <h1 class="mt-1.5 text-2xl font-bold text-white sm:text-3xl">
                            {{ course.title }}
                        </h1>
                        <p v-if="course.short_description" class="mt-3 text-base font-semibold text-white/90">
                            {{ stripHtml(course.short_description) }}
                        </p>

                        <div
                            v-if="course.description"
                            class="prose prose-sm prose-invert prose-p:text-white/70 prose-headings:text-white mt-4 max-w-none"
                            v-html="course.description"
                        />

                        <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm text-white/60">
                            <span class="flex items-center gap-1.5">
                                <Layers class="h-4 w-4" />
                                {{ course.modules.length }}
                                {{ course.modules.length === 1 ? 'module' : 'modules' }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <PlayCircle class="h-4 w-4" />
                                {{ course.total_lessons }}
                                {{ course.total_lessons === 1 ? 'lesson' : 'lessons' }}
                            </span>
                        </div>
                    </div>

                    <!-- video preview card -->
                    <div>
                        <button
                            type="button"
                            class="group relative aspect-video w-full overflow-hidden rounded-2xl border-4 border-white/90 bg-gray-800 shadow-2xl"
                            :class="course.has_preview_video ? 'cursor-pointer' : 'cursor-default'"
                            @click="course.has_preview_video && openCoursePreview()"
                        >
                            <img
                                v-if="course.preview_image || course.thumbnail"
                                :src="course.preview_image || course.thumbnail"
                                :alt="course.title"
                                class="h-full w-full object-cover transition duration-300"
                                :class="course.has_preview_video ? 'group-hover:brightness-75' : ''"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center">
                                <BookOpen class="h-10 w-10 text-gray-500" />
                            </div>

                            <span
                                v-if="course.has_discount"
                                class="bg-error-500 absolute top-3 left-3 rounded-full px-3 py-1.5 text-xs font-bold text-white shadow-sm"
                            >
                                -{{ discountPercent }}% OFF
                            </span>

                            <span
                                v-if="course.has_preview_video"
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span
                                    class="flex h-16 w-16 items-center justify-center rounded-full bg-white/90 shadow-lg backdrop-blur transition duration-200 group-hover:scale-110 group-hover:bg-white"
                                >
                                    <Play class="text-brand-600 ml-1 h-6 w-6 fill-current" />
                                </span>
                            </span>
                            <span
                                v-if="course.has_preview_video"
                                class="absolute bottom-3 left-3 rounded-full bg-black/60 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur"
                            >
                                Preview this course
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── flash messages ── -->
        <div class="mx-auto max-w-6xl px-6 pt-6">
            <div
                v-if="$page.props.flash?.status"
                class="bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400 mb-6 flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium"
            >
                <CircleCheck class="h-4 w-4 shrink-0" />
                {{ $page.props.flash.status }}
            </div>
            <div
                v-if="$page.props.flash?.error"
                class="bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400 mb-6 flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium"
            >
                {{ $page.props.flash.error }}
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-6 pb-16">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-[1fr_360px]">
                <!-- ── Main content ── -->
                <div>
                    <!-- tabs -->
                    <div class="flex gap-2 overflow-x-auto border-b border-gray-100 dark:border-gray-800">
                        <button
                            type="button"
                            class="shrink-0 border-b-2 px-1 py-3 text-sm font-semibold whitespace-nowrap"
                            :class="
                                activeTab === 'content'
                                    ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                            "
                            @click="activeTab = 'content'"
                        >
                            কোর্স কারিকুলাম
                        </button>
                        <button
                            v-if="course.faqs?.length"
                            type="button"
                            class="ml-6 shrink-0 border-b-2 px-1 py-3 text-sm font-semibold whitespace-nowrap"
                            :class="
                                activeTab === 'faqs'
                                    ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                            "
                            @click="activeTab = 'faqs'"
                        >
                            সচরাচর জিজ্ঞাসা
                        </button>
                    </div>

                    <!-- countdown -->
                    <CountdownBox v-if="course.live_class_starts_at" :target-date="course.live_class_starts_at" class="mt-6" />

                    <!-- instructors -->
                    <div v-if="course.instructors?.length" class="mt-8">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">কোর্স ইন্সট্রাক্টর</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 rounded-2xl border border-gray-100 p-5 sm:grid-cols-2 dark:border-gray-800">
                            <div
                                v-for="instructor in course.instructors"
                                :key="instructor.id"
                                class="flex items-start gap-3"
                            >
                                <img
                                    :src="instructor.avatar"
                                    :alt="instructor.name"
                                    class="h-14 w-14 shrink-0 rounded-full object-cover"
                                />
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                                        <GraduationCap class="text-brand-500 h-4 w-4 shrink-0" />
                                        {{ instructor.name }}
                                    </p>
                                    <p v-if="instructor.bio" class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                                        {{ stripHtml(instructor.bio) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Course content tab ── -->
                    <div v-if="activeTab === 'content'" class="mt-8">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            Course content
                        </h2>

                        <div class="mt-4 space-y-3">
                            <div
                                v-for="courseModule in course.modules"
                                :key="courseModule.id"
                                class="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-800"
                            >
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 bg-gray-50/70 px-5 py-4 text-left dark:bg-gray-900/60"
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
                                        class="group flex items-center justify-between gap-3 px-5 py-3 transition-colors"
                                        :class="
                                            (isEnrolled && lesson.is_unlocked) ||
                                            (lesson.is_free_preview && lesson.preview_video_url)
                                                ? 'cursor-pointer hover:bg-brand-50/60 dark:hover:bg-brand-500/5'
                                                : ''
                                        "
                                        @click="openLessonPreview(lesson)"
                                    >
                                        <span class="flex min-w-0 items-center gap-3">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full transition"
                                                :class="
                                                    (isEnrolled && lesson.is_complete)
                                                        ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400'
                                                        : (isEnrolled && lesson.is_unlocked) || lesson.is_free_preview
                                                          ? 'bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 group-hover:bg-brand-500 group-hover:text-white'
                                                          : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500'
                                                "
                                            >
                                                <CircleCheck
                                                    v-if="isEnrolled && lesson.is_complete"
                                                    class="h-3.5 w-3.5"
                                                />
                                                <Lock
                                                    v-else-if="isEnrolled ? !lesson.is_unlocked : !lesson.is_free_preview"
                                                    class="h-3.5 w-3.5"
                                                />
                                                <Play
                                                    v-else-if="!isEnrolled && lesson.is_free_preview && lesson.preview_video_url"
                                                    class="h-3 w-3 fill-current"
                                                />
                                                <PlayCircle v-else class="h-3.5 w-3.5" />
                                            </span>
                                            <span class="truncate text-sm text-gray-700 dark:text-gray-300">
                                                {{ lesson.title }}
                                            </span>
                                            <span
                                                v-if="lesson.is_free_preview && !isEnrolled"
                                                class="bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                            >
                                                Preview
                                            </span>
                                        </span>

                                        <span class="flex shrink-0 items-center gap-2.5 text-gray-400 dark:text-gray-500">
                                            <a
                                                v-if="lesson.has_ebook && lesson.is_free_preview && !isEnrolled && lesson.preview_ebook_url"
                                                :href="lesson.preview_ebook_url"
                                                target="_blank"
                                                rel="noopener"
                                                class="hover:text-brand-500 dark:hover:text-brand-400 flex items-center gap-1 transition"
                                                title="Open ebook preview"
                                                @click.stop
                                            >
                                                <FileDown class="h-3.5 w-3.5" />
                                            </a>
                                            <FileDown v-else-if="lesson.has_ebook" class="h-3.5 w-3.5" />
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

                    <!-- ── FAQ tab ── -->
                    <div v-else-if="activeTab === 'faqs'" class="mt-8">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                সচরাচর জিজ্ঞাসা
                            </h2>
                            <button
                                type="button"
                                class="text-brand-500 hover:text-brand-600 flex items-center gap-1 text-xs font-semibold"
                                @click="toggleAllFaqs"
                            >
                                সকল প্রশ্ন-উত্তর
                                <ChevronDown
                                    class="h-3.5 w-3.5 transition-transform duration-200"
                                    :class="{ 'rotate-180': allFaqsOpen }"
                                />
                            </button>
                        </div>

                        <div class="divide-y divide-gray-100 rounded-2xl border border-gray-100 dark:divide-gray-800 dark:border-gray-800">
                            <div v-for="faq in course.faqs" :key="faq.id">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left"
                                    @click="toggleFaq(faq.id)"
                                >
                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                        {{ faq.question }}
                                    </span>
                                    <ChevronDown
                                        class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': openFaqIds.includes(faq.id) }"
                                    />
                                </button>
                                <div
                                    v-show="openFaqIds.includes(faq.id)"
                                    class="prose prose-sm dark:prose-invert prose-p:text-gray-600 dark:prose-p:text-gray-300 prose-strong:text-gray-800 dark:prose-strong:text-gray-100 max-w-none px-5 pb-4"
                                    v-html="faq.answer"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ── Reviews ── -->
                    <div class="mt-8">
                        <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">
                            Student reviews
                        </h2>
                        <ReviewsList
                            :average-rating="course.average_rating"
                            :reviews-count="course.reviews_count"
                            :reviews="course.reviews"
                        />
                    </div>
                </div>

                <!-- ── Enroll card ── -->
                <div>
                    <div
                        class="sticky top-24 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-[0_20px_50px_-20px_rgba(16,24,40,0.35)] lg:-mt-24 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="p-6">
                            <div class="flex items-baseline justify-between">
                                <span
                                    v-if="course.is_free"
                                    class="text-success-600 dark:text-success-400 text-2xl font-extrabold"
                                >
                                    Free
                                </span>
                                <span v-else-if="course.has_discount" class="flex items-baseline gap-2">
                                    <span class="text-2xl font-extrabold text-gray-900 dark:text-white">
                                        ৳{{ money(course.discount_price) }}
                                    </span>
                                    <span class="text-sm text-gray-400 line-through">
                                        ৳{{ money(course.price) }}
                                    </span>
                                    <span
                                        class="bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400 rounded-full px-2 py-0.5 text-xs font-bold"
                                    >
                                        -{{ discountPercent }}%
                                    </span>
                                </span>
                                <span v-else class="text-2xl font-extrabold text-gray-900 dark:text-white">
                                    ৳{{ money(course.price) }}
                                </span>
                            </div>

                            <!-- already enrolled -->
                            <div v-if="isEnrolled" class="mt-5">
                                <div
                                    class="bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400 flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium"
                                >
                                    <BadgeCheck class="h-4 w-4 shrink-0" />
                                    You're enrolled since {{ enrollment.enrolled_at }}
                                </div>
                                <Link
                                    :href="learnRoutes.myCourseShow(course.id)"
                                    class="bg-success-500 hover:bg-success-600 mt-3 flex w-full items-center justify-center rounded-xl py-3 text-sm font-semibold text-white transition"
                                >
                                    Continue learning
                                </Link>
                            </div>

                            <!-- not authenticated -->
                            <div v-else-if="!isAuthenticated" class="mt-5">
                                <Link
                                    :href="learnRoutes.login"
                                    class="bg-success-500 hover:bg-success-600 flex w-full items-center justify-center rounded-xl py-3 text-sm font-semibold text-white transition"
                                >
                                    Sign in to enroll
                                </Link>
                                <p class="mt-2.5 text-center text-xs text-gray-400 dark:text-gray-500">
                                    New here? Signing in also lets you track progress.
                                </p>
                            </div>

                            <!-- free course, not enrolled -->
                            <form v-else-if="course.is_free" class="mt-5" @submit.prevent="submitEnroll">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-success-500 hover:bg-success-600 flex w-full items-center justify-center rounded-xl py-3 text-sm font-semibold text-white transition disabled:opacity-60"
                                >
                                    {{ form.processing ? 'Enrolling...' : 'Enroll for free' }}
                                </button>
                            </form>

                            <!-- paid course, not enrolled -->
                            <div v-else class="mt-5">
                                <button
                                    type="button"
                                    class="bg-success-500 hover:bg-success-600 flex w-full items-center justify-center rounded-xl py-3 text-sm font-semibold text-white transition"
                                    @click="paymentModalOpen = true"
                                >
                                    Enroll now
                                </button>
                            </div>

                            <CountdownTimer
                                v-if="course.live_class_starts_at"
                                :target-date="course.live_class_starts_at"
                                class="mt-4"
                            />

                            <ul class="mt-6 space-y-2.5 text-sm text-gray-500 dark:text-gray-400">
                                <li v-if="course.has_discount" class="text-error-600 dark:text-error-400 flex items-center gap-2 font-medium">
                                    <Zap class="h-4 w-4 shrink-0 fill-current" />
                                    Save ৳{{ money(course.price - course.discount_price) }} today
                                </li>
                                <li class="flex items-center gap-2">
                                    <PlayCircle class="h-4 w-4 shrink-0" />
                                    {{ course.total_lessons }} lessons across {{ course.modules.length }} modules
                                </li>
                                <li class="flex items-center gap-2">
                                    <ShieldCheck class="h-4 w-4 shrink-0" />
                                    Learn at your own pace, no deadlines
                                </li>
                            </ul>

                            <div v-if="course.short_description" class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
                                <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                                    এই কোর্সে যা থাকছে
                                </h2>
                                <div
                                    class="prose prose-sm dark:prose-invert prose-p:text-gray-500 dark:prose-p:text-gray-400 prose-li:text-gray-500 dark:prose-li:text-gray-400 prose-headings:text-gray-700 dark:prose-headings:text-gray-200 mt-3 max-w-none"
                                    v-html="course.short_description"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <LearnFooter />

        <LessonPreviewModal
            :open="previewOpen"
            :title="activePreview?.title"
            :video-url="activePreview?.videoUrl"
            @close="closePreview"
        />

        <PaymentEnrollModal
            :open="paymentModalOpen"
            :course="course"
            :payment-methods="payment_methods"
            @close="paymentModalOpen = false"
        />
    </div>
</template>