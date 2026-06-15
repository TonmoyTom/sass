<template>
    <AdminLayout title="Create Seller">
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Create Seller
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Add a new seller/agent who can refer tenants and earn
                        commission.
                    </p>
                </div>
                <Link
                    :href="route('admin.sellers.index')"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ← Back to list
                </Link>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <!-- Account -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Account
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2"
                    >
                        <FormInput
                            v-model="form.first_name"
                            label="First Name"
                            :error="form.errors.first_name"
                        />
                        <FormInput
                            v-model="form.last_name"
                            label="Last Name"
                            :error="form.errors.last_name"
                        />
                        <FormInput
                            v-model="form.email"
                            label="Email"
                            type="email"
                            :error="form.errors.email"
                        />
                        <PhoneInput
                            v-model="form.phone"
                            label="Phone"
                            default-country="BD"
                            :error="form.errors.phone"
                        />
                        <FormInput
                            v-model="form.password"
                            label="Password"
                            type="password"
                            :error="form.errors.password"
                        />
                        <FormInput
                            v-model="form.password_confirmation"
                            label="Confirm Password"
                            type="password"
                        />
                    </div>
                </section>

                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Address
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2"
                    >
                        <FormInput
                            v-model="form.country"
                            label="Country"
                            :error="form.errors.country"
                        />
                        <FormInput
                            v-model="form.city"
                            label="City/State"
                            :error="form.errors.city"
                        />
                        <FormInput
                            v-model="form.postal_code"
                            label="Postal Code"
                            :error="form.errors.postal_code"
                        />
                    </div>
                </section>

                <!-- Seller settings -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Seller Settings
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Commission Rate
                            </label>
                            <div class="flex">
                                <input
                                    v-model="form.commission_rate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="dark:bg-dark-900 h-11 w-full rounded-l-lg border border-r-0 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:bg-gray-900 dark:text-white/90"
                                    :class="
                                        form.errors.commission_rate
                                            ? 'border-red-500'
                                            : 'border-gray-300 dark:border-gray-700'
                                    "
                                />
                                <span
                                    class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    %
                                </span>
                            </div>
                            <p
                                v-if="form.errors.commission_rate"
                                class="mt-1.5 text-sm text-red-500"
                            >
                                {{ form.errors.commission_rate }}
                            </p>
                            <p v-else class="mt-1.5 text-xs text-gray-400">
                                Default 70% — seller-er sale theke commission.
                            </p>
                        </div>

                        <FormSelect
                            v-model="form.status"
                            label="Status"
                            :options="statuses"
                            :error="form.errors.status"
                        />
                    </div>
                </section>

                <!-- Payout details -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Payout Details
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2"
                    >
                        <FormInput
                            v-model="form.bkash_number"
                            label="bKash Number"
                            :error="form.errors.bkash_number"
                        />
                        <FormInput
                            v-model="form.nid_number"
                            label="NID Number"
                            :error="form.errors.nid_number"
                        />
                        <FormInput
                            v-model="form.bank_name"
                            label="Bank Name"
                            :error="form.errors.bank_name"
                        />
                        <FormInput
                            v-model="form.bank_account"
                            label="Bank Account"
                            :error="form.errors.bank_account"
                        />
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="route('admin.sellers.index')"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Seller' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import PhoneInput from '@/Components/ui/PhoneInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const statuses = [
    { value: 'active', label: 'Active' },
    { value: 'pending', label: 'Pending' },
    { value: 'suspended', label: 'Suspended' },
];

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  commission_rate: 70,
  status: 'active',
  bkash_number: '',
  nid_number: '',
  bank_name: '',
  bank_account: '',
  country: '',       
  city: '',          
  postal_code: '',   
})

const submit = () => {
    form.post(route('admin.sellers.store'));
};
</script>
