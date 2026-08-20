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
                        Short description
                    </label>
                    <FormTexera
                        v-model="form.short_description"
                        placeholder="House, Road, Area"
                        :error="form.errors.short_description"
                        :rows="4"
                        class="w-full rounded-lg border-gray-300 bg-transparent text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />
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

                <!-- ── Preview image ── -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Preview image
                    </label>
                    <p class="mb-2 text-xs text-gray-400">
                        Larger banner shown on the course page — falls back to the thumbnail if left empty.
                    </p>

                    <!-- Preview state -->
                    <div v-if="previewImagePreview" class="flex items-center gap-4">
                        <div class="relative">
                            <img
                                :src="previewImagePreview"
                                class="h-24 w-36 rounded-xl border border-gray-200 object-cover dark:border-gray-700"
                            />
                            <button
                                type="button"
                                @click="removePreviewImage"
                                class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                                title="Remove image"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ form.preview_image?.name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ formatFileSize(form.preview_image?.size) }}
                            </p>
                            <button
                                type="button"
                                @click="previewImageInput?.click()"
                                class="text-brand-500 mt-1 text-xs font-medium hover:underline"
                            >
                                Change image
                            </button>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-else
                        @click="previewImageInput?.click()"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-6 text-center transition hover:border-gray-400 dark:border-gray-700"
                    >
                        <ImageIcon class="mb-1.5 h-6 w-6 text-gray-400" />
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="text-brand-500 font-medium">Upload a preview image</span> or drag it here
                        </p>
                        <p class="mt-0.5 text-xs text-gray-400">PNG, JPG up to 4MB</p>
                    </div>

                    <input
                        ref="previewImageInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="onPreviewImageChange"
                    />

                    <p v-if="form.errors.preview_image" class="mt-1.5 text-sm text-red-500">
                        {{ form.errors.preview_image }}
                    </p>
                </div>

                <!-- ── Preview video (course trailer) ── -->
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Preview video
                    </label>
                    <p class="mb-3 text-xs text-gray-400">
                        A short trailer students can watch before enrolling — paste a YouTube link or upload a file.
                    </p>

                    <!-- already have a video (either source) -->
                    <div
                        v-if="form.preview_video_path || form.preview_video_url"
                        class="mb-2 flex items-center justify-between gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700"
                    >
                        <div
                            v-if="form.preview_video_path"
                            class="flex min-w-0 items-center gap-2 text-green-700 dark:text-green-400"
                        >
                            <CheckCircle class="h-3.5 w-3.5 shrink-0" />
                            <span class="truncate">{{ displayPreviewVideoName }}</span>
                        </div>
                        <div v-else class="min-w-0 truncate text-gray-600 dark:text-gray-300">
                            {{ form.preview_video_url }}
                        </div>
                        <button
                            type="button"
                            @click="clearPreviewVideo(); form.preview_video_url = ''"
                            class="shrink-0 text-red-500 hover:underline"
                        >
                            Remove
                        </button>
                    </div>

                    <input
                        v-if="!form.preview_video_path"
                        v-model="form.preview_video_url"
                        type="text"
                        placeholder="YouTube URL"
                        class="mb-2 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
                    />

                    <template v-if="!form.preview_video_path && !form.preview_video_url">
                        <p class="mb-2 text-center text-xs text-gray-400">— OR —</p>
                        <VideoUploader
                            upload-url="/lms/courses/upload-video"
                            :max-size-mb="500"
                            @uploaded="onPreviewVideoUploaded"
                            @removed="onPreviewVideoRemoved"
                        />
                    </template>

                    <p v-if="form.errors.preview_video_url" class="mt-1.5 text-sm text-red-500">
                        {{ form.errors.preview_video_url }}
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

                <!-- ── Instructors ── -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Instructors
                    </label>
                    <p class="mb-2 text-xs text-gray-400">
                        Everyone selected here is credited as "By ..." on the course page.
                    </p>

                    <input
                        v-model="instructorSearch"
                        type="text"
                        placeholder="Search instructors..."
                        class="mb-2 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />

                    <div
                        v-if="instructorList.length"
                        class="max-h-48 space-y-1 overflow-y-auto rounded-xl border border-gray-200 p-2 dark:border-gray-700"
                        @scroll="onInstructorListScroll"
                    >
                        <label
                            v-for="instructor in instructorList"
                            :key="instructor.id"
                            class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                        >
                            <input
                                type="checkbox"
                                class="rounded"
                                :checked="form.instructor_ids.includes(instructor.id)"
                                @change="toggleInstructor(instructor.id)"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ instructor.name }}
                            </span>
                            <span class="text-xs text-gray-400">{{ instructor.email }}</span>
                        </label>

                        <p
                            v-if="instructorLoading"
                            class="py-1.5 text-center text-xs text-gray-400"
                        >
                            Loading...
                        </p>
                    </div>
                    <p v-else-if="!instructorLoading" class="text-xs text-gray-400">
                        No instructors found — add one from the Instructors page first, or leave this empty to credit yourself.
                    </p>

                    <p v-if="form.errors.instructor_ids" class="mt-1.5 text-sm text-red-500">
                        {{ form.errors.instructor_ids }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <label class="flex items-center gap-2">
                        <input v-model="form.is_free" type="checkbox" class="rounded" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">This is a free course</span>
                    </label>

                    <div v-if="!form.is_free" class="mt-3 grid grid-cols-2 gap-4">
                        <div>
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
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Discount price (৳)
                            </label>
                            <input
                                v-model.number="form.discount_price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="Optional"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                            <p class="mt-1.5 text-xs text-gray-400">Leave empty for no discount.</p>
                            <p v-if="form.errors.discount_price" class="mt-1.5 text-sm text-red-500">
                                {{ form.errors.discount_price }}
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Live class starts at (optional)
                    </label>
                    <p class="mb-2 text-xs text-gray-400">
                        Shows a live countdown on the course page until this time.
                    </p>
                    <input
                        v-model="form.live_class_starts_at"
                        type="datetime-local"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />
                    <p v-if="form.errors.live_class_starts_at" class="mt-1.5 text-sm text-red-500">
                        {{ form.errors.live_class_starts_at }}
                    </p>
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
import FormTexera from '@/Components/ui/FormTexera.vue';
import VideoUploader from '@/Components/ui/VideoUploader.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { CheckCircle, Image as ImageIcon, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    categories: Array,
    subcategories: Array,
    instructors: Object,
});

const form = useForm({
    category_id: '',
    subcategory_id: '',
    title: '',
    short_description: '',
    description: '',
    thumbnail: null,
    preview_image: null,
    preview_video_url: '',
    preview_video_path: null,
    preview_video_source: null,
    is_free: false,
    price: '',
    discount_price: '',
    live_class_starts_at: '',
    sequential_unlock: false,
    status: 'draft',
    instructor_ids: [],
});

const filteredSubcategories = computed(() =>
    props.subcategories.filter((s) => s.category_id === form.category_id),
);

const toggleInstructor = (id) => {
    const idx = form.instructor_ids.indexOf(id);
    if (idx === -1) {
        form.instructor_ids.push(id);
    } else {
        form.instructor_ids.splice(idx, 1);
    }
};

// ── Instructor picker — scroll pagination + search ──
const instructorList = ref([...(props.instructors.data ?? [])]);
const instructorNextPage = ref(props.instructors.next_page ?? null);
const instructorLoading = ref(false);
const instructorSearch = ref('');
let instructorSearchTimer = null;

const fetchInstructors = async ({ page = 1, reset = false } = {}) => {
    if (instructorLoading.value) return;
    instructorLoading.value = true;

    try {
        const { data } = await window.axios.get('/lms/courses-instructors-search', {
            params: { page, q: instructorSearch.value || undefined },
        });

        instructorList.value = reset ? data.data : [...instructorList.value, ...data.data];
        instructorNextPage.value = data.next_page;
    } finally {
        instructorLoading.value = false;
    }
};

const onInstructorListScroll = (e) => {
    const el = e.target;
    const nearBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 40;

    if (nearBottom && instructorNextPage.value && !instructorLoading.value) {
        fetchInstructors({ page: instructorNextPage.value });
    }
};

watch(instructorSearch, () => {
    clearTimeout(instructorSearchTimer);
    instructorSearchTimer = setTimeout(() => {
        fetchInstructors({ page: 1, reset: true });
    }, 300);
});

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

// ── Preview image ──
const previewImageInput = ref(null);
const previewImagePreview = ref(null);

const onPreviewImageChange = (e) => {
    const file = e.target.files[0];
    form.preview_image = file ?? null;

    if (file) {
        previewImagePreview.value = URL.createObjectURL(file);
    }
};

const removePreviewImage = () => {
    form.preview_image = null;
    previewImagePreview.value = null;
    if (previewImageInput.value) {
        previewImageInput.value.value = '';
    }
};

// ── Preview video (course trailer) — YouTube URL or uploaded file ──
const displayPreviewVideoName = computed(() => {
    if (!form.preview_video_path) return '';
    return form.preview_video_path.split('/').pop();
});

const onPreviewVideoUploaded = (file) => {
    form.preview_video_path = file.path;
    form.preview_video_source = 'upload';
};

const onPreviewVideoRemoved = () => {
    form.preview_video_path = null;
    form.preview_video_source = null;
};

const clearPreviewVideo = () => {
    form.preview_video_path = null;
    form.preview_video_source = null;
};

const submit = () => {
    form.post('/lms/courses', {
        forceFormData: true,
    });
};
</script>
