import { ref, watch } from 'vue';

/**
 * Animates a number from 0 up to `target` once `trigger` becomes truthy.
 * Respects prefers-reduced-motion by jumping straight to the final value.
 */
export function useCountUp(target, trigger, duration = 900) {
    const value = ref(0);
    const prefersReducedMotion =
        typeof window !== 'undefined' &&
        window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    watch(
        trigger,
        (shouldAnimate) => {
            if (!shouldAnimate) return;

            const to = Number(target.value ?? target) || 0;

            if (prefersReducedMotion) {
                value.value = to;
                return;
            }

            const start = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
                value.value = Math.round(to * eased);
                if (progress < 1) requestAnimationFrame(tick);
            };

            requestAnimationFrame(tick);
        },
        { immediate: true },
    );

    return value;
}