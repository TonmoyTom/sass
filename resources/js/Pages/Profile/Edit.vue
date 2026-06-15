<template>
    <component :is="layout">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">
                Profile
            </h3>
            <PHeader :user="user" />
            <Personal :user="user" :must-verify-email="mustVerifyEmail" :status="status" />
            <Address :user="user" />
        </div>
    </component>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SellerLayout from '@/Layouts/SellerLayout.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';

import Address from '../../Components/ui/Profle/Address.vue';
import PHeader from '../../Components/ui/Profle/PHeader.vue';
import Personal from '../../Components/ui/Profle/Personal.vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
});

const page = usePage();
const layout = computed(() => {
    const type = page.props.auth?.user?.user_type;
    if (type === 'seller') return SellerLayout;
    if (type === 'tenant_owner') return TenantLayout;
    return AdminLayout;
});
</script>
