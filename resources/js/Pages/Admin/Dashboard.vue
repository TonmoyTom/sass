<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'
import {
    LayoutDashboard, Users, Building2, Wallet, TrendingUp,
    AlertCircle, ChevronRight, LogOut, Settings, Bell,
    Package, BarChart3, CreditCard
} from 'lucide-vue-next'

defineProps<{
    stats: {
        total_tenants: number
        active_tenants: number
        trial_tenants: number
        total_sellers: number
        active_sellers: number
        pending_sellers: number
        monthly_revenue: number
        pending_withdrawals: number
    }
    recentTenants: any[]
    topSellers: any[]
    recentActivity: any[]
}>()

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 0 }).format(val)
}

const logout = () => {
    router.post(route('logout'))
}
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <div>
            <p>Hello</p>
        </div>
    </AdminLayout>

</template>
