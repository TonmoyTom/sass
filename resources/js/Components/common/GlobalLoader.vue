<!-- resources/js/Components/common/GlobalLoader.vue -->
<template>
    <Transition name="fade">
        <div v-if="loading" class="pointer-events-none fixed inset-0 z-[99999]">
            <div
                class="absolute top-0 left-0 h-[3px] transition-all duration-300 ease-out"
                :style="{ width: progress + '%', backgroundColor: '#465fff' }"
            ></div>
        </div>
    </Transition>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const loading = ref(false);
const progress = ref(0);
let timer = null;
let stopStart = null;
let stopFinish = null;

console.log('GlobalLoader: component setup ran'); // mount hocche kina

onMounted(() => {
    console.log('GlobalLoader: onMounted ran'); // mounted hocche kina

    stopStart = router.on('start', () => {
        console.log('GlobalLoader: START fired'); // start event
        loading.value = true;
        progress.value = 20;
        clearInterval(timer);
        timer = setInterval(() => {
            if (progress.value < 90) progress.value += Math.random() * 10;
            console.log(
                'GlobalLoader: progress =',
                progress.value,
                'loading =',
                loading.value,
            );
        }, 200);
    });

    stopFinish = router.on('finish', () => {
        console.log('GlobalLoader: FINISH fired'); // finish event
        progress.value = 100;
        clearInterval(timer);
        setTimeout(() => {
            loading.value = false;
            progress.value = 0;
            console.log('GlobalLoader: reset, loading =', loading.value);
        }, 300);
    });
});

onUnmounted(() => {
    console.log('GlobalLoader: onUnmounted ran'); // unmount hole
    clearInterval(timer);
    stopStart?.();
    stopFinish?.();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
