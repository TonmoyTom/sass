<template>
  <div :class="wrapperClass">
    <label
      v-if="label"
      :for="id"
      class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
    >
      {{ label }}
    </label>

    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :autocomplete="autocomplete"
      class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
      :class="error
        ? 'border-error-500 dark:border-error-500'
        : 'border-gray-300 dark:border-gray-700'"
      @input="$emit('update:modelValue', $event.target.value)"
    />

    <p v-if="error" class="mt-1.5 text-sm text-red-500">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
let uid = 0

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  autocomplete: { type: String, default: 'off' },
  wrapperClass: { type: String, default: '' },
})

defineEmits(['update:modelValue'])

const id = `field-${++uid}`
</script>
