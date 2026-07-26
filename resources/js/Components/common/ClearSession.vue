<template>
    <div class="space-y-5">
        <!-- Section 1: Clear Session -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h5 class="font-semibold text-gray-800 dark:text-white/90">
                        Clear Session
                    </h5>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Removes all stored session data. You may be logged out
                        after this action.
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
                >
                    <RotateCcw
                        class="h-5 w-5 text-amber-600 dark:text-amber-400"
                    />
                </div>
            </div>

            <button
                @click="clearSession"
                :disabled="clearingSession"
                class="rounded-lg border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50 disabled:opacity-50 dark:border-amber-800 dark:text-amber-400 dark:hover:bg-amber-900/20"
            >
                {{ clearingSession ? 'Clearing...' : 'Clear Session' }}
            </button>

            <p
                v-if="sessionMessage"
                class="mt-3 text-sm text-green-600 dark:text-green-400"
            >
                {{ sessionMessage }}
            </p>
        </div>

        <!-- Section 2: Clear Cookies -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h5 class="font-semibold text-gray-800 dark:text-white/90">
                        Clear Cookies
                    </h5>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Removes all browser cookies stored for this site.
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"
                >
                    <Trash2 class="h-5 w-5 text-red-600 dark:text-red-400" />
                </div>
            </div>

            <button
                @click="clearCookies"
                :disabled="clearingCookies"
                class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20"
            >
                {{ clearingCookies ? 'Clearing...' : 'Clear Cookies' }}
            </button>

            <p
                v-if="cookieMessage"
                class="mt-3 text-sm text-green-600 dark:text-green-400"
            >
                {{ cookieMessage }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { RotateCcw, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const clearingSession = ref(false);
const clearingCookies = ref(false);
const sessionMessage = ref('');
const cookieMessage = ref('');

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const clearSession = async () => {
    if (!confirm('Clear session? You may be logged out.')) return;

    clearingSession.value = true;
    sessionMessage.value = '';

    try {
        const res = await fetch('/session/clear', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        });
        const data = await res.json();
        sessionMessage.value = data.message;

        // session flush hole login state-o jete pare, reload koro
        setTimeout(() => window.location.reload(), 800);
    } catch (e) {
        sessionMessage.value = 'Failed to clear session.';
    } finally {
        clearingSession.value = false;
    }
};

const clearCookies = async () => {
    if (!confirm('Clear all cookies for this site?')) return;

    clearingCookies.value = true;
    cookieMessage.value = '';

    try {
        const res = await fetch('/session/clear-cookies', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        });

        console.log('Status:', res.status); // ← debug
        const text = await res.text(); // ← raw response dekho
        console.log('Response:', text);

        const data = JSON.parse(text);
        cookieMessage.value = `${data.message} (${data.cleared.length} cookie(s) removed)`;
    } catch (e) {
        console.error('Actual error:', e); // ← full error console-e
        cookieMessage.value = 'Failed to clear cookies.';
    } finally {
        clearingCookies.value = false;
    }
};
</script>
