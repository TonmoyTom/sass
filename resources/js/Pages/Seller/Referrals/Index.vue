<template>
    <SellerLayout title="Referrals">
        <div class="mx-auto">
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Referrals
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Share your link, refer schools, and earn commission on every
                    sale.
                </p>
            </div>

            <!-- Hero: referral link -->
            <div
                class="from-brand-500 to-brand-600 relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br p-6 text-white sm:p-8"
            >
                <!-- decorative bg circles -->
                <div
                    class="pointer-events-none absolute -top-8 -right-8 h-40 w-40 rounded-full bg-white/10"
                ></div>
                <div
                    class="pointer-events-none absolute -bottom-12 -left-6 h-48 w-48 rounded-full bg-white/5"
                ></div>

                <div class="relative">
                    <div class="mb-5 flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-white/70">
                                Your Referral Code
                            </p>
                            <p class="mt-0.5 text-xs text-white/60">
                                Share this code to earn on every purchase
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-white/25 px-4 py-2 text-base font-bold backdrop-blur"
                        >
                            {{ referral.commission_rate }}% commission
                        </span>
                    </div>

                    <!-- big code -->
                    <div
                        class="flex flex-col items-center gap-4 rounded-2xl bg-white/15 p-6 backdrop-blur sm:flex-row sm:justify-between"
                    >
                        <span
                            class="font-mono text-3xl font-bold tracking-widest sm:text-4xl"
                        >
                            {{ referral.code }}
                        </span>
                        <button
                            @click="copy(referral.code, 'code')"
                            class="text-brand-600 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold transition hover:bg-white/90"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <rect
                                    x="9"
                                    y="9"
                                    width="13"
                                    height="13"
                                    rx="2"
                                />
                                <path
                                    d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            {{ copied === 'code' ? 'Copied!' : 'Copy Code' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Conversions
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ referral.conversions }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Sales
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90"
                    >
                        {{ referral.total_sales }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Earned
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        ৳{{ money(referral.total_earned) }}
                    </p>
                </div>
            </div>

            <!-- How it works -->
            <!-- How it works -->
            <div
                class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-1 font-semibold text-gray-800 dark:text-white/90">
                    How you earn
                </h5>
                <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
                    Refer modules using your code — earn commission on every
                    purchase made with it.
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <!-- Step 1 -->
                    <div
                        class="relative rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]"
                    >
                        <div
                            class="bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 mb-3 flex h-10 w-10 items-center justify-center rounded-xl"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            1. Share your code
                        </p>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Copy your referral code or link and share it with
                            anyone interested in buying a module.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div
                        class="relative rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]"
                    >
                        <div
                            class="bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 mb-3 flex h-10 w-10 items-center justify-center rounded-xl"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            2. They enter your code
                        </p>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            When they purchase a module, they enter your
                            referral code at checkout.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div
                        class="relative rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]"
                    >
                        <div
                            class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            3. You earn commission
                        </p>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Once the purchase is confirmed, your commission is
                            added to your wallet.
                        </p>
                    </div>
                </div>

                <!-- highlight note -->
                <div
                    class="bg-brand-50 dark:bg-brand-900/20 mt-5 flex items-start gap-3 rounded-xl p-4"
                >
                    <svg
                        class="text-brand-600 dark:text-brand-400 mt-0.5 h-5 w-5 flex-shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="9" />
                        <path
                            d="M12 8h.01M11 12h1v4h1"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <p class="text-brand-700 dark:text-brand-300 text-sm">
                        <strong>Important:</strong> Commission is earned only
                        when a module is actually purchased with your code — not
                        just by sharing it. Make sure your code is entered at
                        checkout so you get credited.
                    </p>
                </div>
            </div>

            <!-- Recent referrals -->
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <h5 class="mb-4 font-semibold text-gray-800 dark:text-white/90">
                    Recent Conversions
                </h5>
                <div
                    v-if="referral.recent.length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <div
                        v-for="(r, i) in referral.recent"
                        :key="i"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                >
                                    <path
                                        d="M20 6L9 17l-5-5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                            <span
                                class="text-sm font-medium text-gray-800 dark:text-white/90"
                                >{{ r.tenant_name }}</span
                            >
                        </div>
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400"
                            >{{ r.converted_at }}</span
                        >
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-gray-400">
                    No conversions yet. Share your link to get started!
                </p>
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { ref } from 'vue';

defineProps({
    referral: Object,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const copied = ref(null);

const copy = async (text, type) => {
    try {
        await navigator.clipboard.writeText(text);
        copied.value = type;
        setTimeout(() => (copied.value = null), 2000);
    } catch {
        copied.value = null;
    }
};
</script>
