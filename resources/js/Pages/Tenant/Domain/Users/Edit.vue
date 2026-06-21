<template>
    <WorkspaceLayout title="Edit User">
        <div class="mx-auto">
            <!-- header -->
            <div class="mb-6 flex items-center gap-3">
                <a
                    href="/users"
                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M19 12H5M12 19l-7-7 7-7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </a>
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-white/90"
                    >
                        Edit User
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Update user details and roles.
                    </p>
                </div>
            </div>

            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <!-- basic info -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="mb-5 font-semibold text-gray-800 dark:text-white/90"
                    >
                        User Information
                    </h5>

                    <!-- avatar -->
                    <div class="mb-6 flex items-center gap-5">
                        <div
                            class="bg-brand-50 dark:bg-brand-900/20 flex h-20 w-20 flex-shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 dark:border-gray-700"
                        >
                            <img
                                v-if="avatarPreview || user.avatar_url"
                                :src="avatarPreview || user.avatar_url"
                                alt=""
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
                            :error="form.errors.phone"
                        />
                        <FormInput
                            v-model="form.password"
                            label="New Password"
                            type="password"
                            placeholder="Leave blank to keep"
                            :error="form.errors.password"
                        />
                        <FormInput
                            v-model="form.password_confirmation"
                            label="Confirm Password"
                            type="password"
                        />
                    </div>
                </section>

                <!-- roles -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <h5
                        class="mb-1 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Roles
                    </h5>
                    <p class="mb-5 text-xs text-gray-400">
                        {{ form.roles.length }} selected
                    </p>

                    <div
                        v-if="roles.length"
                        class="grid grid-cols-1 gap-2 sm:grid-cols-2"
                    >
                        <label
                            v-for="role in roles"
                            :key="role"
                            class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-200 px-3 py-2.5 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/[0.03]"
                            :class="
                                form.roles.includes(role)
                                    ? 'border-brand-300 bg-brand-50/40 dark:border-brand-700 dark:bg-brand-900/10'
                                    : ''
                            "
                        >
                            <input
                                v-model="form.roles"
                                type="checkbox"
                                :value="role"
                                class="text-brand-500 focus:ring-brand-500/20 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                            />
                            <span
                                class="text-sm font-medium text-gray-700 capitalize dark:text-gray-300"
                                >{{ role }}</span
                            >
                        </label>
                    </div>

                    <div v-else class="py-6 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No roles yet.
                        </p>
                        <a
                            href="/roles/create"
                            class="text-brand-600 dark:text-brand-400 mt-1 inline-block text-sm font-medium hover:underline"
                            >Create a role first</a
                        >
                    </div>

                    <p
                        v-if="form.errors.roles"
                        class="mt-3 text-sm text-red-500"
                    >
                        {{ form.errors.roles }}
                    </p>
                </section>

                <!-- actions -->
                <div class="flex items-center justify-end gap-3">
                    <a
                        href="/users"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand-500 hover:bg-brand-600 rounded-lg px-6 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import FormInput from '@/Components/ui/FormInput.vue';
import PhoneInput from '@/Components/ui/PhoneInput.vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    user: Object,
    roles: Array,
});

const avatarPreview = ref(null);

const initials = computed(() =>
    ((props.user.first_name ?? '') + ' ' + (props.user.last_name ?? ''))
        .trim()
        .split(' ')
        .map((p) => p[0])
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);

const form = useForm({
    _method: 'put',
    first_name: props.user.first_name ?? '',
    last_name: props.user.last_name ?? '',
    email: props.user.email ?? '',
    phone: props.user.phone ?? '',
    password: '',
    password_confirmation: '',
    avatar: null,
    roles: [...(props.user.roles ?? [])],
});

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const submit = () => {
    form.post(`/users/${props.user.id}`, {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>
