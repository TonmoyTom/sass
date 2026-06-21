<template>
    <TenantLayout title="Modules">
        <div class="mx-auto">
            <!-- header -->
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Modules
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Choose modules and plans for your school. Add to cart
                        and checkout when ready.
                    </p>
                </div>

                <!-- cart button -->
                <Link
                    :href="route('tenant.cart.index')"
                    class="relative inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path
                            d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    Cart
                    <span
                        v-if="cartCount > 0"
                        class="bg-brand-500 absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold text-white"
                    >
                        {{ cartCount }}
                    </span>
                </Link>
            </div>

            <!-- billing cycle toggle -->
            <div
                class="mb-6 inline-flex rounded-xl border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800"
            >
                <button
                    @click="billingCycle = 'monthly'"
                    :class="
                        billingCycle === 'monthly'
                            ? 'bg-brand-500 text-white'
                            : 'text-gray-600 dark:text-gray-400'
                    "
                    class="rounded-lg px-4 py-1.5 text-sm font-medium transition"
                >
                    Monthly
                </button>
                <button
                    @click="billingCycle = 'yearly'"
                    :class="
                        billingCycle === 'yearly'
                            ? 'bg-brand-500 text-white'
                            : 'text-gray-600 dark:text-gray-400'
                    "
                    class="rounded-lg px-4 py-1.5 text-sm font-medium transition"
                >
                    Yearly <span class="text-xs opacity-80">(save more)</span>
                </button>
            </div>

            <!-- modules -->
            <div class="flex flex-col gap-6">
                <div
                    v-for="m in modules"
                    :key="m.id"
                    class="rounded-2xl border bg-white p-5 lg:p-6 dark:bg-white/[0.03]"
                    :class="
                        m.is_owned
                            ? 'border-green-200 dark:border-green-900/40'
                            : 'border-gray-200 dark:border-gray-800'
                    "
                >
                    <!-- module header -->
                    <div class="mb-5 flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4
                                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                                >
                                    {{ m.name }}
                                </h4>
                                <span
                                    class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 capitalize dark:bg-gray-800 dark:text-gray-400"
                                    >{{ m.category }}</span
                                >

                                <span
                                    v-if="m.is_owned"
                                    class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                >
                                    ✓ Purchased
                                </span>
                                <span
                                    v-else-if="m.in_cart"
                                    class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
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
                                    ? 'border-green-300 bg-green-50/30 dark:border-green-700 dark:bg-green-900/10'
                                    : tier.is_popular
                                      ? 'border-brand-300 bg-brand-50/30 dark:border-brand-700 dark:bg-brand-900/10'
                                      : 'border-gray-200 dark:border-gray-700'
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
                                class="font-semibold text-gray-800 dark:text-white/90"
                            >
                                {{ tier.name }}
                            </p>

                            <p class="mt-2">
                                <span
                                    class="text-2xl font-bold text-gray-800 dark:text-white/90"
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
                                class="mt-3 space-y-1"
                            >
                                <li
                                    v-for="(val, key) in tier.limits"
                                    :key="key"
                                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400"
                                >
                                    <svg
                                        class="h-3.5 w-3.5 text-green-500"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                    >
                                        <path
                                            d="M20 6L9 17l-5-5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    {{ formatLimit(key) }}: {{ val }}
                                </li>
                            </ul>

                            <!-- button -->
                            <div
                                v-if="isCurrentPlan(m, tier)"
                                class="mt-4 w-full rounded-lg bg-green-100 py-2 text-center text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
                            >
                                ✓ Current Plan
                            </div>
                            <template v-else-if="m.is_owned">
                                <button
                                    @click="addToCart(m, tier)"
                                    class="border-brand-300 text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 mt-4 w-full rounded-lg border py-2 text-sm font-medium"
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
                                    ৳{{ money(m.credit) }} credit applied · You
                                    pay ৳{{
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
                </div>

                <div
                    v-if="!modules.length"
                    class="py-10 text-center text-gray-500 dark:text-gray-400"
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
