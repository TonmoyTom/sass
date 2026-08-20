<template>
    <WorkspaceLayout title="Instructors">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                        Instructors
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        People credited as "By ..." on your courses.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition"
                >
                    <Plus class="h-4 w-4" />
                    Add Instructor
                </button>
            </div>

            <DataTable
                :data="instructors"
                :filters="filters"
                :columns="[
                    { key: 'name', label: 'Instructor', sortable: true },
                    { key: 'email', label: 'Email', sortable: true },
                    { key: 'courses', label: 'Courses' },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #row="{ row: instructor }">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img
                                :src="instructor.avatar"
                                :alt="instructor.name"
                                class="h-9 w-9 shrink-0 rounded-full object-cover"
                            />
                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-800 dark:text-white/90">
                                    {{ instructor.name }}
                                </p>
                                <p
                                    v-if="instructor.bio"
                                    class="max-w-[220px] cursor-default truncate text-xs text-gray-500 dark:text-gray-400"
                                    @mouseenter="showBioPreview($event, instructor)"
                                    @mouseleave="hideBioPreview"
                                >
                                    {{ truncate(instructor.bio) }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                        <div>{{ instructor.email }}</div>
                        <div v-if="instructor.phone" class="text-xs text-gray-400">
                            {{ instructor.phone }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                        {{ instructor.courses_count }}
                        {{ instructor.courses_count === 1 ? 'course' : 'courses' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button
                                @click="openEdit(instructor)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                Edit
                            </button>
                            <button
                                @click="destroy(instructor)"
                                class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </template>
            </DataTable>
        </div>

        <!-- Bio hover popover -->
        <Teleport to="body">
            <div
                v-if="bioPreview"
                class="pointer-events-none fixed z-[99998] w-72 rounded-xl border border-gray-200 bg-white p-3.5 text-sm text-gray-600 shadow-lg dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                :style="{ top: bioPreview.top + 'px', left: bioPreview.left + 'px' }"
            >
                <p class="mb-1 text-xs font-semibold text-gray-800 dark:text-white/90">
                    {{ bioPreview.name }}
                </p>
                <p class="leading-relaxed whitespace-pre-line">
                    {{ bioPreview.text }}
                </p>
            </div>
        </Teleport>

        <!-- Create/Edit Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="closeModal"
        >
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-6 dark:bg-gray-900">
                <h4 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
                    {{ editingInstructor ? 'Edit Instructor' : 'Add Instructor' }}
                </h4>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Avatar -->
                    <div class="flex items-center gap-4">
                        <img
                            :src="avatarPreview ?? editingInstructor?.avatar ?? defaultAvatar"
                            class="h-16 w-16 shrink-0 rounded-full border border-gray-200 object-cover dark:border-gray-700"
                        />
                        <div>
                            <button
                                type="button"
                                @click="avatarInput?.click()"
                                class="text-brand-500 text-xs font-medium hover:underline"
                            >
                                {{ editingInstructor ? 'Change photo' : 'Upload photo' }}
                            </button>
                            <p class="text-xs text-gray-400">PNG, JPG up to 2MB</p>
                        </div>
                        <input
                            ref="avatarInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onAvatarChange"
                        />
                    </div>
                    <p v-if="form.errors.avatar" class="text-sm text-red-500">
                        {{ form.errors.avatar }}
                    </p>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Name
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Sarah Ahmed"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-500">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Phone (optional)
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Bio (optional)
                        </label>
                        <FormTexera
                               v-model="form.bio"
                               placeholder="House, Road, Area"
                               :error="form.errors.bio"
                               :rows="4"
                               class="w-full rounded-lg border-gray-300 bg-transparent text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                           />
                        
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            {{ editingInstructor ? 'New password (optional)' : 'Password' }}
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div v-if="form.password">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Confirm password
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Saving...'
                                    : editingInstructor
                                      ? 'Update'
                                      : 'Create'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import FormTexera from '@/Components/ui/FormTexera.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import { truncate , stripHtml} from '@/composables/text.js';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    instructors: Object,
    filters: Object,
});

const defaultAvatar = 'https://ui-avatars.com/api/?background=6366f1&color=fff&size=200';

// bio comes through as plain text but sometimes has leftover HTML tags
// (old data) — strip them for display instead of rendering as HTML.


const bioPreview = ref(null);

const showBioPreview = (event, instructor) => {
    const rect = event.currentTarget.getBoundingClientRect();
    const popoverWidth = 288; // w-72

    let left = rect.left;
    if (left + popoverWidth > window.innerWidth - 16) {
        left = window.innerWidth - popoverWidth - 16;
    }

    bioPreview.value = {
        name: instructor.name,
        text: stripHtml(instructor.bio),
        top: rect.bottom + 8,
        left,
    };
};

const hideBioPreview = () => {
    bioPreview.value = null;
};

const showModal = ref(false);
const editingInstructor = ref(null);
const avatarInput = ref(null);
const avatarPreview = ref(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    bio: '',
    password: '',
    password_confirmation: '',
    avatar: null,
});

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    form.avatar = file ?? null;

    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const openCreate = () => {
    editingInstructor.value = null;
    form.reset();
    avatarPreview.value = null;
    showModal.value = true;
};

const openEdit = (instructor) => {
    editingInstructor.value = instructor;
    form.name = instructor.name;
    form.email = instructor.email;
    form.phone = instructor.phone ?? '';
    form.bio = instructor.bio ?? '';
    form.password = '';
    form.password_confirmation = '';
    form.avatar = null;
    avatarPreview.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingInstructor.value = null;
    avatarPreview.value = null;
    form.reset();
};

const submit = () => {
    if (editingInstructor.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            `/lms/instructors/${editingInstructor.value.id}`,
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: closeModal,
            },
        );
    } else {
        form.post('/lms/instructors', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: closeModal,
        });
    }
};

const destroy = (instructor) => {
    if (!confirm(`Remove instructor "${instructor.name}"?`)) return;

    router.delete(`/lms/instructors/${instructor.id}`, {
        preserveScroll: true,
    });
};
</script>
