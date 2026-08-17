<template>
    <div class="space-y-3">
        <!-- Upload area (kono file na thakle) -->
        <div
            v-if="!uploading && !uploadedFile"
            class="rounded-xl border-2 border-dashed border-gray-300 p-6 text-center transition hover:border-gray-400 dark:border-gray-700"
            @dragover.prevent
            @drop.prevent="onDrop"
        >
            <UploadCloud class="mx-auto mb-2 h-8 w-8 text-gray-400" />
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Drag & drop a video, or
                <button
                    type="button"
                    @click="fileInput?.click()"
                    class="text-brand-500 font-medium hover:underline"
                >
                    browse
                </button>
            </p>
            <p class="mt-1 text-xs text-gray-400">MP4, MOV — up to 500MB</p>
            <input
                ref="fileInput"
                type="file"
                accept="video/mp4,video/quicktime"
                class="hidden"
                @change="onFileSelect"
            />
        </div>

        <!-- Uploading state — progress bar -->
        <div
            v-if="uploading"
            class="rounded-xl border border-gray-200 p-4 dark:border-gray-700"
        >
            <div class="mb-2 flex items-center justify-between">
                <div class="flex min-w-0 items-center gap-2">
                    <FileVideo class="h-4 w-4 shrink-0 text-gray-400" />
                    <p
                        class="truncate text-sm text-gray-700 dark:text-gray-300"
                    >
                        {{ selectedFileName }}
                    </p>
                </div>
                <button
                    type="button"
                    @click="cancelUpload"
                    class="shrink-0 text-xs text-red-500 hover:underline"
                >
                    Cancel
                </button>
            </div>

            <div
                class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
            >
                <div
                    class="bg-brand-500 h-full rounded-full transition-all duration-300"
                    :style="{ width: progress + '%' }"
                ></div>
            </div>

            <div
                class="mt-1.5 flex items-center justify-between text-xs text-gray-400"
            >
                <span>{{ progress }}% uploaded</span>
                <span
                    >{{ formatSize(uploadedBytes) }} /
                    {{ formatSize(totalBytes) }}</span
                >
            </div>
        </div>

        <!-- Uploaded state -->
        <div
            v-if="uploadedFile && !uploading"
            class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900/40 dark:bg-green-900/10"
        >
            <div class="flex min-w-0 items-center gap-2">
                <CheckCircle
                    class="h-4 w-4 shrink-0 text-green-600 dark:text-green-400"
                />
                <p class="truncate text-sm text-green-700 dark:text-green-400">
                    {{ selectedFileName }}
                </p>
            </div>
            <button
                type="button"
                @click="removeFile"
                class="shrink-0 text-xs text-red-500 hover:underline"
            >
                Remove
            </button>
        </div>

        <!-- Error state -->
        <div
            v-if="error"
            class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/10 dark:text-red-400"
        >
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { CheckCircle, FileVideo, UploadCloud } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    uploadUrl: { type: String, required: true }, // e.g. /lms/lessons/{id}/upload-video
    maxSizeMb: { type: Number, default: 500 },
});

const emit = defineEmits(['uploaded', 'removed']);

const fileInput = ref(null);
const uploading = ref(false);
const progress = ref(0);
const uploadedBytes = ref(0);
const totalBytes = ref(0);
const selectedFileName = ref('');
const uploadedFile = ref(null);
const error = ref('');
let xhr = null;

const formatSize = (bytes) => {
    if (!bytes) return '0 MB';
    const mb = bytes / (1024 * 1024);
    return mb >= 1024 ? `${(mb / 1024).toFixed(2)} GB` : `${mb.toFixed(1)} MB`;
};

const onDrop = (e) => {
    const file = e.dataTransfer.files[0];
    if (file) handleFile(file);
};

const onFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) handleFile(file);
};

const handleFile = (file) => {
    error.value = '';

    const maxBytes = props.maxSizeMb * 1024 * 1024;
    if (file.size > maxBytes) {
        error.value = `File too large. Maximum size is ${props.maxSizeMb}MB.`;
        return;
    }

    if (!['video/mp4', 'video/quicktime'].includes(file.type)) {
        error.value = 'Only MP4 and MOV files are supported.';
        return;
    }

    uploadFile(file);
};

const uploadFile = (file) => {
    selectedFileName.value = file.name;
    uploading.value = true;
    progress.value = 0;
    uploadedBytes.value = 0;
    totalBytes.value = file.size;
    error.value = '';

    const formData = new FormData();
    formData.append('video', file);

    xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            uploadedBytes.value = e.loaded;
            totalBytes.value = e.total;
            progress.value = Math.round((e.loaded / e.total) * 100);
        }
    });

    xhr.addEventListener('load', () => {
        uploading.value = false;

        if (xhr.status >= 200 && xhr.status < 300) {
            const response = JSON.parse(xhr.responseText);
            uploadedFile.value = response;
            emit('uploaded', response);
        } else {
            error.value = 'Upload failed. Please try again.';
        }
    });

    xhr.addEventListener('error', () => {
        uploading.value = false;
        error.value = 'Upload failed. Check your connection and try again.';
    });

    xhr.addEventListener('abort', () => {
        uploading.value = false;
        progress.value = 0;
    });

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    xhr.open('POST', props.uploadUrl);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
};

const cancelUpload = () => {
    if (xhr) {
        xhr.abort();
    }
    uploading.value = false;
    progress.value = 0;
};

const removeFile = () => {
    uploadedFile.value = null;
    selectedFileName.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    emit('removed');
};
</script>
