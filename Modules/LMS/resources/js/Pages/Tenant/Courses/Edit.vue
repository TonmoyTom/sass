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
                    v-for="tab in ['content', 'settings']"
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

            <!-- ── SETTINGS TAB ── -->
            <div
                v-else
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
                        <div v-if="!settingsForm.is_free" class="mt-3">
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
    </WorkspaceLayout>
</template>

<script setup>
import VideoUploader from '@/Components/ui/VideoUploader.vue';
import FormTexera from '@/Components/ui/FormTexera.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
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
            break;
        }
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
        lessonForm.ebook_file = null;
        lessonForm.is_free_preview = lesson.is_free_preview;
        lessonForm.requires_completion = lesson.requires_completion;
        existingEbookName.value = lesson.ebook_path
            ? lesson.ebook_path.split('/').pop()
            : null;

        if (!lesson.quizzes) {
            lesson.quizzes = [];
        }

        fetchQuizzes();
    } else {
        lessonForm.reset();
        existingEbookName.value = null;
    }

    clearQuizSelection();

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
                        lessonModal.value = {
                            open: true,
                            module: updatedModule,
                            lesson: newLesson,
                        };
                        fetchQuizzes();
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
const settingsForm = useForm({
    title: props.course.title,
    description: props.course.description ?? '',
    thumbnail: null,
    is_free: props.course.is_free,
    price: props.course.price,
    sequential_unlock: props.course.sequential_unlock,
    status: props.course.status,
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

const saveSettings = () => {
    settingsForm
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(`/lms/courses/${props.course.id}`, {
            forceFormData: true,
            preserveScroll: true,
        });
};
</script>
