<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    videoUrl: {
        type: String,
        default: null,
    },
    videoSource: {
        type: String,
        default: null, // 'youtube' | 'upload'
    },
});

const emit = defineEmits(['progress']);

const isYoutube = () => props.videoSource === 'youtube' || (props.videoUrl ?? '').includes('youtube.com/embed/');

const iframeId = `yt-player-${Math.random().toString(36).slice(2, 9)}`;
let ytPlayer = null;
let pollTimer = null;

const loadYoutubeApi = () =>
    new Promise((resolve) => {
        if (window.YT && window.YT.Player) {
            resolve();
            return;
        }
        const existing = document.getElementById('youtube-iframe-api');
        if (!existing) {
            const tag = document.createElement('script');
            tag.id = 'youtube-iframe-api';
            tag.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(tag);
        }
        const previousCallback = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = () => {
            previousCallback?.();
            resolve();
        };
    });

const initYoutubePlayer = async () => {
    await loadYoutubeApi();

    ytPlayer = new window.YT.Player(iframeId, {
        events: {
            onStateChange: (event) => {
                if (event.data === window.YT.PlayerState.PLAYING) {
                    startPolling();
                } else {
                    stopPolling();
                }
                if (event.data === window.YT.PlayerState.ENDED) {
                    const duration = Math.floor(ytPlayer.getDuration() || 0);
                    emit('progress', duration, duration);
                }
            },
        },
    });
};

const startPolling = () => {
    stopPolling();
    pollTimer = setInterval(() => {
        if (ytPlayer?.getCurrentTime) {
            emit('progress', Math.floor(ytPlayer.getCurrentTime()), Math.floor(ytPlayer.getDuration() || 0));
        }
    }, 5000);
};

const stopPolling = () => {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = null;
};

// ── native <video> tracking ──
const videoEl = ref(null);
let lastReported = 0;

const onTimeUpdate = () => {
    const current = Math.floor(videoEl.value?.currentTime ?? 0);
    if (current - lastReported >= 5) {
        lastReported = current;
        emit('progress', current, Math.floor(videoEl.value?.duration || 0));
    }
};

const onEnded = () => {
    const duration = Math.floor(videoEl.value?.duration ?? lastReported);
    emit('progress', duration, duration);
};

onMounted(() => {
    if (isYoutube()) {
        initYoutubePlayer();
    }
});

onBeforeUnmount(() => {
    stopPolling();
    ytPlayer?.destroy?.();
});

watch(
    () => props.videoUrl,
    () => {
        stopPolling();
        ytPlayer?.destroy?.();
        lastReported = 0;
        if (isYoutube()) initYoutubePlayer();
    },
);
</script>

<template>
    <div class="aspect-video w-full overflow-hidden rounded-2xl bg-black">
        <iframe
            v-if="isYoutube()"
            :id="iframeId"
            :src="`${videoUrl}?enablejsapi=1&rel=0`"
            class="h-full w-full"
            title="Lesson video"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
        />
        <video
            v-else-if="videoUrl"
            ref="videoEl"
            :src="videoUrl"
            controls
            class="h-full w-full"
            @timeupdate="onTimeUpdate"
            @ended="onEnded"
        />
        <div v-else class="flex h-full w-full items-center justify-center text-sm text-gray-500">
            No video for this lesson.
        </div>
    </div>
</template>