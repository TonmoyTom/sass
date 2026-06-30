<template>
    <AdminLayout title="Sellers">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-5 flex items-center justify-between">
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Sellers
                </h3>
                <Link
                    :href="route('admin.sellers.create')"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white"
                >
                    + Create Seller
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-200 text-xs text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Seller</th>
                            <th class="px-4 py-3 font-medium">Referral Code</th>
                            <th class="px-4 py-3 font-medium">Commission</th>
                            <th class="px-4 py-3 font-medium">Sales</th>
                            <th class="px-4 py-3 font-medium">Balance</th>
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
                            v-for="seller in sellers.data"
                            :key="seller.id"
                            class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        >
                            <td class="px-4 py-3">
                                <div
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ seller.name }}
                                </div>
                                <div
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ seller.email }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    {{ seller.referral_code }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ seller.commission_rate }}%
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {{ seller.total_sales }}
                            </td>
                            <td
                                class="px-4 py-3 font-medium text-gray-800 dark:text-white/90"
                            >
                                ৳{{ formatMoney(seller.balance) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="statusClass(seller.status)"
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                >
                                    {{ seller.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'admin.sellers.show',
                                                seller.id,
                                            )
                                        "
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="
                                            route(
                                                'admin.sellers.edit',
                                                seller.id,
                                            )
                                        "
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="
                                            $page.props.auth?.user
                                                ?.user_type === 'super_admin' &&
                                            seller.user_id
                                        "
                                        @click="loginAsSeller(seller)"
                                        class="bg-brand-500 hover:bg-brand-600 cursor-pointer rounded-lg px-3 py-1.5 text-xs font-medium text-white"
                                    >
                                        Login
                                    </button>
                                    <button
                                        @click="destroy(seller)"
                                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!sellers.data.length">
                            <td
                                colspan="7"
                                class="px-4 py-10 text-center text-gray-500 dark:text-gray-400"
                            >
                                No sellers yet. Create your first one.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="sellers.links.length > 3"
                class="mt-5 flex flex-wrap gap-1"
            >
                <template v-for="(link, i) in sellers.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="rounded-lg px-3 py-1.5 text-sm"
                        :class="
                            link.active
                                ? 'bg-brand-500 text-white'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'
                        "
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="cursor-default rounded-lg px-3 py-1.5 text-sm text-gray-400 opacity-50"
                    />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    sellers: Object,
});

const formatMoney = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const statusClass = (status) =>
    ({
        active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        pending:
            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        suspended:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[status] ?? 'bg-gray-100 text-gray-700';

const destroy = (seller) => {
    if (!confirm(`Delete seller "${seller.name}"? Eta undo kora jabe na.`))
        return;
    router.delete(route('admin.sellers.destroy', seller.id), {
        preserveScroll: true,
    });
};

const loginAsSeller = (seller) => {
    router.post(
        route('admin.users.impersonate', seller.user_id),
        {},
        { preserveScroll: true },
    );
};
</script>
