<script setup>
import SeoHead from '@/Components/seo/SeoHead.vue';

defineProps({
    module: Object,
    seo: Object,
});
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="mx-auto max-w-4xl px-4 py-10">
        <h1 class="text-2xl font-bold">{{ module.name }}</h1>
        <p class="mt-2 text-gray-600">{{ module.description }}</p>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div
                v-for="tier in module.tiers"
                :key="tier.id"
                class="rounded-xl border border-gray-200 p-5"
            >
                <h3 class="font-semibold">{{ tier.name }}</h3>
                <p class="mt-2 text-xl font-bold">
                    ৳{{ tier.monthly_price
                    }}<span class="text-sm font-normal">/mo</span>
                </p>
            </div>
        </div>
    </div>
</template>
