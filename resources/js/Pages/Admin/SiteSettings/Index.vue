<script setup>
import DataTable from '@/Components/ui/DataTable.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { FileText, Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineProps({
    settings: Object,
    filters: { type: Object, default: () => ({}) },
});

const confirmDelete = (setting) => {
    if (
        confirm(
            `"${setting.page_name}" delete korте চান? Eta সাথে SEO data-o remove হবে।`,
        )
    ) {
        router.delete(route('admin.site-settings.destroy', setting.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout title="Site Settings">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Site Settings
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage SEO meta data for static pages (Home, Pricing,
                        About, etc.)
                    </p>
                </div>
                <Link
                    :href="route('admin.site-settings.create')"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition"
                >
                    <Plus class="h-4 w-4" />
                    Add New Page
                </Link>
            </div>

            <DataTable
                :data="settings"
                :filters="filters"
                route-name="admin.site-settings.index"
                :columns="[
                    { key: 'page', label: 'Page', sortable: true },
                    { key: 'url', label: 'URL' },
                    { key: 'actions', label: '' },
                ]"
            >
                <template #row="{ row: setting }">
                    <td class="p-4">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="bg-brand-50 dark:bg-brand-500/10 flex h-8 w-8 items-center justify-center rounded-lg"
                            >
                                <FileText class="text-brand-500 h-4 w-4" />
                            </div>
                            <span
                                class="font-medium text-gray-800 dark:text-white/90"
                            >
                                {{ setting.page_name }}
                            </span>
                        </div>
                    </td>
                    <td class="p-4">
                        <code
                            class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                        >
                            {{ setting.page_url }}
                        </code>
                    </td>
                    <td class="p-4">
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="
                                    route(
                                        'admin.site-settings.edit',
                                        setting.id,
                                    )
                                "
                                class="text-brand-600 hover:text-brand-700 inline-flex items-center gap-1.5 text-sm font-medium"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                                Edit SEO
                            </Link>
                            <button
                                @click="confirmDelete(setting)"
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-red-500 hover:text-red-600"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                                Delete
                            </button>
                        </div>
                    </td>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
