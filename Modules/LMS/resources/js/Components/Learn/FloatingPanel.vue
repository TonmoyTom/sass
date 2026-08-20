<script setup>
import { X } from 'lucide-vue-next';
import { onMounted, onUnmounted, reactive } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    initialX: {
        type: Number,
        default: null,
    },
    initialY: {
        type: Number,
        default: null,
    },
    width: {
        type: Number,
        default: 300,
    },
    height: {
        type: Number,
        default: null, // null = auto height, sizes to content
    },
    minWidth: {
        type: Number,
        default: 220,
    },
    minHeight: {
        type: Number,
        default: 180,
    },
});

const emit = defineEmits(['close']);

// default spawn point: bottom-right, near the floating launcher buttons —
// avoids landing under a left sidebar (e.g. WorkspaceLayout's nav)
const defaultX = () => Math.max(16, window.innerWidth - props.width - 32);
const defaultY = () => Math.max(16, window.innerHeight - 460);

const pos = reactive({
    x: props.initialX ?? defaultX(),
    y: props.initialY ?? defaultY(),
});

const size = reactive({
    width: props.width,
    height: props.height, // null = auto
});

let dragging = false;
let offsetX = 0;
let offsetY = 0;

let resizing = false;
let resizeStartX = 0;
let resizeStartY = 0;
let resizeStartWidth = 0;
let resizeStartHeight = 0;

const clampToViewport = () => {
    const maxX = window.innerWidth - size.width - 8;
    const maxY = window.innerHeight - 60;
    pos.x = Math.min(Math.max(8, pos.x), Math.max(8, maxX));
    pos.y = Math.min(Math.max(8, pos.y), Math.max(8, maxY));
};

// ── drag (move) ──
const startDrag = (event) => {
    dragging = true;
    const point = event.touches ? event.touches[0] : event;
    offsetX = point.clientX - pos.x;
    offsetY = point.clientY - pos.y;
};

const onDrag = (event) => {
    if (!dragging) return;
    const point = event.touches ? event.touches[0] : event;
    pos.x = point.clientX - offsetX;
    pos.y = point.clientY - offsetY;
};

const stopDrag = () => {
    if (!dragging) return;
    dragging = false;
    clampToViewport();
};

// ── resize (bottom-right handle) ──
const startResize = (event) => {
    resizing = true;
    const point = event.touches ? event.touches[0] : event;
    resizeStartX = point.clientX;
    resizeStartY = point.clientY;
    resizeStartWidth = size.width;
    // measure current rendered height as the resize starting point,
    // since height may still be "auto" (null) at this point
    resizeStartHeight = event.currentTarget
        ?.closest('[data-floating-panel]')
        ?.getBoundingClientRect().height ?? props.minHeight;
    event.stopPropagation();
};

const onResize = (event) => {
    if (!resizing) return;
    const point = event.touches ? event.touches[0] : event;
    const deltaX = point.clientX - resizeStartX;
    const deltaY = point.clientY - resizeStartY;

    const maxWidth = window.innerWidth - pos.x - 8;
    const maxHeight = window.innerHeight - pos.y - 8;

    size.width = Math.min(maxWidth, Math.max(props.minWidth, resizeStartWidth + deltaX));
    size.height = Math.min(maxHeight, Math.max(props.minHeight, resizeStartHeight + deltaY));
};

const stopResize = () => {
    resizing = false;
};

const onPointerMove = (event) => {
    onDrag(event);
    onResize(event);
};

const onPointerUp = () => {
    stopDrag();
    stopResize();
};

onMounted(() => {
    clampToViewport();
    window.addEventListener('mousemove', onPointerMove);
    window.addEventListener('mouseup', onPointerUp);
    window.addEventListener('touchmove', onPointerMove, { passive: true });
    window.addEventListener('touchend', onPointerUp);
    window.addEventListener('resize', clampToViewport);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', onPointerMove);
    window.removeEventListener('mouseup', onPointerUp);
    window.removeEventListener('touchmove', onPointerMove);
    window.removeEventListener('touchend', onPointerUp);
    window.removeEventListener('resize', clampToViewport);
});
</script>

<template>
    <Teleport to="body">
        <div
            data-floating-panel
            class="fixed z-[9999] flex flex-col rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900"
            :style="{
                left: pos.x + 'px',
                top: pos.y + 'px',
                width: size.width + 'px',
                height: size.height ? size.height + 'px' : 'auto',
                minWidth: minWidth + 'px',
                minHeight: minHeight + 'px',
            }"
        >
            <div
                class="flex shrink-0 cursor-grab items-center justify-between rounded-t-2xl border-b border-gray-100 bg-gray-50 px-3.5 py-2.5 active:cursor-grabbing dark:border-gray-800 dark:bg-white/5"
                @mousedown="startDrag"
                @touchstart="startDrag"
            >
                <span class="text-sm font-semibold text-gray-700 select-none dark:text-gray-200">
                    {{ title }}
                </span>
                <button
                    type="button"
                    class="flex h-6 w-6 items-center justify-center rounded-full text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700"
                    @click="emit('close')"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-3.5">
                <slot />
            </div>

            <!-- resize handle -->
            <div
                class="absolute right-0 bottom-0 h-5 w-5 cursor-nwse-resize touch-none"
                title="Drag to resize"
                @mousedown="startResize"
                @touchstart="startResize"
            >
                <svg viewBox="0 0 16 16" class="absolute right-1 bottom-1 h-3 w-3 text-gray-300 dark:text-gray-600">
                    <path
                        fill="currentColor"
                        d="M14 14v-2.5L11.5 14H14zm0-4.5V7L7 14h2.5L14 9.5zM14 5V2.5L2.5 14H5L14 5z"
                    />
                </svg>
            </div>
        </div>
    </Teleport>
</template>