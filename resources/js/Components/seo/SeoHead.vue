<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    seo: {
        type: Object,
        default: () => ({}),
    },
});

const schemaJson = computed(() => {
    if (!props.seo?.schema_type) return null;

    return JSON.stringify({
        '@context': 'https://schema.org',
        '@type': props.seo.schema_type,
        name: props.seo.title,
        description: props.seo.description,
        ...(props.seo.schema_data || {}),
    });
});
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <meta
            v-if="seo.meta_keywords"
            name="keywords"
            :content="seo.meta_keywords"
        />
        <meta name="robots" :content="seo.robots || 'index,follow'" />
        <link rel="canonical" :href="seo.canonical" />

        <!-- Open Graph -->
        <meta property="og:title" :content="seo.og_title || seo.title" />
        <meta
            property="og:description"
            :content="seo.og_description || seo.description"
        />
        <meta v-if="seo.og_image" property="og:image" :content="seo.og_image" />
        <meta property="og:type" :content="seo.og_type || 'website'" />
        <meta property="og:url" :content="seo.canonical" />

        <!-- Twitter Card -->
        <meta
            name="twitter:card"
            :content="seo.twitter_card || 'summary_large_image'"
        />
        <meta name="twitter:title" :content="seo.og_title || seo.title" />
        <meta
            name="twitter:description"
            :content="seo.og_description || seo.description"
        />
        <meta
            v-if="seo.og_image"
            name="twitter:image"
            :content="seo.og_image"
        />

        <!-- JSON-LD Schema -->
        <component
            v-if="schemaJson"
            :is="'script'"
            type="application/ld+json"
            v-html="schemaJson"
        />
    </Head>
</template>
