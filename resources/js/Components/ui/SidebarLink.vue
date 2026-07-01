<template>
    <Link
        :href="href"
        :class="[
            'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
            active
                ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400'
                : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]',
            collapsed ? 'lg:justify-center' : '',
        ]"
    >
        <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center">
            <component :is="icon" class="h-5 w-5" />
        </span>
        <span v-if="!collapsed" class="truncate">{{ label }}</span>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    label: String,
    icon: String,
    active: Boolean,
    collapsed: Boolean,
});
</script>
