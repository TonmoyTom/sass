<template>
    <WorkspaceLayout title="Edit Role">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center gap-3">
                <a
                    href="/roles"
                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M19 12H5M12 19l-7-7 7-7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </a>
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 capitalize dark:text-white/90"
                    >
                        Edit {{ role.name }}
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Update role name and permissions.
                    </p>
                </div>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <!-- role name -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <FormInput
                        v-model="form.name"
                        label="Role Name"
                        placeholder="e.g. Shop Manager"
                        :error="form.errors.name"
                    />
                </section>

                <!-- permissions -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-5">
                        <h5
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            Permissions
                        </h5>
                        <p class="mt-0.5 text-xs text-gray-400">
                            {{ form.permissions.length }} selected
                        </p>
                    </div>

                    <div
                        v-if="permissionGroups.length"
                        class="flex flex-col gap-5"
                    >
                        <div
                            v-for="group in permissionGroups"
                            :key="group.group"
                            class="rounded-xl border border-gray-200 dark:border-gray-700"
                        >
                            <!-- group header -->
                            <div
                                class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700"
                            >
                                <span
                                    class="text-sm font-semibold text-gray-700 capitalize dark:text-gray-200"
                                    >{{ group.label }}</span
                                >
                                <button
                                    type="button"
                                    class="text-brand-600 dark:text-brand-400 text-xs font-medium hover:underline"
                                    @click="toggleGroup(group)"
                                >
                                    {{
                                        isGroupAllSelected(group)
                                            ? 'Deselect all'
                                            : 'Select all'
                                    }}
                                </button>
                            </div>

                            <!-- group permissions -->
                            <div
                                class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2"
                            >
                                <label
                                    v-for="perm in group.permissions"
                                    :key="perm.name"
                                    class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                                >
                                    <input
                                        v-model="form.permissions"
                                        type="checkbox"
                                        :value="perm.name"
                                        class="text-brand-500 focus:ring-brand-500/20 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                                    />
                                    <span
                                        class="text-sm text-gray-700 dark:text-gray-300"
                                        >{{ perm.label }}</span
                                    >
                                </label>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No permissions available yet.
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            Enable a module to add permissions.
                        </p>
                    </div>

                    <p
                        v-if="form.errors.permissions"
                        class="mt-3 text-sm text-red-500"
                    >
                        {{ form.errors.permissions }}
                    </p>
                </section>

                <!-- actions -->
                <div class="flex items-center justify-end gap-3">
                    <a
                        href="/roles"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-6 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import FormInput from '@/Components/ui/FormInput.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    role: Object,
    permissionGroups: Array,
});

const form = useForm({
    name: props.role.name,
    permissions: [...(props.role.permissions ?? [])], // pre-checked
});

const isGroupAllSelected = (group) =>
    group.permissions.every((p) => form.permissions.includes(p.name));

const toggleGroup = (group) => {
    const names = group.permissions.map((p) => p.name);
    if (isGroupAllSelected(group)) {
        form.permissions = form.permissions.filter((p) => !names.includes(p));
    } else {
        form.permissions = [...new Set([...form.permissions, ...names])];
    }
};

const submit = () => {
    form.put(`/roles/${props.role.id}`, {
        preserveScroll: true,
    });
};
</script>
