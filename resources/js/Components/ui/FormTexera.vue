<template>
    <div>
        <label
            v-if="label"
            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            {{ label }}
        </label>

        <div
            class="rounded-lg border border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500/20 focus-within:ring-2 dark:border-gray-700 dark:bg-gray-900"
        >
            <!-- Toolbar -->
            <div
                v-if="editor"
                class="flex flex-wrap items-center gap-1 border-b border-gray-200 px-2 py-1.5 dark:border-gray-800"
            >
                <ToolbarButton
                    :active="editor.isActive('bold')"
                    @click="editor.chain().focus().toggleBold().run()"
                >
                    <Bold class="h-3.5 w-3.5" />
                </ToolbarButton>
                <ToolbarButton
                    :active="editor.isActive('italic')"
                    @click="editor.chain().focus().toggleItalic().run()"
                >
                    <Italic class="h-3.5 w-3.5" />
                </ToolbarButton>
                <ToolbarButton
                    :active="editor.isActive('strike')"
                    @click="editor.chain().focus().toggleStrike().run()"
                >
                    <Strikethrough class="h-3.5 w-3.5" />
                </ToolbarButton>

                <span class="mx-1 h-4 w-px bg-gray-200 dark:bg-gray-700" />

                <ToolbarButton
                    :active="editor.isActive('bulletList')"
                    @click="editor.chain().focus().toggleBulletList().run()"
                >
                    <List class="h-3.5 w-3.5" />
                </ToolbarButton>
                <ToolbarButton
                    :active="editor.isActive('orderedList')"
                    @click="editor.chain().focus().toggleOrderedList().run()"
                >
                    <ListOrdered class="h-3.5 w-3.5" />
                </ToolbarButton>

                <span class="mx-1 h-4 w-px bg-gray-200 dark:bg-gray-700" />

                <ToolbarButton @click="editor.chain().focus().undo().run()">
                    <Undo2 class="h-3.5 w-3.5" />
                </ToolbarButton>
                <ToolbarButton @click="editor.chain().focus().redo().run()">
                    <Redo2 class="h-3.5 w-3.5" />
                </ToolbarButton>
            </div>

            <!-- Editor content -->
            <EditorContent
                :editor="editor"
                class="tiptap-content min-h-[120px] px-3.5 py-2.5 text-sm text-gray-800 dark:text-white"
            />
        </div>

        <p v-if="error" class="mt-1.5 text-xs text-red-500">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { watch, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    Italic,
    Strikethrough,
    List,
    ListOrdered,
    Undo2,
    Redo2,
} from 'lucide-vue-next';
import { h } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [StarterKit],
    editorProps: {
        attributes: {
            class: 'focus:outline-none prose prose-sm dark:prose-invert max-w-none',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(
    () => props.modelValue,
    (val) => {
        if (editor.value && val !== editor.value.getHTML()) {
            editor.value.commands.setContent(val, false);
        }
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const ToolbarButton = (props, { slots }) =>
    h(
        'button',
        {
            type: 'button',
            onClick: props.onClick,
            class: [
                'rounded p-1.5 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.08]',
                props.active
                    ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400'
                    : '',
            ],
        },
        slots.default?.(),
    );
</script>

<style>
.tiptap-content .ProseMirror {
    min-height: 100px;
}

.tiptap-content .ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #9ca3af;
    pointer-events: none;
    height: 0;
}
</style>
