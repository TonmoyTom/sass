<template>
    <WorkspaceLayout title="Settings">
        <div
            class="min-h-screen bg-gray-50 px-4 sm:px-6 lg:px-8 dark:bg-gray-900"
        >
            <div class="mx-auto">
                <!-- header -->
                <div class="mb-8">
                    <h3
                        class="text-2xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Settings
                    </h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                        Manage your company details and preferences.
                    </p>
                </div>

                <form class="flex flex-col gap-5" @submit.prevent="submit">
                    <!-- Logo + Identity -->
                    <section
                        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h5
                            class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                        >
                            Company Identity
                        </h5>
                        <p class="mb-5 text-xs text-gray-400">
                            Your business name and logo.
                        </p>

                        <!-- logo -->
                        <div class="mb-6 flex items-center gap-5">
                            <div
                                class="flex h-20 w-20 flex-shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <img
                                    v-if="logoPreview || settings?.logo"
                                    :src="logoPreview || logoUrl"
                                    alt="Logo"
                                    class="h-full w-full object-cover"
                                />
                                <svg
                                    v-else
                                    class="h-8 w-8 text-gray-300 dark:text-gray-600"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <rect
                                        x="3"
                                        y="3"
                                        width="18"
                                        height="18"
                                        rx="2"
                                    />
                                    <circle cx="9" cy="9" r="2" />
                                    <path d="M21 15l-5-5L5 21" />
                                </svg>
                            </div>
                            <div>
                                <label
                                    class="inline-block cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    Upload Logo
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="onLogoChange"
                                    />
                                </label>
                                <p class="mt-1.5 text-xs text-gray-400">
                                    PNG, JPG, SVG. Max 2MB.
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2"
                        >
                            <FormInput
                                v-model="form.company_name"
                                label="Company Name"
                                :error="form.errors.company_name"
                            />
                            <FormInput
                                v-model="form.legal_name"
                                label="Legal Name"
                                :error="form.errors.legal_name"
                            />
                            <div class="sm:col-span-2">
                                <FormInput
                                    v-model="form.tagline"
                                    label="Tagline"
                                    :error="form.errors.tagline"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Contact -->
                    <section
                        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h5
                            class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                        >
                            Contact
                        </h5>
                        <p class="mb-5 text-xs text-gray-400">
                            How people reach you.
                        </p>
                        <div
                            class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2"
                        >
                            <FormInput
                                v-model="form.email"
                                label="Email"
                                type="email"
                                :error="form.errors.email"
                            />
                            <PhoneInput
                                v-model="form.phone"
                                label="Phone"
                                :error="form.errors.phone"
                            />

                            <!-- alt phone — PhoneInput -->
                            <PhoneInput
                                v-model="form.alt_phone"
                                label="Alternate Phone"
                                :error="form.errors.alt_phone"
                            />
                            <FormInput
                                v-model="form.website"
                                label="Website"
                                :error="form.errors.website"
                            />
                        </div>
                    </section>

                    <!-- Address -->
                    <section
                        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h5
                            class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                        >
                            Address
                        </h5>
                        <p class="mb-5 text-xs text-gray-400">
                            Your business location.
                        </p>
                        <div
                            class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2"
                        >
                            <div class="sm:col-span-2">
                                <FormInput
                                    v-model="form.address_line1"
                                    label="Address Line 1"
                                    :error="form.errors.address_line1"
                                />
                            </div>
                            <div class="sm:col-span-2">
                                <FormInput
                                    v-model="form.address_line2"
                                    label="Address Line 2"
                                    :error="form.errors.address_line2"
                                />
                            </div>
                            <FormInput
                                v-model="form.city"
                                label="City"
                                :error="form.errors.city"
                            />
                            <FormInput
                                v-model="form.state"
                                label="State / Division"
                                :error="form.errors.state"
                            />
                            <FormInput
                                v-model="form.postal_code"
                                label="Postal Code"
                                :error="form.errors.postal_code"
                            />
                            <FormInput
                                v-model="form.country"
                                label="Country"
                                :error="form.errors.country"
                            />
                        </div>
                    </section>

                    <!-- Legal -->
                    <section
                        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h5
                            class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                        >
                            Business & Legal
                        </h5>
                        <p class="mb-5 text-xs text-gray-400">
                            Registration and tax details.
                        </p>
                        <div
                            class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-3"
                        >
                            <FormInput
                                v-model="form.registration_no"
                                label="Trade License No."
                                :error="form.errors.registration_no"
                            />
                            <FormInput
                                v-model="form.tax_id"
                                label="TIN / BIN"
                                :error="form.errors.tax_id"
                            />
                            <FormInput
                                v-model="form.vat_no"
                                label="VAT No."
                                :error="form.errors.vat_no"
                            />
                        </div>
                    </section>

                    <!-- Localization -->
                    <section
                        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h5
                            class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                        >
                            Localization
                        </h5>
                        <p class="mb-5 text-xs text-gray-400">
                            Currency, timezone and formats.
                        </p>
                        <div
                            class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-3"
                        >
                            <FormInput
                                v-model="form.currency"
                                label="Currency"
                                :error="form.errors.currency"
                            />
                            <FormInput
                                v-model="form.currency_symbol"
                                label="Currency Symbol"
                                :error="form.errors.currency_symbol"
                            />
                            <FormInput
                                v-model="form.timezone"
                                label="Timezone"
                                :error="form.errors.timezone"
                            />
                            <FormInput
                                v-model="form.locale"
                                label="Locale"
                                :error="form.errors.locale"
                            />
                            <FormInput
                                v-model="form.date_format"
                                label="Date Format"
                                :error="form.errors.date_format"
                            />
                        </div>
                    </section>

                    <!-- Social -->
                    <section
                        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h5
                            class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                        >
                            Social
                        </h5>
                        <p class="mb-5 text-xs text-gray-400">
                            Your social media handles.
                        </p>
                        <div
                            class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-3"
                        >
                            <FormInput
                                v-model="form.facebook"
                                label="Facebook"
                                :error="form.errors.facebook"
                            />
                            <FormInput
                                v-model="form.instagram"
                                label="Instagram"
                                :error="form.errors.instagram"
                            />
                            <FormInput
                                v-model="form.whatsapp"
                                label="WhatsApp"
                                :error="form.errors.whatsapp"
                            />
                        </div>
                    </section>

                    <!-- actions -->
                    <div
                        class="sticky bottom-4 flex items-center justify-end gap-3 rounded-2xl border border-gray-200 bg-white/80 px-5 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80"
                    >
                        <span
                            v-if="saved"
                            class="text-sm font-medium text-green-600 dark:text-green-400"
                            >✓ Saved</span
                        >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-brand-500 hover:bg-brand-600 rounded-lg px-6 py-2.5 text-sm font-medium text-white transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import FormInput from '@/Components/ui/FormInput.vue';
import PhoneInput from '@/Components/ui/PhoneInput.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
    tenant: Object,
    user: {
        type: Object,
        default: () => ({ name: '', email: '' }),
    },
});

const logoPreview = ref(null);
const logoUrl = props.settings?.logo_url ?? props.settings?.logo ?? null;
const saved = ref(false);

const form = useForm({
    company_name: props.settings?.company_name ?? '',
    legal_name: props.settings?.legal_name ?? '',
    tagline: props.settings?.tagline ?? '',
    email: props.settings?.email ?? '',
    phone: props.settings?.phone ?? '',
    alt_phone: props.settings?.alt_phone ?? '',
    website: props.settings?.website ?? '',
    address_line1: props.settings?.address_line1 ?? '',
    address_line2: props.settings?.address_line2 ?? '',
    city: props.settings?.city ?? '',
    state: props.settings?.state ?? '',
    postal_code: props.settings?.postal_code ?? '',
    country: props.settings?.country ?? 'Bangladesh',
    registration_no: props.settings?.registration_no ?? '',
    tax_id: props.settings?.tax_id ?? '',
    vat_no: props.settings?.vat_no ?? '',
    currency: props.settings?.currency ?? 'BDT',
    currency_symbol: props.settings?.currency_symbol ?? '৳',
    timezone: props.settings?.timezone ?? 'Asia/Dhaka',
    locale: props.settings?.locale ?? 'en',
    date_format: props.settings?.date_format ?? 'd M Y',
    facebook: props.settings?.facebook ?? '',
    instagram: props.settings?.instagram ?? '',
    whatsapp: props.settings?.whatsapp ?? '',
});

const submit = () => {
    form.post('/settings', {
        preserveScroll: true,
        onSuccess: () => {
            saved.value = true;
            setTimeout(() => (saved.value = false), 2000);
        },
    });
};

const onLogoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    logoPreview.value = URL.createObjectURL(file);

    router.post(
        '/settings/logo',
        { logo: file },
        { preserveScroll: true, forceFormData: true },
    );
};
</script>
