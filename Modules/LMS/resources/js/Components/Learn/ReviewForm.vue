<script setup>
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { useForm } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    courseId: {
        type: [String, Number],
        required: true,
    },
    ownReview: {
        type: Object,
        default: null,
    },
});

const hoverRating = ref(0);
const submitted = ref(false);

const form = useForm({
    rating: props.ownReview?.rating ?? 0,
    comment: props.ownReview?.comment ?? '',
});

const submit = () => {
    form.post(learnRoutes.submitReview(props.courseId), {
        preserveScroll: true,
        onSuccess: () => {
            submitted.value = true;
        },
    });
};
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
            {{ ownReview ? 'Your review' : 'Leave a review' }}
        </p>
        <p v-if="!ownReview" class="mt-0.5 text-xs text-gray-400">
            Let other students know what you thought of this course.
        </p>

        <div v-if="submitted && !ownReview" class="bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400 mt-3 rounded-xl px-4 py-3 text-sm font-medium">
            Thanks for your review!
        </div>

        <form class="mt-3 space-y-3" @submit.prevent="submit">
            <div class="flex items-center gap-1">
                <button
                    v-for="star in 5"
                    :key="star"
                    type="button"
                    class="p-0.5"
                    @click="form.rating = star"
                    @mouseenter="hoverRating = star"
                    @mouseleave="hoverRating = 0"
                >
                    <Star
                        class="h-6 w-6 transition"
                        :class="
                            star <= (hoverRating || form.rating)
                                ? 'fill-amber-400 text-amber-400'
                                : 'text-gray-300 dark:text-gray-600'
                        "
                    />
                </button>
                <span v-if="form.rating" class="ml-2 text-xs text-gray-400">{{ form.rating }}/5</span>
            </div>
            <p v-if="form.errors.rating" class="text-error-500 text-xs">
                {{ form.errors.rating }}
            </p>

            <textarea
                v-model="form.comment"
                rows="3"
                placeholder="What did you like or think could be better? (optional)"
                class="focus:border-brand-300 dark:focus:border-brand-500 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />

            <button
                type="submit"
                :disabled="!form.rating || form.processing"
                class="bg-brand-500 hover:bg-brand-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{ form.processing ? 'Saving...' : ownReview ? 'Update review' : 'Submit review' }}
            </button>
        </form>
    </div>
</template>