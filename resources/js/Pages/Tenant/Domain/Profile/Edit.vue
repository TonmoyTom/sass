<template>
    <WorkspaceLayout title="Profile">
        <div class="mx-auto max-w-3xl">
            <!-- header -->
            <div class="mb-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Profile
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage your personal information.
                </p>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submitProfile">
                <!-- Avatar + Basic -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Basic Information
                    </h5>

                    <div class="mb-6 flex items-center gap-5">
                        <div
                            class="bg-brand-50 dark:bg-brand-900/20 flex h-20 w-20 flex-shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 dark:border-gray-700"
                        >
                            <img
                                v-if="avatarPreview || user.avatar"
                                :src="avatarPreview || avatarUrl"
                                alt="Avatar"
                                class="h-full w-full object-cover"
                            />
                            <span
                                v-else
                                class="text-brand-600 dark:text-brand-400 text-2xl font-bold"
                                >{{ initials }}</span
                            >
                        </div>
                        <div>
                            <label
                                class="inline-block cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                            >
                                Change Avatar
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="onAvatarChange"
                                />
                            </label>
                            <p class="mt-1.5 text-xs text-gray-400">
                                PNG, JPG. Max 2MB.
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2"
                    >
                        <FormInput
                            v-model="profileForm.first_name"
                            label="First Name"
                            :error="profileForm.errors.first_name"
                        />
                        <FormInput
                            v-model="profileForm.last_name"
                            label="Last Name"
                            :error="profileForm.errors.last_name"
                        />
                        <FormInput
                            v-model="profileForm.name"
                            label="Display Name"
                            :error="profileForm.errors.name"
                        />
                        <FormInput
                            v-model="profileForm.email"
                            label="Email"
                            type="email"
                            :error="profileForm.errors.email"
                        />
                        <div class="sm:col-span-2">
                            <PhoneInput
                                v-model="profileForm.phone"
                                label="Phone"
                                :error="profileForm.errors.phone"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >Bio</label
                            >
                            <textarea
                                v-model="profileForm.bio"
                                rows="3"
                                class="focus:border-brand-300 focus:ring-brand-500/10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-2 dark:border-gray-700 dark:text-white/90"
                                placeholder="Tell us about yourself"
                            />
                            <p
                                v-if="profileForm.errors.bio"
                                class="mt-1.5 text-sm text-red-500"
                            >
                                {{ profileForm.errors.bio }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Address -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Address
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-3"
                    >
                        <FormInput
                            v-model="profileForm.country"
                            label="Country"
                            :error="profileForm.errors.country"
                        />
                        <FormInput
                            v-model="profileForm.city"
                            label="City"
                            :error="profileForm.errors.city"
                        />
                        <FormInput
                            v-model="profileForm.postal_code"
                            label="Postal Code"
                            :error="profileForm.errors.postal_code"
                        />
                    </div>
                </section>

                <!-- Social -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Social Links
                    </h5>
                    <div
                        class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2"
                    >
                        <FormInput
                            v-model="profileForm.facebook"
                            label="Facebook"
                            :error="profileForm.errors.facebook"
                        />
                        <FormInput
                            v-model="profileForm.twitter"
                            label="Twitter"
                            :error="profileForm.errors.twitter"
                        />
                        <FormInput
                            v-model="profileForm.linkedin"
                            label="LinkedIn"
                            :error="profileForm.errors.linkedin"
                        />
                        <FormInput
                            v-model="profileForm.instagram"
                            label="Instagram"
                            :error="profileForm.errors.instagram"
                        />
                    </div>
                </section>

                <!-- save profile -->
                <div class="flex items-center justify-end gap-3">
                    <span
                        v-if="profileSaved"
                        class="text-sm font-medium text-green-600 dark:text-green-400"
                        >✓ Saved</span
                    >
                    <button
                        type="submit"
                        :disabled="profileForm.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-6 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{
                            profileForm.processing
                                ? 'Saving...'
                                : 'Save Profile'
                        }}
                    </button>
                </div>
            </form>

            <!-- Password (alada form) -->
            <form
                class="mt-5 flex flex-col gap-5"
                @submit.prevent="submitPassword"
            >
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Change Password
                    </h5>
                    <p class="mb-5 text-xs text-gray-400">
                        Update your account password.
                    </p>
                    <div
                        class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2"
                    >
                        <div class="sm:col-span-2">
                            <FormInput
                                v-model="passwordForm.current_password"
                                label="Current Password"
                                type="password"
                                :error="passwordForm.errors.current_password"
                            />
                        </div>
                        <FormInput
                            v-model="passwordForm.password"
                            label="New Password"
                            type="password"
                            :error="passwordForm.errors.password"
                        />
                        <FormInput
                            v-model="passwordForm.password_confirmation"
                            label="Confirm Password"
                            type="password"
                            :error="passwordForm.errors.password_confirmation"
                        />
                    </div>
                    <div class="mt-5 flex items-center justify-end gap-3">
                        <span
                            v-if="passwordSaved"
                            class="text-sm font-medium text-green-600 dark:text-green-400"
                            >✓ Updated</span
                        >
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                        >
                            {{
                                passwordForm.processing
                                    ? 'Updating...'
                                    : 'Update Password'
                            }}
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import FormInput from '@/Components/ui/FormInput.vue';
import PhoneInput from '@/Components/ui/PhoneInput.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    user: Object,
});

const avatarPreview = ref(null);
const avatarUrl = props.user?.avatar ? props.user.avatar : null;
const profileSaved = ref(false);
const passwordSaved = ref(false);
const initials = computed(() =>
    (props.user?.name ?? '')
        .split(' ')
        .map((p) => p[0])
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);

const profileForm = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    phone: props.user?.phone ?? '',
    first_name: props.user?.info?.first_name ?? '',
    last_name: props.user?.info?.last_name ?? '',
    bio: props.user?.info?.bio ?? '',
    country: props.user?.info?.country ?? '',
    city: props.user?.info?.city ?? '',
    postal_code: props.user?.info?.postal_code ?? '',
    facebook: props.user?.info?.facebook ?? '',
    twitter: props.user?.info?.twitter ?? '',
    linkedin: props.user?.info?.linkedin ?? '',
    instagram: props.user?.info?.instagram ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitProfile = () => {
    profileForm.post('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            profileSaved.value = true;
            setTimeout(() => (profileSaved.value = false), 2000);
        },
    });
};

const submitPassword = () => {
    passwordForm.post('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordSaved.value = true;
            passwordForm.reset();
            setTimeout(() => (passwordSaved.value = false), 2000);
        },
    });
};

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    avatarPreview.value = URL.createObjectURL(file);

    router.post(
        '/profile/avatar',
        { avatar: file },
        {
            preserveScroll: true,
            forceFormData: true,
        },
    );
};
</script>
