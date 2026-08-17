<template>
    <WorkspaceLayout title="Course Categories">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Course Categories
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Organize your courses into categories.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition"
                >
                    <Plus class="h-4 w-4" />
                    Add Category
                </button>
            </div>

            <DataTable
                :data="categories"
                :filters="filters"
                :columns="[
                    { key: 'name', label: 'Name', sortable: true },
                    { key: 'courses', label: 'Courses Count' },
                    { key: 'status', label: 'Status' },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #filters="{ filters: f, apply }">
                    <select
                        v-model="f.is_active"
                        @change="apply"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                    >
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </template>

                <template #row="{ row: c }">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800 dark:text-white/90">
                            {{ c.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ c.slug }}
                        </p>
                    </td>
                    <td
                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ c.courses_count }} {{
                            c.courses_count === 1 ? '' : '0'
                        }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-medium"
                            :class="
                                c.is_active
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'
                            "
                        >
                            {{ c.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button
                                @click="openEdit(c)"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                            >
                                Edit
                            </button>
                            <button
                                @click="destroy(c)"
                                class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </template>
            </DataTable>
        </div>

        <!-- Create/Edit Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="closeModal"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900"
            >
                <h4
                    class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    {{ editingCategory ? 'Edit Category' : 'Add Category' }}
                </h4>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Name
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Web Development"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1.5 text-sm text-red-500"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Description (optional)
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Sort Order
                        </label>
                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                        />
                    </div>

                    <label class="flex items-center gap-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="rounded"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300"
                            >Active</span
                        >
                    </label>

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
                                    : editingCategory
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
import DataTable from '@/Components/ui/DataTable.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    categories: Object,
    filters: Object,
});

const showModal = ref(false);
const editingCategory = ref(null);

const form = useForm({
    name: '',
    description: '',
    is_active: true,
    sort_order: 0,
});

const openCreate = () => {
    editingCategory.value = null;
    form.reset();
    form.is_active = true;
    form.sort_order = 0;
    showModal.value = true;
};

const openEdit = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description ?? '';
    form.is_active = category.is_active;
    form.sort_order = category.sort_order ?? 0;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
    form.reset();
};

const submit = () => {
    if (editingCategory.value) {
        form.put(`/lms/categories/${editingCategory.value.id}`, {
            preserveScroll: true,
            onSuccess: closeModal,
        });
    } else {
        form.post('/lms/categories', {
            preserveScroll: true,
            onSuccess: closeModal,
        });
    }
};

const destroy = (category) => {
    if (!confirm(`Delete category "${category.name}"?`)) return;

    router.delete(`/lms/categories/${category.id}`, {
        preserveScroll: true,
    });
};
</script>
