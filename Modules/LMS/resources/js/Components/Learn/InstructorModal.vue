<script setup>
import { stripHtml } from '@/composables/text.js';
import { GraduationCap } from 'lucide-vue-next';

defineProps({
    instructor: {
        type: Object,
        default: null,
    },
    top: {
        type: Number,
        default: 0,
    },
    left: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="instructor"
                class="pointer-events-none fixed z-[9999] w-72 rounded-2xl border border-gray-100 bg-white p-4 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                :style="{ top: top + 'px', left: left + 'px' }"
            >
                <div class="flex items-center gap-3">
                    <img
                        :src="instructor.avatar"
                        :alt="instructor.name"
                        class="h-12 w-12 shrink-0 rounded-full object-cover"
                    />
                    <p class="flex min-w-0 items-center gap-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                        <GraduationCap class="text-brand-500 h-3.5 w-3.5 shrink-0" />
                        {{ instructor.name }}
                    </p>
                </div>

                <p
                    v-if="instructor.bio"
                    class="mt-3 text-xs leading-relaxed whitespace-pre-line text-gray-500 dark:text-gray-400"
                >
                    {{ stripHtml(instructor.bio) }}
                </p>
                <p v-else class="mt-3 text-xs text-gray-400">
                    No bio added yet.
                </p>
            </div>
        </Transition>
    </Teleport>
</template>