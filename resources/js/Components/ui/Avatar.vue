<!-- resources/js/Components/ui/Avatar.vue -->
<template>
  <div class="relative inline-block" :class="sizeClass">
    <img
      :src="imgSrc"
      :alt="alt"
      class="h-full w-full rounded-full object-cover"
      @error="onError"
    />

    <!-- upload button (only if uploadable) -->
    <template v-if="uploadable">
      <button
        type="button"
        :disabled="form.processing"
        class="absolute -bottom-0.5 -right-0.5 flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
        title="Change photo"
        @click="fileInput?.click()"
      >
        <svg
          v-if="!form.processing"
          class="fill-current"
          width="14"
          height="14"
          viewBox="0 0 18 18"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206Z"
            fill=""
          />
        </svg>
        <svg
          v-else
          class="animate-spin"
          width="14"
          height="14"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25" />
          <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
        </svg>
      </button>

      <input
        ref="fileInput"
        type="file"
        accept="image/png, image/jpeg, image/jpg, image/webp"
        class="hidden"
        @change="onFileChange"
      />
    </template>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  src: { type: String, default: '/images/user/owner.jpg' },
  alt: { type: String, default: 'user' },
  size: { type: String, default: 'md' }, // sm | md | lg | xl
  fallback: { type: String, default: '/images/user/owner.jpg' },
  uploadable: { type: Boolean, default: false },
  uploadUrl: { type: String, default: '' }, // route override
})

const emit = defineEmits(['uploaded', 'error'])

const fileInput = ref(null)
const imgSrc = ref(props.src || props.fallback)

watch(
  () => props.src,
  (val) => {
    imgSrc.value = val || props.fallback
  }
)

const onError = () => {
  imgSrc.value = props.fallback
}

const sizes = {
  sm: 'h-8 w-8',
  md: 'h-12 w-12',
  lg: 'h-16 w-16',
  xl: 'h-24 w-24',
}
const sizeClass = sizes[props.size] || sizes.md

// upload logic
const form = useForm({ avatar: null })

const onFileChange = (e) => {
  const file = e.target.files?.[0]
  if (!file) return

  // instant local preview
  imgSrc.value = URL.createObjectURL(file)
  form.avatar = file

  const url = props.uploadUrl || route('profile.avatar.update')

  form.post(url, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => emit('uploaded'),
    onError: () => {
      imgSrc.value = props.src || props.fallback // revert on fail
      emit('error', form.errors.avatar)
    },
    onFinish: () => {
      if (fileInput.value) fileInput.value.value = '' // reset input
    },
  })
}
</script>
