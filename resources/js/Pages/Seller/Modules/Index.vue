<template>
    <SellerLayout title="Modules">
        <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                Available Modules
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Je module sell korte chao, request pathaও. Admin approve korle
                commission paba.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="m in modules"
                :key="m.id"
                class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="mb-3 flex items-start justify-between">
                    <div>
                        <h4
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ m.name }}
                        </h4>
                        <span
                            class="text-xs text-gray-500 capitalize dark:text-gray-400"
                            >{{ m.category }}</span
                        >
                    </div>
                    <span
                        class="bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 rounded-full px-2.5 py-1 text-xs font-medium"
                    >
                        {{ m.commission_rate }}% commission
                    </span>
                </div>

                <p class="mb-4 flex-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ m.description || 'No description.' }}
                </p>

                <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-800 dark:text-white/90">
                        ৳{{ money(m.starting_price) }}
                    </span>
                    theke shuru · {{ m.tiers_count }} tier
                </div>

                <!-- request status / button -->
                <!-- request status / button -->
                <div class="space-y-2">
                    <button
                        v-if="!m.request_status"
                        @click="requestModule(m)"
                        class="bg-brand-500 hover:bg-brand-600 w-full rounded-lg py-2.5 text-sm font-medium text-white"
                    >
                        Request to Sell
                    </button>

                    <template v-else>
                        <div
                            v-if="m.request_status === 'pending'"
                            class="rounded-lg bg-yellow-50 py-2.5 text-center text-sm font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400"
                        >
                            Pending Review
                        </div>

                        <div
                            v-else-if="m.request_status === 'approved'"
                            class="rounded-lg bg-green-50 py-2.5 text-center text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400"
                        >
                            ✓ Approved — Sell korte parba
                        </div>

                        <div v-else-if="m.request_status === 'rejected'">
                            <div
                                class="rounded-lg bg-red-50 py-2.5 text-center text-sm font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400"
                            >
                                Rejected
                            </div>
                            <p
                                v-if="m.admin_note"
                                class="mt-1 text-center text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ m.admin_note }}
                            </p>
                        </div>
                        <Link
                            v-if="m.request_id"
                            :href="route('seller.modules.show', m.request_id)"
                            class="block w-full rounded-lg border border-gray-300 py-2 text-center text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                        >
                            View Details
                        </Link>
                    </template>
                </div>
            </div>

            <div
                v-if="!modules.length"
                class="col-span-full py-10 text-center text-gray-500 dark:text-gray-400"
            >
                No modules available right now.
            </div>
        </div>
    </SellerLayout>
</template>

<script setup>
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    modules: Array,
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

const requestModule = (m) => {
    if (!confirm(`"${m.name}" module sell korar request pathabe?`)) return;
    router.post(
        route('seller.modules.request'),
        { module_id: m.id },
        { preserveScroll: true },
    );
};
</script>
