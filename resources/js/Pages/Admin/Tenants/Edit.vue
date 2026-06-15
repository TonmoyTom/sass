<template>
    <AdminLayout>
        <div class="mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Edit Tenant
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ form.subdomain }}.{{ centralDomain }}
                    </p>
                </div>
                <Link
                    :href="route('admin.tenants.show', tenant.id)"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ← Back
                </Link>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <!-- Workspace -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Workspace
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2"
                    >
                        <FormInput
                            v-model="form.name"
                            label="Tenant Name"
                            :error="form.errors.name"
                        />

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >Subdomain</label
                            >
                            <div class="flex">
                                <input
                                    :value="form.subdomain"
                                    type="text"
                                    disabled
                                    class="h-11 w-full cursor-not-allowed rounded-l-lg border border-r-0 border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800"
                                />
                                <span
                                    class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    .{{ centralDomain }}
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400">
                                Subdomain Not Change.
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

                <!-- Owner -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Owner Account
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2"
                    >
                        <FormInput
                            v-model="form.owner_first_name"
                            label="First Name"
                            :error="form.errors.owner_first_name"
                        />
                        <FormInput
                            v-model="form.owner_last_name"
                            label="Last Name"
                            :error="form.errors.owner_last_name"
                        />
                        <FormInput
                            v-model="form.owner_email"
                            label="Email"
                            type="email"
                            :error="form.errors.owner_email"
                        />
                        <PhoneInput
                            v-model="form.phone"
                            label="Phone"
                            default-country="BD"
                            :error="form.errors.phone"
                        />
                    </div>
                </section>

                <!-- Address -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Address
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-3"
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

                <!-- Social -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Social Links
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-3"
                    >
                        <FormInput
                            v-model="form.facebook"
                            label="Facebook"
                            :error="form.errors.facebook"
                        />
                        <FormInput
                            v-model="form.twitter"
                            label="X.com"
                            :error="form.errors.twitter"
                        />
                        <FormInput
                            v-model="form.linkedin"
                            label="Linkedin"
                            :error="form.errors.linkedin"
                        />
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="route('admin.tenants.show', tenant.id)"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
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

const props = defineProps({
    tenant: Object,
    centralDomain: { type: String, default: 'localhost' },
    statuses: Array,
});

const form = useForm({
    name: props.tenant.name,
    subdomain: props.tenant.subdomain,
    status: props.tenant.status,
    owner_first_name: props.tenant.owner_first_name,
    owner_last_name: props.tenant.owner_last_name,
    owner_email: props.tenant.owner_email,
    phone: props.tenant.phone,
    country: props.tenant.country,
    city: props.tenant.city,
    postal_code: props.tenant.postal_code,
    facebook: props.tenant.facebook,
    twitter: props.tenant.twitter,
    linkedin: props.tenant.linkedin,
});

const submit = () => {
    form.put(route('admin.tenants.update', props.tenant.id));
};
</script>
