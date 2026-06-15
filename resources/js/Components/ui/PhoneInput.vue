<template>
  <div>
    <label
      v-if="label"
      class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
    >
      {{ label }}
    </label>

    <vue-tel-input
      v-model="phoneValue"
      :default-country="defaultCountry"
      :preferred-countries="preferredCountries"
      mode="international"
      :input-options="{ placeholder: 'Phone number' }"
      class="phone-custom"
      @on-input="onInput"
    />

    <p v-if="error" class="mt-1.5 text-sm text-red-500">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { VueTelInput } from 'vue-tel-input'
import 'vue-tel-input/vue-tel-input.css'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Phone' },
  defaultCountry: { type: String, default: 'BD' },
  preferredCountries: { type: Array, default: () => ['BD', 'IN', 'US', 'GB'] },
  error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'data'])

const phoneValue = ref(props.modelValue)

watch(
  () => props.modelValue,
  (val) => {
    if (val !== phoneValue.value) phoneValue.value = val
  }
)

const onInput = (number, phoneObject) => {
  // phoneObject: { number: { international, national, e164 }, valid, country, ... }
  emit('update:modelValue', phoneObject?.number ?? number)
  emit('data', phoneObject)
}
</script>

<style scoped>
.phone-custom {
  height: 2.75rem; /* h-11 */
  border-radius: 0.5rem; /* rounded-lg */
  border: 1px solid #d1d5db; /* gray-300 */
  background-color: transparent;
}

.phone-custom:focus-within {
  border-color: #7592ff; /* brand-300 */
  box-shadow: 0 0 0 3px rgb(70 95 255 / 0.1);
}

.phone-custom :deep(.vti__input) {
  background-color: transparent;
  border-radius: 0.5rem;
  font-size: 0.875rem; /* text-sm */
  color: #1f2937; /* gray-800 */
}

.phone-custom :deep(.vti__input::placeholder) {
  color: #9ca3af; /* gray-400 */
}

.phone-custom :deep(.vti__dropdown) {
  border-radius: 0.5rem 0 0 0.5rem;
}

.phone-custom :deep(.vti__dropdown:hover) {
  background-color: #f9fafb; /* gray-50 */
}

/* dark mode */
:global(html.dark) .phone-custom {
  border-color: #374151; /* gray-700 */
  background-color: #111827; /* gray-900 */
}

:global(html.dark) .phone-custom :deep(.vti__input) {
  background-color: #111827;
  color: rgba(255, 255, 255, 0.9);
}

:global(html.dark) .phone-custom :deep(.vti__dropdown:hover) {
  background-color: rgba(255, 255, 255, 0.05);
}

:global(html.dark) .phone-custom :deep(.vti__dropdown-list) {
  background-color: #1f2937; /* gray-800 */
  color: rgba(255, 255, 255, 0.9);
  border-color: #374151;
}
</style>
