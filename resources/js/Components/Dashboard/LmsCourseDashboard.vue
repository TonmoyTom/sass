<script setup>
import { computed } from 'vue';
import { BookOpen, ClipboardList, GraduationCap, HelpCircle, Star, TrendingUp, Users } from 'lucide-vue-next';

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
});

const isAdmin = computed(() => props.data.view === 'admin');

const taka = (n) =>
    '৳' + Number(n || 0).toLocaleString('en-BD', { maximumFractionDigits: 0 });

// ── simple SVG area chart, same technique as the sales-overview chart above ──
const W = 760;
const H = 140;
const chart = computed(() => {
    const pts = props.data.revenue_chart ?? [];
    if (pts.length < 2) return null;
    const max = Math.max(...pts);
    const min = Math.min(...pts);
    const range = max - min || 1;
    const step = W / (pts.length - 1);
    const c = pts.map((v, i) => [
        +(i * step).toFixed(1),
        +(H - 10 - ((v - min) / range) * (H - 20)).toFixed(1),
    ]);
    const line = c.map(([x, y], i) => `${i ? 'L' : 'M'}${x},${y}`).join(' ');
    return { line, area: `${line} L${W},${H} L0,${H} Z`, last: c.at(-1) };
});

const months = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
</script>

<template>
    <div class="space-y-6">
        <!-- stat tiles -->
        <div class="grid grid-cols-2 gap-4" :class="isAdmin ? 'lg:grid-cols-4' : 'lg:grid-cols-3'">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 flex h-9 w-9 items-center justify-center rounded-lg">
                    <BookOpen class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.total_courses }}</p>
                <p class="text-xs text-gray-400">{{ isAdmin ? 'Total courses' : 'Your courses' }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 text-purple-500 dark:bg-purple-500/10 dark:text-purple-400">
                    <Users class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.total_students }}</p>
                <p class="text-xs text-gray-400">{{ isAdmin ? 'Total students' : 'Your students' }}</p>
            </div>
            <div v-if="isAdmin" class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 flex h-9 w-9 items-center justify-center rounded-lg">
                    <TrendingUp class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ taka(data.revenue) }}</p>
                <p class="text-xs text-gray-400">Revenue (12mo)</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-500 dark:bg-amber-500/10 dark:text-amber-400">
                    <ClipboardList class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.pending_grading }}</p>
                <p class="text-xs text-gray-400">Pending grading</p>
            </div>
        </div>

        <!-- extra admin-only detail tiles -->
        <div v-if="isAdmin" class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-500 dark:bg-sky-500/10 dark:text-sky-400">
                    <HelpCircle class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.total_quizzes }}</p>
                <p class="text-xs text-gray-400">Total quizzes</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <ClipboardList class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.total_assignments }}</p>
                <p class="text-xs text-gray-400">Total assignments</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-500 dark:bg-amber-500/10 dark:text-amber-400">
                    <GraduationCap class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.total_certificates }}</p>
                <p class="text-xs text-gray-400">Certificates issued</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-pink-50 text-pink-500 dark:bg-pink-500/10 dark:text-pink-400">
                    <Star class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ data.avg_rating ?? '—' }}
                </p>
                <p class="text-xs text-gray-400">Avg. course rating</p>
            </div>
        </div>

        <!-- revenue chart (admin only) -->
        <section
            v-if="isAdmin"
            class="rounded-2xl border border-gray-100 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <h2 class="font-semibold text-gray-900 dark:text-white">Course revenue</h2>
            <p class="text-sm text-gray-400">Last 12 months · {{ data.total_orders }} completed orders</p>

            <svg v-if="chart" :viewBox="`0 0 ${W} ${H}`" class="mt-4 h-36 w-full" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="lmsRevenueFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="rgb(99 102 241)" stop-opacity="0.18" />
                        <stop offset="100%" stop-color="rgb(99 102 241)" stop-opacity="0" />
                    </linearGradient>
                </defs>
                <path :d="chart.area" fill="url(#lmsRevenueFill)" />
                <path :d="chart.line" fill="none" stroke="rgb(99 102 241)" stroke-width="2.5" stroke-linejoin="round" />
                <circle :cx="chart.last[0]" :cy="chart.last[1]" r="4.5" fill="white" stroke="rgb(99 102 241)" stroke-width="2.5" />
            </svg>
            <div v-else class="mt-4 flex h-36 items-center justify-center text-sm text-gray-400">
                No revenue data yet
            </div>
            <div v-if="chart" class="mt-1 flex justify-between text-[11px] text-gray-400">
                <span v-for="m in months" :key="m">{{ m }}</span>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- top courses -->
            <section class="rounded-2xl border border-gray-100 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="mb-4 font-semibold text-gray-900 dark:text-white">Top courses</h2>
                <div v-if="data.top_courses?.length" class="space-y-3">
                    <div
                        v-for="(course, i) in data.top_courses"
                        :key="course.id"
                        class="flex items-center gap-3"
                    >
                        <span class="w-5 shrink-0 text-sm font-semibold text-gray-400">{{ i + 1 }}</span>
                        <p class="min-w-0 flex-1 truncate text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ course.title }}
                        </p>
                        <span class="shrink-0 text-xs text-gray-400">{{ course.enrollments }} students</span>
                    </div>
                </div>
                <p v-else class="py-6 text-center text-sm text-gray-400">No courses yet</p>
            </section>

            <!-- recent enrollments -->
            <section class="rounded-2xl border border-gray-100 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="mb-4 font-semibold text-gray-900 dark:text-white">Recent enrollments</h2>
                <div v-if="data.recent_enrollments?.length" class="space-y-3">
                    <div
                        v-for="(e, i) in data.recent_enrollments"
                        :key="i"
                        class="flex items-center justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">
                                {{ e.student_name }}
                            </p>
                            <p class="truncate text-xs text-gray-400">{{ e.course_title }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-gray-400">{{ e.enrolled_at }}</span>
                    </div>
                </div>
                <p v-else class="py-6 text-center text-sm text-gray-400">No enrollments yet</p>
            </section>

            <!-- recent orders (admin only) -->
            <section
                v-if="isAdmin"
                class="rounded-2xl border border-gray-100 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] lg:col-span-2"
            >
                <h2 class="mb-4 font-semibold text-gray-900 dark:text-white">Recent orders</h2>
                <div v-if="data.recent_orders?.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="(o, i) in data.recent_orders"
                        :key="i"
                        class="flex items-center justify-between gap-3 py-3 first:pt-0"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">
                                {{ o.student_name }}
                            </p>
                            <p class="truncate text-xs text-gray-400">{{ o.course_title }} · {{ o.purchased_at }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-semibold capitalize"
                                :class="
                                    o.status === 'completed'
                                        ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'
                                        : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'
                                "
                            >
                                {{ o.status }}
                            </span>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ o.amount > 0 ? taka(o.amount) : 'Free' }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="py-6 text-center text-sm text-gray-400">No orders yet</p>
            </section>
        </div>
    </div>
</template>
