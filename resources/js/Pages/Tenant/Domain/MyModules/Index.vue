<template>
    <WorkspaceLayout title="My Modules">
        <div class="mx-auto">
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        My Modules
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Modules you have purchased and are currently using.
                    </p>
                </div>
                <Link
                    href="/my-modules/history"
                    class="text-brand-500 hover:text-brand-600 text-sm font-medium hover:underline"
                >
                    Purchase History →
                </Link>
            </div>

            <!-- Stats -->
            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Modules
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ modules.length }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Active
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        {{ stats.active }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Expiring Soon
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400"
                    >
                        {{ stats.expiringSoon }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Expired
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400"
                    >
                        {{ stats.expired }}
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-5 flex flex-wrap items-center gap-3">
                <div class="relative min-w-[220px] flex-1 sm:flex-none">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search modules..."
                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:text-white/90"
                    />
                </div>

                <select
                    v-model="statusFilter"
                    class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="expiring_soon">Expiring Soon</option>
                    <option value="expired">Expired</option>
                </select>

                <button
                    v-if="search || statusFilter"
                    @click="resetFilters"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"
                >
                    Clear
                </button>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="m in filteredModules"
                    :key="m.id"
                    class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-3 flex items-start justify-between">
                        <h4
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ m.module_name }}
                        </h4>
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                            :class="statusClass(m)"
                        >
                            {{ m.is_expired ? 'Expired' : m.status }}
                        </span>
                    </div>

                    <p
                        v-if="m.tier_name"
                        class="mb-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        Tier: {{ m.tier_name }}
                    </p>

                    <div
                        class="mb-4 space-y-1 text-sm text-gray-500 dark:text-gray-400"
                    >
                        <p>Purchased: {{ m.purchased_at }}</p>
                        <p v-if="m.expires_at">
                            {{
                                m.access_type === 'lifetime'
                                    ? 'Lifetime access'
                                    : `Expires: ${m.expires_at}`
                            }}
                        </p>
                        <p class="font-medium text-gray-800 dark:text-white/90">
                            ৳{{ money(m.price_paid) }} / {{ m.billing_cycle }}
                        </p>
                    </div>

                    <div
                        v-if="m.is_expiring_soon && !m.is_expired"
                        class="mt-auto space-y-2"
                    >
                        <div
                            class="rounded-lg bg-yellow-50 p-3 text-xs text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400"
                        >
                            ⚠ Expiring soon — renewal upcoming
                        </div>
                        <button
                            @click="renewModule(m)"
                            :disabled="renewingId === m.id"
                            class="bg-brand-500 hover:bg-brand-600 w-full rounded-lg py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {{
                                renewingId === m.id
                                    ? 'Processing...'
                                    : 'Renew Now'
                            }}
                        </button>
                    </div>
                    <div v-else-if="m.is_expired" class="mt-auto space-y-2">
                        <div
                            class="rounded-lg bg-red-50 p-3 text-xs text-red-700 dark:bg-red-900/20 dark:text-red-400"
                        >
                            This module has expired.
                        </div>
                        <button
                            @click="renewModule(m)"
                            :disabled="renewingId === m.id"
                            class="bg-brand-500 hover:bg-brand-600 w-full rounded-lg py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {{
                                renewingId === m.id
                                    ? 'Processing...'
                                    : 'Renew Now'
                            }}
                        </button>
                    </div>
                </div>

                <div
                    v-if="!filteredModules.length"
                    class="col-span-full py-16 text-center text-gray-500 dark:text-gray-400"
                >
                    No modules found.
                </div>
            </div>

            <div v-if="availableModules.length" class="mt-10">
                <h4
                    class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Available Modules
                </h4>
                <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
                    Modules you haven't purchased yet.
                </p>

                <div
                    class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="m in availableModules"
                        :key="m.id"
                        class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                    >
                        <div class="mb-3 flex items-start justify-between">
                            <h5
                                class="font-semibold text-gray-800 dark:text-white/90"
                            >
                                {{ m.name }}
                            </h5>
                            <span
                                class="text-xs text-gray-500 capitalize dark:text-gray-400"
                                >{{ m.category }}</span
                            >
                        </div>

                        <p
                            class="mb-4 flex-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ m.description || 'No description.' }}
                        </p>

                        <div
                            class="mb-4 text-sm text-gray-500 dark:text-gray-400"
                        >
                            <span
                                class="font-medium text-gray-800 dark:text-white/90"
                            >
                                ৳{{ money(m.starting_price) }}
                            </span>
                            {{
                                m.pricing_type === 'one_time'
                                    ? ''
                                    : `theke shuru · ${m.tiers_count} tier`
                            }}
                        </div>
                        <a
                            :href="centralModuleUrl(m.alias)"
                            class="bg-brand-500 hover:bg-brand-600 w-full rounded-lg py-2.5 text-center text-sm font-medium text-white"
                        >
                            View & Purchase
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { Link, router , usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    modules: { type: Array, default: () => [] },
    availableModules: { type: Array, default: () => [] },
});

const search = ref('');
const statusFilter = ref('');
const renewingId = ref(null);

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const statusClass = (m) => {
    if (m.is_expired)
        return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    if (m.status === 'active')
        return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
    return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300';
};

const stats = computed(() => ({
    active: props.modules.filter((m) => m.status === 'active' && !m.is_expired)
        .length,
    expiringSoon: props.modules.filter(
        (m) => m.is_expiring_soon && !m.is_expired,
    ).length,
    expired: props.modules.filter((m) => m.is_expired).length,
}));

const filteredModules = computed(() => {
    return props.modules.filter((m) => {
        const matchesSearch =
            !search.value ||
            m.module_name?.toLowerCase().includes(search.value.toLowerCase());

        const matchesStatus =
            !statusFilter.value ||
            (statusFilter.value === 'expired' && m.is_expired) ||
            (statusFilter.value === 'expiring_soon' &&
                m.is_expiring_soon &&
                !m.is_expired) ||
            (statusFilter.value === 'active' &&
                m.status === 'active' &&
                !m.is_expired);

        return matchesSearch && matchesStatus;
    });
});

const resetFilters = () => {
    search.value = '';
    statusFilter.value = '';
};

const renewModule = (m) => {
    renewingId.value = m.id;
    router.post(
        `/my-modules/${m.id}/renew`,
        {},
        {
            preserveScroll: true,
            onFinish: () => (renewingId.value = null),
        },
    );
};

const centralModuleUrl = (alias) => {
    const centralDomain = usePage().props.centralDomain ?? 'myapp.test';
    return `${window.location.protocol}//${centralDomain}/modules/${alias}`;
};
</script>
