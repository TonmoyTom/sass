<template>
    <AdminLayout title="Modules">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-5 flex items-center justify-between">
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Modules
                </h3>
                <Link
                    :href="route('admin.modules.create')"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                >
                    + Create Module
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-200 text-xs text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Module</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Pricing</th>
                            <th class="px-4 py-3 font-medium">Tiers</th>
                            <th class="px-4 py-3 font-medium">Starting</th>
                            <th class="px-4 py-3 font-medium">Commission</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr
                            v-for="m in modules"
                            :key="m.id"
                            class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        >
                            <td class="px-4 py-3">
                                <div
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ m.name }}
                                    <span
                                        v-if="m.is_core"
                                        class="ml-1 rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                                        >CORE</span
                                    >
                                </div>
                                <span
                                    class="font-mono text-xs text-gray-500 dark:text-gray-400"
                                    >{{ m.alias }}</span
                                >
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 capitalize dark:text-gray-400"
                            >
                                {{ m.category }}
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{
                                    m.pricing_type === 'one_time'
                                        ? 'One-time'
                                        : 'Subscription'
                                }}
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ m.tiers_count }}
                            </td>
                            <td
                                class="px-4 py-3 font-medium text-gray-800 dark:text-white/90"
                            >
                                {{
                                    m.starting_price != null
                                        ? '৳' + money(m.starting_price)
                                        : '—'
                                }}
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ m.commission_rate }}%
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="
                                        m.is_active
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                                    "
                                    class="rounded-full px-2.5 py-1 text-xs font-medium"
                                >
                                    {{ m.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="
                                            route('admin.modules.edit', m.id)
                                        "
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        :href="
                                            route('admin.modules.show', m.id)
                                        "
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="
                                            route('admin.modules.edit', m.id)
                                        "
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="destroy(m)"
                                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!modules.length">
                            <td
                                colspan="8"
                                class="px-4 py-10 text-center text-gray-500 dark:text-gray-400"
                            >
                                No modules yet. Create your first one.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    modules: Array,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const destroy = (m) => {
    if (
        !confirm(
            `Delete module "${m.name}"? Tier gula-o delete hobe. Undo kora jabe na.`,
        )
    )
        return;
    router.delete(route('admin.modules.destroy', m.id), {
        preserveScroll: true,
    });
};
</script>
