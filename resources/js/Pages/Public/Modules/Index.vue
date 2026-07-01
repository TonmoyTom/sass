<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    modules: Array,
    seo: Object,
});
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.canonical" />
    </Head>

    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="mb-6 text-2xl font-bold">Modules & Add-ons</h1>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="module in modules"
                :key="module.id"
                :href="route('modules.show', module.alias)"
                class="rounded-xl border border-gray-200 p-5 hover:shadow-md"
            >
                <h3 class="font-semibold">{{ module.name }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ module.description }}
                </p>
                <p
                    v-if="module.starting_price"
                    class="mt-3 text-sm font-medium"
                >
                    From ৳{{ module.starting_price }}/mo
                </p>
            </Link>
        </div>
    </div>
</template>
