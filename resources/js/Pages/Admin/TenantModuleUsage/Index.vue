<template>
    <AdminLayout title="Tenant Module Usage">
        <div class="mx-auto">
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Tenant Module Usage
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Which tenant is using which modules and their subscription
                    status.
                </p>
            </div>

            <!-- Stats -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Active Subscriptions
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        {{ stats.total_active }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Expired
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400"
                    >
                        {{ stats.total_expired }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Unique Tenants
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ stats.unique_tenants }}
                    </p>
                </div>
            </div>

            <DataTable
                :data="usage"
                :filters="filters"
                :columns="[
                    { key: 'tenant', label: 'Tenant', sortable: true },
                    { key: 'module', label: 'Module' },
                    { key: 'billing', label: 'Billing' },
                    { key: 'price', label: 'Price', sortable: true },
                    { key: 'status', label: 'Status' },
                    { key: 'expires', label: 'Expires', sortable: true },
                    { key: 'referred_by', label: 'Referred by' },
                    { key: 'actions', label: 'Action' },
                ]"
            >
                <template #filters="{ filters: f, apply }">
                    <select
                        v-model="f.status"
                        @change="apply"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <select
                        v-model="f.access_type"
                        @change="apply"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 focus:outline-hidden dark:border-gray-700 dark:text-gray-300"
                    >
                        <option value="">All Types</option>
                        <option value="subscription">Subscription</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </template>

                <template #row="{ row: u }">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800 dark:text-white/90">
                            {{ u.tenant_name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ u.tenant_owner_email }}
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-gray-800 dark:text-white/90">
                            {{ u.module_name }}
                        </p>
                        <p
                            v-if="u.tier_name"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ u.tier_name }}
                        </p>
                    </td>
                    <td
                        class="px-4 py-3 text-sm text-gray-600 capitalize dark:text-gray-400"
                    >
                        {{
                            u.access_type === 'lifetime'
                                ? 'Lifetime'
                                : u.billing_cycle
                        }}
                    </td>
                    <td
                        class="px-4 py-3 font-medium text-gray-800 dark:text-white/90"
                    >
                        ৳{{ money(u.price_paid) }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                            :class="
                                u.is_expired
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                    : u.status === 'active'
                                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                      : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'
                            "
                        >
                            {{ u.is_expired ? 'Expired' : u.status }}
                        </span>
                    </td>
                    <td
                        class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                    >
                        {{ u.expires_at ?? '— (lifetime)' }}
                    </td>

                    <td
                        class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                    >
                        <p class="font-medium text-gray-800 dark:text-white/90">
                            {{ u.referred_by }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ u.referred_by_email }}
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        <button
                            v-if="
                                u.access_type !== 'lifetime' &&
                                (u.is_expired || u.is_expiring_soon)
                            "
                            @click="openFreeRenew(u)"
                            class="rounded-lg border border-green-300 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-900/20"
                        >
                            Free Renew
                        </button>
                        <button
                            v-if="
                                u.access_type !== 'lifetime' &&
                                (u.is_expired || u.is_expiring_soon)
                            "
                            @click="paidRenew(u)"
                            class="rounded-lg border border-blue-300 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50 dark:border-blue-700 dark:text-blue-400 dark:hover:bg-blue-900/20"
                        >
                            Renew
                        </button>
                    </td>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
    <!-- page-er shesh e modal -->
    <div
        v-if="renewing"
        class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
        @click.self="renewing = null"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Free Renew Subscription
            </h4>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ renewing.tenant_name }} — {{ renewing.module_name }}
            </p>
            <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                This will extend the subscription by one billing cycle without
                charging the tenant.
            </p>

            <textarea
                v-model="renewForm.note"
                rows="2"
                placeholder="Reason (optional, e.g. goodwill gesture, compensation)"
                class="mt-3 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:text-white/90"
            ></textarea>

            <div class="mt-5 flex justify-end gap-3">
                <button
                    @click="renewing = null"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                >
                    Cancel
                </button>
                <button
                    @click="confirmFreeRenew"
                    :disabled="renewForm.processing"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
                >
                    {{
                        renewForm.processing
                            ? 'Renewing...'
                            : 'Confirm Free Renew'
                    }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import DataTable from '@/Components/ui/DataTable.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    usage: Object,
    stats: Object,
    filters: Object,
});

const renewing = ref(null);
const renewForm = useForm({ note: '' });

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const openFreeRenew = (u) => {
    renewing.value = u;
    renewForm.note = '';
};

const confirmFreeRenew = () => {
    renewForm.post(`/admin/tenant-modules/${renewing.value.id}/free-renew`, {
        preserveScroll: true,
        onSuccess: () => (renewing.value = null),
    });
};

const paidRenew = (u) => {
    if (
        !confirm(
            `Renew "${u.module_name}" for "${u.tenant_name}"? The tenant will be charged the regular price.`,
        )
    ) {
        return;
    }

    router.post(
        `/admin/tenant-modules/${u.id}/renew`,
        {},
        { preserveScroll: true },
    );
};
</script>
