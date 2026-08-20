<script setup>
import { useInView } from '@modules/LMS/resources/js/composables/useInView.js';

defineProps({
    as: {
        type: String,
        default: 'div',
    },
});

const { target, isVisible } = useInView();
</script>

<template>
    <component
        :is="as"
        ref="target"
        class="motion-reduce:!translate-y-0 motion-reduce:!opacity-100 transition-all duration-700 ease-out"
        :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
    >
        <slot />
    </component>
</template>