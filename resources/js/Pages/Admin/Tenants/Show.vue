<template>
    <AdminLayout>
        <div class="mx-auto ">
            <!-- Header -->
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.tenants.index')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                d="M15 18l-6-6 6-6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </Link>
                    <div>
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-white/90"
                        >
                            {{ tenant.name }}
                        </h3>
                        <a
                            v-if="tenant.domain"
                            :href="`http://${tenant.domain}`"
                            target="_blank"
                            class="text-brand-500 text-sm hover:underline"
                        >
                            {{ tenant.domain }}
                        </a>
                    </div>
                    <span
                        :class="statusClass(tenant.status)"
                        class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                    >
                        {{ tenant.status }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.tenants.edit', tenant.id)"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="tenant.status !== 'suspended'"
                        @click="suspend"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                    >
                        Suspend
                    </button>
                    <button
                        v-else
                        @click="reactivate"
                        class="rounded-lg border border-green-300 px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50 dark:border-green-800"
                    >
                        Reactivate
                    </button>
                    <button
                        @click="destroy"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <!-- Cards -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- Workspace -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Workspace Details
                    </h5>
                    <dl class="space-y-3">
                        <Row label="Subdomain" :value="tenant.subdomain" />
                        <Row
                            label="Database"
                            :value="tenant.database_name"
                            mono
                        />
                        <Row label="Email" :value="tenant.email" />
                        <Row label="Phone" :value="tenant.phone" />
                        <Row label="Trial Ends" :value="tenant.trial_ends_at" />
                        <Row label="Created" :value="tenant.created_at" />
                        <Row
                            v-if="tenant.suspended_at"
                            label="Suspended At"
                            :value="tenant.suspended_at"
                        />
                    </dl>
                </section>

                <!-- Owner -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Owner
                    </h5>
                    <dl class="space-y-3">
                        <Row label="Name" :value="tenant.owner.name" />
                        <Row label="Email" :value="tenant.owner.email" />
                        <Row label="Phone" :value="tenant.owner.phone" />
                        <Row label="Bio" :value="tenant.owner.bio" />
                    </dl>
                </section>

                <!-- Address -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Address
                    </h5>
                    <dl class="space-y-3">
                        <Row label="Country" :value="tenant.owner.country" />
                        <Row label="City/State" :value="tenant.owner.city" />
                        <Row
                            label="Postal Code"
                            :value="tenant.owner.postal_code"
                        />
                    </dl>
                </section>

                <!-- Social -->
                <section
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <h5
                        class="mb-4 font-semibold text-gray-800 dark:text-white/90"
                    >
                        Social Links
                    </h5>
                    <dl class="space-y-3">
                        <RowLink
                            label="Facebook"
                            :value="tenant.owner.facebook"
                        />
                        <RowLink label="X.com" :value="tenant.owner.twitter" />
                        <RowLink
                            label="Linkedin"
                            :value="tenant.owner.linkedin"
                        />
                        <RowLink
                            label="Instagram"
                            :value="tenant.owner.instagram"
                        />
                    </dl>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { h } from 'vue';

const props = defineProps({
    tenant: Object,
});

// inline Row (text value)
const Row = (p) =>
    h('div', { class: 'flex justify-between gap-4' }, [
        h('dt', { class: 'text-sm text-gray-500 dark:text-gray-400' }, p.label),
        h(
            'dd',
            {
                class: [
                    'text-sm font-medium text-gray-800 dark:text-white/90 text-right',
                    p.mono ? 'font-mono text-xs' : '',
                ],
            },
            p.value || '—',
        ),
    ]);

// inline RowLink (clickable link value)
const RowLink = (p) =>
    h('div', { class: 'flex justify-between gap-4' }, [
        h('dt', { class: 'text-sm text-gray-500 dark:text-gray-400' }, p.label),
        p.value
            ? h(
                  'a',
                  {
                      href: p.value,
                      target: '_blank',
                      rel: 'noopener',
                      class: 'max-w-[180px] truncate text-right text-sm font-medium text-brand-500 hover:underline',
                  },
                  p.value,
              )
            : h(
                  'dd',
                  { class: 'text-right text-sm font-medium text-gray-400' },
                  '—',
              ),
    ]);

const statusClass = (status) =>
    ({
        active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        trial: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        suspended:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        expired:
            'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    })[status] ?? 'bg-gray-100 text-gray-700';

const suspend = () => {
    if (!confirm(`Suspend "${props.tenant.name}"?`)) return;
    router.post(
        route('admin.tenants.suspend', props.tenant.id),
        {},
        { preserveScroll: true },
    );
};

const reactivate = () => {
    router.post(
        route('admin.tenants.reactivate', props.tenant.id),
        {},
        { preserveScroll: true },
    );
};

const destroy = () => {
    if (
        !confirm(
            `Delete "${props.tenant.name}"? Eta tenant database-o delete kore dibe. Undo kora jabe na.`,
        )
    )
        return;
    router.delete(route('admin.tenants.destroy', props.tenant.id));
};
</script>
