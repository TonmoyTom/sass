<script setup>
import SeoHead from '@/Components/seo/SeoHead.vue';
import LearnFooter from '@modules/LMS/resources/js/Components/Learn/LearnFooter.vue';
import LearnHeader from '@modules/LMS/resources/js/Components/Learn/LearnHeader.vue';
import RevealOnScroll from '@modules/LMS/resources/js/Components/Learn/RevealOnScroll.vue';
import { useAuthStudent } from '@modules/LMS/resources/js/composables/useAuthStudent.js';
import { useCountUp } from '@modules/LMS/resources/js/composables/useCountUp.js';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    CheckCircle2,
    ChevronRight,
    CirclePlay,
    Clock,
    Download,
    GraduationCap,
    Layers,
    ListChecks,
    Search,
    Sparkles,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    featuredCourses: Array,
    categories: Array,
    stats: Object,
    seo: {
        type: Object,
        default: () => ({}),
    },
});

const { isAuthenticated } = useAuthStudent();

const page = usePage();
const brand = computed(() => ({
    name: page.props.workspace?.company_name || page.props.tenant?.name || 'Campus',
    logo: page.props.workspace?.logo_url || page.props.tenant?.logo || null,
}));

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const compact = (n) =>
    new Intl.NumberFormat('en-US', { notation: 'compact' }).format(n ?? 0);

// deterministic-but-varied icon per category, without needing a DB column
const categoryIcons = [BookOpen, Layers, GraduationCap, Sparkles, CirclePlay, Clock];
const iconFor = (index) => categoryIcons[index % categoryIcons.length];

// hero stats are above the fold, so animate them in as soon as we've mounted
const heroMounted = ref(false);
onMounted(() => (heroMounted.value = true));

const coursesCount = useCountUp(
    computed(() => props.stats.total_courses),
    heroMounted,
);
const studentsCount = useCountUp(
    computed(() => props.stats.total_students),
    heroMounted,
);
const categoriesCount = useCountUp(
    computed(() => props.stats.total_categories),
    heroMounted,
);

const whyLearn = [
    {
        icon: CirclePlay,
        color: 'brand',
        title: 'Self-paced video lessons',
        description:
            'Watch on your own schedule, pause and rewind anything that needs a second look.',
    },
    {
        icon: Download,
        color: 'orange',
        title: 'Downloadable material',
        description:
            'Ebooks and notes attached to lessons, ready to keep even after you finish the course.',
    },
    {
        icon: ListChecks,
        color: 'success',
        title: 'Quizzes & progress tracking',
        description:
            'Short checks after each module so you actually know what stuck, not just what you watched.',
    },
];
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="min-h-screen bg-white dark:bg-gray-950">
        <LearnHeader />

        <!-- ── Hero ── -->
        <section class="relative overflow-hidden">
            <!-- gradient mesh atmosphere -->
            <div
                class="bg-brand-200/40 dark:bg-brand-500/10 pointer-events-none absolute -top-24 -left-24 -z-20 h-96 w-96 rounded-full blur-3xl"
            />
            <div
                class="pointer-events-none absolute -top-10 right-0 -z-20 h-80 w-80 rounded-full bg-orange-200/30 blur-3xl dark:bg-orange-500/10"
            />

            <!-- dot-grid atmosphere -->
            <div
                class="pointer-events-none absolute inset-0 -z-10 opacity-[0.4] dark:opacity-[0.15]"
                style="
                    background-image: radial-gradient(
                        var(--color-gray-300) 1px,
                        transparent 1px
                    );
                    background-size: 22px 22px;
                    mask-image: radial-gradient(
                        ellipse 60% 50% at 50% 0%,
                        black 40%,
                        transparent 100%
                    );
                "
            />

            <div
                class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-12 px-6 pt-16 pb-20 lg:grid-cols-[1.1fr_0.9fr] lg:pt-24 lg:pb-28"
            >
                <!-- copy -->
                <div>
                    <span
                        class="bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                    >
                        <Sparkles class="h-3.5 w-3.5" />
                        {{ stats.total_courses }} courses live right now
                    </span>

                    <h1
                        class="mt-5 text-4xl leading-[1.1] font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-[3.25rem] dark:text-white"
                    >
                        Learn a skill.
                        <br />
                        Finish what you
                        <span class="text-brand-500 relative inline-block">
                            start.
                            <svg
                                class="text-brand-200 dark:text-brand-500/40 absolute -bottom-1.5 left-0 w-full"
                                height="8"
                                viewBox="0 0 120 8"
                                preserveAspectRatio="none"
                                fill="none"
                            >
                                <path
                                    d="M2 6C20 2 40 2 60 4C80 6 100 2 118 4"
                                    stroke="currentColor"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </span>
                    </h1>

                    <p
                        class="mt-5 max-w-md text-base leading-relaxed text-gray-500 dark:text-gray-400"
                    >
                        Self-paced video lessons, downloadable material, and
                        short quizzes to check what actually stuck &mdash; all
                        in one place on {{ brand.name }}.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <Link
                            :href="learnRoutes.browse"
                            class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-[0_8px_20px_-6px_rgba(70,95,255,0.5)] transition"
                        >
                            <Search class="h-4 w-4" />
                            Browse courses
                        </Link>
                        <Link
                            v-if="isAuthenticated"
                            :href="learnRoutes.myCourses"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            Continue learning
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <!-- stat trio -->
                    <dl class="mt-10 flex gap-8">
                        <div>
                            <dt
                                class="text-2xl font-bold text-gray-900 tabular-nums dark:text-white"
                            >
                                {{ compact(coursesCount) }}
                            </dt>
                            <dd class="text-xs text-gray-500 dark:text-gray-400">
                                Courses
                            </dd>
                        </div>
                        <div class="border-l border-gray-200 pl-8 dark:border-gray-800">
                            <dt
                                class="text-2xl font-bold text-gray-900 tabular-nums dark:text-white"
                            >
                                {{ compact(studentsCount) }}
                            </dt>
                            <dd class="text-xs text-gray-500 dark:text-gray-400">
                                Students learning
                            </dd>
                        </div>
                        <div class="border-l border-gray-200 pl-8 dark:border-gray-800">
                            <dt
                                class="text-2xl font-bold text-gray-900 tabular-nums dark:text-white"
                            >
                                {{ compact(categoriesCount) }}
                            </dt>
                            <dd class="text-xs text-gray-500 dark:text-gray-400">
                                Categories
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- signature: mock lesson-progress card -->
                <div class="relative mx-auto w-full max-w-sm">
                    <div
                        class="relative rotate-2 rounded-2xl border border-gray-100 bg-white p-5 shadow-[0_30px_60px_-20px_rgba(16,24,40,0.25)] transition hover:rotate-0 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                UI Design Fundamentals
                            </p>
                            <span
                                class="bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                            >
                                62%
                            </span>
                        </div>
                        <div
                            class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                        >
                            <div
                                class="bg-brand-500 h-full w-[62%] rounded-full"
                            />
                        </div>

                        <ul class="mt-5 space-y-3">
                            <li class="flex items-center gap-3">
                                <span
                                    class="bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                                >
                                    <CheckCircle2 class="h-4 w-4" />
                                </span>
                                <span
                                    class="text-sm text-gray-400 line-through decoration-gray-300 dark:text-gray-500"
                                >
                                    Layout &amp; spacing basics
                                </span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span
                                    class="bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                                >
                                    <CheckCircle2 class="h-4 w-4" />
                                </span>
                                <span
                                    class="text-sm text-gray-400 line-through decoration-gray-300 dark:text-gray-500"
                                >
                                    Color theory in practice
                                </span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span
                                    class="bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                                >
                                    <CirclePlay class="h-4 w-4" />
                                </span>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    Building a component grid
                                </span>
                            </li>
                            <li class="flex items-center gap-3 opacity-50">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-gray-300 dark:border-gray-700"
                                />
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    Final project review
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- floating badge -->
                    <div
                        class="absolute -top-4 -left-4 flex items-center gap-2 rounded-xl border border-white/60 bg-white/80 px-3.5 py-2.5 shadow-lg backdrop-blur-md dark:border-gray-800/60 dark:bg-gray-900/80"
                    >
                        <span
                            class="bg-orange-50 text-orange-500 dark:bg-orange-500/15 flex h-8 w-8 items-center justify-center rounded-lg"
                        >
                            <Users class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-sm font-bold text-gray-900 tabular-nums dark:text-white">
                                {{ compact(studentsCount) }}+
                            </p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                learning today
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Why learn here ── -->
        <RevealOnScroll as="section" class="py-16">
            <div class="mx-auto max-w-6xl px-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div
                        v-for="item in whyLearn"
                        :key="item.title"
                        class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="absolute -top-8 -right-8 h-24 w-24 rounded-full opacity-0 blur-2xl transition duration-500 group-hover:opacity-100"
                            :class="{
                                'bg-brand-300/40': item.color === 'brand',
                                'bg-orange-300/40': item.color === 'orange',
                                'bg-success-300/40': item.color === 'success',
                            }"
                        />
                        <span
                            class="relative flex h-11 w-11 items-center justify-center rounded-xl"
                            :class="{
                                'bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400':
                                    item.color === 'brand',
                                'bg-orange-50 text-orange-500 dark:bg-orange-500/10':
                                    item.color === 'orange',
                                'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400':
                                    item.color === 'success',
                            }"
                        >
                            <component :is="item.icon" class="h-5 w-5" />
                        </span>
                        <h3
                            class="relative mt-4 text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            {{ item.title }}
                        </h3>
                        <p
                            class="relative mt-1.5 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ item.description }}
                        </p>
                    </div>
                </div>
            </div>
        </RevealOnScroll>

        <!-- ── Categories ── -->
        <RevealOnScroll
            v-if="categories.length"
            as="section"
            class="border-t border-gray-100 bg-gray-50/60 py-16 dark:border-gray-800 dark:bg-gray-900/40"
        >
            <div class="mx-auto max-w-6xl px-6">
                <div class="mb-8 flex items-end justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            Explore by category
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Find the track that matches what you want to learn.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <Link
                        v-for="(cat, i) in categories"
                        :key="cat.id"
                        :href="learnRoutes.browseByCategory(cat.id)"
                        class="hover:border-brand-200 dark:hover:border-brand-500/40 group flex flex-col items-center gap-2.5 rounded-2xl border border-gray-100 bg-white p-5 text-center transition duration-300 hover:-translate-y-1 hover:shadow-[0_16px_32px_-16px_rgba(70,95,255,0.35)] dark:border-gray-800 dark:bg-gray-900"
                    >
                        <span
                            class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 flex h-11 w-11 items-center justify-center rounded-xl transition duration-300 group-hover:scale-110"
                        >
                            <component :is="iconFor(i)" class="h-5 w-5" />
                        </span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            {{ cat.name }}
                        </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            {{ cat.courses_count }}
                            {{ cat.courses_count === 1 ? 'course' : 'courses' }}
                        </span>
                    </Link>
                </div>
            </div>
        </RevealOnScroll>

        <!-- ── Featured courses ── -->
        <RevealOnScroll as="section" class="py-16">
            <div class="mx-auto max-w-6xl px-6">
                <div class="mb-8 flex items-end justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            Recently added
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Fresh courses, ready whenever you are.
                        </p>
                    </div>
                    <Link
                        :href="learnRoutes.browse"
                        class="text-brand-500 hover:text-brand-600 hidden items-center gap-1 text-sm font-semibold sm:flex"
                    >
                        View all
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>

                <div
                    v-if="featuredCourses.length"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <Link
                        v-for="course in featuredCourses"
                        :key="course.id"
                        :href="learnRoutes.courseShow(course.id)"
                        class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-transparent hover:shadow-[0_20px_40px_-16px_rgba(70,95,255,0.3)] dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800"
                        >
                            <img
                                v-if="course.thumbnail"
                                :src="course.thumbnail"
                                :alt="course.title"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center"
                            >
                                <BookOpen class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                            </div>

                            <span
                                v-if="course.is_enrolled"
                                class="bg-success-500 absolute top-3 left-3 rounded-full px-2.5 py-1 text-[11px] font-semibold text-white"
                            >
                                Enrolled
                            </span>
                            <span
                                v-else-if="course.is_free"
                                class="absolute top-3 left-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-gray-700"
                            >
                                Free
                            </span>
                        </div>

                        <div class="p-4">
                            <p
                                v-if="course.category_name"
                                class="text-brand-500 dark:text-brand-400 text-[11px] font-semibold tracking-wide uppercase"
                            >
                                {{ course.category_name }}
                            </p>
                            <h3
                                class="mt-1 line-clamp-2 text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                {{ course.title }}
                            </h3>
                            <p
                                v-if="course.instructor_name"
                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ course.instructor_name }}
                            </p>

                            <div
                                class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800"
                            >
                                <span
                                    v-if="course.is_free"
                                    class="text-success-600 dark:text-success-400 text-sm font-bold"
                                >
                                    Free
                                </span>
                                <span
                                    v-else
                                    class="text-sm font-bold text-gray-900 dark:text-white"
                                >
                                    ৳{{ money(course.price) }}
                                </span>
                                <span
                                    class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500"
                                >
                                    <Users class="h-3.5 w-3.5" />
                                    {{ compact(course.enrollments_count) }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-dashed border-gray-200 py-16 text-center dark:border-gray-800"
                >
                    <BookOpen class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        No courses published yet &mdash; check back soon.
                    </p>
                </div>

                <div class="mt-8 flex justify-center sm:hidden">
                    <Link
                        :href="learnRoutes.browse"
                        class="text-brand-500 flex items-center gap-1 text-sm font-semibold"
                    >
                        View all courses
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </RevealOnScroll>

        <!-- ── CTA banner ── -->
        <RevealOnScroll as="section" class="px-6 pb-20">
            <div
                class="from-brand-500 via-brand-600 to-brand-700 relative mx-auto max-w-6xl overflow-hidden rounded-3xl bg-gradient-to-br px-8 py-14 text-center sm:px-16"
            >
                <div
                    class="pointer-events-none absolute -top-16 -right-16 h-64 w-64 rounded-full bg-orange-400/30 blur-3xl"
                />
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.15]"
                    style="
                        background-image: radial-gradient(
                            white 1px,
                            transparent 1px
                        );
                        background-size: 20px 20px;
                    "
                />
                <h2 class="relative text-2xl font-extrabold text-white sm:text-3xl">
                    Ready to start your next course?
                </h2>
                <p class="relative mx-auto mt-3 max-w-md text-sm text-white/80">
                    Pick a track, work through the lessons at your own pace,
                    and track every bit of progress along the way.
                </p>
                <Link
                    :href="learnRoutes.browse"
                    class="text-brand-600 relative mt-7 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl"
                >
                    Browse all courses
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>
        </RevealOnScroll>

        <LearnFooter />
    </div>
</template>