<script setup>
import SeoSettings from '@/Components/seo/SeoSettings.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { useSeoForm } from '@/composables/useSeoForm';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    setting: Object,
    seo: Object,
});

const { form: seoForm, submitUrlSeo } = useSeoForm(props.seo);
</script>

<template>
    <WorkspaceLayout :title="`Edit SEO — ${setting.page_name}`">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Edit SEO — {{ setting.page_name }}
                </h3>
                <Link
                    :href="route('admin.site-settings.index')"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400"
                >
                    ← Back
                </Link>
            </div>

            <SeoSettings
                :form="seoForm"
                @submit="() => submitUrlSeo(`/settings/seo/${setting.id}`)"
            />
        </div>
    </WorkspaceLayout>
</template>
