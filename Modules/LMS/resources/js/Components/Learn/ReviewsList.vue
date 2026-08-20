<script setup>
import { ChevronLeft, ChevronRight, Quote, Star } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    averageRating: {
        type: Number,
        default: null,
    },
    reviewsCount: {
        type: Number,
        default: 0,
    },
    reviews: {
        type: Array,
        default: () => [],
    },
    intervalMs: {
        type: Number,
        default: 4500,
    },
});

const activeIndex = ref(0);
let timer = null;

// Illustrated avatars (DiceBear) instead of the plain initials-based
// fallback — seeded by review id so each reviewer gets a consistent,
// distinct-looking illustrated character.
const avatarFor = (review) => {
    const seed = encodeURIComponent(review.student_name || review.id);
    return `https://api.dicebear.com/9.x/personas/svg?seed=${seed}&backgroundColor=fce7f3,dbeafe,dcfce7,fef9c3,ede9fe`;
};

const go = (index) => {
    const total = props.reviews.length;
    activeIndex.value = ((index % total) + total) % total;
};

const next = () => go(activeIndex.value + 1);
const prev = () => go(activeIndex.value - 1);

const startAutoplay = () => {
    stopAutoplay();
    if (props.reviews.length > 1) {
        timer = setInterval(next, props.intervalMs);
    }
};
const stopAutoplay = () => {
    if (timer) clearInterval(timer);
    timer = null;
};

onMounted(startAutoplay);
onUnmounted(stopAutoplay);

const active = computed(() => props.reviews[activeIndex.value]);
</script>

<template>
    <div v-if="reviewsCount > 0" class="rounded-2xl border border-gray-100 p-5 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <span class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ averageRating }}</span>
            <div>
                <div class="flex items-center gap-0.5">
                    <Star
                        v-for="star in 5"
                        :key="star"
                        class="h-4 w-4"
                        :class="
                            star <= Math.round(averageRating)
                                ? 'fill-amber-400 text-amber-400'
                                : 'text-gray-300 dark:text-gray-700'
                        "
                    />
                </div>
                <p class="mt-0.5 text-xs text-gray-400">
                    {{ reviewsCount }} {{ reviewsCount === 1 ? 'review' : 'reviews' }}
                </p>
            </div>
        </div>

        <!-- auto-sliding carousel -->
        <div
            class="relative mt-5 overflow-hidden"
            @mouseenter="stopAutoplay"
            @mouseleave="startAutoplay"
        >
            <Transition
                mode="out-in"
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-x-3"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0 -translate-x-3"
            >
                <div v-if="active" :key="active.id" class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                    <Quote class="text-brand-200 dark:text-brand-500/30 h-5 w-5" />

                    <p
                        v-if="active.comment"
                        class="mt-2 line-clamp-4 text-sm leading-relaxed text-gray-600 dark:text-gray-300"
                    >
                        {{ active.comment }}
                    </p>
                    <p v-else class="mt-2 text-sm text-gray-400 italic">No written comment.</p>

                    <div class="mt-4 flex items-center gap-3">
                        <img
                            :src="avatarFor(active)"
                            :alt="active.student_name"
                            class="h-10 w-10 shrink-0 rounded-full bg-gray-100 object-cover dark:bg-gray-800"
                        />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ active.student_name }}
                            </p>
                            <div class="mt-0.5 flex items-center gap-2">
                                <div class="flex items-center gap-0.5">
                                    <Star
                                        v-for="star in 5"
                                        :key="star"
                                        class="h-3 w-3"
                                        :class="
                                            star <= active.rating
                                                ? 'fill-amber-400 text-amber-400'
                                                : 'text-gray-300 dark:text-gray-700'
                                        "
                                    />
                                </div>
                                <span class="text-[11px] text-gray-400">{{ active.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- controls -->
            <div v-if="reviews.length > 1" class="mt-4 flex items-center justify-between">
                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-400 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5"
                    @click="prev"
                >
                    <ChevronLeft class="h-4 w-4" />
                </button>

                <div class="flex items-center gap-1.5">
                    <button
                        v-for="(review, i) in reviews"
                        :key="review.id"
                        type="button"
                        class="h-1.5 rounded-full transition-all"
                        :class="i === activeIndex ? 'bg-brand-500 w-5' : 'w-1.5 bg-gray-200 dark:bg-gray-700'"
                        @click="go(i)"
                    />
                </div>

                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-400 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5"
                    @click="next"
                >
                    <ChevronRight class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>