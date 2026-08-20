<script setup>
import { learnRoutes } from '@modules/LMS/resources/js/lib/learn-routes.js';
import { Award, ChevronLeft, ChevronRight, Loader2, Medal, Search, Trophy, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    courseId: {
        type: [String, Number],
        required: true,
    },
});

const emit = defineEmits(['close']);

const loading = ref(false);
const leaderboard = ref([]);
const currentStudentId = ref(null);
const searchTerm = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
let searchTimer = null;

const fetchLeaderboard = async (search = '', page = 1) => {
    loading.value = true;
    try {
        const { data } = await window.axios.get(learnRoutes.leaderboardJson(props.courseId), {
            params: { search, page },
        });
        leaderboard.value = data.leaderboard;
        currentStudentId.value = data.current_student_id;
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } catch {
        leaderboard.value = [];
    } finally {
        loading.value = false;
    }
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            searchTerm.value = '';
            fetchLeaderboard('', 1);
        }
    },
);

const onSearchInput = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchLeaderboard(searchTerm.value, 1), 350);
};

const goToPage = (page) => {
    if (page < 1 || page > lastPage.value || page === currentPage.value) return;
    fetchLeaderboard(searchTerm.value, page);
};

const medal = (rank) => {
    if (rank === 1) return { icon: Trophy, class: 'text-amber-400' };
    if (rank === 2) return { icon: Medal, class: 'text-gray-400' };
    if (rank === 3) return { icon: Award, class: 'text-amber-700' };
    return null;
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-950/50 p-4 backdrop-blur-sm"
                @click.self="emit('close')"
            >
                <div class="flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                    <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <p class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <Trophy class="h-4 w-4 text-amber-500" />
                            Leaderboard
                        </p>
                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                            @click="emit('close')"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="shrink-0 p-4 pb-0">
                        <div class="relative">
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="searchTerm"
                                type="text"
                                placeholder="Search by name..."
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white pr-3 pl-9 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                @input="onSearchInput"
                            />
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto p-4">
                        <div v-if="loading" class="flex items-center justify-center gap-2 py-10 text-sm text-gray-400">
                            <Loader2 class="h-4 w-4 animate-spin" />
                            Loading...
                        </div>

                        <div v-else-if="!leaderboard.length" class="py-10 text-center text-sm text-gray-400">
                            {{ searchTerm ? 'No students match your search.' : 'No enrolled students yet.' }}
                        </div>

                        <div v-else class="divide-y divide-gray-100 rounded-xl border border-gray-100 dark:divide-gray-800 dark:border-gray-800">
                            <div
                                v-for="row in leaderboard"
                                :key="row.student_id"
                                class="flex items-center gap-3 px-3 py-3"
                                :class="row.student_id === currentStudentId ? 'bg-brand-50 dark:bg-brand-500/10' : ''"
                            >
                                <div class="flex w-7 shrink-0 items-center justify-center">
                                    <component
                                        :is="medal(row.rank).icon"
                                        v-if="medal(row.rank)"
                                        class="h-4.5 w-4.5"
                                        :class="medal(row.rank).class"
                                    />
                                    <span v-else class="text-sm font-semibold text-gray-400">
                                        {{ row.rank }}
                                    </span>
                                </div>

                                <img :src="row.avatar" :alt="row.name" class="h-8 w-8 shrink-0 rounded-full object-cover" />

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-semibold"
                                        :class="
                                            row.student_id === currentStudentId
                                                ? 'text-brand-700 dark:text-brand-400'
                                                : 'text-gray-800 dark:text-gray-100'
                                        "
                                    >
                                        {{ row.name }}
                                        <span v-if="row.student_id === currentStudentId" class="text-brand-500 text-xs font-normal">
                                            (you)
                                        </span>
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Quiz {{ row.quiz_score }} + Assignment {{ row.assignment_score }}
                                    </p>
                                </div>

                                <p class="shrink-0 text-sm font-bold text-gray-900 dark:text-white">
                                    {{ row.total_score }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!loading && leaderboard.length && lastPage > 1"
                        class="flex shrink-0 items-center justify-between border-t border-gray-100 px-4 py-3 dark:border-gray-800"
                    >
                        <button
                            type="button"
                            :disabled="currentPage <= 1"
                            class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-400 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:hover:bg-white/5"
                            @click="goToPage(currentPage - 1)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>

                        <p class="text-xs text-gray-400">
                            Page {{ currentPage }} of {{ lastPage }} · {{ total }} students
                        </p>

                        <button
                            type="button"
                            :disabled="currentPage >= lastPage"
                            class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-400 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:hover:bg-white/5"
                            @click="goToPage(currentPage + 1)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>