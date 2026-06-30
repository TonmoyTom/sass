<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  Image, SquarePen, ExternalLink, Home, User,
  ShoppingCart, Plus, TrendingUp, ChevronRight, Globe,
} from 'lucide-vue-next'

import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';

// import AppLayout from '@/Layouts/AppLayout.vue'
// defineOptions({ layout: AppLayout })

const props = defineProps({
  tenant: { type: Object, default: () => ({}) },
  owner: { type: Object, default: () => ({}) },
  sales: { type: Object, default: () => ({}) },
  recentOrders: { type: Array, default: () => [] },
  modules: { type: Array, default: () => [] },
})

const period = ref('12M')
const periods = ['7D', '30D', '12M']
const months = ['Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr','May','Jun']

const ownerInitials = computed(() =>
  (props.owner?.name || '?').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
)

// ── BDT formatting ──
const taka = (n) => '৳' + Number(n || 0).toLocaleString('en-IN')
const takaShort = (n) => {
  n = Number(n || 0)
  if (n >= 1e7) return '৳' + (n / 1e7).toFixed(1) + 'Cr'
  if (n >= 1e5) return '৳' + (n / 1e5).toFixed(1) + 'L'
  if (n >= 1e3) return '৳' + (n / 1e3).toFixed(1) + 'k'
  return '৳' + n
}

const statusPill = (s) => ({
  active:    'text-green-600 bg-green-50',
  trial:     'text-amber-600 bg-amber-50',
  suspended: 'text-red-600 bg-red-50',
  expired:   'text-gray-500 bg-gray-100',
  paid:      'text-green-700 bg-green-50',
  pending:   'text-amber-700 bg-amber-50',
}[String(s).toLowerCase()] ?? 'text-gray-600 bg-gray-100')

// ── SVG area chart path ──
const W = 760, H = 170, PAD = 12
const chart = computed(() => {
  const pts = props.sales?.chart?.length ? props.sales.chart : []
  if (pts.length < 2) return null
  const max = Math.max(...pts), min = Math.min(...pts)
  const range = (max - min) || 1
  const step = W / (pts.length - 1)
  const c = pts.map((v, i) => [
    +(i * step).toFixed(1),
    +(H - PAD - ((v - min) / range) * (H - PAD * 2)).toFixed(1),
  ])
  const line = c.map(([x, y], i) => `${i ? 'L' : 'M'}${x},${y}`).join(' ')
  return { line, area: `${line} L${W},${H} L0,${H} Z`, last: c.at(-1) }
})
</script>

<template>
   <WorkspaceLayout title="Edit User">
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-3xl space-y-6 px-4">

      <!-- ── Header card ── -->
      <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex h-16 w-16 items-center justify-center rounded-xl border border-dashed border-gray-300 text-gray-400">
          <img v-if="tenant.logo" :src="tenant.logo" alt="" class="h-full w-full rounded-xl object-cover" />
          <Image v-else class="h-6 w-6" />
        </div>

        <div class="mt-4 flex items-center gap-3">
          <h1 class="text-2xl font-bold text-gray-900">{{ tenant.name }}</h1>
          <span :class="statusPill(tenant.status)" class="flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
            <span class="h-1.5 w-1.5 rounded-full bg-current" /> {{ tenant.status }}
          </span>
        </div>

        <p class="mt-1 flex items-center gap-2 text-sm text-gray-500">
          <Globe class="h-4 w-4" /> {{ tenant.domain }}
          <span class="text-gray-300">·</span>
          Tenant ID <span class="font-mono text-gray-700">{{ tenant.code }}</span>
        </p>

        <div class="mt-5 flex gap-3">
          <Link href="/dashboard/edit"
            class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-200 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <SquarePen class="h-4 w-4" /> Edit
          </Link>
          <Link href="/workspace"
            class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 py-3 text-sm font-semibold text-white hover:bg-brand-600">
            <ExternalLink class="h-4 w-4" /> Login as tenant
          </Link>
        </div>
      </section>

      <!-- ── Tenant details ── -->
      <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 flex items-center gap-2 font-semibold text-gray-900">
          <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-500"><Home class="h-4 w-4" /></span>
          Tenant details
        </h2>
        <div class="grid grid-cols-2 gap-y-5">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Plan</p>
            <span class="mt-1 inline-block rounded-md bg-brand-50 px-2 py-0.5 text-sm font-medium text-brand-600">{{ tenant.plan }}</span>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Status</p>
            <span :class="statusPill(tenant.status)" class="mt-1 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-sm font-medium capitalize">
              <span class="h-1.5 w-1.5 rounded-full bg-current" /> {{ tenant.status }}
            </span>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Renews</p>
            <p class="mt-1 font-medium text-gray-900">{{ tenant.renews_at ?? '—' }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Domain</p>
            <p class="mt-1 font-mono text-sm font-medium text-gray-900">{{ tenant.domain }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Created</p>
            <p class="mt-1 font-medium text-gray-900">{{ tenant.created_at }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Region</p>
            <p class="mt-1 font-medium text-gray-900">{{ tenant.region }}</p>
          </div>
        </div>
      </section>

      <!-- ── Owner profile ── -->
      <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="flex items-center gap-2 font-semibold text-gray-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-500"><User class="h-4 w-4" /></span>
            Owner profile
          </h2>
          <Link href="/dashboard/owner" class="flex items-center gap-1 text-sm font-medium text-brand-500 hover:text-brand-600">
            Manage <ChevronRight class="h-4 w-4" />
          </Link>
        </div>
        <div class="flex gap-4">
          <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-lg font-semibold text-white">
            {{ ownerInitials }}
          </div>
          <div class="space-y-2">
            <p class="font-semibold text-gray-900">{{ owner.name }}</p>
            <div><p class="text-xs text-gray-400">Email</p><p class="text-sm text-gray-800">{{ owner.email }}</p></div>
            <div><p class="text-xs text-gray-400">Phone</p><p class="text-sm text-gray-800">{{ owner.phone ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-400">Role</p><p class="text-sm text-gray-800">{{ owner.role }}</p></div>
          </div>
        </div>
      </section>

      <!-- ── Sales overview ── -->
      <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between">
          <h2 class="flex items-center gap-2 font-semibold text-gray-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-500"><TrendingUp class="h-4 w-4" /></span>
            Sales overview
          </h2>
          <div class="flex rounded-lg bg-gray-100 p-1 text-xs font-medium">
            <button v-for="p in periods" :key="p" @click="period = p"
              :class="period === p ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500'"
              class="rounded-md px-3 py-1">{{ p }}</button>
          </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
          <p class="text-3xl font-bold text-gray-900">{{ takaShort(sales.revenue) }}</p>
          <span v-if="sales.change_pct" class="flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-600">
            ↗ +{{ sales.change_pct }}%
          </span>
        </div>
        <p class="text-sm text-gray-400">Gross revenue · last 12 months</p>

        <!-- chart -->
        <div class="mt-4">
          <svg v-if="chart" :viewBox="`0 0 ${W} ${H}`" class="h-44 w-full" preserveAspectRatio="none">
            <defs>
              <linearGradient id="salesFill" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="rgb(99 102 241)" stop-opacity="0.18" />
                <stop offset="100%" stop-color="rgb(99 102 241)" stop-opacity="0" />
              </linearGradient>
            </defs>
            <path :d="chart.area" fill="url(#salesFill)" />
            <path :d="chart.line" fill="none" stroke="rgb(99 102 241)" stroke-width="2.5" stroke-linejoin="round" />
            <circle :cx="chart.last[0]" :cy="chart.last[1]" r="5" fill="white" stroke="rgb(99 102 241)" stroke-width="2.5" />
          </svg>
          <div v-else class="flex h-44 items-center justify-center text-sm text-gray-400">No sales data yet</div>
          <div class="mt-1 flex justify-between text-[11px] text-gray-400">
            <span v-for="m in months" :key="m">{{ m }}</span>
          </div>
        </div>

        <!-- stat tiles -->
        <div class="mt-6 grid grid-cols-2 gap-4">
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Total orders</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ sales.total_orders ?? 0 }}</p>
          </div>
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Avg. order value</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ taka(sales.avg_order_value) }}</p>
          </div>
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="mt-1 text-2xl font-bold text-green-600">{{ sales.pending ?? 0 }}</p>
          </div>
        </div>
      </section>

      <!-- ── Recent orders ── -->
      <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="flex items-center gap-2 font-semibold text-gray-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-500"><ShoppingCart class="h-4 w-4" /></span>
            Recent orders
          </h2>
          <Link href="/orders" class="flex items-center gap-1 text-sm font-medium text-brand-500 hover:text-brand-600">
            View all <ChevronRight class="h-4 w-4" />
          </Link>
        </div>

        <div v-if="recentOrders.length" class="divide-y divide-gray-100">
          <div v-for="o in recentOrders" :key="o.id" class="py-4 first:pt-0">
            <div class="flex items-start justify-between">
              <div>
                <p class="font-semibold text-gray-900">{{ o.customer }}</p>
                <p class="text-sm text-gray-400">{{ o.date }} · {{ o.item_count }} item{{ o.item_count > 1 ? 's' : '' }}</p>
              </div>
              <p class="font-bold text-gray-900">{{ taka(o.amount) }}</p>
            </div>
            <div :class="statusPill(o.status)" class="mt-2 rounded-full px-3 py-1 text-xs font-medium capitalize">{{ o.status }}</div>
          </div>
        </div>
        <p v-else class="py-6 text-center text-sm text-gray-400">No orders yet</p>
      </section>

      <!-- ── Purchased modules ── -->
      <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="flex items-center gap-2 font-semibold text-gray-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-500">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><rect x="2" y="2" width="6" height="6" rx="1.5"/><rect x="12" y="2" width="6" height="6" rx="1.5"/><rect x="2" y="12" width="6" height="6" rx="1.5"/><rect x="12" y="12" width="6" height="6" rx="1.5"/></svg>
            </span>
            Purchased modules
          </h2>
          <span class="text-sm text-gray-400">{{ modules.filter(m => !m.is_expired).length }} active</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div v-for="m in modules" :key="m.id" class="flex items-center gap-3 rounded-xl border border-gray-100 p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-500">
              <ShoppingCart class="h-5 w-5" />
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate font-semibold text-gray-900">{{ m.name }}</p>
              <p class="text-xs text-gray-400">{{ m.tier_name }} · until {{ m.expires_at ?? '—' }}</p>
            </div>
            <span :class="statusPill(m.is_expired ? 'expired' : 'active')" class="flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium">
              <span class="h-1.5 w-1.5 rounded-full bg-current" /> {{ m.is_expired ? 'Expired' : 'Active' }}
            </span>
          </div>

          <Link href="/modules"
            class="flex items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 p-4 text-sm font-medium text-brand-500 hover:border-brand-300 hover:bg-brand-50/50">
            <Plus class="h-5 w-5" /> Browse modules
          </Link>
        </div>
      </section>

    </div>
  </div>
  </WorkspaceLayout>
</template>

