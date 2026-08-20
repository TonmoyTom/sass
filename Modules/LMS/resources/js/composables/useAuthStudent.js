    import { usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';

    /**
     * `auth.user` is already shared on every page by HandleInertiaRequests —
     * on tenant routes SetTenantAuthGuard makes it resolve the logged-in
     * tenant-side user (student, staff, or owner). Reading it here means no
     * controller ever needs to compute/pass its own "isAuthenticated" prop.
     */
    export function useAuthStudent() {
        const page = usePage();

        const student = computed(() => page.props.workspace?.user ?? null);
        const isAuthenticated = computed(() => !!student.value);
        console.log(student.value)

        return { student, isAuthenticated };
    }
