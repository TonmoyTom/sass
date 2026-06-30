<template>
    <Head :title="title" />

    <ImpersonationBanner />

    <div class="min-h-screen xl:flex">
        <SellerSideBar />
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
import { Head } from '@inertiajs/vue3';
import AppHeader from '../Components/ui/AppHeader.vue';
import Backdrop from '../Components/ui/Backdrop.vue';
import { useSidebar } from '../composables/useSidebar.js';
import SellerSideBar from '@/Components/ui/SellerSideBar.vue';
import ImpersonationBanner from '@/Components/ui/ImpersonationBanner.vue';
const { isExpanded, isHovered } = useSidebar();
</script>
