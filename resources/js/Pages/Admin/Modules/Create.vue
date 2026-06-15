<template>
    <AdminLayout title="Create Module">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Create Module
                </h3>
                <Link
                    :href="route('admin.modules.index')"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ← Back
                </Link>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <!-- Module info -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Module Info
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2"
                    >
                        <FormInput
                            v-model="form.name"
                            label="Name"
                            :error="form.errors.name"
                        />
                        <FormInput
                            v-model="form.alias"
                            label="Alias (auto if empty)"
                            :error="form.errors.alias"
                        />
                        <FormSelect
                            v-model="form.module_category"
                            label="Category"
                            :options="categories"
                            :error="form.errors.module_category"
                        />
                        <FormSelect
                            v-model="form.pricing_type"
                            label="Pricing Type"
                            :options="pricingTypes"
                            :error="form.errors.pricing_type"
                        />
                        <FormInput
                            v-model="form.icon"
                            label="Icon (lucide name)"
                            :error="form.errors.icon"
                        />
                        <FormInput
                            v-model="form.version"
                            label="Version"
                            :error="form.errors.version"
                        />

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >Commission Rate</label
                            >
                            <div class="flex">
                                <input
                                    v-model="form.commission_rate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="h-11 w-full rounded-l-lg border border-r-0 border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                />
                                <span
                                    class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                    >%</span
                                >
                            </div>
                        </div>

                        <FormInput
                            v-model="form.sort_order"
                            label="Sort Order"
                            type="number"
                        />

                        <div class="lg:col-span-2">
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >Description</label
                            >
                            <textarea
                                v-model="form.description"
                                rows="2"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            ></textarea>
                        </div>

                        <div class="lg:col-span-2">
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >Features (one per line)</label
                            >
                            <textarea
                                v-model="form.features"
                                rows="3"
                                placeholder="Student management&#10;Attendance tracking&#10;Fee collection"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            ></textarea>
                        </div>

                        <div class="flex items-center gap-6 lg:col-span-2">
                            <label
                                class="flex cursor-pointer items-center gap-2"
                            >
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="text-brand-500 h-5 w-5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                                />
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-400"
                                    >Active</span
                                >
                            </label>
                            <label
                                class="flex cursor-pointer items-center gap-2"
                            >
                                <input
                                    v-model="form.is_core"
                                    type="checkbox"
                                    class="text-brand-500 h-5 w-5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                                />
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-400"
                                    >Core (can't delete)</span
                                >
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Tiers -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <h5
                            class="font-semibold text-gray-800 dark:text-white/90"
                        >
                            Tiers (pricing + limits)
                        </h5>
                        <button
                            type="button"
                            @click="addTier"
                            class="border-brand-300 text-brand-600 hover:bg-brand-50 dark:border-brand-800 rounded-lg border px-3 py-1.5 text-xs font-medium"
                        >
                            + Add Tier
                        </button>
                    </div>

                    <p
                        v-if="form.errors.tiers"
                        class="mb-3 text-sm text-red-500"
                    >
                        {{ form.errors.tiers }}
                    </p>

                    <div class="flex flex-col gap-4">
                        <div
                            v-for="(tier, ti) in form.tiers"
                            :key="ti"
                            class="rounded-xl border border-gray-200 p-4 dark:border-gray-700"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <span
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300"
                                    >Tier {{ ti + 1 }}</span
                                >
                                <button
                                    type="button"
                                    @click="removeTier(ti)"
                                    class="text-xs font-medium text-red-500 hover:text-red-700"
                                >
                                    Remove
                                </button>
                            </div>

                            <div
                                class="grid grid-cols-1 gap-x-4 gap-y-3 lg:grid-cols-3"
                            >
                                <div>
                                    <label
                                        class="mb-1 block text-xs text-gray-500 dark:text-gray-400"
                                        >Name</label
                                    >
                                    <input
                                        v-model="tier.name"
                                        placeholder="Basic"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs text-gray-500 dark:text-gray-400"
                                        >Monthly ৳</label
                                    >
                                    <input
                                        v-model="tier.monthly_price"
                                        type="number"
                                        step="0.01"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs text-gray-500 dark:text-gray-400"
                                        >Yearly ৳</label
                                    >
                                    <input
                                        v-model="tier.yearly_price"
                                        type="number"
                                        step="0.01"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    />
                                </div>
                            </div>

                            <!-- limits (dynamic key-value) -->
                            <div class="mt-3">
                                <div
                                    class="mb-1 flex items-center justify-between"
                                >
                                    <label
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                        >Limits</label
                                    >
                                    <button
                                        type="button"
                                        @click="addLimit(tier)"
                                        class="text-brand-600 text-xs font-medium"
                                    >
                                        + Add limit
                                    </button>
                                </div>
                                <div
                                    v-for="(lim, li) in tier._limitPairs"
                                    :key="li"
                                    class="mb-2 flex items-center gap-2"
                                >
                                    <input
                                        v-model="lim.key"
                                        placeholder="max_students"
                                        class="h-9 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    />
                                    <input
                                        v-model="lim.value"
                                        type="number"
                                        placeholder="100"
                                        class="h-9 w-28 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                    />
                                    <button
                                        type="button"
                                        @click="removeLimit(tier, li)"
                                        class="text-red-500"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-6">
                                <label
                                    class="flex cursor-pointer items-center gap-2"
                                >
                                    <input
                                        v-model="tier.is_active"
                                        type="checkbox"
                                        class="text-brand-500 h-4 w-4 rounded border-gray-300 dark:border-gray-700"
                                    />
                                    <span
                                        class="text-xs text-gray-600 dark:text-gray-400"
                                        >Active</span
                                    >
                                </label>
                                <label
                                    class="flex cursor-pointer items-center gap-2"
                                >
                                    <input
                                        v-model="tier.is_popular"
                                        type="checkbox"
                                        class="text-brand-500 h-4 w-4 rounded border-gray-300 dark:border-gray-700"
                                    />
                                    <span
                                        class="text-xs text-gray-600 dark:text-gray-400"
                                        >Popular</span
                                    >
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="route('admin.modules.index')"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Module' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    pricingTypes: Array,
    categories: Array,
});

const makeTier = () => ({
    name: '',
    slug: '',
    monthly_price: 0,
    yearly_price: 0,
    one_time_price: null,
    is_active: true,
    is_popular: false,
    _limitPairs: [{ key: '', value: '' }],
});

const form = useForm({
    name: '',
    alias: '',
    description: '',
    icon: '',
    version: '1.0.0',
    pricing_type: 'subscription',
    commission_rate: 70,
    module_category: 'core',
    is_active: true,
    is_core: false,
    sort_order: 0,
    features: '',
    tiers: [makeTier()],
});

const addTier = () => form.tiers.push(makeTier());
const removeTier = (i) => form.tiers.splice(i, 1);

const addLimit = (tier) => tier._limitPairs.push({ key: '', value: '' });
const removeLimit = (tier, i) => tier._limitPairs.splice(i, 1);

const submit = () => {
    form.transform((data) => ({
        ...data,
        tiers: data.tiers.map((t) => ({
            name: t.name,
            slug: t.slug,
            monthly_price: t.monthly_price,
            yearly_price: t.yearly_price,
            one_time_price: t.one_time_price,
            is_active: t.is_active,
            is_popular: t.is_popular,
            limits: t._limitPairs.reduce((acc, p) => {
                if (p.key) acc[p.key] = p.value;
                return acc;
            }, {}),
        })),
    })).post(route('admin.modules.store'));
};
</script>
