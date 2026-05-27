<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Building2, Globe, Phone, Loader2, Check } from 'lucide-vue-next'
import { ref, watch } from 'vue'

const form = useForm({
    school_name: '',
    subdomain: '',
    phone: '',
    address: '',
})

const subdomainAvailable = ref<boolean | null>(null)
const checkingSubdomain = ref(false)

// Auto-generate subdomain from school name
watch(() => form.school_name, (val) => {
    if (val && !form.subdomain) {
        form.subdomain = val.toLowerCase().replace(/[^a-z0-9]/g, '').slice(0, 20)
    }
})

const submit = () => {
    form.post(route('tenant.signup.store'))
}
</script>

<template>
    <Head title="Setup Your School" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-12">
        <div class="max-w-2xl mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 mb-4">
                    <Building2 class="h-6 w-6 text-white" />
                </div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2">Setup Your School</h1>
                <p class="text-slate-600">Just a few details to get your school up and running.</p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl border shadow-sm p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- School Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            School Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.school_name"
                            type="text"
                            required
                            placeholder="ABC High School"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            :class="{ 'border-red-300': form.errors.school_name }"
                        />
                        <p v-if="form.errors.school_name" class="mt-1 text-xs text-red-600">
                            {{ form.errors.school_name }}
                        </p>
                    </div>

                    <!-- Subdomain -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Choose Your Subdomain <span class="text-red-500">*</span>
                        </label>
                        <div class="flex">
                            <div class="flex items-center rounded-l-lg border border-r-0 border-slate-200 bg-slate-50 px-3">
                                <Globe class="h-4 w-4 text-slate-400 mr-1" />
                            </div>
                            <input
                                v-model="form.subdomain"
                                type="text"
                                required
                                placeholder="abcschool"
                                class="flex-1 border-y border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500"
                                :class="{ 'border-red-300': form.errors.subdomain }"
                            />
                            <div class="flex items-center rounded-r-lg border border-l-0 border-slate-200 bg-slate-50 px-3 text-sm text-slate-500">
                                .edusaas.com
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Your school will be accessible at: <span class="font-medium text-slate-700">{{ form.subdomain || 'subdomain' }}.edusaas.com</span>
                        </p>
                        <p v-if="form.errors.subdomain" class="mt-1 text-xs text-red-600">
                            {{ form.errors.subdomain }}
                        </p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Phone Number
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <Phone class="h-4 w-4 text-slate-400" />
                            </div>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="01700000000"
                                class="w-full rounded-lg border border-slate-200 pl-10 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            School Address
                        </label>
                        <textarea
                            v-model="form.address"
                            rows="3"
                            placeholder="Full address with district..."
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 resize-none"
                        ></textarea>
                    </div>

                    <!-- Trial Info -->
                    <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                        <div class="flex items-start gap-3">
                            <Check class="h-5 w-5 text-green-600 mt-0.5" />
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-green-900">14-Day Free Trial</h3>
                                <p class="text-xs text-green-700 mt-1">
                                    No credit card required. Cancel anytime. Get full access during trial.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 text-sm font-semibold text-white shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-70 transition-all"
                    >
                        <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Creating your school...' : 'Create My School' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
