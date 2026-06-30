<template>
    <AdminLayout title="New Role">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Create Role
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Role-er naam dao, ar je permissions dorkar segula select
                    koro.
                </p>
            </div>
            <Link
                :href="route('admin.roles.index')"
                class="text-sm font-medium text-gray-600 hover:underline dark:text-gray-400"
            >
                ← Back to list
            </Link>
        </div>

        <!-- Role name -->
        <div
            class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <label
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Role Name
            </label>
            <input
                v-model="form.name"
                type="text"
                placeholder="e.g. support-staff"
                class="w-full max-w-md rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">
                {{ form.errors.name }}
            </p>
        </div>

        <!-- Permissions -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-4 flex items-center justify-between">
                <h4
                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Permissions
                </h4>
                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="selectAll"
                        class="text-brand-600 dark:text-brand-400 text-xs font-medium hover:underline"
                    >
                        Select all
                    </button>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <button
                        type="button"
                        @click="clearAll"
                        class="text-xs font-medium text-gray-500 hover:underline dark:text-gray-400"
                    >
                        Clear all
                    </button>
                </div>
            </div>
            <p v-if="form.errors.permissions" class="mb-3 text-xs text-red-500">
                {{ form.errors.permissions }}
            </p>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="group in permissions"
                    :key="group.label"
                    class="rounded-xl border border-gray-200 p-4 dark:border-gray-800"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <h5
                            class="text-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ group.label }}
                        </h5>
                        <input
                            type="checkbox"
                            :checked="isGroupFullySelected(group)"
                            @change="toggleGroup(group)"
                            class="rounded border-gray-300"
                        />
                    </div>
                    <div class="space-y-2">
                        <label
                            v-for="perm in group.items"
                            :key="perm.id"
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <input
                                type="checkbox"
                                :value="perm.id"
                                v-model="form.permissions"
                                class="rounded border-gray-300"
                            />
                            {{ perm.name }}
                        </label>
                    </div>
                </div>

                <div
                    v-if="!permissions.length"
                    class="col-span-full py-10 text-center text-gray-500 dark:text-gray-400"
                >
                    No permissions found.
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <Link
                :href="route('admin.roles.index')"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-400"
            >
                Cancel
            </Link>
            <button
                @click="submit"
                :disabled="form.processing"
                class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
            >
                Create Role
            </button>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    permissions: Array, // [{ label, items: [{ id, name }] }]
});

const form = useForm({
    name: '',
    permissions: [],
});

const isGroupFullySelected = (group) =>
    group.items.every((p) => form.permissions.includes(p.id));

const toggleGroup = (group) => {
    const ids = group.items.map((p) => p.id);
    if (isGroupFullySelected(group)) {
        form.permissions = form.permissions.filter((id) => !ids.includes(id));
    } else {
        form.permissions = [...new Set([...form.permissions, ...ids])];
    }
};

const selectAll = () => {
    form.permissions = props.permissions.flatMap((g) =>
        g.items.map((p) => p.id),
    );
};

const clearAll = () => {
    form.permissions = [];
};

const submit = () => {
    form.post(route('admin.roles.store'));
};
</script>
