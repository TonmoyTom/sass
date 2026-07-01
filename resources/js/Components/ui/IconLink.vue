<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        default: null,
    },
    icon: {
        type: [Object, Function],
        default: null,
    },
    text: {
        type: String,
        default: null,
    },
    tagline: {
        type: String,
        default: null,
    },
    showText: {
        type: Boolean,
        default: true,
    },
    iconClass: {
        type: String,
        default: 'block h-9 w-auto',
    },
    textClass: {
        type: String,
        default:
            'text-lg font-bold tracking-tight text-gray-900 dark:text-white leading-none',
    },
    taglineClass: {
        type: String,
        default:
            'text-[9px] font-medium tracking-wider text-gray-500 dark:text-gray-400 uppercase leading-tight mt-0.5',
    },
});
</script>

<template>
    <Link v-if="href" :href="href" class="inline-flex items-center gap-2">
        <span v-if="icon" :class="iconClass">
            <component :is="icon" class="h-full w-full" />
        </span>
        <div v-if="showText && (text || tagline)" class="flex flex-col">
            <span v-if="text" :class="textClass">{{ text }}</span>
            <span v-if="tagline" :class="taglineClass">{{ tagline }}</span>
        </div>
    </Link>
    <div v-else class="inline-flex items-center gap-2">
        <span v-if="icon" :class="iconClass">
            <component :is="icon" class="h-full w-full" />
        </span>
        <div v-if="showText && (text || tagline)" class="flex flex-col">
            <span v-if="text" :class="textClass">{{ text }}</span>
            <span v-if="tagline" :class="taglineClass">{{ tagline }}</span>
        </div>
    </div>
</template>
