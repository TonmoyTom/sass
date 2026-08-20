<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const brand = computed(
    () => page.props.workspace?.company_name || page.props.tenant?.name || 'Campus',
);

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-gray-100 py-8 dark:border-gray-800">
        <div
            class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-6 text-xs text-gray-400 sm:flex-row dark:text-gray-500"
        >
            <span>&copy; {{ year }} {{ brand }}</span>
            <span>Powered by {{ brand }} Learning</span>
        </div>
    </footer>
</template>