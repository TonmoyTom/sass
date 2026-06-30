<template>
    <AdminLayout title="New Module Request">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    Create Module Request
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Seller select kore, je module sell korar request banaite
                    chao seita select koro.
                </p>
            </div>
            <Link
                :href="route('admin.module-requests.index')"
                class="text-sm font-medium text-gray-600 hover:underline dark:text-gray-400"
            >
                ← Back to list
            </Link>
        </div>

        <!-- Seller select -->
        <div
            class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <label
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Seller
            </label>
            <select
                v-model="form.seller_id"
                class="w-full max-w-md rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            >
                <option value="" disabled>Select seller</option>
                <option v-for="s in sellers" :key="s.id" :value="s.id">
                    {{ s.name }} ({{ s.email }})
                </option>
            </select>
            <p v-if="form.errors.seller_id" class="mt-1 text-xs text-red-500">
                {{ form.errors.seller_id }}
            </p>
        </div>

        <!-- Module cards -->
        <div class="mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Select Module
            </h4>
            <p v-if="form.errors.module_id" class="mt-1 text-xs text-red-500">
                {{ form.errors.module_id }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="m in modules"
                :key="m.id"
                @click="selectModule(m)"
                class="flex cursor-pointer flex-col rounded-2xl border bg-white p-5 transition dark:bg-white/[0.03]"
                :class="
                    form.module_id === m.id
                        ? 'border-brand-500 ring-brand-500/20 ring-2'
                        : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700'
                "
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
                            >{{ m.module_category }}</span
                        >
                    </div>
                    <div
                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                        :class="
                            form.module_id === m.id
                                ? 'bg-brand-500 border-brand-500'
                                : 'border-gray-300 dark:border-gray-600'
                        "
                    >
                        <svg
                            v-if="form.module_id === m.id"
                            class="h-3 w-3 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="3"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                </div>

                <p
                    v-if="m.description"
                    class="mb-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-400"
                >
                    {{ m.description }}
                </p>

                <span
                    class="bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 mt-auto inline-block w-fit rounded-full px-2.5 py-1 text-xs font-medium"
                >
                    {{ m.alias }}
                </span>
            </div>

            <div
                v-if="!modules.length"
                class="col-span-full py-10 text-center text-gray-500 dark:text-gray-400"
            >
                No modules available right now.
            </div>
        </div>

        <!-- Note + actions -->
        <div
            class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <label
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Note (optional)
            </label>
            <textarea
                v-model="form.note"
                rows="3"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                placeholder="Karon ba context likho..."
            ></textarea>

            <label
                class="mt-4 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"
            >
                <input
                    type="checkbox"
                    v-model="form.auto_approve"
                    class="rounded border-gray-300"
                />
                Directly approve kore dao (pending e rakhbe na)
            </label>

            <div class="mt-5 flex justify-end gap-3">
                <Link
                    :href="route('admin.module-requests.index')"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-400"
                >
                    Cancel
                </Link>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                >
                    Create Request
                </button>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    sellers: Array,
    modules: Array,
});

const form = useForm({
    seller_id: '',
    module_id: '',
    note: '',
    auto_approve: false,
});

const selectModule = (m) => {
    form.module_id = m.id;
};

const submit = () => {
    form.post(route('admin.module-requests.store'));
};
</script>
