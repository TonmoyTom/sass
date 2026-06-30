<template>
    <AdminLayout title="Edit Staff">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-white/90"
                >
                    Edit Staff
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Update the staff account's information. Leave the password
                    field blank to keep it unchanged.
                </p>
            </div>
            <Link
                :href="route('admin.staff.index')"
                class="text-sm font-medium text-gray-600 hover:underline dark:text-gray-400"
            >
                ← Back to list
            </Link>
        </div>

        <div
            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <!-- Avatar -->
            <section class="border-b border-gray-100 p-6 dark:border-gray-800">
                <label
                    class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Avatar
                </label>
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border border-dashed border-gray-300 bg-gray-50 text-xs text-gray-400 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            class="h-full w-full object-cover"
                        />
                        <span v-else>No photo</span>
                    </div>
                    <label
                        class="cursor-pointer rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]"
                    >
                        Choose Photo
                        <input
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="hidden"
                            @change="onAvatarChange"
                        />
                    </label>
                </div>
                <p v-if="form.errors.avatar" class="mt-2 text-xs text-red-500">
                    {{ form.errors.avatar }}
                </p>
            </section>

            <!-- Basic info -->
            <section
                class="space-y-5 border-b border-gray-100 p-6 dark:border-gray-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-800 dark:text-white/90"
                >
                    Basic Information
                </h4>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <Field label="First Name" :error="form.errors.first_name">
                        <input
                            v-model="form.first_name"
                            type="text"
                            :class="inputClass"
                        />
                    </Field>

                    <Field label="Last Name">
                        <input
                            v-model="form.last_name"
                            type="text"
                            :class="inputClass"
                        />
                    </Field>

                    <Field label="Email" :error="form.errors.email">
                        <input
                            v-model="form.email"
                            type="email"
                            :class="inputClass"
                        />
                    </Field>

                    <PhoneInput
                        v-model="form.phone"
                        label="Phone (optional)"
                        :error="form.errors.phone"
                    />

                    <Field label="Status">
                        <select v-model="form.status" :class="inputClass">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </Field>
                </div>

                <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                    <p
                        class="mb-3 text-xs font-medium text-gray-500 dark:text-gray-400"
                    >
                        Change Password (optional)
                    </p>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <Field
                            label="New Password"
                            :error="form.errors.password"
                        >
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Leave blank to keep current"
                                :class="inputClass"
                            />
                        </Field>

                        <Field label="Confirm New Password">
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Retype new password"
                                :class="inputClass"
                            />
                        </Field>
                    </div>
                </div>
            </section>

            <!-- Address -->
            <section class="space-y-5 p-6">
                <div class="flex items-center gap-2">
                    <MapPin class="h-4 w-4 text-gray-400" />
                    <h4
                        class="text-sm font-semibold text-gray-800 dark:text-white/90"
                    >
                        Address
                    </h4>
                    <span class="text-xs font-normal text-gray-400"
                        >(optional)</span
                    >
                </div>

                <FormTexera
                    v-model="form.address"
                    placeholder="House, Road, Area"
                    :rows="2"
                    :error="form.errors.address"
                />

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <Field label="City" :error="form.errors.city">
                        <input
                            v-model="form.city"
                            type="text"
                            placeholder="e.g. Chattogram"
                            :class="inputClass"
                        />
                    </Field>

                    <Field label="Country" :error="form.errors.country">
                        <input
                            v-model="form.country"
                            type="text"
                            placeholder="e.g. Bangladesh"
                            :class="inputClass"
                        />
                    </Field>

                    <Field label="Postal Code" :error="form.errors.postal_code">
                        <input
                            v-model="form.postal_code"
                            type="text"
                            placeholder="e.g. 4000"
                            :class="inputClass"
                        />
                    </Field>
                </div>
            </section>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <Link
                :href="route('admin.staff.index')"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-400"
            >
                Cancel
            </Link>
            <button
                @click="submit"
                :disabled="form.processing"
                class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
            >
                Update Staff
            </button>
        </div>
    </AdminLayout>
</template>

<script setup>
import FormTexera from '@/Components/ui/FormTexera.vue';
import PhoneInput from '@/Components/ui/PhoneInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import { h, ref } from 'vue';

const props = defineProps({
    staff: Object,
});

const avatarPreview = ref(
    props.staff.avatar ? `/storage/${props.staff.avatar}` : null,
);

const inputClass =
    'w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500/20 focus:ring-2 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white';

const Field = (props, { slots }) =>
    h('div', [
        h(
            'label',
            {
                class: 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300',
            },
            props.label,
        ),
        slots.default?.(),
        props.error
            ? h('p', { class: 'mt-1 text-xs text-red-500' }, props.error)
            : null,
    ]);

const form = useForm({
    first_name: props.staff.first_name,
    last_name: props.staff.last_name,
    email: props.staff.email,
    phone: props.staff.phone,
    status: props.staff.status,
    password: '',
    password_confirmation: '',
    avatar: null,
    address: props.staff.address,
    city: props.staff.city,
    country: props.staff.country,
    postal_code: props.staff.postal_code,
    _method: 'put',
});

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const submit = () => {
    form.post(route('admin.staff.update', props.staff.id), {
        forceFormData: true,
    });
};
</script>
