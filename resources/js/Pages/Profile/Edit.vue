<template>
 <AdminLayout>
    <div
      class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6"
    >
      <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-7">Profile</h3>
      <PHeader />
      <Personal/>
      <Address />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue'
import PHeader from '../../cComponents/ui/Profle/PHeader'
import Personal from '../../Components/ui/Profle/Personal.vue'
import Address from '../../Components/ui/Profle/Address.vue'
const currentPageTitle = ref('User Profile')

const props = defineProps({
  mustVerifyEmail: Boolean,
  status: String,
  user: Object,
})
</script>
