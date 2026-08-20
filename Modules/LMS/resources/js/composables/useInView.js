import { onMounted, onUnmounted, ref } from 'vue';

/**
 * Tracks whether `target` has entered the viewport. Fires once, then
 * disconnects. Falls back to "always visible" if IntersectionObserver
 * isn't available (old browsers, SSR).
 */
export function useInView(options = { threshold: 0.15, rootMargin: '0px 0px -60px 0px' }) {
    const target = ref(null);
    const isVisible = ref(false);
    let observer;

    onMounted(() => {
        if (!target.value || typeof IntersectionObserver === 'undefined') {
            isVisible.value = true;
            return;
        }

        observer = new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                isVisible.value = true;
                observer?.disconnect();
            }
        }, options);

        observer.observe(target.value);
    });

    onUnmounted(() => observer?.disconnect());

    return { target, isVisible };
}