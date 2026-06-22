<template>
    <TenantLayout title="Modules">
        <div class="space-y-6">
            <!-- ── Header ── -->
            <div
                class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                    >
                        <LayoutGrid class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-xl font-bold tracking-tight text-gray-900 dark:text-white"
                        >
                            Modules
                        </h3>
                        <p
                            class="mt-1 max-w-md text-sm leading-relaxed text-gray-500 dark:text-gray-400"
                        >
                            Choose modules and plans for your school. Add to
                            cart and checkout when ready.
                        </p>
                    </div>
                </div>

                <!-- cart button -->
                <Link
                    :href="route('tenant.cart.index')"
                    class="bg-brand-500 hover:bg-brand-600 relative inline-flex shrink-0 items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm"
                >
                    <ShoppingCart class="h-5 w-5" />
                    Cart
                    <span
                        v-if="cartCount > 0"
                        class="text-brand-600 ring-brand-500 dark:text-brand-400 absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-bold shadow ring-2 dark:bg-gray-900"
                    >
                        {{ cartCount }}
                    </span>
                </Link>
            </div>

            <!-- ── Billing cycle toggle ── -->
            <div
                class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800"
            >
                <button
                    @click="billingCycle = 'monthly'"
                    :class="
                        billingCycle === 'monthly'
                            ? 'text-brand-600 dark:text-brand-400 bg-white shadow-sm dark:bg-gray-700'
                            : 'text-gray-500 dark:text-gray-400'
                    "
                    class="rounded-lg px-4 py-1.5 text-sm font-medium transition"
                >
                    Monthly
                </button>
                <button
                    @click="billingCycle = 'yearly'"
                    :class="
                        billingCycle === 'yearly'
                            ? 'text-brand-600 dark:text-brand-400 bg-white shadow-sm dark:bg-gray-700'
                            : 'text-gray-500 dark:text-gray-400'
                    "
                    class="rounded-lg px-4 py-1.5 text-sm font-medium transition"
                >
                    Yearly
                    <span class="text-xs text-green-600 dark:text-green-400"
                        >· save more</span
                    >
                </button>
            </div>

            <!-- ── Modules ── -->
            <div class="space-y-6">
                <section
                    v-for="m in modules"
                    :key="m.id"
                    class="rounded-2xl border bg-white p-6 shadow-sm transition dark:bg-gray-900"
                    :class="
                        m.is_owned
                            ? 'border-green-200 dark:border-green-900/40'
                            : 'border-gray-100 dark:border-gray-800'
                    "
                >
                    <!-- module header -->
                    <div class="mb-5 flex items-start gap-3">
                        <div
                            class="bg-brand-50 text-brand-500 dark:bg-brand-900/20 dark:text-brand-400 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                        >
                            <ShoppingCart class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4
                                    class="text-lg font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ m.name }}
                                </h4>
                                <span
                                    class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 capitalize dark:bg-gray-800 dark:text-gray-400"
                                    >{{ m.category }}</span
                                >
                                <span
                                    v-if="m.is_owned"
                                    class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400"
                                >
                                    <Check class="h-3 w-3" /> Purchased
                                </span>
                                <span
                                    v-else-if="m.in_cart"
                                    class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400"
                                >
                                    In Cart
                                </span>
                            </div>
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ m.description || 'No description.' }}
                            </p>
                            <p
                                v-if="m.is_owned && m.expires_at"
                                class="mt-1 text-xs font-medium text-green-600 dark:text-green-400"
                            >
                                {{ capitalize(m.owned_billing_cycle) }} · Active
                                until {{ m.expires_at }}
                            </p>
                            <p
                                v-else-if="m.is_owned && !m.expires_at"
                                class="mt-1 text-xs font-medium text-green-600 dark:text-green-400"
                            >
                                Lifetime access
                            </p>
                        </div>
                    </div>

                    <!-- tiers -->
                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="tier in m.tiers"
                            :key="tier.id"
                            class="relative rounded-xl border p-4 transition"
                            :class="
                                isCurrentPlan(m, tier)
                                    ? 'border-green-300 bg-green-50/40 dark:border-green-700 dark:bg-green-900/10'
                                    : tier.is_popular
                                      ? 'border-brand-300 bg-brand-50/40 ring-brand-100 dark:border-brand-700 dark:bg-brand-900/10 dark:ring-brand-900/40 ring-1'
                                      : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'
                            "
                        >
                            <span
                                v-if="isCurrentPlan(m, tier)"
                                class="absolute -top-2.5 left-4 rounded-full bg-green-500 px-2 py-0.5 text-[10px] font-bold text-white"
                            >
                                CURRENT
                            </span>
                            <span
                                v-else-if="tier.is_popular"
                                class="bg-brand-500 absolute -top-2.5 left-4 rounded-full px-2 py-0.5 text-[10px] font-bold text-white"
                            >
                                POPULAR
                            </span>

                            <p
                                class="font-semibold text-gray-900 dark:text-white"
                            >
                                {{ tier.name }}
                            </p>

                            <p class="mt-2">
                                <span
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    ৳{{
                                        money(
                                            billingCycle === 'yearly'
                                                ? tier.yearly_price
                                                : tier.monthly_price,
                                        )
                                    }}
                                </span>
                                <span
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                    >/{{
                                        billingCycle === 'yearly' ? 'yr' : 'mo'
                                    }}</span
                                >
                            </p>

                            <!-- limits -->
                            <ul
                                v-if="Object.keys(tier.limits).length"
                                class="mt-3 space-y-1.5"
                            >
                                <li
                                    v-for="(val, key) in tier.limits"
                                    :key="key"
                                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400"
                                >
                                    <Check
                                        class="h-3.5 w-3.5 shrink-0 text-green-500"
                                    />
                                    {{ formatLimit(key) }}: {{ val }}
                                </li>
                            </ul>

                            <!-- button -->
                            <div
                                v-if="isCurrentPlan(m, tier)"
                                class="mt-4 w-full rounded-lg bg-green-50 py-2 text-center text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400"
                            >
                                ✓ Current Plan
                            </div>
                            <template v-else-if="m.is_owned">
                                <button
                                    @click="addToCart(m, tier)"
                                    class="border-brand-300 text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-900/20 mt-4 w-full rounded-lg border py-2 text-sm font-medium"
                                >
                                    {{
                                        m.owned_tier_id === tier.id
                                            ? 'Change billing'
                                            : 'Switch to this'
                                    }}
                                </button>
                                <p
                                    v-if="m.credit > 0"
                                    class="mt-1.5 text-center text-[11px] text-green-600 dark:text-green-400"
                                >
                                    ৳{{ money(m.credit) }} credit · You pay ৳{{
                                        money(
                                            Math.max(
                                                0,
                                                tierPrice(tier) - m.credit,
                                            ),
                                        )
                                    }}
                                </p>
                            </template>
                            <button
                                v-else
                                @click="addToCart(m, tier)"
                                class="bg-brand-500 hover:bg-brand-600 mt-4 w-full rounded-lg py-2 text-sm font-medium text-white"
                            >
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </section>

                <!-- empty -->
                <div
                    v-if="!modules.length"
                    class="rounded-2xl border border-dashed border-gray-200 py-12 text-center text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500"
                >
                    No modules available right now.
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Check, LayoutGrid, ShoppingCart } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    modules: Array,
    cartCount: Number,
});

const billingCycle = ref('monthly');

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const formatLimit = (key) =>
    key
        .replace(/^max_/, '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());

const capitalize = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '');

const addToCart = (module, tier) => {
    router.post(
        route('tenant.cart.add'),
        {
            module_tier_id: tier.id,
            billing_cycle: billingCycle.value,
        },
        { preserveScroll: true },
    );
};

const isCurrentPlan = (module, tier) =>
    module.owned_tier_id === tier.id &&
    module.owned_billing_cycle === billingCycle.value;

const tierPrice = (tier) =>
    billingCycle.value === 'yearly' ? tier.yearly_price : tier.monthly_price;
</script>
