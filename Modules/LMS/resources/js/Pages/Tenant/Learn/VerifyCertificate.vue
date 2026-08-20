<script setup>
import LearnFooter from '@modules/LMS/resources/js/Components/Learn/LearnFooter.vue';
import LearnHeader from '@modules/LMS/resources/js/Components/Learn/LearnHeader.vue';
import { router } from '@inertiajs/vue3';
import { CheckCircle2, GraduationCap, Search, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    certificate: {
        type: Object,
        default: null,
    },
    searched_number: {
        type: String,
        default: null,
    },
    not_found: {
        type: Boolean,
        default: false,
    },
});

const searchValue = ref(props.searched_number ?? '');

const submitSearch = () => {
    if (!searchValue.value.trim()) return;
    router.get(`/lms/verify-certificate/${encodeURIComponent(searchValue.value.trim())}`);
};
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-950">
        <LearnHeader />

        <div class="mx-auto max-w-lg px-6 py-16">
            <div class="text-center">
                <span
                    class="bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 mx-auto flex h-14 w-14 items-center justify-center rounded-2xl"
                >
                    <GraduationCap class="h-7 w-7" />
                </span>
                <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">
                    Verify a Certificate
                </h1>
                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                    Enter a certificate number to check its authenticity.
                </p>
            </div>

            <form class="mt-6 flex gap-2" @submit.prevent="submitSearch">
                <input
                    v-model="searchValue"
                    type="text"
                    placeholder="e.g. CERT-8F3K2Q9X"
                    class="focus:border-brand-300 dark:focus:border-brand-500 h-12 flex-1 rounded-xl border border-gray-200 bg-white px-4 font-mono text-sm text-gray-800 outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
                <button
                    type="submit"
                    class="bg-brand-500 hover:bg-brand-600 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-white transition"
                >
                    <Search class="h-5 w-5" />
                </button>
            </form>

            <!-- valid certificate -->
            <div
                v-if="certificate"
                class="bg-success-50 dark:bg-success-500/10 mt-8 rounded-2xl border border-success-100 p-6 dark:border-success-500/20"
            >
                <div class="flex items-center gap-2">
                    <CheckCircle2 class="text-success-600 dark:text-success-400 h-5 w-5" />
                    <p class="text-success-700 dark:text-success-400 text-sm font-bold">
                        This certificate is valid
                    </p>
                </div>

                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Issued to</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ certificate.student_name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Course</dt>
                        <dd class="text-right font-semibold text-gray-900 dark:text-white">{{ certificate.course_title }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Issued on</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ certificate.issued_at }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-success-100 pt-2.5 dark:border-success-500/20">
                        <dt class="text-gray-500 dark:text-gray-400">Certificate No.</dt>
                        <dd class="font-mono text-gray-900 dark:text-white">{{ certificate.certificate_number }}</dd>
                    </div>
                </dl>
            </div>

            <!-- not found -->
            <div
                v-else-if="not_found"
                class="bg-error-50 dark:bg-error-500/10 mt-8 flex items-center gap-3 rounded-2xl border border-error-100 p-5 dark:border-error-500/20"
            >
                <XCircle class="text-error-600 dark:text-error-400 h-5 w-5 shrink-0" />
                <div>
                    <p class="text-error-700 dark:text-error-400 text-sm font-bold">
                        Certificate not found
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        "{{ searched_number }}" doesn't match any issued certificate.
                    </p>
                </div>
            </div>
        </div>

        <LearnFooter />
    </div>
</template>