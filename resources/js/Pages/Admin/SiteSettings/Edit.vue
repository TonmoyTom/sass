<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useSeoForm } from '@/composables/useSeoForm'
import { Link } from '@inertiajs/vue3'
import SeoSettings from '@/Components/seo/SeoSettings.vue'

const props = defineProps({
    setting: Object,
    seo: Object,
})

const { form: seoForm, submitSeo } = useSeoForm(props.seo)
</script>

<template>
    <AdminLayout :title="`Edit SEO — ${setting.page_name}`">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
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
                @submit="() => submitSeo('admin.site-settings.seo.update', setting.id)"
            />
        </div>
    </AdminLayout>
</template>
