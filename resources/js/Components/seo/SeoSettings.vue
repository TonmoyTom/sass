<template>
    <div>
        <SeoSettingsFields :form="form" />

        <div class="mt-5 flex items-center justify-end gap-3">
            <span v-if="form.recentlySuccessful" class="text-sm text-green-600">Saved.</span>
            <button
                type="button"
                @click="$emit('submit')"
                :disabled="form.processing"
                class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
            >
                {{ form.processing ? 'Saving...' : 'Save SEO' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import SeoSettingsFields from './SeoSettingsFields.vue';

defineProps({ form: Object });
defineEmits(['submit']);
</script>
