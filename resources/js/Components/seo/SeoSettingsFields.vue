<template>
    <section
        class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <h5 class="mb-5 font-semibold text-gray-800 dark:text-white/90">
            SEO Settings
        </h5>

        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >Meta Title</label
                >
                <input
                    v-model="form.meta_title"
                    maxlength="60"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                />
                <p class="mt-1 text-xs text-gray-400">
                    {{ (form.meta_title || '').length }}/60
                </p>
                <p
                    v-if="form.errors.meta_title"
                    class="mt-1 text-xs text-red-500"
                >
                    {{ form.errors.meta_title }}
                </p>
            </div>

            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >Meta Keywords</label
                >
                <input
                    v-model="form.meta_keywords"
                    placeholder="keyword1, keyword2"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                />
            </div>

            <div class="lg:col-span-2">
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >Meta Description</label
                >
                <textarea
                    v-model="form.meta_description"
                    maxlength="160"
                    rows="3"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                ></textarea>
                <p class="mt-1 text-xs text-gray-400">
                    {{ (form.meta_description || '').length }}/160
                </p>
            </div>

            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >Focus Keyword</label
                >
                <input
                    v-model="form.focus_keyword"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                />
            </div>

            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >Canonical URL</label
                >
                <input
                    v-model="form.canonical_url"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                />
            </div>

            <FormSelect
                v-model="form.robots"
                label="Robots"
                :options="robotsOptions"
            />

            <div class="lg:col-span-2">
                <hr class="my-2 border-gray-200 dark:border-gray-700" />
                <h6
                    class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300"
                >
                    Open Graph
                </h6>
            </div>

            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >OG Title</label
                >
                <input
                    v-model="form.og_title"
                    maxlength="60"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                />
            </div>

            <!-- OG Image with preview -->
            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >OG Image</label
                >

                <div v-if="ogImagePreview" class="mb-3">
                    <img
                        :src="ogImagePreview"
                        class="h-24 w-40 rounded-lg border border-gray-200 object-cover dark:border-gray-700"
                    />
                </div>

                <input
                    type="file"
                    accept="image/*"
                    @change="handleOgImageChange"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                />

                <button
                    v-if="ogImagePreview"
                    type="button"
                    @click="removeOgImage"
                    class="mt-1.5 text-xs font-medium text-red-500 hover:underline"
                >
                    Remove image
                </button>

                <p
                    v-if="form.errors.og_image"
                    class="mt-1 text-xs text-red-500"
                >
                    {{ form.errors.og_image }}
                </p>
            </div>

            <div class="lg:col-span-2">
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >OG Description</label
                >
                <textarea
                    v-model="form.og_description"
                    maxlength="160"
                    rows="2"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                ></textarea>
            </div>

            <div class="lg:col-span-2">
                <hr class="my-2 border-gray-200 dark:border-gray-700" />
                <h6
                    class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300"
                >
                    Twitter Card
                </h6>
            </div>

            <FormSelect
                v-model="form.twitter_card"
                label="Card Type"
                :options="twitterCardOptions"
            />

            <div class="lg:col-span-2">
                <hr class="my-2 border-gray-200 dark:border-gray-700" />
                <h6
                    class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300"
                >
                    Sitemap
                </h6>
            </div>

            <div class="flex items-center gap-2">
                <input
                    v-model="form.sitemap_include"
                    type="checkbox"
                    class="text-brand-500 h-5 w-5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                />
                <label
                    class="text-sm font-medium text-gray-700 dark:text-gray-400"
                    >Include in sitemap</label
                >
            </div>

            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    >Priority (0.0 - 1.0)</label
                >
                <input
                    v-model="form.sitemap_priority"
                    type="number"
                    step="0.1"
                    min="0"
                    max="1"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                />
            </div>

            <FormSelect
                v-model="form.sitemap_frequency"
                label="Change Frequency"
                :options="sitemapFrequencyOptions"
            />
        </div>
    </section>
</template>

<script setup>
import FormSelect from '@/Components/ui/FormSelect.vue';
import { ref } from 'vue';

const props = defineProps({
    form: Object,
});

// Preview URL — existing (string, DB theke asa URL) ba notun uploaded file (File object) duitar jonno
const ogImagePreview = ref(
    typeof props.form.og_image === 'string' ? props.form.og_image : null,
);

function handleOgImageChange(e) {
    const file = e.target.files[0];
    if (!file) return;

    props.form.og_image = file;
    ogImagePreview.value = URL.createObjectURL(file);
}

function removeOgImage() {
    props.form.og_image = null;
    ogImagePreview.value = null;
}

const robotsOptions = [
    { value: 'index,follow', label: 'Index, Follow' },
    { value: 'noindex,follow', label: 'No Index, Follow' },
    { value: 'index,nofollow', label: 'Index, No Follow' },
    { value: 'noindex,nofollow', label: 'No Index, No Follow' },
];

const twitterCardOptions = [
    { value: 'summary', label: 'Summary' },
    { value: 'summary_large_image', label: 'Summary Large Image' },
];

const sitemapFrequencyOptions = [
    { value: 'always', label: 'Always' },
    { value: 'hourly', label: 'Hourly' },
    { value: 'daily', label: 'Daily' },
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Yearly' },
    { value: 'never', label: 'Never' },
];
</script>
