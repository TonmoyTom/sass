import { computed, onMounted, onUnmounted, ref } from 'vue';

/**
 * Ticks down to `targetDate` (string | Date | null) every second.
 * Returns { days, hours, minutes, seconds, isPast, isActive } — all
 * zero-padded-ready numbers, `isPast` true once the target has passed.
 */
export function useCountdown(targetDate) {
    const now = ref(Date.now());
    let timer = null;

    const target = computed(() => {
        const value = typeof targetDate === 'object' && 'value' in targetDate ? targetDate.value : targetDate;
        return value ? new Date(value).getTime() : null;
    });

    const remainingMs = computed(() => (target.value ? target.value - now.value : 0));
    const isActive = computed(() => target.value !== null);
    const isPast = computed(() => isActive.value && remainingMs.value <= 0);

    const days = computed(() => Math.max(0, Math.floor(remainingMs.value / 86_400_000)));
    const hours = computed(() => Math.max(0, Math.floor((remainingMs.value % 86_400_000) / 3_600_000)));
    const minutes = computed(() => Math.max(0, Math.floor((remainingMs.value % 3_600_000) / 60_000)));
    const seconds = computed(() => Math.max(0, Math.floor((remainingMs.value % 60_000) / 1000)));

    onMounted(() => {
        timer = setInterval(() => {
            now.value = Date.now();
        }, 1000);
    });

    onUnmounted(() => clearInterval(timer));

    return { days, hours, minutes, seconds, isPast, isActive };
}
