<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSidebar } from '../../composables/useSidebar.js';

const page = usePage();
const company = computed(() => page.props.company);

const logoUrl = computed(() => company.value?.logo_url ?? null);
const companyName = computed(() => company.value?.name ?? '');

const { isExpanded, isMobileOpen, isHovered } = useSidebar();

const showFullLogo = computed(
    () => isExpanded.value || isHovered.value || isMobileOpen.value,
);
</script>

<template>
    <Link href="/dashboard" class="inline-flex items-center gap-2">
        <span v-if="logoUrl" class="block h-9 w-auto flex-shrink-0">
            <img
                :src="logoUrl"
                :alt="companyName"
                class="h-full w-full object-contain"
            />
        </span>
        <span
            v-if="companyName && showFullLogo"
            class="text-lg leading-none font-bold tracking-tight whitespace-nowrap text-gray-900 dark:text-white"
        >
            {{ companyName }}
        </span>
    </Link>
</template>
