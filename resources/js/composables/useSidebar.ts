import type { ComputedRef, Ref } from 'vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface SidebarContextType {
    isExpanded: ComputedRef<boolean>;
    isMobileOpen: Ref<boolean>;
    isHovered: Ref<boolean>;
    activeItem: Ref<string | null>;
    openSubmenu: Ref<string | null>;
    toggleSidebar: () => void;
    toggleMobileSidebar: () => void;
    setIsHovered: (isHovered: boolean) => void;
    setActiveItem: (item: string | null) => void;
    toggleSubmenu: (item: string) => void;
}

// Module-level singleton state (shared across all components)
const isExpandedRaw = ref(true);
const isMobileOpen = ref(false);
const isMobile = ref(false);
const isHovered = ref(false);
const activeItem = ref<string | null>(null);
const openSubmenu = ref<string | null>(null);

const isExpanded = computed(() =>
    isMobile.value ? false : isExpandedRaw.value,
);

const toggleSidebar = () => {
    if (isMobile.value) {
        isMobileOpen.value = !isMobileOpen.value;
    } else {
        isExpandedRaw.value = !isExpandedRaw.value;
    }
};

const toggleMobileSidebar = () => {
    isMobileOpen.value = !isMobileOpen.value;
};

const setIsHovered = (value: boolean) => {
    isHovered.value = value;
};

const setActiveItem = (item: string | null) => {
    activeItem.value = item;
};

const toggleSubmenu = (item: string) => {
    openSubmenu.value = openSubmenu.value === item ? null : item;
};

// Resize listener setup — only once, lazily
let resizeInitialized = false;

const initResizeListener = () => {
    if (resizeInitialized || typeof window === 'undefined') return;
    resizeInitialized = true;

    const handleResize = () => {
        const mobile = window.innerWidth < 768;
        isMobile.value = mobile;
        if (!mobile) {
            isMobileOpen.value = false;
        }
    };

    handleResize();
    window.addEventListener('resize', handleResize);
};

export function useSidebar(): SidebarContextType {
    // Lazy init on first use within a component context
    if (!resizeInitialized) {
        try {
            onMounted(initResizeListener);
            onUnmounted(() => {
                // Keep listener alive since state is module-level
            });
        } catch {
            // If called outside setup, just init directly
            initResizeListener();
        }
    }

    return {
        isExpanded,
        isMobileOpen,
        isHovered,
        activeItem,
        openSubmenu,
        toggleSidebar,
        toggleMobileSidebar,
        setIsHovered,
        setActiveItem,
        toggleSubmenu,
    };
}

// Kept for backward compatibility — same as useSidebar now
export function useSidebarProvider(): SidebarContextType {
    return useSidebar();
}
