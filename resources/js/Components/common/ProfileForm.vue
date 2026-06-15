<template>
  <form class="flex flex-col" @submit.prevent="submit">
    <div class="custom-scrollbar h-[458px] overflow-y-auto p-2">
      <!-- Social Links -->
      <div>
        <h5 class="mb-5 text-lg font-medium text-gray-800 lg:mb-6 dark:text-white/90">
          Social Links
        </h5>

        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
          <FormInput v-model="form.facebook" label="Facebook" :error="form.errors.facebook" />
          <FormInput v-model="form.twitter" label="X.com" :error="form.errors.twitter" />
          <FormInput v-model="form.linkedin" label="Linkedin" :error="form.errors.linkedin" />
          <FormInput v-model="form.instagram" label="Instagram" :error="form.errors.instagram" />
        </div>
      </div>

      <!-- Personal Information -->
      <div class="mt-7">
        <h5 class="mb-5 text-lg font-medium text-gray-800 lg:mb-6 dark:text-white/90">
          Personal Information
        </h5>

        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
          <FormInput
            v-model="form.first_name"
            label="First Name"
            :error="form.errors.first_name"
            wrapper-class="col-span-2 lg:col-span-1"
          />
          <FormInput
            v-model="form.last_name"
            label="Last Name"
            :error="form.errors.last_name"
            wrapper-class="col-span-2 lg:col-span-1"
          />
          <FormInput
            v-model="form.email"
            label="Email Address"
            type="email"
            :error="form.errors.email"
            wrapper-class="col-span-2 lg:col-span-1"
          />

          <div class="col-span-2 lg:col-span-1">
            <PhoneInput
              v-model="form.phone"
              label="Phone"
              default-country="BD"
              :error="form.errors.phone"
              @data="phoneMeta = $event"
            />
          </div>

          <FormInput
            v-model="form.bio"
            label="Bio"
            :error="form.errors.bio"
            wrapper-class="col-span-2"
          />
        </div>
      </div>
    </div>

    <div class="mt-6 flex items-center gap-3 px-2 lg:justify-end">
      <button
        type="button"
        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
        @click="$emit('cancel')"
      >
        Close
      </button>
      <button
        type="submit"
        :disabled="form.processing"
        class="bg-brand-500 hover:bg-brand-600 flex w-full justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white disabled:opacity-50 sm:w-auto"
      >
        {{ form.processing ? 'Saving...' : 'Save Changes' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/Components/ui/FormInput.vue'
import PhoneInput from '@/Components/ui/PhoneInput.vue'

const props = defineProps({
  user: { type: Object, default: () => ({}) },
  action: { type: String, default: '' }, // route URL, optional override
})

const emit = defineEmits(['cancel', 'success'])

const phoneMeta = ref(null)

const form = useForm({
  first_name: props.user?.info?.first_name ?? '',
  last_name: props.user?.info?.last_name ?? '',
  email: props.user?.email ?? '',
  phone: props.user?.phone ?? '',
  bio: props.user?.info?.bio ?? '',
  facebook: props.user?.info?.facebook ?? '',
  twitter: props.user?.info?.twitter ?? '',
  linkedin: props.user?.info?.lnkedin ?? '', 
  instagram: props.user?.info?.instagram ?? '',
})

const submit = () => {
  const url = props.action || route('profile.update')

  form.patch(url, {
    preserveScroll: true,
    onSuccess: () => emit('success'),
  })
}
</script>
