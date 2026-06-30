<template>
    <AdminLayout>
        <div class="mx-auto">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Create Tenant
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Set up a new school or business workspace.
                    </p>
                </div>
                <Link
                    :href="route('admin.tenants.index')"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ← Back to list
                </Link>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <!-- Workspace -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-5 flex items-center gap-3">
                        <span
                            class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </span>
                        <div>
                            <h5
                                class="font-semibold text-gray-800 dark:text-white/90"
                            >
                                Workspace
                            </h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Name and subdomain for the tenant.
                            </p>
                        </div>
                    </div>

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
                                    v-model="form.subdomain"
                                    type="text"
                                    class="dark:bg-dark-900 h-11 w-full rounded-l-lg border border-r-0 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:outline-hidden dark:bg-gray-900 dark:text-white/90"
                                    :class="
                                        form.errors.subdomain
                                            ? 'border-red-500'
                                            : 'border-gray-300 dark:border-gray-700'
                                    "
                                    placeholder="abchigh"
                                    @input="
                                        form.subdomain = form.subdomain
                                            .toLowerCase()
                                            .replace(/[^a-z0-9-]/g, '')
                                    "
                                />
                                <span
                                    class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    .{{ centralDomain }}
                                </span>
                            </div>
                            <p
                                v-if="form.errors.subdomain"
                                class="mt-1.5 text-sm text-red-500"
                            >
                                {{ form.errors.subdomain }}
                            </p>
                            <p
                                v-else-if="form.subdomain"
                                class="mt-1.5 text-xs text-gray-400"
                            >
                                Workspace URL:
                                <span class="text-brand-500 font-medium"
                                    >{{ form.subdomain }}.{{
                                        centralDomain
                                    }}</span
                                >
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Owner Account -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-5 flex items-center gap-3">
                        <span
                            class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="8" r="4" />
                                <path
                                    d="M4 21a8 8 0 0 1 16 0"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </span>
                        <div>
                            <h5
                                class="font-semibold text-gray-800 dark:text-white/90"
                            >
                                Owner Account
                            </h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Login credentials for the tenant owner.
                            </p>
                        </div>
                    </div>

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

                <!-- Address -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-5 flex items-center gap-3">
                        <span
                            class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M12 21s-7-6.5-7-11a7 7 0 0 1 14 0c0 4.5-7 11-7 11Z"
                                    stroke-linejoin="round"
                                />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>
                        </span>
                        <div>
                            <h5
                                class="font-semibold text-gray-800 dark:text-white/90"
                            >
                                Address
                            </h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Optional location details.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-3">
                      <FormTexera
                          v-model="form.address"
                        
                          placeholder="House, Road, Area"
                          :rows="2"
                          class="lg:col-span-3"
                          :error="form.errors.address"
                      />

                      <FormInput
                          v-model="form.city"
                          label="City/State"
                          placeholder="e.g. Chattogram"
                          :error="form.errors.city"
                      />

                      <FormInput
                          v-model="form.country"
                          label="Country"
                          placeholder="e.g. Bangladesh"
                          :error="form.errors.country"
                      />

                      <FormInput
                          v-model="form.postal_code"
                          label="Postal Code"
                          placeholder="e.g. 4000"
                          :error="form.errors.postal_code"
                      />
                  </div>
                </section>

                <!-- Social -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="mb-5 flex items-center gap-3">
                        <span
                            class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M4 12a8 8 0 1 0 16 0 8 8 0 0 0-16 0ZM4 12h16M12 4c2.5 2.5 2.5 13 0 16M12 4c-2.5 2.5-2.5 13 0 16"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </span>
                        <div>
                            <h5
                                class="font-semibold text-gray-800 dark:text-white/90"
                            >
                                Social Links
                            </h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Optional profiles.
                            </p>
                        </div>
                    </div>

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

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="route('admin.tenants.index')"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-opacity="0.25"
                            />
                            <path
                                d="M12 2a10 10 0 0 1 10 10"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                            />
                        </svg>
                        {{ form.processing ? 'Creating...' : 'Create Tenant' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import FormInput from '@/Components/ui/FormInput.vue';
import FormTexera from '@/Components/ui/FormTexera.vue';
import PhoneInput from '@/Components/ui/PhoneInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    centralDomain: { type: String, default: 'platform.com' },
});

const form = useForm({
    name: '',
    subdomain: '',
    owner_first_name: '',
    owner_last_name: '',
    owner_email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    country: '',
    city: '',
    postal_code: '',
    facebook: '',
    twitter: '',
    linkedin: '',
    address: '',
});

const submit = () => {
    form.post(route('admin.tenants.store'));
};
</script>
