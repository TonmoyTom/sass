<script setup>
import { X } from 'lucide-vue-next';
import { onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    videoUrl: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close']);

const isYoutube = (url) => !!url && url.includes('youtube.com/embed/');

const onKeydown = (e) => {
    if (e.key === 'Escape' && props.open) emit('close');
};

onMounted(() => document.addEventListener('keydown', onKeydown));
onUnmounted(() => document.removeEventListener('keydown', onKeydown));

// lock body scroll while the modal is open
watch(
    () => props.open,
    (isOpen) => {
        document.body.style.overflow = isOpen ? 'hidden' : '';
    },
);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/70 p-4 backdrop-blur-sm"
                @click.self="emit('close')"
            >
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div
                        v-if="open"
                        class="relative w-full max-w-3xl overflow-hidden rounded-2xl bg-gray-950 shadow-2xl"
                    >
                        <button
                            type="button"
                            class="absolute top-3 right-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur transition hover:bg-white/20"
                            @click="emit('close')"
                        >
                            <X class="h-4.5 w-4.5" />
                        </button>

                        <div class="aspect-video w-full bg-black">
                            <iframe
                                v-if="isYoutube(videoUrl)"
                                :src="videoUrl"
                                class="h-full w-full"
                                title="Lesson preview"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            />
                            <video
                                v-else-if="videoUrl"
                                :src="videoUrl"
                                controls
                                autoplay
                                class="h-full w-full"
                            />
                        </div>

                        <div v-if="title" class="border-t border-white/10 px-5 py-4">
                            <p class="flex items-center gap-2 text-sm font-medium text-white">
                                <span
                                    class="bg-brand-500/20 text-brand-300 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                >
                                    Preview
                                </span>
                                {{ title }}
                            </p>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
