<template>
  <div class="min-h-screen xl:flex">
    <AppSidebar />
    <Backdrop />
    <div
      class="flex-1 transition-all duration-300 ease-in-out"
      :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']"
    >
      <AppHeader />
      <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
const { isExpanded, isHovered } = useSidebar()
import AppSidebar from '../Components/ui/AppSidebar.vue'
import AppHeader from '../Components/ui/AppHeader.vue'
import { useSidebar } from '../composables/useSidebar.js'
import Backdrop from '../Components/ui/Backdrop.vue'

</script>
