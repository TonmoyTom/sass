<template>
    <TenantLayout title="Order Confirmed">
        <div class="mx-auto max-w-lg">
            <div
                class="rounded-2xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                >
                    <Check
                        class="h-8 w-8 text-green-600 dark:text-green-400"
                        stroke-width="3"
                    />
                </div>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Purchase Successful!
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    <template v-if="purchase?.count">
                        {{ purchase.count }} module{{
                            purchase.count > 1 ? 's' : ''
                        }}
                        activated for your school.
                    </template>
                    <template v-else>
                        Your modules have been activated.
                    </template>
                </p>

                <div
                    v-if="purchase?.commission > 0"
                    class="mt-5 rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Referral commission generated
                    </p>
                    <p
                        class="mt-1 text-lg font-bold text-gray-800 dark:text-white/90"
                    >
                        ৳{{ money(purchase.commission) }}
                    </p>
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="route('tenant.modules.index')"
                        class="bg-brand-500 hover:bg-brand-600 flex-1 rounded-xl py-3 text-center text-sm font-semibold text-white"
                    >
                        Go to My Modules
                    </Link>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';

defineProps({
    purchase: { type: Object, default: null },
});

const money = (val) =>
    Number(val ?? 0).toLocaleString('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
</script>
