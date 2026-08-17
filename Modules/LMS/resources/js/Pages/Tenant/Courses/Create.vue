<template>
    <WorkspaceLayout title="New Course">
        <div class="mx-auto ">
            <div class="mb-6">
                <Link
                    href="/lms/courses"
                    class="mb-3 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"
                >
                    ← Back to Courses
                </Link>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    Create Course
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Start with basic details. You can add lessons after creating.
                </p>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Title
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g. Complete Web Development Bootcamp"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />
                    <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-500">
                        {{ form.errors.title }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Description
                    </label>
                     <FormTexera
                            v-model="form.description"
                            placeholder="House, Road, Area"
                            :error="form.errors.description"
                            :rows="4"
                            class="w-full rounded-lg border-gray-300 bg-transparent text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                    
                </div>

                <!-- ── Thumbnail ── -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Thumbnail
                    </label>

                    <!-- Preview state -->
                    <div v-if="thumbnailPreview" class="flex items-center gap-4">
                        <div class="relative">
                            <img
                                :src="thumbnailPreview"
                                class="h-24 w-36 rounded-xl border border-gray-200 object-cover dark:border-gray-700"
                            />
                            <button
                                type="button"
                                @click="removeThumbnail"
                                class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                                title="Remove image"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ form.thumbnail?.name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ formatFileSize(form.thumbnail?.size) }}
                            </p>
                            <button
                                type="button"
                                @click="thumbnailInput?.click()"
                                class="text-brand-500 mt-1 text-xs font-medium hover:underline"
                            >
                                Change image
                            </button>
                        </div>
                    </div>

                    <!-- Empty state — explicit click handler, NOT a <label> wrapper -->
                    <div
                        v-else
                        @click="thumbnailInput?.click()"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-6 text-center transition hover:border-gray-400 dark:border-gray-700"
                    >
                        <ImageIcon class="mb-1.5 h-6 w-6 text-gray-400" />
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="text-brand-500 font-medium">Upload a thumbnail</span> or drag it here
                        </p>
                        <p class="mt-0.5 text-xs text-gray-400">PNG, JPG up to 2MB</p>
                    </div>

                    <input
                        ref="thumbnailInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="onThumbnailChange"
                    />

                    <p v-if="form.errors.thumbnail" class="mt-1.5 text-sm text-red-500">
                        {{ form.errors.thumbnail }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Category
                        </label>
                        <select
                            v-model="form.category_id"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        >
                            <option value="">— None —</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Subcategory
                        </label>
                        <select
                            v-model="form.subcategory_id"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        >
                            <option value="">— None —</option>
                            <option
                                v-for="s in filteredSubcategories"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <label class="flex items-center gap-2">
                        <input v-model="form.is_free" type="checkbox" class="rounded" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">This is a free course</span>
                    </label>

                    <div v-if="!form.is_free" class="mt-3">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Price (৳)
                        </label>
                        <input
                            v-model.number="form.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p v-if="form.errors.price" class="mt-1.5 text-sm text-red-500">
                            {{ form.errors.price }}
                        </p>
                    </div>
                </div>

                <label class="flex items-center gap-2">
                    <input v-model="form.sequential_unlock" type="checkbox" class="rounded" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        Require sequential completion (students must finish a lesson before unlocking the next)
                    </span>
                </label>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Status
                    </label>
                    <select
                        v-model="form.status"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    >
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <Link
                        href="/lms/courses"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create & Continue' }}
                    </button>
                </div>
            </form>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue'
import FormTexera from '@/Components/ui/FormTexera.vue';;
import { Link, useForm } from '@inertiajs/vue3';
import { Image as ImageIcon, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    categories: Array,
    subcategories: Array,
});

const form = useForm({
    category_id: '',
    subcategory_id: '',
    title: '',
    description: '',
    thumbnail: null,
    is_free: false,
    price: '',
    sequential_unlock: false,
    status: 'draft',
});

const filteredSubcategories = computed(() =>
    props.subcategories.filter((s) => s.category_id === form.category_id),
);

// ── Thumbnail ──
const thumbnailInput = ref(null);
const thumbnailPreview = ref(null);

const formatFileSize = (bytes) => {
    if (!bytes) return '';
    const mb = bytes / (1024 * 1024);
    return mb >= 1 ? `${mb.toFixed(1)} MB` : `${(bytes / 1024).toFixed(0)} KB`;
};

const onThumbnailChange = (e) => {
    const file = e.target.files[0];
    form.thumbnail = file ?? null;

    if (file) {
        thumbnailPreview.value = URL.createObjectURL(file);
    }
};

const removeThumbnail = () => {
    form.thumbnail = null;
    thumbnailPreview.value = null;
    if (thumbnailInput.value) {
        thumbnailInput.value.value = '';
    }
};

const submit = () => {
    form.post('/lms/courses', {
        forceFormData: true,
    });
};
</script>
