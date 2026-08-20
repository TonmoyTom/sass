<template>
    <WorkspaceLayout :title="course.title">
        <div class="mx-auto">
            <div class="mb-6">
                <Link
                    href="/lms/courses"
                    class="mb-3 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"
                >
                    ← Back to Courses
                </Link>
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-16 w-24 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800"
                    >
                        <BookOpen class="h-6 w-6 text-gray-400" />
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3
                                class="text-xl font-semibold text-gray-800 dark:text-white/90"
                            >
                                {{ course.title }}
                            </h3>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                :class="statusClass(course.status)"
                            >
                                {{ course.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div
                class="mb-5 flex gap-2 border-b border-gray-200 dark:border-gray-800"
            >
                <button
                    v-for="tab in ['content', 'faqs', 'assignments', 'settings']"
                    :key="tab"
                    @click="activeTab = tab"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium capitalize transition"
                    :class="
                        activeTab === tab
                            ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                    "
                >
                    {{ tab }}
                </button>
            </div>

            <!-- ── CONTENT TAB ── -->
            <div v-if="activeTab === 'content'" class="space-y-4">
                <div
                    v-for="module in course.modules"
                    :key="module.id"
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-100 p-4 dark:border-gray-800"
                    >
                        <h5
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ module.title }}
                        </h5>
                        <button
                            @click="deleteModule(module)"
                            class="text-xs text-red-500 hover:underline"
                        >
                            Delete Section
                        </button>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div
                            v-for="lesson in module.lessons"
                            :key="lesson.id"
                            class="flex items-center justify-between p-4"
                        >
                            <div class="flex items-center gap-3">
                                <component
                                    :is="lessonIcon(lesson)"
                                    class="h-4 w-4 text-gray-400"
                                />
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-800 dark:text-white/90"
                                    >
                                        {{ lesson.title }}
                                    </p>
                                    <div
                                        class="mt-0.5 flex gap-2 text-xs text-gray-400"
                                    >
                                        <span
                                            v-if="
                                                lesson.video_url ||
                                                lesson.video_path
                                            "
                                            >Video</span
                                        >
                                        <span v-if="lesson.ebook_path"
                                            >Ebook</span
                                        >
                                        <span v-if="lesson.quizzes?.length"
                                            >{{
                                                lesson.quizzes.length
                                            }}
                                            Quiz</span
                                        >
                                        <span
                                            v-if="lesson.is_free_preview"
                                            class="text-green-500"
                                            >Free preview</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button
                                    @click="openLessonForm(module, lesson)"
                                    class="text-brand-500 text-xs hover:underline"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteLesson(lesson)"
                                    class="text-xs text-red-500 hover:underline"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>

                        <p
                            v-if="!module.lessons.length"
                            class="p-4 text-sm text-gray-400"
                        >
                            No lessons yet.
                        </p>
                    </div>

                    <div
                        class="border-t border-gray-100 p-3 dark:border-gray-800"
                    >
                        <button
                            @click="openLessonForm(module, null)"
                            class="text-brand-500 text-sm font-medium hover:underline"
                        >
                            + Add Lesson
                        </button>
                    </div>
                </div>

                <form @submit.prevent="addModule" class="flex gap-2">
                    <input
                        v-model="newModuleTitle"
                        type="text"
                        placeholder="New section title (e.g. Getting Started)"
                        class="h-11 flex-1 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />
                    <button
                        type="submit"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 text-sm font-medium text-white"
                    >
                        + Add Section
                    </button>
                </form>
            </div>

            <!-- ── FAQS TAB ── -->
            <div v-else-if="activeTab === 'faqs'" class="space-y-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div
                        v-if="!course.faqs?.length"
                        class="p-6 text-center text-sm text-gray-400"
                    >
                        No FAQs yet. Add the questions students ask most before enrolling.
                    </div>

                    <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div
                            v-for="faq in course.faqs"
                            :key="faq.id"
                            class="flex items-start justify-between gap-4 p-4"
                        >
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800 dark:text-white/90">
                                    {{ faq.question }}
                                </p>
                                <p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ truncate(faq.answer) }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <button
                                    @click="openFaqForm(faq)"
                                    class="text-brand-500 text-xs hover:underline"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteFaq(faq)"
                                    class="text-xs text-red-500 hover:underline"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    @click="openFaqForm(null)"
                    class="text-brand-500 text-sm font-medium hover:underline"
                >
                    + Add FAQ
                </button>
            </div>

            <!-- ── ASSIGNMENTS TAB ── -->
            <div v-else-if="activeTab === 'assignments'" class="space-y-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div
                        v-if="!course.assignments?.length"
                        class="p-6 text-center text-sm text-gray-400"
                    >
                        No assignments yet. Add a task for students to submit and get graded on.
                    </div>

                    <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div
                            v-for="assignment in course.assignments"
                            :key="assignment.id"
                            class="flex items-start justify-between gap-4 p-4"
                        >
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800 dark:text-white/90">
                                    {{ assignment.title }}
                                </p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400">
                                    <span>{{ assignment.max_score }} points</span>
                                    <span v-if="assignment.due_date">Due {{ assignment.due_date }}</span>
                                    <span v-if="assignment.allow_late_submission" class="text-amber-500">
                                        Late submissions allowed
                                    </span>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <Link
                                    :href="`/lms/assignments/${assignment.id}/submissions`"
                                    class="text-xs font-medium text-gray-500 hover:underline dark:text-gray-400"
                                >
                                    Submissions
                                </Link>
                                <button
                                    @click="openAssignmentForm(assignment)"
                                    class="text-brand-500 text-xs hover:underline"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteAssignment(assignment)"
                                    class="text-xs text-red-500 hover:underline"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    @click="openAssignmentForm(null)"
                    class="text-brand-500 text-sm font-medium hover:underline"
                >
                    + Add Assignment
                </button>
            </div>

            <!-- ── SETTINGS TAB ── -->
            <div
                v-else-if="activeTab === 'settings'"
                class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <form @submit.prevent="saveSettings" class="space-y-5">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Title</label
                        >
                        <input
                            v-model="settingsForm.title"
                            type="text"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p
                            v-if="settingsForm.errors.title"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ settingsForm.errors.title }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Short description</label
                        >
                        <p class="mb-1.5 text-xs text-gray-400">
                            One line shown on course cards and search results.
                        </p>
                        <input
                            v-model="settingsForm.short_description"
                            type="text"
                            maxlength="255"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p
                            v-if="settingsForm.errors.short_description"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ settingsForm.errors.short_description }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Description</label
                        >

                        <FormTexera
                            v-model="settingsForm.description"
                            placeholder="House, Road, Area"
                            :error="settingsForm.errors.description"
                            :rows="4"
                            class="w-full rounded-lg border-gray-300 bg-transparent text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Thumbnail
                        </label>

                        <div v-if="thumbnailPreview" class="relative">
                            <img
                                :src="thumbnailPreview"
                                class="h-48 w-full rounded-xl border border-gray-200 object-cover dark:border-gray-700"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center gap-2 rounded-xl bg-black/0 opacity-0 transition hover:bg-black/40 hover:opacity-100"
                            >
                                <button
                                    type="button"
                                    @click="thumbnailInput?.click()"
                                    class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                    Change
                                </button>
                                <button
                                    v-if="newThumbnailPreview"
                                    type="button"
                                    @click="removeNewThumbnail"
                                    class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-red-600 shadow-sm hover:bg-red-50"
                                >
                                    <X class="h-3.5 w-3.5" />
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div
                            v-else
                            @click="thumbnailInput?.click()"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 py-10 text-center transition hover:border-gray-400 dark:border-gray-700"
                        >
                            <div
                                class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800"
                            >
                                <ImageIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="text-brand-500 font-medium"
                                    >Upload a thumbnail</span
                                >
                                or drag it here
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                PNG, JPG up to 2MB
                            </p>
                        </div>

                        <p
                            v-if="settingsForm.thumbnail"
                            class="mt-2 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400"
                        >
                            <FileImage class="h-3.5 w-3.5" />
                            {{ settingsForm.thumbnail.name }} ·
                            {{ formatFileSize(settingsForm.thumbnail.size) }}
                        </p>

                        <input
                            ref="thumbnailInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onThumbnailChange"
                        />

                        <p
                            v-if="settingsForm.errors.thumbnail"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ settingsForm.errors.thumbnail }}
                        </p>
                    </div>

                    <!-- ── Preview image ── -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Preview image
                        </label>
                        <p class="mb-2 text-xs text-gray-400">
                            Larger banner shown on the course page — falls back to the thumbnail if left empty.
                        </p>

                        <div v-if="previewImagePreview" class="relative">
                            <img
                                :src="previewImagePreview"
                                class="h-48 w-full rounded-xl border border-gray-200 object-cover dark:border-gray-700"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center gap-2 rounded-xl bg-black/0 opacity-0 transition hover:bg-black/40 hover:opacity-100"
                            >
                                <button
                                    type="button"
                                    @click="previewImageInput?.click()"
                                    class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                    Change
                                </button>
                                <button
                                    v-if="newPreviewImagePreview"
                                    type="button"
                                    @click="removeNewPreviewImage"
                                    class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-red-600 shadow-sm hover:bg-red-50"
                                >
                                    <X class="h-3.5 w-3.5" />
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div
                            v-else
                            @click="previewImageInput?.click()"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 py-10 text-center transition hover:border-gray-400 dark:border-gray-700"
                        >
                            <div
                                class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800"
                            >
                                <ImageIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="text-brand-500 font-medium"
                                    >Upload a preview image</span
                                >
                                or drag it here
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                PNG, JPG up to 4MB
                            </p>
                        </div>

                        <p
                            v-if="settingsForm.preview_image"
                            class="mt-2 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400"
                        >
                            <FileImage class="h-3.5 w-3.5" />
                            {{ settingsForm.preview_image.name }} ·
                            {{ formatFileSize(settingsForm.preview_image.size) }}
                        </p>

                        <input
                            ref="previewImageInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onPreviewImageChange"
                        />

                        <p
                            v-if="settingsForm.errors.preview_image"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ settingsForm.errors.preview_image }}
                        </p>
                    </div>

                    <!-- ── Preview video (course trailer) ── -->
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <p class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Preview video
                        </p>
                        <p class="mb-3 text-xs text-gray-400">
                            A short trailer students can watch before enrolling — paste a YouTube link or upload a file.
                        </p>

                        <div
                            v-if="settingsForm.preview_video_path || settingsForm.preview_video_url"
                            class="mb-2 flex items-center justify-between gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700"
                        >
                            <div
                                v-if="settingsForm.preview_video_path"
                                class="flex min-w-0 items-center gap-2 text-green-700 dark:text-green-400"
                            >
                                <CheckCircle class="h-3.5 w-3.5 shrink-0" />
                                <span class="truncate">{{ displayPreviewVideoName }}</span>
                            </div>
                            <div v-else class="min-w-0 truncate text-gray-600 dark:text-gray-300">
                                {{ settingsForm.preview_video_url }}
                            </div>
                            <button
                                type="button"
                                @click="clearPreviewVideo"
                                class="shrink-0 text-red-500 hover:underline"
                            >
                                Remove
                            </button>
                        </div>

                        <input
                            v-if="!settingsForm.preview_video_path"
                            v-model="settingsForm.preview_video_url"
                            type="text"
                            placeholder="YouTube URL"
                            class="mb-2 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
                        />

                        <template
                            v-if="!settingsForm.preview_video_path && !settingsForm.preview_video_url"
                        >
                            <p class="mb-2 text-center text-xs text-gray-400">— OR —</p>
                            <VideoUploader
                                upload-url="/lms/courses/upload-video"
                                :max-size-mb="500"
                                @uploaded="onPreviewVideoUploaded"
                                @removed="onPreviewVideoRemoved"
                            />
                        </template>

                        <p
                            v-if="settingsForm.errors.preview_video_url"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ settingsForm.errors.preview_video_url }}
                        </p>
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
                                    :checked="settingsForm.instructor_ids.includes(instructor.id)"
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
                            No instructors found — add one from the Instructors page first.
                        </p>

                        <p
                            v-if="settingsForm.errors.instructor_ids"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ settingsForm.errors.instructor_ids }}
                        </p>
                    </div>

                    <div
                        class="rounded-xl border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <label class="flex items-center gap-2">
                            <input
                                v-model="settingsForm.is_free"
                                type="checkbox"
                                class="rounded"
                            />
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Free course</span
                            >
                        </label>
                        <div v-if="!settingsForm.is_free" class="mt-3 grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                    >Price (৳)</label
                                >
                                <input
                                    v-model.number="settingsForm.price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                />
                                <p
                                    v-if="settingsForm.errors.price"
                                    class="mt-1.5 text-sm text-red-500"
                                >
                                    {{ settingsForm.errors.price }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                    >Discount price (৳)</label
                                >
                                <input
                                    v-model.number="settingsForm.discount_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="Optional"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                                />
                                <p class="mt-1.5 text-xs text-gray-400">Leave empty for no discount.</p>
                                <p
                                    v-if="settingsForm.errors.discount_price"
                                    class="mt-1.5 text-sm text-red-500"
                                >
                                    {{ settingsForm.errors.discount_price }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Live class starts at (optional)</label
                        >
                        <p class="mb-2 text-xs text-gray-400">
                            Shows a live countdown on the course page until this time.
                        </p>
                        <input
                            v-model="settingsForm.live_class_starts_at"
                            type="datetime-local"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p
                            v-if="settingsForm.errors.live_class_starts_at"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ settingsForm.errors.live_class_starts_at }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2">
                        <input
                            v-model="settingsForm.sequential_unlock"
                            type="checkbox"
                            class="rounded"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300"
                            >Require sequential completion</span
                        >
                    </label>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Status</label
                        >
                        <select
                            v-model="settingsForm.status"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        :disabled="settingsForm.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{
                            settingsForm.processing
                                ? 'Saving...'
                                : 'Save Changes'
                        }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Lesson Modal -->
        <div
            v-if="lessonModal.open"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="closeLessonForm"
        >
            <div
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 dark:bg-gray-900"
            >
                <h4
                    class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    {{ lessonModal.lesson ? 'Edit Lesson' : 'Add Lesson' }}
                </h4>

                <form @submit.prevent="submitLesson" class="space-y-4">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Title</label
                        >
                        <input
                            v-model="lessonForm.title"
                            type="text"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p
                            v-if="lessonForm.errors.title"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ lessonForm.errors.title }}
                        </p>
                    </div>

                    <!-- Video -->
                    <div
                        class="rounded-xl border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <p
                            class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Video (optional)
                        </p>

                        <div
                            v-if="lessonForm.video_path"
                            class="mb-3 flex items-center justify-between rounded-lg bg-green-50 p-2.5 text-xs dark:bg-green-900/10"
                        >
                            <div
                                class="flex min-w-0 items-center gap-2 text-green-700 dark:text-green-400"
                            >
                                <CheckCircle class="h-3.5 w-3.5 shrink-0" />
                                <span class="truncate">{{
                                    displayVideoName
                                }}</span>
                            </div>
                            <button
                                type="button"
                                @click="clearVideo"
                                class="shrink-0 text-red-500 hover:underline"
                            >
                                Remove
                            </button>
                        </div>

                        <input
                            v-if="!lessonForm.video_path"
                            v-model="lessonForm.video_url"
                            type="text"
                            placeholder="YouTube URL"
                            class="mb-2 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
                        />

                        <template
                            v-if="
                                !lessonForm.video_path && !lessonForm.video_url
                            "
                        >
                            <p class="mb-2 text-center text-xs text-gray-400">
                                — OR —
                            </p>
                            <VideoUploader
                                upload-url="/lms/lessons/upload-video"
                                :max-size-mb="500"
                                @uploaded="onVideoUploaded"
                                @removed="onVideoRemoved"
                            />
                        </template>

                        <input
                            v-model.number="lessonForm.video_duration_minutes"
                            type="number"
                            placeholder="Duration (minutes)"
                            class="mt-2 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
                        />

                        <input
                            v-model.number="lessonForm.video_complete_threshold_seconds"
                            type="number"
                            placeholder="Mark complete after (seconds) — default 120"
                            class="mt-2 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
                        />
                        <p class="mt-1 text-xs text-gray-400">
                            How many seconds a student must watch before this lesson counts as complete.
                        </p>
                    </div>

                    <!-- Ebook -->
                    <div
                        class="rounded-xl border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <p
                            class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Ebook (optional)
                        </p>

                        <div
                            v-if="existingEbookName && !lessonForm.ebook_file"
                            class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-white/[0.03]"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800"
                                >
                                    <FileText
                                        class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                    />
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        {{ existingEbookName }}
                                    </p>
                                    <p
                                        class="text-xs text-green-600 dark:text-green-400"
                                    >
                                        Uploaded
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="ebookInput?.click()"
                                class="text-brand-500 shrink-0 text-xs font-medium hover:underline"
                            >
                                Replace
                            </button>
                        </div>

                        <div
                            v-else-if="lessonForm.ebook_file"
                            class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 p-3 dark:border-green-900/40 dark:bg-green-900/10"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30"
                                >
                                    <CheckCircle
                                        class="h-4 w-4 text-green-600 dark:text-green-400"
                                    />
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-medium text-green-700 dark:text-green-400"
                                    >
                                        {{ lessonForm.ebook_file.name }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            formatFileSize(
                                                lessonForm.ebook_file.size,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="clearEbook"
                                class="shrink-0 text-xs font-medium text-red-500 hover:underline"
                            >
                                Remove
                            </button>
                        </div>

                        <div
                            v-else
                            @click="ebookInput?.click()"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-5 text-center transition hover:border-gray-400 dark:border-gray-700"
                        >
                            <UploadCloud class="mb-1.5 h-6 w-6 text-gray-400" />
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="text-brand-500 font-medium"
                                    >Upload a PDF</span
                                >
                                or drag it here
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                PDF up to 20MB
                            </p>
                        </div>

                        <input
                            ref="ebookInput"
                            type="file"
                            accept=".pdf"
                            class="hidden"
                            @change="onEbookChange"
                        />

                        <p class="mt-2 text-xs text-gray-400">
                            Leave empty to keep current ebook.
                        </p>
                    </div>

                    <!-- ── Quiz — computed-based selection, mousedown.prevent, instant local update ── -->
                    <div
                        v-if="lessonModal.lesson"
                        class="rounded-xl border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <p
                                class="text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Quiz (optional)
                            </p>
                            <Link
                                href="/lms/quizzes/create"
                                class="text-brand-500 text-xs font-medium hover:underline"
                            >
                                + Create New Quiz
                            </Link>
                        </div>

                        <div
                            v-if="lessonModal.lesson.quizzes?.length"
                            class="mb-3 space-y-2"
                        >
                            <div
                                v-for="quiz in lessonModal.lesson.quizzes"
                                :key="quiz.id"
                                class="flex items-center justify-between rounded-lg bg-gray-50 p-2.5 text-xs dark:bg-white/[0.03]"
                            >
                                <div class="flex min-w-0 items-center gap-2">
                                    <HelpCircle
                                        class="h-3.5 w-3.5 shrink-0 text-gray-500"
                                    />
                                    <span
                                        class="truncate text-gray-700 dark:text-gray-300"
                                        >{{ quiz.title }}</span
                                    >
                                    <span class="shrink-0 text-gray-400"
                                        >({{
                                            quiz.questions_count ?? 0
                                        }}
                                        Q)</span
                                    >
                                </div>
                                <button
                                    type="button"
                                    @click="detachQuiz(quiz)"
                                    :disabled="quizActionLoading"
                                    class="shrink-0 text-red-500 hover:underline disabled:opacity-50"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div ref="quizSearchWrapper" class="relative">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <Search
                                        class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-gray-400"
                                    />
                                    <input
                                        v-model="quizSearchTerm"
                                        type="text"
                                        placeholder="Search quiz by name..."
                                        class="h-9 w-full rounded-lg border border-gray-300 bg-transparent py-2 pr-3 pl-9 text-sm dark:border-gray-700 dark:text-white/90"
                                        @click="showQuizDropdown = true"
                                    />

                                    <div
                                        v-if="showQuizDropdown"
                                        @mousedown.prevent
                                        class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                                    >
                                        <div
                                            v-if="quizSearchLoading"
                                            class="p-3 text-center text-xs text-gray-400"
                                        >
                                            Searching...
                                        </div>
                                        <template
                                            v-else-if="quizSearchResults.length"
                                        >
                                            <button
                                                v-for="q in quizSearchResults"
                                                :key="q.id"
                                                type="button"
                                                @click="selectQuiz(q)"
                                                class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                                            >
                                                <span
                                                    class="truncate text-gray-700 dark:text-gray-300"
                                                    >{{ q.title }}</span
                                                >
                                                <span
                                                    class="shrink-0 text-xs text-gray-400"
                                                    >{{
                                                        q.questions_count
                                                    }}
                                                    Q</span
                                                >
                                            </button>
                                        </template>
                                        <div
                                            v-else
                                            class="p-3 text-center text-xs text-gray-400"
                                        >
                                            No matching quiz found.
                                            <br />
                                            <Link
                                                href="/lms/quizzes/create"
                                                class="text-brand-500 hover:underline"
                                            >
                                                Create a new quiz instead?
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="attachQuiz"
                                    :disabled="!canAttach"
                                    class="rounded-lg px-4 text-sm font-medium text-white transition disabled:cursor-not-allowed"
                                    :class="
                                        canAttach
                                            ? 'bg-brand-500 hover:bg-brand-600'
                                            : 'bg-gray-300 dark:bg-gray-700'
                                    "
                                >
                                    {{ quizActionLoading ? '...' : 'Attach' }}
                                </button>
                            </div>

                            <p
                                v-if="selectedQuiz"
                                class="mt-1.5 text-xs text-gray-500 dark:text-gray-400"
                            >
                                Selected:
                                <span
                                    class="text-brand-600 dark:text-brand-400 font-medium"
                                    >{{ selectedQuiz.title }}</span
                                >
                                <button
                                    type="button"
                                    @click="clearQuizSelection"
                                    class="ml-1 text-red-500 hover:underline"
                                >
                                    ✕
                                </button>
                            </p>
                            <p
                                v-else-if="quizSearchTerm"
                                class="mt-1.5 text-xs text-amber-600 dark:text-amber-400"
                            >
                                Please select a quiz from the list above.
                            </p>
                        </div>
                    </div>

                    <!-- ── Assignment attach — same search-and-attach pattern as quiz ── -->
                    <div v-if="lessonModal.lesson" class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Assignment (optional)
                            </p>
                            <button
                                type="button"
                                @click="openAssignmentForm(null)"
                                class="text-brand-500 text-xs font-medium hover:underline"
                            >
                                + Create New Assignment
                            </button>
                        </div>

                        <div
                            v-if="lessonModal.lesson.assignments?.length"
                            class="mb-3 space-y-2"
                        >
                            <div
                                v-for="assignment in lessonModal.lesson.assignments"
                                :key="assignment.id"
                                class="flex items-center justify-between rounded-lg bg-gray-50 p-2.5 text-xs dark:bg-white/[0.03]"
                            >
                                <div class="flex min-w-0 items-center gap-2">
                                    <ClipboardList class="h-3.5 w-3.5 shrink-0 text-gray-500" />
                                    <span class="truncate text-gray-700 dark:text-gray-300">{{ assignment.title }}</span>
                                    <span class="shrink-0 text-gray-400">({{ assignment.max_score }} pts)</span>
                                </div>
                                <button
                                    type="button"
                                    @click="detachAssignment(assignment)"
                                    :disabled="assignmentActionLoading"
                                    class="shrink-0 text-red-500 hover:underline disabled:opacity-50"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div ref="assignmentSearchWrapper" class="relative">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <Search class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
                                    <input
                                        v-model="assignmentSearchTerm"
                                        type="text"
                                        placeholder="Search assignment by title..."
                                        class="h-9 w-full rounded-lg border border-gray-300 bg-transparent py-2 pr-3 pl-9 text-sm dark:border-gray-700 dark:text-white/90"
                                        @click="showAssignmentDropdown = true"
                                    />

                                    <div
                                        v-if="showAssignmentDropdown"
                                        @mousedown.prevent
                                        class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                                    >
                                        <div v-if="assignmentSearchLoading" class="p-3 text-center text-xs text-gray-400">
                                            Searching...
                                        </div>
                                        <template v-else-if="assignmentSearchResults.length">
                                            <button
                                                v-for="a in assignmentSearchResults"
                                                :key="a.id"
                                                type="button"
                                                @click="selectAssignment(a)"
                                                class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                                            >
                                                <span class="truncate text-gray-700 dark:text-gray-300">{{ a.title }}</span>
                                            </button>
                                        </template>
                                        <div v-else class="p-3 text-center text-xs text-gray-400">
                                            No matching assignment found. Use "Create New Assignment" above.
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="attachAssignment"
                                    :disabled="!canAttachAssignment"
                                    class="rounded-lg px-4 text-sm font-medium text-white transition disabled:cursor-not-allowed"
                                    :class="
                                        canAttachAssignment
                                            ? 'bg-brand-500 hover:bg-brand-600'
                                            : 'bg-gray-300 dark:bg-gray-700'
                                    "
                                >
                                    {{ assignmentActionLoading ? '...' : 'Attach' }}
                                </button>
                            </div>

                            <p v-if="selectedAssignment" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                Selected:
                                <span class="text-brand-600 dark:text-brand-400 font-medium">{{ selectedAssignment.title }}</span>
                                <button
                                    type="button"
                                    @click="clearAssignmentSelection"
                                    class="ml-1 text-red-500 hover:underline"
                                >
                                    ✕
                                </button>
                            </p>
                            <p v-else-if="assignmentSearchTerm" class="mt-1.5 text-xs text-amber-600 dark:text-amber-400">
                                Please select an assignment from the list above.
                            </p>
                        </div>
                    </div>
                    <p
                        v-else
                        class="rounded-lg bg-amber-50 p-3 text-center text-xs text-amber-700 dark:bg-amber-900/10 dark:text-amber-400"
                    >
                        Save this lesson first to unlock quiz attachment.
                    </p>

                    <label class="flex items-center gap-2">
                        <input
                            v-model="lessonForm.is_free_preview"
                            type="checkbox"
                            class="rounded"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300"
                            >Free preview (visible without enrollment)</span
                        >
                    </label>

                    <label class="flex items-center gap-2">
                        <input
                            v-model="lessonForm.requires_completion"
                            type="checkbox"
                            class="rounded"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300"
                            >Must complete before next lesson unlocks</span
                        >
                    </label>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="closeLessonForm"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                        >
                            {{ lessonModal.lesson ? 'Done' : 'Cancel' }}
                        </button>
                        <button
                            type="submit"
                            :disabled="lessonForm.processing"
                            class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {{
                                lessonForm.processing
                                    ? 'Saving...'
                                    : lessonModal.lesson
                                      ? 'Update Lesson'
                                      : 'Save & Continue'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- FAQ Modal -->
        <div
            v-if="faqModal.open"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="closeFaqForm"
        >
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 dark:bg-gray-900">
                <h4 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
                    {{ faqModal.faq ? 'Edit FAQ' : 'Add FAQ' }}
                </h4>

                <form @submit.prevent="submitFaq" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Question
                        </label>
                        <input
                            v-model="faqForm.question"
                            type="text"
                            placeholder="e.g. Class কখন শুরু?"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p v-if="faqForm.errors.question" class="mt-1.5 text-sm text-red-500">
                            {{ faqForm.errors.question }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Answer
                        </label>
                        <FormTexera
                            v-model="faqForm.answer"
                            placeholder="Full answer shown when this question is expanded"
                            :error="faqForm.errors.answer"
                            :rows="5"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="closeFaqForm"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="faqForm.processing"
                            class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {{
                                faqForm.processing
                                    ? 'Saving...'
                                    : faqModal.faq
                                      ? 'Update FAQ'
                                      : 'Add FAQ'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div
            v-if="assignmentModal.open"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="closeAssignmentForm"
        >
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 dark:bg-gray-900">
                <h4 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
                    {{ assignmentModal.assignment ? 'Edit Assignment' : 'Add Assignment' }}
                </h4>

                <form @submit.prevent="submitAssignment" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Title
                        </label>
                        <input
                            v-model="assignmentForm.title"
                            type="text"
                            placeholder="e.g. Final project submission"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p v-if="assignmentForm.errors.title" class="mt-1.5 text-sm text-red-500">
                            {{ assignmentForm.errors.title }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Instructions
                        </label>
                        <FormTexera
                            v-model="assignmentForm.instructions"
                            placeholder="What should students submit, and how will it be graded?"
                            :error="assignmentForm.errors.instructions"
                            :rows="5"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Attach a file (optional)
                        </label>
                        <p class="mb-2 text-xs text-gray-400">
                            e.g. a question paper, template, or rubric for students to reference.
                        </p>

                        <div
                            v-if="assignmentModal.assignment?.file_name && !assignmentForm.file && !removeExistingAssignmentFile"
                            class="mb-2 flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-xs dark:bg-white/5"
                        >
                            <span class="truncate text-gray-600 dark:text-gray-300">
                                {{ assignmentModal.assignment.file_name }}
                            </span>
                            <button
                                type="button"
                                class="shrink-0 text-red-500 hover:underline"
                                @click="removeExistingAssignmentFile = true"
                            >
                                Remove
                            </button>
                        </div>

                        <input
                            ref="assignmentFileInput"
                            type="file"
                            class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium dark:text-gray-300 dark:file:bg-gray-800 dark:file:text-gray-200"
                            @change="assignmentForm.file = $event.target.files[0] ?? null"
                        />
                        <p v-if="assignmentForm.errors.file" class="mt-1.5 text-sm text-red-500">
                            {{ assignmentForm.errors.file }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Due date (optional)
                            </label>
                            <input
                                v-model="assignmentForm.due_date"
                                type="datetime-local"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                            <p v-if="assignmentForm.errors.due_date" class="mt-1.5 text-sm text-red-500">
                                {{ assignmentForm.errors.due_date }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Max score
                            </label>
                            <input
                                v-model.number="assignmentForm.max_score"
                                type="number"
                                min="1"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                            />
                            <p v-if="assignmentForm.errors.max_score" class="mt-1.5 text-sm text-red-500">
                                {{ assignmentForm.errors.max_score }}
                            </p>
                        </div>
                    </div>

                    <label class="flex items-center gap-2">
                        <input v-model="assignmentForm.allow_late_submission" type="checkbox" class="rounded" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            Allow submissions after the due date (marked as late)
                        </span>
                    </label>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="closeAssignmentForm"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="assignmentForm.processing"
                            class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {{
                                assignmentForm.processing
                                    ? 'Saving...'
                                    : assignmentModal.assignment
                                      ? 'Update Assignment'
                                      : 'Add Assignment'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import VideoUploader from '@/Components/ui/VideoUploader.vue';
import FormTexera from '@/Components/ui/FormTexera.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { truncate } from '@/composables/text.js';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
    ClipboardList,
    FileImage,
    FileText,
    HelpCircle,
    Image as ImageIcon,
    Pencil,
    PlayCircle,
    Search,
    UploadCloud,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    course: Object,
    categories: Array,
    subcategories: Array,
    instructors: Object,
});

const page = usePage();

const activeTab = ref('content');
const newModuleTitle = ref('');

const formatFileSize = (bytes) => {
    if (!bytes) return '';
    const mb = bytes / (1024 * 1024);
    return mb >= 1 ? `${mb.toFixed(1)} MB` : `${(bytes / 1024).toFixed(0)} KB`;
};

const statusClass = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
        published:
            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        archived:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};

const lessonIcon = (lesson) => {
    if (lesson.video_url || lesson.video_path) return PlayCircle;
    if (lesson.ebook_path) return FileText;
    if (lesson.quizzes?.length) return HelpCircle;
    return BookOpen;
};

// ── Module ──
const addModule = () => {
    if (!newModuleTitle.value.trim()) return;

    router.post(
        `/lms/courses/${props.course.id}/modules`,
        { title: newModuleTitle.value },
        {
            preserveScroll: true,
            onSuccess: () => (newModuleTitle.value = ''),
        },
    );
};

const deleteModule = (module) => {
    if (!confirm(`Delete section "${module.title}" and all its lessons?`))
        return;
    router.delete(`/lms/modules/${module.id}`, { preserveScroll: true });
};

// ── FAQ ──
const faqModal = ref({ open: false, faq: null });

const faqForm = useForm({
    question: '',
    answer: '',
});

const openFaqForm = (faq) => {
    faqModal.value = { open: true, faq };
    faqForm.clearErrors();

    if (faq) {
        faqForm.question = faq.question;
        faqForm.answer = faq.answer;
    } else {
        faqForm.reset();
    }
};

const closeFaqForm = () => {
    faqModal.value = { open: false, faq: null };
    faqForm.reset();
};

const submitFaq = () => {
    const { faq } = faqModal.value;

    if (faq) {
        faqForm
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(`/lms/faqs/${faq.id}`, {
                preserveScroll: true,
                onSuccess: closeFaqForm,
            });
    } else {
        faqForm.post(`/lms/courses/${props.course.id}/faqs`, {
            preserveScroll: true,
            onSuccess: closeFaqForm,
        });
    }
};

const deleteFaq = (faq) => {
    if (!confirm(`Delete the FAQ "${faq.question}"?`)) return;
    router.delete(`/lms/faqs/${faq.id}`, { preserveScroll: true });
};

// ── Assignment ──
const assignmentModal = ref({ open: false, assignment: null });
const assignmentFileInput = ref(null);
const removeExistingAssignmentFile = ref(false);

const assignmentForm = useForm({
    title: '',
    instructions: '',
    file: null,
    due_date: '',
    max_score: 100,
    allow_late_submission: false,
});

const openAssignmentForm = (assignment) => {
    assignmentModal.value = { open: true, assignment };
    assignmentForm.clearErrors();
    removeExistingAssignmentFile.value = false;

    if (assignment) {
        assignmentForm.title = assignment.title;
        assignmentForm.instructions = assignment.instructions ?? '';
        assignmentForm.file = null;
        assignmentForm.due_date = toLocalDatetimeInput(assignment.due_date) ?? '';
        assignmentForm.max_score = assignment.max_score;
        assignmentForm.allow_late_submission = assignment.allow_late_submission;
    } else {
        assignmentForm.reset();
        assignmentForm.max_score = 100;
    }

    if (assignmentFileInput.value) {
        assignmentFileInput.value.value = '';
    }
};

const closeAssignmentForm = () => {
    assignmentModal.value = { open: false, assignment: null };
    assignmentForm.reset();
    removeExistingAssignmentFile.value = false;
};

const submitAssignment = () => {
    const { assignment } = assignmentModal.value;

    if (assignment) {
        assignmentForm
            .transform((data) => ({
                ...data,
                _method: 'put',
                remove_file: removeExistingAssignmentFile.value,
            }))
            .post(`/lms/assignments/${assignment.id}`, {
                preserveScroll: true,
                onSuccess: closeAssignmentForm,
            });
    } else {
        assignmentForm.post(`/lms/courses/${props.course.id}/assignments`, {
            preserveScroll: true,
            onSuccess: closeAssignmentForm,
        });
    }
};

const deleteAssignment = (assignment) => {
    if (!confirm(`Delete the assignment "${assignment.title}"? Student submissions will also be removed.`)) return;
    router.delete(`/lms/assignments/${assignment.id}`, { preserveScroll: true });
};

// ── Lesson ──
const lessonModal = ref({ open: false, module: null, lesson: null });
const existingEbookName = ref(null);
const ebookInput = ref(null);
const quizActionLoading = ref(false);

const lessonForm = useForm({
    title: '',
    video_url: '',
    video_path: null,
    video_source: null,
    video_duration_minutes: '',
    video_complete_threshold_seconds: '',
    ebook_file: null,
    is_free_preview: false,
    requires_completion: false,
});

const displayVideoName = computed(() => {
    if (!lessonForm.video_path) return '';
    return lessonForm.video_path.split('/').pop();
});

// ── Quiz search — computed-based selection state (single source of truth) ──
const quizSearchTerm = ref('');
const quizSearchResults = ref([]);
const quizSearchLoading = ref(false);
const showQuizDropdown = ref(false);
const selectedQuiz = ref(null); // { id, title, questions_count } — EK-ta object, split refs na
const quizSearchWrapper = ref(null);
let quizSearchTimer = null;

const canAttach = computed(
    () => !!selectedQuiz.value && !quizActionLoading.value,
);

const fetchQuizzes = async (term = '') => {
    quizSearchLoading.value = true;

    try {
        const attachedIds = (lessonModal.value.lesson?.quizzes ?? []).map(
            (q) => q.id,
        );
        const res = await fetch(
            `/lms/quizzes-search?q=${encodeURIComponent(term)}`,
            {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            },
        );
        const data = await res.json();
        quizSearchResults.value = data.filter(
            (q) => !attachedIds.includes(q.id),
        );
    } catch (e) {
        quizSearchResults.value = [];
    } finally {
        quizSearchLoading.value = false;
    }
};

const selectQuiz = (quiz) => {
    selectedQuiz.value = quiz;
    quizSearchTerm.value = quiz.title;
    showQuizDropdown.value = false;
};

const clearQuizSelection = () => {
    selectedQuiz.value = null;
    quizSearchTerm.value = '';
    quizSearchResults.value = [];
};

watch(quizSearchTerm, (newValue) => {
    // user selected quiz-er title-er baire type korle, selection clear hoye jabe (expected)
    if (selectedQuiz.value && newValue !== selectedQuiz.value.title) {
        selectedQuiz.value = null;
    }

    clearTimeout(quizSearchTimer);
    quizSearchTimer = setTimeout(() => {
        fetchQuizzes(newValue);
    }, 300);
});

// click-outside — document-level, dropdown-er mousedown.prevent-er sathe compatible
const handleClickOutside = (event) => {
    if (
        quizSearchWrapper.value &&
        !quizSearchWrapper.value.contains(event.target)
    ) {
        showQuizDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// ── Attach — instant local update, modal khola thake ──
const attachQuiz = async () => {
    if (!canAttach.value || !lessonModal.value.lesson) return;

    quizActionLoading.value = true;

    try {
        const res = await fetch(
            `/lms/lessons/${lessonModal.value.lesson.id}/quiz`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ quiz_id: selectedQuiz.value.id }),
            },
        );

        if (res.ok) {
            const attachedQuiz = {
                id: selectedQuiz.value.id,
                title: selectedQuiz.value.title,
                questions_count: selectedQuiz.value.questions_count ?? 0,
            };

            if (!lessonModal.value.lesson.quizzes) {
                lessonModal.value.lesson.quizzes = [];
            }
            lessonModal.value.lesson.quizzes.push(attachedQuiz);

            syncLessonInCourseTree(lessonModal.value.lesson);
            clearQuizSelection();
        }
    } finally {
        quizActionLoading.value = false;
    }
};

// ── Detach — instant local update, modal khola thake ──
const detachQuiz = async (quiz) => {
    if (!lessonModal.value.lesson) return;

    quizActionLoading.value = true;

    try {
        const res = await fetch(
            `/lms/lessons/${lessonModal.value.lesson.id}/quiz/${quiz.id}`,
            {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            },
        );

        if (res.ok) {
            lessonModal.value.lesson.quizzes =
                lessonModal.value.lesson.quizzes.filter(
                    (q) => q.id !== quiz.id,
                );
            syncLessonInCourseTree(lessonModal.value.lesson);
        }
    } finally {
        quizActionLoading.value = false;
    }
};

const syncLessonInCourseTree = (updatedLesson) => {
    for (const module of props.course.modules) {
        const idx = module.lessons.findIndex((l) => l.id === updatedLesson.id);
        if (idx !== -1) {
            module.lessons[idx].quizzes = updatedLesson.quizzes;
            module.lessons[idx].assignments = updatedLesson.assignments;
            break;
        }
    }
};

// ── Assignment search — same pattern as quiz search ──
const assignmentSearchTerm = ref('');
const assignmentSearchResults = ref([]);
const assignmentSearchLoading = ref(false);
const showAssignmentDropdown = ref(false);
const selectedAssignment = ref(null);
const assignmentSearchWrapper = ref(null);
const assignmentActionLoading = ref(false);
let assignmentSearchTimer = null;

const canAttachAssignment = computed(
    () => !!selectedAssignment.value && !assignmentActionLoading.value,
);

const fetchAssignments = async (term = '') => {
    assignmentSearchLoading.value = true;

    try {
        const attachedIds = (lessonModal.value.lesson?.assignments ?? []).map((a) => a.id);
        const res = await fetch(
            `/lms/assignments-search?q=${encodeURIComponent(term)}&course_id=${props.course.id}`,
            { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
        );
        const data = await res.json();
        assignmentSearchResults.value = data.filter((a) => !attachedIds.includes(a.id));
    } catch (e) {
        assignmentSearchResults.value = [];
    } finally {
        assignmentSearchLoading.value = false;
    }
};

const selectAssignment = (assignment) => {
    selectedAssignment.value = assignment;
    assignmentSearchTerm.value = assignment.title;
    showAssignmentDropdown.value = false;
};

const clearAssignmentSelection = () => {
    selectedAssignment.value = null;
    assignmentSearchTerm.value = '';
    assignmentSearchResults.value = [];
};

watch(assignmentSearchTerm, (newValue) => {
    if (selectedAssignment.value && newValue !== selectedAssignment.value.title) {
        selectedAssignment.value = null;
    }

    clearTimeout(assignmentSearchTimer);
    assignmentSearchTimer = setTimeout(() => {
        fetchAssignments(newValue);
    }, 300);
});

const handleAssignmentClickOutside = (event) => {
    if (assignmentSearchWrapper.value && !assignmentSearchWrapper.value.contains(event.target)) {
        showAssignmentDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleAssignmentClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleAssignmentClickOutside);
});

const attachAssignment = async () => {
    if (!canAttachAssignment.value || !lessonModal.value.lesson) return;

    assignmentActionLoading.value = true;

    try {
        const res = await fetch(`/lms/lessons/${lessonModal.value.lesson.id}/assignment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body: JSON.stringify({ assignment_id: selectedAssignment.value.id }),
        });

        if (res.ok) {
            const attached = { id: selectedAssignment.value.id, title: selectedAssignment.value.title };

            if (!lessonModal.value.lesson.assignments) {
                lessonModal.value.lesson.assignments = [];
            }
            lessonModal.value.lesson.assignments.push(attached);

            syncLessonInCourseTree(lessonModal.value.lesson);
            clearAssignmentSelection();
        }
    } finally {
        assignmentActionLoading.value = false;
    }
};

const detachAssignment = async (assignment) => {
    if (!lessonModal.value.lesson) return;

    assignmentActionLoading.value = true;

    try {
        const res = await fetch(`/lms/lessons/${lessonModal.value.lesson.id}/assignment/${assignment.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        if (res.ok) {
            lessonModal.value.lesson.assignments = lessonModal.value.lesson.assignments.filter(
                (a) => a.id !== assignment.id,
            );
            syncLessonInCourseTree(lessonModal.value.lesson);
        }
    } finally {
        assignmentActionLoading.value = false;
    }
};

const openLessonForm = (module, lesson) => {
    lessonModal.value = { open: true, module, lesson };

    if (lesson) {
        lessonForm.title = lesson.title;
        lessonForm.video_url = lesson.video_url ?? '';
        lessonForm.video_path = lesson.video_path ?? null;
        lessonForm.video_source = lesson.video_source ?? null;
        lessonForm.video_duration_minutes = lesson.video_duration_minutes ?? '';
        lessonForm.video_complete_threshold_seconds = lesson.video_complete_threshold_seconds ?? '';
        lessonForm.ebook_file = null;
        lessonForm.is_free_preview = lesson.is_free_preview;
        lessonForm.requires_completion = lesson.requires_completion;
        existingEbookName.value = lesson.ebook_path
            ? lesson.ebook_path.split('/').pop()
            : null;

        if (!lesson.quizzes) {
            lesson.quizzes = [];
        }
        if (!lesson.assignments) {
            lesson.assignments = [];
        }

        fetchQuizzes();
        fetchAssignments();
    } else {
        lessonForm.reset();
        existingEbookName.value = null;
    }

    clearQuizSelection();
    clearAssignmentSelection();

    if (ebookInput.value) {
        ebookInput.value.value = '';
    }
};

const closeLessonForm = () => {
    lessonModal.value = { open: false, module: null, lesson: null };
    lessonForm.reset();
    existingEbookName.value = null;
    clearQuizSelection();
};

const onVideoUploaded = (file) => {
    lessonForm.video_path = file.path;
    lessonForm.video_source = 'upload';
};

const onVideoRemoved = () => {
    lessonForm.video_path = null;
    lessonForm.video_source = null;
};

const clearVideo = () => {
    lessonForm.video_path = null;
    lessonForm.video_source = null;
};

const onEbookChange = (e) => {
    lessonForm.ebook_file = e.target.files[0] ?? null;
};

const clearEbook = () => {
    lessonForm.ebook_file = null;
    if (ebookInput.value) {
        ebookInput.value.value = '';
    }
};

// ── Submit — create hole modal khola thake (quiz attach korar jonno), update hole bondho hoy ──
const submitLesson = () => {
    const { module, lesson } = lessonModal.value;
    const isCreating = !lesson;

    const url = lesson
        ? `/lms/lessons/${lesson.id}`
        : `/lms/modules/${module.id}/lessons`;

    lessonForm
        .transform((data) => (lesson ? { ...data, _method: 'put' } : data))
        .post(url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                if (isCreating) {
                    const freshCourse = page.props.course;
                    const updatedModule = freshCourse.modules.find(
                        (m) => m.id === module.id,
                    );
                    const newLesson =
                        updatedModule?.lessons[
                            updatedModule.lessons.length - 1
                        ];

                    if (newLesson) {
                        newLesson.quizzes = newLesson.quizzes ?? [];
                        newLesson.assignments = newLesson.assignments ?? [];
                        lessonModal.value = {
                            open: true,
                            module: updatedModule,
                            lesson: newLesson,
                        };
                        fetchQuizzes();
                        fetchAssignments();
                    } else {
                        closeLessonForm();
                    }
                } else {
                    closeLessonForm();
                }
            },
        });
};

const deleteLesson = (lesson) => {
    if (!confirm(`Delete lesson "${lesson.title}"?`)) return;
    router.delete(`/lms/lessons/${lesson.id}`, { preserveScroll: true });
};

// ── Settings ──
// datetime-local inputs interpret their value as LOCAL time, so a plain
// toISOString() (UTC) would show the wrong time — build the local parts.
const toLocalDatetimeInput = (isoString) => {
    if (!isoString) return '';
    const d = new Date(isoString);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const settingsForm = useForm({
    title: props.course.title,
    short_description: props.course.short_description ?? '',
    description: props.course.description ?? '',
    thumbnail: null,
    preview_image: null,
    preview_video_url: props.course.preview_video_url ?? '',
    preview_video_path: props.course.preview_video_path ?? null,
    preview_video_source: props.course.preview_video_source ?? null,
    is_free: props.course.is_free,
    price: props.course.price,
    discount_price: props.course.discount_price ?? '',
    live_class_starts_at: toLocalDatetimeInput(props.course.live_class_starts_at),
    sequential_unlock: props.course.sequential_unlock,
    status: props.course.status,
    instructor_ids: (props.course.instructors ?? []).map((i) => i.id),
});

const toggleInstructor = (id) => {
    const idx = settingsForm.instructor_ids.indexOf(id);
    if (idx === -1) {
        settingsForm.instructor_ids.push(id);
    } else {
        settingsForm.instructor_ids.splice(idx, 1);
    }
};

// ── Instructor picker — scroll pagination + search ──
const initialInstructors = [...(props.instructors.data ?? [])];
(props.course.instructors ?? []).forEach((assigned) => {
    if (!initialInstructors.some((i) => i.id === assigned.id)) {
        initialInstructors.unshift({ id: assigned.id, name: assigned.name, email: assigned.email ?? '' });
    }
});

const instructorList = ref(initialInstructors);
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

const thumbnailInput = ref(null);
const newThumbnailPreview = ref(null);

const thumbnailPreview = computed(() => {
    if (newThumbnailPreview.value) return newThumbnailPreview.value;
    if (props.course.thumbnail) return props.course.thumbnail_url;
    return null;
});

const onThumbnailChange = (e) => {
    const file = e.target.files[0];
    settingsForm.thumbnail = file ?? null;

    if (file) {
        newThumbnailPreview.value = URL.createObjectURL(file);
    }
};

const removeNewThumbnail = () => {
    settingsForm.thumbnail = null;
    newThumbnailPreview.value = null;
    if (thumbnailInput.value) {
        thumbnailInput.value.value = '';
    }
};

// ── Preview image ──
const previewImageInput = ref(null);
const newPreviewImagePreview = ref(null);

const previewImagePreview = computed(() => {
    if (newPreviewImagePreview.value) return newPreviewImagePreview.value;
    if (props.course.preview_image) return props.course.preview_image_url;
    return null;
});

const onPreviewImageChange = (e) => {
    const file = e.target.files[0];
    settingsForm.preview_image = file ?? null;

    if (file) {
        newPreviewImagePreview.value = URL.createObjectURL(file);
    }
};

const removeNewPreviewImage = () => {
    settingsForm.preview_image = null;
    newPreviewImagePreview.value = null;
    if (previewImageInput.value) {
        previewImageInput.value.value = '';
    }
};

// ── Preview video (course trailer) — YouTube URL or uploaded file ──
const displayPreviewVideoName = computed(() => {
    if (!settingsForm.preview_video_path) return '';
    return settingsForm.preview_video_path.split('/').pop();
});

const onPreviewVideoUploaded = (file) => {
    settingsForm.preview_video_path = file.path;
    settingsForm.preview_video_source = 'upload';
};

const onPreviewVideoRemoved = () => {
    settingsForm.preview_video_path = null;
    settingsForm.preview_video_source = null;
};

const clearPreviewVideo = () => {
    settingsForm.preview_video_path = null;
    settingsForm.preview_video_source = null;
    settingsForm.preview_video_url = '';
};

const saveSettings = () => {
    settingsForm
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(`/lms/courses/${props.course.id}`, {
            forceFormData: true,
            preserveScroll: true,
        });
};
</script>