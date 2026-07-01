<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, FileText } from 'lucide-vue-next';

defineProps({
    settings: Array,
});

const confirmDelete = (setting) => {
    if (confirm(`"${setting.page_name}" delete korте চান? Eta সাথে SEO data-o remove হবে।`)) {
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
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                        Site Settings
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage SEO meta data for static pages (Home, Pricing, About, etc.)
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

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div v-if="settings.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 text-left dark:border-gray-700 dark:bg-white/[0.02]">
                                <th class="p-4 font-medium text-gray-600 dark:text-gray-400">Page</th>
                                <th class="p-4 font-medium text-gray-600 dark:text-gray-400">URL</th>
                                <th class="p-4 text-right font-medium text-gray-600 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="setting in settings"
                                :key="setting.id"
                                class="border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.02]"
                            >
                                <td class="p-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="bg-brand-50 dark:bg-brand-500/10 flex h-8 w-8 items-center justify-center rounded-lg">
                                            <FileText class="text-brand-500 h-4 w-4" />
                                        </div>
                                        <span class="font-medium text-gray-800 dark:text-white/90">
                                            {{ setting.page_name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <code class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        {{ setting.page_url }}
                                    </code>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-end gap-4">
                                        <Link
                                            :href="route('admin.site-settings.edit', setting.id)"
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
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <FileText class="h-6 w-6 text-gray-400" />
                    </div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Kono page ekhono add kora hoy nai</p>
                    <p class="mt-1 text-sm text-gray-400">"Add New Page" click kore শুরু koro</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
