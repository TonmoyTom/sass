<template>
    <WorkspaceLayout title="Roles">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Roles
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage roles and their permissions.
                    </p>
                </div>
                <a
                    href="/roles/create"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                    </svg>
                    New Role
                </a>
            </div>

            <!-- status -->
            <div
                v-if="$page.props.flash?.status"
                class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ $page.props.flash.status }}
            </div>

            <!-- table -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <table class="w-full">
                    <thead>
                        <tr
                            class="border-b border-gray-200 text-left text-xs tracking-wide text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400"
                        >
                            <th class="px-5 py-3.5 font-medium">Role</th>
                            <th class="px-5 py-3.5 font-medium">Permissions</th>
                            <th class="px-5 py-3.5 font-medium">Users</th>
                            <th class="px-5 py-3.5 font-medium">Created</th>
                            <th class="px-5 py-3.5 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="role in roles"
                            :key="role.id"
                            class="border-b border-gray-100 last:border-0 dark:border-gray-800"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 flex h-9 w-9 items-center justify-center rounded-lg"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"
                                            />
                                        </svg>
                                    </div>
                                    <span
                                        class="font-medium text-gray-800 capitalize dark:text-white/90"
                                        >{{ role.name }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    {{ role.permissions_count }} permissions
                                </span>
                            </td>
                            <td
                                class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300"
                            >
                                {{ role.users_count }}
                            </td>
                            <td
                                class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ role.created_at }}
                            </td>
                            <td class="px-5 py-4">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <a
                                        :href="`/roles/${role.id}/edit`"
                                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800"
                                        title="Edit"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"
                                            />
                                            <path
                                                d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                            />
                                        </svg>
                                    </a>
                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                        title="Delete"
                                        @click="confirmDelete(role)"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!roles.length">
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    No roles yet.
                                </p>
                                <a
                                    href="/roles/create"
                                    class="bg-brand-500 hover:bg-brand-600 mt-3 inline-block rounded-lg px-5 py-2.5 text-sm font-medium text-white"
                                >
                                    Create First Role
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- delete modal -->
        <div
            v-if="deleting"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
            @click.self="deleting = null"
        >
            <div
                class="w-full max-w-sm rounded-2xl bg-white p-6 dark:bg-gray-900"
            >
                <h4
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Delete Role
                </h4>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Delete
                    <span
                        class="font-medium text-gray-800 capitalize dark:text-white/90"
                        >{{ deleting.name }}</span
                    >? This can't be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                        @click="deleting = null"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600"
                        @click="performDelete"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    roles: Array,
});

const deleting = ref(null);

const confirmDelete = (role) => {
    deleting.value = role;
};

const performDelete = () => {
    router.delete(`/roles/${deleting.value.id}`, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
};
</script>
