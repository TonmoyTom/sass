<template>
    <AdminLayout title="Module Details">
        <div class="mx-auto">
            <!-- Header -->
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.modules.index')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                d="M15 18l-6-6 6-6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </Link>
                    <div>
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ module.name }}
                            <span
                                v-if="module.is_core"
                                class="ml-1 rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                                >CORE</span
                            >
                        </h3>
                        <span
                            class="font-mono text-sm text-gray-500 dark:text-gray-400"
                            >{{ module.alias }}</span
                        >
                    </div>
                    <span
                        :class="
                            module.is_active
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                        "
                        class="rounded-full px-2.5 py-1 text-xs font-medium"
                    >
                        {{ module.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.modules.edit', module.id)"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="!module.is_core"
                        @click="destroy"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <!-- Module info + Features -->
            <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Details
                    </h5>
                    <dl class="space-y-3">
                        <Row label="Category" :value="module.module_category" />
                        <Row
                            label="Pricing Type"
                            :value="
                                module.pricing_type === 'one_time'
                                    ? 'One-time'
                                    : 'Subscription'
                            "
                        />
                        <Row
                            label="Commission"
                            :value="module.commission_rate + '%'"
                        />
                        <Row label="Version" :value="module.version" />
                    </dl>
                    <p
                        v-if="module.description"
                        class="mt-4 border-t border-gray-100 pt-4 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-400"
                    >
                        {{ module.description }}
                    </p>
                </section>

                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Features
                    </h5>
                    <ul v-if="features.length" class="space-y-2">
                        <li
                            v-for="(f, i) in features"
                            :key="i"
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <svg
                                class="h-4 w-4 flex-shrink-0 text-green-500"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M20 6L9 17l-5-5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            {{ f }}
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-400">
                        No features listed.
                    </p>
                </section>
            </div>

            <!-- Tiers -->
            <section
                class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">
                    Tiers
                </h5>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b border-gray-200 text-xs text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400"
                        >
                            <tr>
                                <th class="px-3 py-2 font-medium">Tier</th>
                                <th class="px-3 py-2 font-medium">Limits</th>
                                <th class="px-3 py-2 font-medium">Monthly</th>
                                <th class="px-3 py-2 font-medium">Yearly</th>
                                <th class="px-3 py-2 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100 dark:divide-gray-800"
                        >
                            <tr v-for="tier in module.tiers" :key="tier.id">
                                <td class="px-3 py-3">
                                    <span
                                        class="font-medium text-gray-800 dark:text-white/90"
                                        >{{ tier.name }}</span
                                    >
                                    <span
                                        v-if="tier.is_popular"
                                        class="bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 ml-1 rounded px-1.5 py-0.5 text-[10px] font-medium"
                                        >POPULAR</span
                                    >
                                </td>
                                <td class="px-3 py-3">
                                    <div
                                        v-if="
                                            Object.keys(tier.limits || {})
                                                .length
                                        "
                                        class="flex flex-wrap gap-1"
                                    >
                                        <span
                                            v-for="(val, key) in tier.limits"
                                            :key="key"
                                            class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                        >
                                            {{ key }}: {{ val }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-gray-400"
                                        >—</span
                                    >
                                </td>
                                <td
                                    class="px-3 py-3 text-gray-600 dark:text-gray-400"
                                >
                                    ৳{{ money(tier.monthly_price) }}
                                </td>
                                <td
                                    class="px-3 py-3 text-gray-600 dark:text-gray-400"
                                >
                                    ৳{{ money(tier.yearly_price) }}
                                </td>
                                <td class="px-3 py-3">
                                    <span
                                        :class="
                                            tier.is_active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                                        "
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    >
                                        {{
                                            tier.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="!module.tiers || !module.tiers.length">
                                <td
                                    colspan="5"
                                    class="px-3 py-8 text-center text-gray-400"
                                >
                                    No tiers configured.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, h } from 'vue';

const props = defineProps({
    module: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

// features: array ba newline string — dono handle
const features = computed(() => {
    const f = props.module.features;
    if (Array.isArray(f)) return f;
    if (typeof f === 'string' && f.trim())
        return f
            .split('\n')
            .map((l) => l.trim())
            .filter(Boolean);
    return [];
});

const Row = (p) =>
    h('div', { class: 'flex justify-between gap-4' }, [
        h('dt', { class: 'text-sm text-gray-500 dark:text-gray-400' }, p.label),
        h(
            'dd',
            {
                class: 'text-sm font-medium text-gray-800 dark:text-white/90 text-right capitalize',
            },
            p.value || '—',
        ),
    ]);

const destroy = () => {
    if (
        !confirm(
            `Delete module "${props.module.name}"? Tier gula-o delete hobe.`,
        )
    )
        return;
    router.delete(route('admin.modules.destroy', props.module.id));
};
</script>
