<template>
    <AdminLayout title="Add New Page">
        <div class="mx-auto ">
            <div class="mb-6 flex items-center justify-between">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Add New Page
                </h3>
                <Link
                    :href="route('admin.site-settings.index')"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400"
                >
                    ← Back
                </Link>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Page Info
                    </h5>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5">
                        <FormInput
                            v-model="form.page_name"
                            label="Page Name"
                            placeholder="e.g. Terms & Conditions"
                            :error="form.errors.page_name"
                        />
                        <FormInput
                            v-model="form.page_key"
                            label="Page Key (auto if empty)"
                            placeholder="e.g. terms"
                            :error="form.errors.page_key"
                        />
                        <FormInput
                            v-model="form.page_url"
                            label="Page URL"
                            placeholder="e.g. /terms"
                            :error="form.errors.page_url"
                        />
                    </div>
                </section>

                <!-- SEO Settings — reusable component, same form object -->
                <SeoSettingsFields :form="form" />

                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="route('admin.site-settings.index')"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Create Page' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>

import SeoSettingsFields from '@/Components/seo/SeoSettingsFields.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    // Page info
    page_name: '',
    page_key: '',
    page_url: '',

    // SEO fields (flat, SeoSettingsFields component eigula bind korবে)
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    canonical_url: '',
    robots: 'index,follow',
    og_title: '',
    og_description: '',
    og_image: null,
    og_type: 'website',
    twitter_title: '',
    twitter_description: '',
    twitter_card: 'summary_large_image',
    schema_type: '',
    focus_keyword: '',
    sitemap_include: true,
    sitemap_priority: 0.5,
    sitemap_frequency: 'weekly',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        og_image: data.og_image instanceof File ? data.og_image : undefined,
    })).post(route('admin.site-settings.store'), {
        forceFormData: true,
    });
};
</script>
