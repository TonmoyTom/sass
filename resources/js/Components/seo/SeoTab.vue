<!-- resources/js/Components/Seo/SeoTab.vue -->
<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  modelValue: Record<string, any>
}>()

const emit = defineEmits(['update:modelValue'])

function update(field: string, value: any) {
  emit('update:modelValue', { ...props.modelValue, [field]: value })
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <label class="block text-sm font-medium mb-1">Meta Title</label>
      <input
        type="text"
        :value="modelValue.meta_title"
        @input="update('meta_title', ($event.target as HTMLInputElement).value)"
        maxlength="60"
        class="w-full border rounded px-3 py-2"
        placeholder="SEO title (max 60 chars)"
      />
      <p class="text-xs text-gray-500">{{ (modelValue.meta_title || '').length }}/60</p>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Meta Description</label>
      <textarea
        :value="modelValue.meta_description"
        @input="update('meta_description', ($event.target as HTMLTextAreaElement).value)"
        maxlength="160"
        rows="3"
        class="w-full border rounded px-3 py-2"
        placeholder="SEO description (max 160 chars)"
      ></textarea>
      <p class="text-xs text-gray-500">{{ (modelValue.meta_description || '').length }}/160</p>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Focus Keyword</label>
      <input
        type="text"
        :value="modelValue.focus_keyword"
        @input="update('focus_keyword', ($event.target as HTMLInputElement).value)"
        class="w-full border rounded px-3 py-2"
      />
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Canonical URL</label>
      <input
        type="text"
        :value="modelValue.canonical_url"
        @input="update('canonical_url', ($event.target as HTMLInputElement).value)"
        class="w-full border rounded px-3 py-2"
      />
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Robots</label>
      <select
        :value="modelValue.robots"
        @change="update('robots', ($event.target as HTMLSelectElement).value)"
        class="w-full border rounded px-3 py-2"
      >
        <option value="index,follow">Index, Follow</option>
        <option value="noindex,follow">No Index, Follow</option>
        <option value="index,nofollow">Index, No Follow</option>
        <option value="noindex,nofollow">No Index, No Follow</option>
      </select>
    </div>

    <hr />

    <h3 class="font-semibold">Open Graph (Facebook/LinkedIn)</h3>

    <div>
      <label class="block text-sm font-medium mb-1">OG Title</label>
      <input
        type="text"
        :value="modelValue.og_title"
        @input="update('og_title', ($event.target as HTMLInputElement).value)"
        maxlength="60"
        class="w-full border rounded px-3 py-2"
      />
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">OG Description</label>
      <textarea
        :value="modelValue.og_description"
        @input="update('og_description', ($event.target as HTMLTextAreaElement).value)"
        maxlength="160"
        rows="2"
        class="w-full border rounded px-3 py-2"
      ></textarea>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">OG Image</label>
      <input
        type="file"
        accept="image/*"
        @change="update('og_image', ($event.target as HTMLInputElement).files?.[0])"
        class="w-full border rounded px-3 py-2"
      />
      <img v-if="modelValue.og_image && typeof modelValue.og_image === 'string'" :src="modelValue.og_image" class="mt-2 h-24 rounded" />
    </div>

    <hr />

    <h3 class="font-semibold">Twitter Card</h3>

    <div>
      <label class="block text-sm font-medium mb-1">Twitter Card Type</label>
      <select
        :value="modelValue.twitter_card"
        @change="update('twitter_card', ($event.target as HTMLSelectElement).value)"
        class="w-full border rounded px-3 py-2"
      >
        <option value="summary">Summary</option>
        <option value="summary_large_image">Summary Large Image</option>
      </select>
    </div>

    <hr />

    <h3 class="font-semibold">Sitemap</h3>

    <div class="flex items-center gap-2">
      <input
        type="checkbox"
        :checked="modelValue.sitemap_include"
        @change="update('sitemap_include', ($event.target as HTMLInputElement).checked)"
      />
      <label>Include in sitemap</label>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Priority (0.0 - 1.0)</label>
      <input
        type="number"
        step="0.1"
        min="0"
        max="1"
        :value="modelValue.sitemap_priority"
        @input="update('sitemap_priority', ($event.target as HTMLInputElement).value)"
        class="w-full border rounded px-3 py-2"
      />
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Change Frequency</label>
      <select
        :value="modelValue.sitemap_frequency"
        @change="update('sitemap_frequency', ($event.target as HTMLSelectElement).value)"
        class="w-full border rounded px-3 py-2"
      >
        <option value="always">Always</option>
        <option value="hourly">Hourly</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        <option value="yearly">Yearly</option>
        <option value="never">Never</option>
      </select>
    </div>
  </div>
</template>
