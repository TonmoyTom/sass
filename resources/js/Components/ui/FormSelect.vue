<template>
    <div :class="wrapperClass">
        <label
            v-if="label"
            :for="id"
            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
        >
            {{ label }}
        </label>

        <div class="relative">
            <select
                :id="id"
                :value="modelValue"
                :disabled="disabled"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full appearance-none rounded-lg border bg-transparent bg-none px-4 py-2.5 pr-10 text-sm text-gray-800 focus:ring-3 focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-900 dark:text-white/90"
                :class="
                    error
                        ? 'border-error-500 dark:border-error-500'
                        : 'border-gray-300 dark:border-gray-700'
                "
                @change="$emit('update:modelValue', $event.target.value)"
            >
                <option v-if="placeholder" value="" disabled>
                    {{ placeholder }}
                </option>
                <option
                    v-for="opt in normalizedOptions"
                    :key="opt.value"
                    :value="opt.value"
                >
                    {{ opt.label }}
                </option>
            </select>

            <!-- chevron icon -->
            <span
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400"
            >
                <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M6 9l6 6 6-6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </span>
        </div>

        <p v-if="error" class="mt-1.5 text-sm text-red-500">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

let uid = 0;

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    options: { type: [Array, Object], default: () => [] },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    wrapperClass: { type: String, default: '' },
});

defineEmits(['update:modelValue']);

const id = `select-${++uid}`;

// normalize all option formats to [{ value, label }]
const normalizedOptions = computed(() => {
    const opts = props.options;

    if (Array.isArray(opts)) {
        return opts.map((o) =>
            typeof o === 'object' && o !== null
                ? { value: o.value, label: o.label ?? o.value }
                : { value: o, label: o },
        );
    }

    // object: { key: 'Label' }
    return Object.entries(opts).map(([value, label]) => ({ value, label }));
});
</script>
