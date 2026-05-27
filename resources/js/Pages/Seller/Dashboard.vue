<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import {
    LayoutDashboard, Users, Wallet, Copy, Check,
    LogOut, User, TrendingUp, DollarSign, Link2, BarChart3
} from 'lucide-vue-next'

defineProps<{
    stats: any
    recentReferrals: any[]
    recentCommissions: any[]
    referralLink: string
}>()

const copied = ref(false)

const copyLink = async (link: string) => {
    await navigator.clipboard.writeText(link)
    copied.value = true
    setTimeout(() => copied.value = false, 2000)
}

const logout = () => {
    router.post(route('logout'))
}
</script>

<template>
    <Head title="Seller Dashboard" />

    <div class="min-h-screen bg-slate-50">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-white border-r">
            <div class="flex h-16 items-center gap-3 border-b px-6">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500">
                    <DollarSign class="h-5 w-5 text-white" />
                </div>
                <span class="text-lg font-bold text-slate-900">Seller Hub</span>
            </div>

            <nav class="p-4 space-y-1">
                <Link href="/seller/dashboard" class="flex items-center gap-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">
                    <LayoutDashboard class="h-5 w-5" />
                    Dashboard
                </Link>
                <Link href="/seller/referrals" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    <Link2 class="h-5 w-5" />
                    Referrals
                </Link>
                <Link href="/seller/commissions" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    <BarChart3 class="h-5 w-5" />
                    Commissions
                </Link>
                <Link href="/seller/wallet" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    <Wallet class="h-5 w-5" />
                    Wallet
                </Link>
                <Link href="/seller/profile" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    <User class="h-5 w-5" />
                    Profile
                </Link>
            </nav>

            <div class="absolute bottom-0 left-0 right-0 border-t p-4">
                <button @click="logout" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 w-full transition-colors">
                    <LogOut class="h-5 w-5" />
                    Sign out
                </button>
            </div>
        </aside>

        <!-- Main -->
        <main class="ml-64 p-8">
            <!-- Welcome -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Welcome back! 👋</h1>
                <p class="text-slate-600 mt-1">Track your earnings and grow your network.</p>
            </div>

            <!-- Referral Link -->
            <div class="mb-8 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 p-6 text-white">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Your Referral Link</h3>
                        <p class="text-emerald-50 text-sm">Share this link to earn 70% commission on every sale</p>
                    </div>
                    <button @click="copyLink(referralLink)" class="flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-sm font-medium hover:bg-white/30 transition-colors backdrop-blur-sm">
                        <Check v-if="copied" class="h-4 w-4" />
                        <Copy v-else class="h-4 w-4" />
                        {{ copied ? 'Copied!' : 'Copy Link' }}
                    </button>
                </div>
                <div class="mt-4 rounded-lg bg-white/10 p-3 font-mono text-sm break-all">
                    {{ referralLink }}
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="rounded-xl border bg-white p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 text-green-600">
                            <DollarSign class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">৳{{ stats.total_earned }}</div>
                    <div class="text-sm text-slate-600 mt-1">Total Earned</div>
                </div>

                <div class="rounded-xl border bg-white p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <Wallet class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">৳{{ stats.available_balance }}</div>
                    <div class="text-sm text-slate-600 mt-1">Available Balance</div>
                </div>

                <div class="rounded-xl border bg-white p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <TrendingUp class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">৳{{ stats.this_month_earnings }}</div>
                    <div class="text-sm text-slate-600 mt-1">This Month</div>
                </div>

                <div class="rounded-xl border bg-white p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                            <Users class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ stats.total_referrals }}</div>
                    <div class="text-sm text-slate-600 mt-1">Total Referrals</div>
                </div>
            </div>

            <!-- Empty State -->
            <div class="rounded-xl border bg-white p-12 text-center">
                <Users class="h-16 w-16 mx-auto mb-4 text-slate-300" />
                <h3 class="text-lg font-semibold text-slate-900 mb-1">No referrals yet</h3>
                <p class="text-slate-600 text-sm mb-6">Share your referral link to start earning commissions</p>
                <button @click="copyLink(referralLink)" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                    <Copy class="h-4 w-4" />
                    Copy Referral Link
                </button>
            </div>
        </main>
    </div>
</template>
