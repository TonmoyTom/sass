<template>
    <Head :title="pageTitle" />
    <div class="min-h-screen xl:flex">
        <ImpersonationBanner />

        <AppSidebar />
        <Backdrop />
        <div
            class="flex-1 transition-all duration-300 ease-in-out"
            :class="[
                isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]',
            ]"
        >
            <AppHeader />
            <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import ImpersonationBanner from '@/Components/ui/ImpersonationBanner.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppHeader from '../Components/ui/AppHeader.vue';
import AppSidebar from '../Components/ui/AppSidebar.vue';
import Backdrop from '../Components/ui/Backdrop.vue';
import { useSidebar } from '../composables/useSidebar.js';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const pageTitle = computed(() =>
    props.title ? `${props.title} - ${appName}` : appName,
);

const { isExpanded, isHovered } = useSidebar();
</script>
