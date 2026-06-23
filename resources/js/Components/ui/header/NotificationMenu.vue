<template>
    <div class="relative" ref="dropdownRef">
        <button
            class="hover:text-dark-900 relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
            @click="toggleDropdown"
        >
            <span
                :class="{ hidden: !notifying, flex: notifying }"
                class="absolute top-0.5 right-0 z-1 h-2 w-2 rounded-full bg-orange-400"
            >
                <span
                    class="absolute -z-1 inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"
                ></span>
            </span>
            <svg
                class="fill-current"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
                    fill=""
                />
            </svg>
            <!-- Badge for count -->
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 z-10 h-2 w-2 rounded-full bg-orange-400"
            >
                <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"
                ></span>
            </span>
        </button>

        <!-- Dropdown Start -->
        <div
            v-if="dropdownOpen"
            class="shadow-theme-lg dark:bg-gray-dark absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 sm:w-[361px] lg:right-0 dark:border-gray-800"
        >
            <div
                class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800"
            >
                <h5
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Notification
                </h5>

                <button
                    @click="closeDropdown"
                    class="text-gray-500 dark:text-gray-400"
                >
                    <svg
                        class="fill-current"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                            fill=""
                        />
                    </svg>
                </button>
            </div>

            <!-- Empty State -->
            <div
                v-if="notifications.length === 0"
                class="flex h-full items-center justify-center"
            >
                <div class="text-center">
                    <p class="text-gray-500 dark:text-gray-400">
                        No notifications yet
                    </p>
                </div>
            </div>

            <!-- Notifications List -->
            <ul
                v-else
                class="custom-scrollbar flex h-auto flex-col overflow-y-auto"
            >
                <li
                    v-for="notification in notifications"
                    :key="notification.id"
                    @click="handleItemClick(notification)"
                    class="cursor-pointer transition-colors"
                >
                    <a
                        class="flex gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5"
                        href="#"
                    >
                        <span
                            class="relative z-1 block h-10 w-full max-w-10 rounded-full"
                        >
                            <img
                                :src="
                                    notification.userImage ||
                                    '/images/user/default.jpg'
                                "
                                alt="User"
                                class="overflow-hidden rounded-full"
                            />
                            <span
                                :class="
                                    notification.status === 'online'
                                        ? 'bg-success-500'
                                        : 'bg-error-500'
                                "
                                class="absolute right-0 bottom-0 z-10 h-2.5 w-full max-w-2.5 rounded-full border-[1.5px] border-white dark:border-gray-900"
                            ></span>
                        </span>

                        <span class="block flex-1">
                            <span
                                class="text-theme-sm mb-1.5 block text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ notification.userName }}
                                </span>
                                {{ notification.action }}
                                <span
                                    class="font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ notification.project }}
                                </span>
                            </span>

                            <span
                                class="text-theme-xs flex items-center gap-2 text-gray-500 dark:text-gray-400"
                            >
                                <span>{{ notification.type }}</span>
                                <span
                                    class="h-1 w-1 rounded-full bg-gray-400"
                                ></span>
                                <span>{{
                                    formatTime(notification.timestamp)
                                }}</span>
                            </span>
                        </span>

                        <!-- Mark as read dot -->
                        <span
                            v-if="!notification.read"
                            class="mt-2 h-2 w-2 rounded-full bg-blue-500"
                        ></span>
                    </a>
                </li>
            </ul>

            <Link
                href="#"
                class="text-theme-sm shadow-theme-xs mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                @click="handleViewAllClick"
            >
                View All Notification
            </Link>
        </div>
        <!-- Dropdown End -->
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const dropdownOpen = ref(false);
const notifying = ref(false);
const dropdownRef = ref(null);
const notifications = ref([]);

const formatTime = (timestamp) => {
    const now = new Date();
    const notifTime = new Date(timestamp);
    const diff = now - notifTime;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    if (minutes < 1) return 'just now';
    if (minutes < 60) return `${minutes} min ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return notifTime.toLocaleDateString();
};

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
    if (dropdownOpen.value) notifying.value = false;
};

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

const handleItemClick = async (notification) => {
    if (!notification.read) {
        await fetch(`/notifications/${notification.id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        });
        notification.read = true;
        notifying.value = notifications.value.some((n) => !n.read);
    }
    closeDropdown();
    if (notification.link && notification.link !== '#') {
        window.location.href = notification.link;
    }
};

const unreadCount = computed(
    () => notifications.value.filter((n) => !n.read).length,
);

const handleViewAllClick = (event) => {
    event.preventDefault();
    closeDropdown();
};

const loadNotifications = async () => {
    try {
        const res = await fetch('/notifications', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'include',
        });
        const data = await res.json();
        notifications.value = data.map((n) => ({
            id: n.id,
            action: n.data.message,
            type: n.data.type || 'info',
            timestamp: n.created_at,
            read: n.read_at !== null,
            link: n.data.link || '#',
            userName: 'System',
            userImage: n.user.avatar_url || '/images/user/default.jpg',
            project: '',
            status: 'online',
        }));
        notifying.value = notifications.value.some((n) => !n.read);
    } catch (e) {
        console.error('Failed to load notifications:', e);
    }
};

const setupEchoListener = () => {
    const userId = page.props.auth?.user?.id;

    if (!userId) {
        console.warn('User not logged in');
        return;
    }

    if (!window.Echo) {
        console.warn('Laravel Echo not initialized');
        return;
    }

    window.Echo.leave(`user.${userId}`);

    window.Echo.private(`user.${userId}`).listen(
        '.notification.sent',
        (data) => {
            console.log('✅ Notification received:', data);
            notifications.value.unshift({
                id: data.id,
                userName: data.userName || 'System',
                userImage: data.userImage || '/images/user/default.jpg',
                action: data.message || '',
                project: data.project || '',
                type: data.type || 'info',
                timestamp: data.timestamp || new Date(),
                status: 'online',
                read: false,
                link: data.link || '#',
            });

            notifying.value = true;

            if (notifications.value.length > 20) {
                notifications.value.pop();
            }
        },
    );

    console.log('Echo listening on channel: user.' + userId);
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    loadNotifications(); // ✅ page load এ recent notifications আনো
    setupEchoListener();
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    const userId = page.props.auth?.user?.id;
    if (window.Echo && userId) {
        window.Echo.leave(`user.${userId}`);
    }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
}
</style>
