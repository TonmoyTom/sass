<template>
    <AdminLayout title="Roles">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div
                class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Roles & Permissions
                </h3>

                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between lg:justify-end"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search role..."
                        class="w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        @keyup.enter="applySearch"
                    />

                    <Link
                        :href="route('admin.roles.create')"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                    >
                        <span class="text-base leading-none">+</span>
                        New Role
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-200 text-xs text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">Guard</th>
                            <th class="px-4 py-3 font-medium">Permissions</th>
                            <th class="px-4 py-3 font-medium">Users</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr
                            v-for="role in roles.data"
                            :key="role.id"
                            class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        >
                            <td class="px-4 py-3">
                                <span
                                    class="font-medium text-gray-800 capitalize dark:text-white/90"
                                >
                                    {{ role.name }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ role.guard_name }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 rounded-full px-2.5 py-1 text-xs font-medium"
                                >
                                    {{ role.permissions_count }} permissions
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ role.users_count }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="
                                            route('admin.roles.edit', role.id)
                                        "
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="destroyRole(role)"
                                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!roles.data.length">
                            <td
                                colspan="5"
                                class="px-4 py-10 text-center text-gray-500 dark:text-gray-400"
                            >
                                No roles found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="roles.links.length > 3"
                class="mt-5 flex flex-wrap gap-1"
            >
                <template v-for="(link, i) in roles.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="rounded-lg px-3 py-1.5 text-sm"
                        :class="
                            link.active
                                ? 'bg-brand-500 text-white'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'
                        "
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="cursor-default rounded-lg px-3 py-1.5 text-sm text-gray-400 opacity-50"
                    />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    roles: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');

const applySearch = () => {
    router.get(
        route('admin.roles.index'),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const destroyRole = (role) => {
    if (!confirm(`"${role.name}" role delete korte chao? Eta undo kora jabe na.`)) return;
    router.delete(route('admin.roles.destroy', role.id), {
        preserveScroll: true,
    });
};
</script>
