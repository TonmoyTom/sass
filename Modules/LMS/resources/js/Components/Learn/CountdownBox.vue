<script setup>
import { useCountdown } from '@modules/LMS/resources/js/composables/useCountdown.js';
import { toRef } from 'vue';

const props = defineProps({
    targetDate: {
        type: [String, Date],
        default: null,
    },
    label: {
        type: String,
        default: 'লাইভ ক্লাস শুরু হতে বাকি আছে আর -',
    },
});

const { days, hours, minutes, seconds, isPast, isActive } = useCountdown(toRef(props, 'targetDate'));

const pad = (n) => String(n).padStart(2, '0');
</script>

<template>
    <div
        v-if="isActive && !isPast"
        class="flex flex-col items-center gap-4 rounded-2xl border border-gray-200 bg-white px-6 py-5 sm:flex-row sm:justify-between dark:border-gray-800 dark:bg-gray-900"
    >
        <p class="text-center text-sm font-semibold text-gray-700 sm:text-left dark:text-gray-200">
            {{ label }}
        </p>

        <div class="flex items-center gap-3 sm:gap-5">
            <div class="text-center">
                <p class="text-2xl font-extrabold text-gray-900 tabular-nums sm:text-3xl dark:text-white">
                    {{ days }}
                </p>
                <p class="mt-0.5 text-[11px] font-medium tracking-wide text-gray-400 uppercase">Days</p>
            </div>
            <span class="text-xl font-bold text-gray-300 dark:text-gray-700">:</span>
            <div class="text-center">
                <p class="text-2xl font-extrabold text-gray-900 tabular-nums sm:text-3xl dark:text-white">
                    {{ pad(hours) }}
                </p>
                <p class="mt-0.5 text-[11px] font-medium tracking-wide text-gray-400 uppercase">Hours</p>
            </div>
            <span class="text-xl font-bold text-gray-300 dark:text-gray-700">:</span>
            <div class="text-center">
                <p class="text-2xl font-extrabold text-gray-900 tabular-nums sm:text-3xl dark:text-white">
                    {{ pad(minutes) }}
                </p>
                <p class="mt-0.5 text-[11px] font-medium tracking-wide text-gray-400 uppercase">Minutes</p>
            </div>
            <span class="text-xl font-bold text-gray-300 dark:text-gray-700">:</span>
            <div class="text-center">
                <p class="text-2xl font-extrabold text-gray-900 tabular-nums sm:text-3xl dark:text-white">
                    {{ pad(seconds) }}
                </p>
                <p class="mt-0.5 text-[11px] font-medium tracking-wide text-gray-400 uppercase">Seconds</p>
            </div>
        </div>
    </div>
</template>