<template>
    <Head :title="pageTitle" />
    <div class="min-h-screen xl:flex">
        <WorkspaceSidebar />
        <Backdrop />
        <div
            class="flex-1 transition-all duration-300 ease-in-out"
            :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']"
        >
            <AppHeader />
            <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppHeader from '../Components/ui/AppHeader.vue';
import Backdrop from '../Components/ui/Backdrop.vue';
import WorkspaceSidebar from '../Components/ui/WorkspaceSidebar.vue';
import { useSidebar } from '../composables/useSidebar.js';

const { isExpanded, isHovered } = useSidebar();

const props = defineProps({
    title: { type: String, default: 'Workspace' },
});

const page = usePage();

const companyName = computed(
    () => page.props.workspace?.company_name ?? 'Workspace',
);

const pageTitle = computed(() => `${props.title} - ${companyName.value}`);
</script>
