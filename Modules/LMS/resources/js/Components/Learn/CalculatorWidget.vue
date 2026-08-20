<script setup>
import FloatingPanel from '@modules/LMS/resources/js/Components/Learn/FloatingPanel.vue';
import { evaluate } from 'mathjs';
import { computed, ref } from 'vue';

defineEmits(['close']);

const expression = ref('');
const error = ref(false);

// live preview of the result as the student types, without committing it
const preview = computed(() => {
    if (!expression.value) return null;
    try {
        const result = evaluate(expression.value);
        return typeof result === 'number' && Number.isFinite(result) ? formatResult(result) : null;
    } catch {
        return null;
    }
});

const formatResult = (value) => {
    // avoid ugly floating-point tails like 0.1 + 0.2 = 0.30000000000000004
    const rounded = Math.round(value * 1e10) / 1e10;
    return String(rounded);
};

const append = (token) => {
    error.value = false;
    expression.value += token;
};

const clear = () => {
    expression.value = '';
    error.value = false;
};

const backspace = () => {
    expression.value = expression.value.slice(0, -1);
    error.value = false;
};

const equals = () => {
    if (!expression.value) return;
    try {
        const result = evaluate(expression.value);
        expression.value = formatResult(result);
        error.value = false;
    } catch {
        error.value = true;
    }
};

const percent = () => {
    // wrap the whole expression as a percentage of itself, e.g. "50" -> "(50)/100"
    if (!expression.value) return;
    expression.value = `(${expression.value})/100`;
};

const buttons = [
    { label: 'C', action: clear, class: 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' },
    { label: '⌫', action: backspace, class: 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' },
    { label: '%', action: percent, class: 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' },
    { label: '÷', action: () => append('/'), class: 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400' },
    { label: '7', action: () => append('7') },
    { label: '8', action: () => append('8') },
    { label: '9', action: () => append('9') },
    { label: '×', action: () => append('*'), class: 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400' },
    { label: '4', action: () => append('4') },
    { label: '5', action: () => append('5') },
    { label: '6', action: () => append('6') },
    { label: '−', action: () => append('-'), class: 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400' },
    { label: '1', action: () => append('1') },
    { label: '2', action: () => append('2') },
    { label: '3', action: () => append('3') },
    { label: '+', action: () => append('+'), class: 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400' },
    { label: '(', action: () => append('('), class: 'bg-gray-50 dark:bg-gray-800/60 text-gray-500' },
    { label: '0', action: () => append('0') },
    { label: ')', action: () => append(')'), class: 'bg-gray-50 dark:bg-gray-800/60 text-gray-500' },
    { label: '.', action: () => append('.') },
    { label: '√', action: () => append('sqrt('), class: 'bg-gray-50 dark:bg-gray-800/60 text-gray-500' },
    { label: 'x²', action: () => append('^2'), class: 'bg-gray-50 dark:bg-gray-800/60 text-gray-500' },
    { label: '=', action: equals, class: 'bg-brand-500 text-white hover:bg-brand-600 col-span-2' },
];
</script>

<template>
    <FloatingPanel title="Calculator" :width="280" :min-width="260" @close="$emit('close')">
        <div
            class="mb-3 rounded-xl px-3 py-4 text-right"
            :class="error ? 'bg-error-50 dark:bg-error-500/10' : 'bg-gray-50 dark:bg-gray-800'"
        >
            <p class="h-4 truncate text-xs text-gray-400">{{ error ? 'Invalid expression' : preview ?? '' }}</p>
            <p class="mt-0.5 min-h-[2rem] truncate text-2xl font-bold text-gray-900 dark:text-white">
                {{ expression || '0' }}
            </p>
        </div>
        <div class="grid grid-cols-4 gap-1.5">
            <button
                v-for="btn in buttons"
                :key="btn.label"
                type="button"
                class="rounded-lg py-2.5 text-sm font-semibold transition hover:opacity-80"
                :class="[btn.class ?? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-gray-100', btn.span ? 'col-span-2' : '']"
                @click="btn.action"
            >
                {{ btn.label }}
            </button>
        </div>
        <p class="mt-2 text-center text-[11px] text-gray-400">
            Supports +, −, ×, ÷, brackets, √, and powers
        </p>
    </FloatingPanel>
</template>