<template>
    <TenantLayout>
        <div class="space-y-6">
            <!-- ── Purchased modules ── -->
            <section
                class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-5 flex items-center justify-between">
                    <h2
                        class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white"
                    >
                        <span
                            class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-7 w-7 items-center justify-center rounded-lg"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <rect
                                    x="2"
                                    y="2"
                                    width="6"
                                    height="6"
                                    rx="1.5"
                                />
                                <rect
                                    x="12"
                                    y="2"
                                    width="6"
                                    height="6"
                                    rx="1.5"
                                />
                                <rect
                                    x="2"
                                    y="12"
                                    width="6"
                                    height="6"
                                    rx="1.5"
                                />
                                <rect
                                    x="12"
                                    y="12"
                                    width="6"
                                    height="6"
                                    rx="1.5"
                                />
                            </svg>
                        </span>
                        Purchased modules
                    </h2>
                    <span class="text-sm text-gray-400 dark:text-gray-500"
                        >{{
                            modules.filter((m) => !m.is_expired).length
                        }}
                        active</span
                    >
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div
                        v-for="m in modules"
                        :key="m.id"
                        class="flex items-center gap-3 rounded-xl border border-gray-100 p-4 dark:border-gray-800"
                    >
                        <div
                            class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-10 w-10 items-center justify-center rounded-lg"
                        >
                            <ShoppingCart class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate font-semibold text-gray-900 dark:text-white"
                            >
                                {{ m.name }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                {{ m.tier_name }} · until
                                {{ m.expires_at ?? '—' }}
                            </p>
                        </div>
                        <span
                            :class="
                                statusPill(m.is_expired ? 'expired' : 'active')
                            "
                            class="flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-current" />
                            {{ m.is_expired ? 'Expired' : 'Active' }}
                        </span>
                    </div>

                    <Link
                        href="/tenant/modules"
                        class="text-brand-500 hover:border-brand-300 hover:bg-brand-50/50 dark:text-brand-400 dark:hover:border-brand-700 dark:hover:bg-brand-900/20 flex items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 p-4 text-sm font-medium dark:border-gray-700"
                    >
                        <Plus class="h-5 w-5" /> Browse modules
                    </Link>
                </div>
            </section>
            <!-- ── Tenant details ── -->
            <section
                class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <h2
                    class="mb-5 flex items-center gap-2 font-semibold text-gray-900 dark:text-white"
                >
                    <span
                        class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-7 w-7 items-center justify-center rounded-lg"
                        ><Home class="h-4 w-4"
                    /></span>
                    Tenant details
                </h2>
                <div class="grid grid-cols-2 gap-y-5">
                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                        >
                            Plan
                        </p>
                        <span
                            class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 mt-1 inline-block rounded-md px-2 py-0.5 text-sm font-medium"
                            >{{ tenant.plan }}</span
                        >
                    </div>
                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                        >
                            Status
                        </p>
                        <span
                            :class="statusPill(tenant.status)"
                            class="mt-1 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-sm font-medium capitalize"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-current" />
                            {{ tenant.status }}
                        </span>
                    </div>
                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                        >
                            Renews
                        </p>
                        <p
                            class="mt-1 font-medium text-gray-900 dark:text-white"
                        >
                            {{ tenant.renews_at ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                        >
                            Domain
                        </p>
                        <p
                            class="mt-1 font-mono text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ tenant.domain }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                        >
                            Created
                        </p>
                        <p
                            class="mt-1 font-medium text-gray-900 dark:text-white"
                        >
                            {{ tenant.created_at }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                        >
                            Region
                        </p>
                        <p
                            class="mt-1 font-medium text-gray-900 dark:text-white"
                        >
                            {{ tenant.region }}
                        </p>
                    </div>
                </div>
                <Link
                    href="/profile"
                    class="hover:border-brand-300 hover:bg-brand-50/50 hover:text-brand-600 dark:hover:border-brand-700 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 mt-4 flex items-center justify-center gap-2 rounded-xl border border-gray-200 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300"
                >
                    <User class="h-4 w-4" /> View full profile
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </section>

            <!-- ── Owner profile ── -->
            <section
                class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="from-brand-500 to-brand-600 relative h-20 bg-gradient-to-r"
                >
                    <Link
                        href="/profile"
                        class="absolute top-4 right-4 flex items-center gap-1 rounded-lg bg-white/15 px-3 py-1.5 text-xs font-medium text-white backdrop-blur hover:bg-white/25"
                    >
                        Manage <ChevronRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <div class="px-6 pb-6">
                    <div class="-mt-10">
                        <div
                            class="bg-brand-500 relative z-10 flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl text-2xl font-semibold text-white shadow-md ring-4 ring-white dark:ring-gray-900"
                        >
                            <img
                                v-if="owner.avatar"
                                :src="owner.avatar"
                                :alt="owner.name"
                                class="h-full w-full object-cover"
                            />
                            <span v-else>{{ ownerInitials }}</span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <p
                            class="text-lg font-bold text-gray-900 dark:text-white"
                        >
                            {{ owner.name }}
                        </p>
                        <span
                            class="bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                        >
                            <User class="h-3 w-3" /> {{ owner.role }}
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div
                            class="rounded-xl border border-gray-100 p-3 dark:border-gray-800"
                        >
                            <p
                                class="flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                            >
                                <Mail class="h-3.5 w-3.5" /> Email
                            </p>
                            <p
                                class="mt-1 truncate text-sm font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ owner.email ?? '—' }}
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-gray-100 p-3 dark:border-gray-800"
                        >
                            <p
                                class="flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase dark:text-gray-500"
                            >
                                <Phone class="h-3.5 w-3.5" /> Phone
                            </p>
                            <p
                                class="mt-1 truncate text-sm font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ owner.phone ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/profile"
                        class="hover:border-brand-300 hover:bg-brand-50/50 hover:text-brand-600 dark:hover:border-brand-700 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 mt-4 flex items-center justify-center gap-2 rounded-xl border border-gray-200 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300"
                    >
                        <User class="h-4 w-4" /> View full profile
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>
            </section>

            <!-- ── Sales overview ── -->
            <section
                class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-start justify-between">
                    <h2
                        class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white"
                    >
                        <span
                            class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-7 w-7 items-center justify-center rounded-lg"
                            ><TrendingUp class="h-4 w-4"
                        /></span>
                        Sales overview
                    </h2>
                    <div
                        class="flex rounded-lg bg-gray-100 p-1 text-xs font-medium dark:bg-gray-800"
                    >
                        <button
                            v-for="p in periods"
                            :key="p"
                            @click="period = p"
                            :class="
                                period === p
                                    ? 'text-brand-600 dark:text-brand-400 bg-white shadow-sm dark:bg-gray-700'
                                    : 'text-gray-500 dark:text-gray-400'
                            "
                            class="rounded-md px-3 py-1"
                        >
                            {{ p }}
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ takaShort(sales.revenue) }}
                    </p>
                    <span
                        v-if="sales.change_pct"
                        class="flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-600 dark:bg-green-900/20 dark:text-green-400"
                    >
                        ↗ +{{ sales.change_pct }}%
                    </span>
                </div>
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    Gross revenue · last 12 months
                </p>

                <div class="mt-4">
                    <svg
                        v-if="chart"
                        :viewBox="`0 0 ${W} ${H}`"
                        class="h-44 w-full"
                        preserveAspectRatio="none"
                    >
                        <defs>
                            <linearGradient
                                id="salesFill"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1"
                            >
                                <stop
                                    offset="0%"
                                    stop-color="rgb(99 102 241)"
                                    stop-opacity="0.18"
                                />
                                <stop
                                    offset="100%"
                                    stop-color="rgb(99 102 241)"
                                    stop-opacity="0"
                                />
                            </linearGradient>
                        </defs>
                        <path :d="chart.area" fill="url(#salesFill)" />
                        <path
                            :d="chart.line"
                            fill="none"
                            stroke="rgb(99 102 241)"
                            stroke-width="2.5"
                            stroke-linejoin="round"
                        />
                        <circle
                            :cx="chart.last[0]"
                            :cy="chart.last[1]"
                            r="5"
                            fill="white"
                            stroke="rgb(99 102 241)"
                            stroke-width="2.5"
                            class="dark:[fill:theme(colors.gray.900)]"
                        />
                    </svg>
                    <div
                        v-else
                        class="flex h-44 items-center justify-center text-sm text-gray-400 dark:text-gray-500"
                    >
                        No sales data yet
                    </div>
                    <div
                        class="mt-1 flex justify-between text-[11px] text-gray-400 dark:text-gray-500"
                    >
                        <span v-for="m in months" :key="m">{{ m }}</span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div
                        class="rounded-xl border border-gray-100 p-4 dark:border-gray-800"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total orders
                        </p>
                        <p
                            class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ sales.total_orders ?? 0 }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-gray-100 p-4 dark:border-gray-800"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Avg. order value
                        </p>
                        <p
                            class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ taka(sales.avg_order_value) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-gray-100 p-4 dark:border-gray-800"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Pending
                        </p>
                        <p
                            class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                        >
                            {{ sales.pending ?? 0 }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ── Recent orders ── -->
            <section
                class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-5 flex items-center justify-between">
                    <h2
                        class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white"
                    >
                        <span
                            class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-7 w-7 items-center justify-center rounded-lg"
                            ><ShoppingCart class="h-4 w-4"
                        /></span>
                        Recent orders
                    </h2>
                    <Link
                        href="/tenant/orders"
                        class="text-brand-500 hover:text-brand-600 dark:text-brand-400 flex items-center gap-1 text-sm font-medium"
                    >
                        View all <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>

                <div
                    v-if="recentOrders.length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <div
                        v-for="o in recentOrders"
                        :key="o.id"
                        class="py-4 first:pt-0"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ o.customer }}
                                </p>
                                <p
                                    class="text-sm text-gray-400 dark:text-gray-500"
                                >
                                    {{ o.date }} · {{ o.item_count }} item{{
                                        o.item_count > 1 ? 's' : ''
                                    }}
                                </p>
                            </div>
                            <p class="font-bold text-gray-900 dark:text-white">
                                {{ taka(o.amount) }}
                            </p>
                        </div>
                        <div
                            :class="statusPill(o.status)"
                            class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-medium capitalize"
                        >
                            {{ o.status }}
                        </div>
                    </div>
                </div>
                <p
                    v-else
                    class="py-6 text-center text-sm text-gray-400 dark:text-gray-500"
                >
                    No orders yet
                </p>
            </section>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
    ChevronRight,
    Home,
    Mail,
    Phone,
    Plus,
    ShoppingCart,
    TrendingUp,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    tenant: { type: Object, default: () => ({}) },
    owner: { type: Object, default: () => ({}) },
    sales: { type: Object, default: () => ({}) },
    recentOrders: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
});

const period = ref('12M');
const periods = ['7D', '30D', '12M'];
const months = [
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
];

const ownerInitials = computed(() =>
    (props.owner?.name || '?')
        .split(' ')
        .map((w) => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);

const taka = (n) => '৳' + Number(n || 0).toLocaleString('en-IN');
const takaShort = (n) => {
    n = Number(n || 0);
    if (n >= 1e7) return '৳' + (n / 1e7).toFixed(1) + 'Cr';
    if (n >= 1e5) return '৳' + (n / 1e5).toFixed(1) + 'L';
    if (n >= 1e3) return '৳' + (n / 1e3).toFixed(1) + 'k';
    return '৳' + n;
};

const statusPill = (s) =>
    ({
        active: 'text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400',
        trial: 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400',
        suspended:
            'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400',
        expired:
            'text-gray-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400',
        paid: 'text-green-700 bg-green-50 dark:bg-green-900/20 dark:text-green-400',
        pending:
            'text-amber-700 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400',
    })[String(s).toLowerCase()] ??
    'text-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-gray-400';

const W = 760,
    H = 170,
    PAD = 12;
const chart = computed(() => {
    const pts = props.sales?.chart?.length ? props.sales.chart : [];
    if (pts.length < 2) return null;
    const max = Math.max(...pts),
        min = Math.min(...pts);
    const range = max - min || 1;
    const step = W / (pts.length - 1);
    const c = pts.map((v, i) => [
        +(i * step).toFixed(1),
        +(H - PAD - ((v - min) / range) * (H - PAD * 2)).toFixed(1),
    ]);
    const line = c.map(([x, y], i) => `${i ? 'L' : 'M'}${x},${y}`).join(' ');
    return { line, area: `${line} L${W},${H} L0,${H} Z`, last: c.at(-1) };
});
</script>
