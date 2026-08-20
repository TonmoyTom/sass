<script setup>
import { ref, computed } from 'vue'
import { GraduationCap } from 'lucide-vue-next'

import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import LmsCourseDashboard from '@/Components/Dashboard/LmsCourseDashboard.vue';
import LmsStudentDashboard from '@/Components/Dashboard/LmsStudentDashboard.vue';

const props = defineProps({
  lms: { type: Object, default: null },
})

// ── module tabs — one tab per enabled module with dashboard data wired
// up. Future modules (E-commerce, POS) slot in here the same way once
// their own dashboard data exists. No "Overview" tab — modules only. ──
const tabs = computed(() => {
  const list = [];
  if (props.lms) list.push({ key: 'lms', label: 'LMS', icon: GraduationCap });
  return list;
});
const activeTab = ref(tabs.value[0]?.key ?? null);
</script>

<template>
   <WorkspaceLayout title="Dashboard">
  <div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-900">
    <div class="mx-auto  space-y-6 px-4">

      <!-- ── Module tabs ── -->
      <div v-if="tabs.length > 0" class="inline-flex gap-1.5 rounded-2xl border border-gray-100 bg-white p-1.5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold whitespace-nowrap transition"
          :class="activeTab === tab.key ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5'"
          @click="activeTab = tab.key"
        >
          <component :is="tab.icon" v-if="tab.icon" class="h-4 w-4" />
          {{ tab.label }}
        </button>
      </div>

      <!-- ── LMS tab ── -->
      <div v-if="activeTab === 'lms' && lms">
        <LmsCourseDashboard v-if="lms.view === 'admin' || lms.view === 'instructor'" :data="lms" />
        <LmsStudentDashboard v-else-if="lms.view === 'student'" :data="lms" />
      </div>


    </div>
  </div>
  </WorkspaceLayout>
</template>
