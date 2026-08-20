<script setup>
import { Link } from '@inertiajs/vue3';
import { BookOpen, CheckCircle2, Clock, PlayCircle } from 'lucide-vue-next';

defineProps({
    data: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="space-y-6">
        <!-- stat tiles -->
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 flex h-9 w-9 items-center justify-center rounded-lg">
                    <BookOpen class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.total_enrolled }}</p>
                <p class="text-xs text-gray-400">Enrolled courses</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-500 dark:bg-amber-500/10 dark:text-amber-400">
                    <PlayCircle class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.in_progress }}</p>
                <p class="text-xs text-gray-400">In progress</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400 flex h-9 w-9 items-center justify-center rounded-lg">
                    <CheckCircle2 class="h-4.5 w-4.5" />
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ data.completed }}</p>
                <p class="text-xs text-gray-400">Completed</p>
            </div>
        </div>

        <!-- upcoming deadlines -->
        <section class="rounded-2xl border border-gray-100 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="mb-4 flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                <Clock class="h-4 w-4 text-amber-500" />
                Upcoming assignment deadlines
            </h2>
            <div v-if="data.upcoming_assignments?.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                <div
                    v-for="(a, i) in data.upcoming_assignments"
                    :key="i"
                    class="flex items-center justify-between gap-3 py-3 first:pt-0"
                >
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ a.title }}</p>
                    <span class="shrink-0 text-xs font-medium text-amber-600 dark:text-amber-400">
                        Due {{ a.due_date }}
                    </span>
                </div>
            </div>
            <p v-else class="py-6 text-center text-sm text-gray-400">
                No upcoming deadlines — you're all caught up!
            </p>
        </section>

        <Link
            href="/lms/my-courses"
            class="bg-brand-500 hover:bg-brand-600 flex items-center justify-center gap-2 rounded-2xl py-3 text-sm font-semibold text-white transition"
        >
            Go to My Courses
        </Link>
    </div>
</template>
