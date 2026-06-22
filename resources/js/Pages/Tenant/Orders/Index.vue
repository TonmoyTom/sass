<template>
    <TenantLayout title="Orders">
        <div class="space-y-6">
            <!-- ── Header ── -->
            <div
                class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                >
                    <ShoppingCart class="h-5 w-5" />
                </div>
                <div>
                    <h3
                        class="text-xl font-bold tracking-tight text-gray-900 dark:text-white"
                    >
                        Orders
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Your module purchases and billing history.
                    </p>
                </div>
            </div>

            <!-- ── Stat tiles ── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total orders
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ counts.all }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Completed
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        {{ counts.completed }}
                    </p>
                </div>
                <div
                    class="col-span-2 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:col-span-1 dark:border-gray-800 dark:bg-gray-900"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total spent
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        ৳{{ money(totals.spent) }}
                    </p>
                </div>
            </div>

            <!-- ── Filters + search ── -->
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="flex rounded-xl bg-gray-100 p-1 text-sm font-medium dark:bg-gray-800"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.value"
                        @click="setStatus(tab.value)"
                        :class="
                            activeStatus === tab.value
                                ? 'text-brand-600 dark:text-brand-400 bg-white shadow-sm dark:bg-gray-700'
                                : 'text-gray-500 dark:text-gray-400'
                        "
                        class="rounded-lg px-4 py-1.5"
                    >
                        {{ tab.label }}
                        <span class="ml-1 text-xs opacity-70">{{
                            tab.count
                        }}</span>
                    </button>
                </div>

                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500"
                    />
                    <input
                        v-model="search"
                        @input="onSearch"
                        type="text"
                        placeholder="Search module..."
                        class="focus:border-brand-400 focus:ring-brand-100 dark:focus:border-brand-600 dark:focus:ring-brand-900/40 h-10 w-full rounded-xl border border-gray-200 bg-white pr-4 pl-9 text-sm text-gray-800 focus:ring-2 focus:outline-none sm:w-64 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500"
                    />
                </div>
            </div>

            <!-- ── Orders list ── -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    v-if="orders.data.length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <Link
                        v-for="o in orders.data"
                        :key="o.id"
                        :href="route('tenant.orders.show', o.id)"
                        class="flex items-center justify-between gap-4 p-5 transition hover:bg-gray-50 dark:hover:bg-gray-800/50"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                            >
                                <LayoutGrid class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ o.module_name }}
                                    </p>
                                    <span
                                        v-if="o.tier_name"
                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                        >{{ o.tier_name }}</span
                                    >
                                </div>
                                <p
                                    class="mt-0.5 text-sm text-gray-400 dark:text-gray-500"
                                >
                                    {{ o.sold_at ?? o.created_at }} ·
                                    <span class="capitalize">{{
                                        o.sale_type
                                    }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p
                                    class="font-bold text-gray-900 dark:text-white"
                                >
                                    ৳{{ money(o.amount) }}
                                </p>
                                <span
                                    :class="statusPill(o.status)"
                                    class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-current"
                                    />
                                    {{ o.status }}
                                </span>
                            </div>
                            <ChevronRight
                                class="h-5 w-5 text-gray-300 dark:text-gray-600"
                            />
                        </div>
                    </Link>
                </div>

                <!-- empty -->
                <div v-else class="p-12 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800"
                    >
                        <ShoppingCart
                            class="h-8 w-8 text-gray-400 dark:text-gray-500"
                        />
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        No orders found
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{
                            activeStatus || search
                                ? 'Try a different filter or search.'
                                : 'Browse modules to make your first purchase.'
                        }}
                    </p>
                    <Link
                        v-if="!activeStatus && !search"
                        :href="route('tenant.modules.index')"
                        class="bg-brand-500 hover:bg-brand-600 mt-5 inline-block rounded-xl px-5 py-2.5 text-sm font-semibold text-white"
                    >
                        Browse Modules
                    </Link>
                </div>
            </div>

            <!-- ── Pagination ── -->
            <div
                v-if="orders.data.length && orders.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ orders.from }}–{{ orders.to }} of {{ orders.total }}
                </p>
                <div class="flex gap-2">
                    <Link
                        v-for="link in orders.links"
                        :key="link.label"
                        :href="link.url ?? ''"
                        :class="[
                            link.active
                                ? 'bg-brand-500 text-white'
                                : 'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                        class="flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-medium"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ChevronRight,
    LayoutGrid,
    Search,
    ShoppingCart,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    orders: Object,
    filters: Object,
    counts: Object,
    totals: Object,
});

const search = ref(props.filters?.search ?? '');
const activeStatus = computed(() => props.filters?.status ?? '');

const tabs = computed(() => [
    { label: 'All', value: '', count: props.counts.all },
    { label: 'Completed', value: 'completed', count: props.counts.completed },
    { label: 'Pending', value: 'pending', count: props.counts.pending },
]);

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const statusPill = (s) =>
    ({
        completed:
            'text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400',
        pending:
            'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400',
        failed: 'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400',
        refunded:
            'text-gray-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400',
    })[String(s).toLowerCase()] ??
    'text-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-gray-400';

const setStatus = (status) => {
    router.get(
        route('tenant.orders.index'),
        { status: status || undefined, search: search.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

let searchTimer = null;
const onSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            route('tenant.orders.index'),
            {
                status: activeStatus.value || undefined,
                search: search.value || undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 350);
};
</script>
