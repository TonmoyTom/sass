<template>
    <div
        v-if="$page.props.impersonating"
        class="flex h-10 items-center justify-between bg-gradient-to-r from-blue-600 to-indigo-600 px-4 text-sm font-medium text-white"
    >
        <span>
            ⚠️ {{ impersonatorName }} is logged in as
            <strong>{{ currentUserName }}</strong>.
        </span>
        <button
            @click="stopImpersonating"
            class="cursor-pointer rounded bg-blue-900 px-3 py-1 text-xs font-medium text-white hover:bg-blue-800"
        >
            Exit Impersonation
        </button>
    </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const currentUserName = computed(() => page.props.auth?.user?.name ?? 'this user');
const impersonatorName = computed(() => page.props.impersonator?.name ?? 'Admin');

const stopImpersonating = () => {
    router.post(route('impersonate.stop'));
};
</script>
