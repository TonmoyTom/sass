<script setup>
import { useCountdown } from '@modules/LMS/resources/js/composables/useCountdown.js';
import { computed, toRef } from 'vue';

const props = defineProps({
    targetDate: {
        type: [String, Date],
        default: null,
    },
    label: {
        type: String,
        default: 'লাইভ ক্লাস শুরু হতে বাকি আছে',
    },
});

const { days, hours, minutes, seconds, isPast, isActive } = useCountdown(toRef(props, 'targetDate'));

const pad = (n) => String(n).padStart(2, '0');

const units = computed(() => [
    { label: 'd', value: days.value },
    { label: 'h', value: pad(hours.value) },
    { label: 'm', value: pad(minutes.value) },
    { label: 's', value: pad(seconds.value) },
]);
</script>

<template>
    <div v-if="isActive && !isPast" class="flex flex-wrap items-center gap-3">
        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
            {{ label }} -
        </span>
        <div class="flex items-center gap-1.5">
            <template v-for="(unit, i) in units" :key="unit.label">
                <div
                    class="bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 flex items-baseline gap-0.5 rounded-lg px-2.5 py-1.5 text-sm font-bold tabular-nums"
                >
                    {{ unit.value }}<span class="text-xs font-medium opacity-70">{{ unit.label }}</span>
                </div>
                <span v-if="i < units.length - 1" class="text-gray-300 dark:text-gray-600">:</span>
            </template>
        </div>
    </div>
</template>