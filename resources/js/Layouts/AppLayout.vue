<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { PhGear, PhUserCircle, PhX, PhList, PhChartLineUp, PhPackage, PhBasket, PhMoney, PhMonitor, PhArrowsClockwise, PhWarningOctagon, PhIdentificationCard } from "@phosphor-icons/vue";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Sidebar -->
            <div class="fixed top-0 left-0 z-30 h-full transition-all duration-300 ease-in-out transform"
                 :class="[sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full w-0']">
                <div class="flex flex-col justify-between h-full overflow-y-auto bg-white shadow-lg dark:bg-gray-800">
                    <div class="space-y-2" :class="{'opacity-0': !sidebarOpen, 'opacity-100 transition-opacity duration-200 delay-300': sidebarOpen}">
                        <div class="flex flex-col items-center justify-between w-full px-2 py-3 text-white bg-lime-900 md:flex-row">
                            <div class="text-3xl ms-2">
                                DPST-<b class="font-extrabold">IMS</b>
                            </div>
                            <div class="flex justify-end w-full md:w-auto md:justify-normal md:block">
                                <button class="p-3 rounded-full hover:text-white hover:bg-red-500" @click="toggleSidebar">
                                    <PhX :size="16" />
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:ms-6">
                            <h1>{{ $page.props.auth.user.name }}</h1>
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm transition border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300">
                                            <img class="object-cover rounded-full size-8" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-full">
                                            <button type="button" class="inline-flex items-center gap-2 p-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out border-2 rounded-full bg-lime-50 border-lime-800 hover:bg-lime-800 hover:text-white dark:text-gray-400 dark:bg-gray-800 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700">
                                                <PhUserCircle :size="32" />
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>
                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>
                                        <div class="border-t border-gray-200 dark:border-gray-600" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                        <nav class="border-t-2">
                            <ul>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white" :class="{ 'bg-lime-700 text-white': route().current('dashboard') }">
                                    <Link :href="route('dashboard')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhChartLineUp :size="28" />
                                            Dashboard
                                        </span>
                                    </Link>
                                </li>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white">
                                    <Link :href="route('customer.index')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhIdentificationCard :size="28" />
                                            Customer
                                        </span>
                                    </Link>
                                </li>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white">
                                    <Link :href="route('supplier.index')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhArrowsClockwise :size="28" />
                                            Suppliers
                                        </span>
                                    </Link>
                                </li>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white">
                                    <Link :href="route('inventory.index')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhPackage :size="28" weight="fill" />
                                            Inventory
                                        </span>
                                    </Link>
                                </li>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white" :class="{ 'bg-lime-700 text-white': route().current('purchase.index') }">
                                    <Link :href="route('purchase.index')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhBasket :size="28" />
                                            Purchase
                                        </span>
                                    </Link>
                                </li>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white" :class="{ 'bg-lime-700 text-white': route().current('sales.index') }">
                                    <Link :href="route('sales.index')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhMoney :size="28" />
                                            Sales
                                        </span>
                                    </Link>
                                </li>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white">
                                    <Link :href="route('about.index')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhWarningOctagon :size="28" />
                                            About
                                        </span>
                                    </Link>
                                </li>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white">
                                    <Link :href="route('activity-logs.index')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhMonitor :size="28" weight="fill" />
                                            Activity Logs
                                        </span>
                                    </Link>
                                </li>
                                <li class="px-6 py-2 hover:bg-lime-700 hover:text-white">
                                    <Link :href="route('settings.index')">
                                        <span class="inline-flex items-center w-full gap-2">
                                            <PhGear :size="28" />
                                            Settings
                                        </span>
                                    </Link>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="flex items-end justify-center w-full">
                        <img src="/img/dpstlogo.png" alt="Logo" class="w-32 h-24">
                    </div>
                </div>
            </div>

            <header v-if="$slots.header" class="fixed top-0 z-10 w-full bg-white shadow dark:bg-gray-800">
                <div class="flex items-center justify-between w-full px-1 py-3 sm:px-2 lg:px-4">
                    <!-- Sidebar Toggle Button -->
                     <div class="inline-flex items-center gap-2 md:gap-20">
                        <button @click="toggleSidebar"
                                class="p-2 text-gray-500 rounded-md hover:bg-lime-200 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none">
                            <PhList :size="32" />
                        </button>
                        <slot name="header" />
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="pt-24 md:pt-24">
                <slot />
            </main>
        </div>
    </div>
</template>

