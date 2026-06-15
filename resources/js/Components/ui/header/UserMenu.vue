<template>
    <div class="relative" ref="dropdownRef">
        <button
            class="flex items-center text-gray-700 dark:text-gray-400"
            @click.prevent="toggleDropdown"
        >
            <!-- <span class="mr-3 overflow-hidden rounded-full h-11 w-11">
        <img :src="user.avatar" :alt="user.name" />
      </span> -->

            <span class="text-theme-sm mr-1 block font-medium">{{
                user.name
            }}</span>

            <ChevronDownIcon :class="{ 'rotate-180': dropdownOpen }" />
        </button>

        <!-- Dropdown Start -->
        <div
            v-if="dropdownOpen"
            class="shadow-theme-lg dark:bg-gray-dark absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800"
        >
            <div>
                <span
                    class="text-theme-sm block font-medium text-gray-700 dark:text-gray-400"
                >
                    {{ user.name }}
                </span>
                <span
                    class="text-theme-xs mt-0.5 block text-gray-500 dark:text-gray-400"
                >
                    {{ user.email }}
                </span>
            </div>

            <ul
                class="flex flex-col gap-1 border-b border-gray-200 pt-4 pb-3 dark:border-gray-800"
            >
                <li v-for="item in menuItems" :key="item.href">
                    <Link
                        :href="item.href"
                        class="group text-theme-sm flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        @click="closeDropdown"
                    >
                        <component
                            :is="item.icon"
                            class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
                        />
                        {{ item.text }}
                    </Link>
                </li>
            </ul>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                @click="closeDropdown"
                class="group text-theme-sm mt-3 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            >
                <LogoutIcon
                    class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
                />
                Sign out
            </Link>
        </div>
        <!-- Dropdown End -->
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronDown as ChevronDownIcon,
    LogOut as LogoutIcon,
    UserCircle as UserCircleIcon,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const dropdownOpen = ref(false);
const dropdownRef = ref(null);

const page = usePage();

const user = computed(() => ({
    name: page.props.auth?.user?.name ?? 'Guest',
    email: page.props.auth?.user?.email ?? '',
    //   avatar: page.props.auth?.user?.avatar ?? '/images/user/owner.jpg',
}));

const menuItems = [
    { href: route('profile.edit'), icon: UserCircleIcon, text: 'Edit profile' },
    //   { href: route('account.settings'), icon: SettingsIcon, text: 'Account settings' },
    //   { href: route('support'), icon: InfoCircleIcon, text: 'Support' },
];

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
