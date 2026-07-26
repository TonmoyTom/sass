<template>
    <WorkspaceLayout title="Profile">
        <div class="mx-auto max-w-2xl">
            <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">
                Notifications
                <span v-if="unread_count > 0" class="ml-2 text-sm font-normal text-gray-500">
                    ({{ unread_count }} unread)
                </span>
            </h3>

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div v-if="notifications.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="n in notifications"
                        :key="n.source + n.id"
                        class="flex items-start gap-3 p-4"
                        :class="!n.read ? 'bg-brand-50/40 dark:bg-brand-900/10' : ''"
                    >
                        <span
                            class="mt-1 h-2 w-2 shrink-0 rounded-full"
                            :class="!n.read ? 'bg-brand-500' : 'bg-transparent'"
                        ></span>

                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm text-gray-800 dark:text-white/90">{{ n.message }}</p>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-medium uppercase"
                                    :class="n.source === 'central'
                                        ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                                >
                                    {{ n.source }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">{{ n.created_at }}</p>
                        </div>

                        <button
                            v-if="!n.read"
                            @click="markRead(n)"
                            class="text-xs text-brand-500 hover:underline"
                        >
                            Mark read
                        </button>
                    </div>
                </div>
                <p v-else class="py-10 text-center text-sm text-gray-400">
                    No notifications yet.
                </p>
            </div>
        </div>
    </WorkspaceLayout>
</template>

<script setup>
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    notifications: Array,
    unread_count: Number,
});

const markRead = (n) => {
    router.post(route('tenant.notifications.read', n.id), { source: n.source }, {
        preserveScroll: true,
    });
};
</script>
