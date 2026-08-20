<script setup>
import FloatingPanel from '@modules/LMS/resources/js/Components/Learn/FloatingPanel.vue';
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { ref, watch } from 'vue';

const props = defineProps({
    lessonId: {
        type: [String, Number],
        required: true,
    },
    note: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close', 'update:note']);

const savedAt = ref(null);
const saving = ref(false);
const errored = ref(false);
let saveTimer = null;

const onInput = (event) => {
    const value = event.target.value;
    emit('update:note', value);

    clearTimeout(saveTimer);
    saveTimer = setTimeout(async () => {
        saving.value = true;
        errored.value = false;
        try {
            const { data } = await window.axios.post(learnRoutes.saveNote(props.lessonId), {
                content: value,
            });
            savedAt.value = new Date(data.saved_at);
        } catch {
            errored.value = true;
        } finally {
            saving.value = false;
        }
    }, 700);
};

const savedLabel = () => {
    if (errored.value) return "Couldn't save — check your connection";
    if (saving.value) return 'Saving...';
    if (!savedAt.value) return 'Notes are saved to your account';
    return `Saved ${savedAt.value.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
};

const notepadX = Math.max(16, window.innerWidth - 300 - 360);
const notepadY = Math.max(16, window.innerHeight - 380);
</script>

<template>
    <FloatingPanel
        title="My Notes"
        :width="300"
        :height="320"
        :min-height="220"
        :initial-x="notepadX"
        :initial-y="notepadY"
        @close="$emit('close')"
    >
        <div class="flex h-full flex-col">
            <textarea
                :value="note"
                placeholder="Jot down anything you want to remember from this lesson..."
                class="focus:border-brand-300 dark:focus:border-brand-500 w-full flex-1 resize-none rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-800 outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                @input="onInput"
            />
            <p
                class="mt-1.5 shrink-0 text-right text-[11px]"
                :class="errored ? 'text-error-500' : 'text-gray-400'"
            >
                {{ savedLabel() }}
            </p>
        </div>
    </FloatingPanel>
</template>