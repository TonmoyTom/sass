<template>
    <AdminLayout title="Staff">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div
                class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Staff
                </h3>

                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between lg:justify-end"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search staff..."
                        class="w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        @keyup.enter="applySearch"
                    />

                    <Link
                        :href="route('admin.staff.create')"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                    >
                        <span class="text-base leading-none">+</span>
                        New Staff
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-200 text-xs text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr
                            v-for="user in staff.data"
                            :key="user.id"
                            class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        >
                            <td
                                class="px-4 py-3 font-medium text-gray-800 dark:text-white/90"
                            >
                                {{ user.name }}
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ user.email }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="statusClass(user.status)"
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                >
                                    {{ user.status }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ user.created_at }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="
                                            route('admin.staff.edit', user.id)
                                        "
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="destroy(user)"
                                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!staff.data.length">
                            <td
                                colspan="5"
                                class="px-4 py-10 text-center text-gray-500 dark:text-gray-400"
                            >
                                No staff found. Create your first one.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="staff.links.length > 3"
                class="mt-5 flex flex-wrap gap-1"
            >
                <template v-for="(link, i) in staff.links" :key="i">
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
    staff: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');

const applySearch = () => {
    router.get(
        route('admin.staff.index'),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const statusClass = (status) =>
    ({
        active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        inactive: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
    })[status] ?? 'bg-gray-100 text-gray-700';

const destroy = (user) => {
    if (!confirm(`"${user.name}" staff account delete korte chao?`)) return;
    router.delete(route('admin.staff.destroy', user.id), {
        preserveScroll: true,
    });
};
</script>
