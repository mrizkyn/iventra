<script setup>
import { ref, computed, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import ThemeSwitcher from "@/Components/ThemeSwitcher.vue";
import { Toaster } from "@/Components/ui/toast";
import { useToast } from "@/Components/ui/toast/use-toast";
import { CheckCircle, AlertTriangle } from "lucide-vue-next";
import { h } from "vue";
import { useColorMode } from "@vueuse/core";

const { toast } = useToast();
const page = usePage();

defineProps({
    title: String,
});
watch(
    () => page.props.flash,
    (flash) => {
        if (flash.message) {
            toast({
                title: "Success!",
                description: flash.message,
                icon: h(CheckCircle, { class: "text-green-500 w-5 h-5" }),
            });
        }
        if (flash.error) {
            toast({
                title: "Error!",
                description: flash.error,
                variant: "destructive",
                icon: h(AlertTriangle, { class: "text-red-500 w-5 h-5" }),
            });
        }
    },
    { deep: true },
);

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(
        route("current-team.update"),
        { team_id: team.id },
        { preserveState: false },
    );
};

const logout = () => {
    router.post("/logout");
};

const userRole = computed(() => usePage().props.auth.user?.role);

useColorMode();
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link href="/dashboard">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <div
                                class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"
                            >
                                <NavLink
                                    href="/dashboard"
                                    :active="$page.url.startsWith('/dashboard')"
                                >
                                    Dashboard
                                </NavLink>

                                <div
                                    v-if="
                                        userRole === 'Owner' ||
                                        userRole === 'Admin'
                                    "
                                    class="flex items-center"
                                >
                                    <div class="ml-3 relative">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span
                                                    class="inline-flex rounded-md"
                                                >
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
                                                    >
                                                        Master Data
                                                        <svg
                                                            class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <template #content>
                                                <DropdownLink href="/suppliers">
                                                    Suppliers
                                                </DropdownLink>
                                                <DropdownLink href="/customers">
                                                    Customers
                                                </DropdownLink>
                                                <DropdownLink href="/products">
                                                    Products
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        userRole === 'Owner' ||
                                        userRole === 'Admin'
                                    "
                                    class="flex items-center"
                                >
                                    <div class="ml-3 relative">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span
                                                    class="inline-flex rounded-md"
                                                >
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
                                                    >
                                                        Transactions
                                                        <svg
                                                            class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <template #content>
                                                <DropdownLink href="/purchases">
                                                    Purchases
                                                </DropdownLink>
                                                <DropdownLink href="/sales">
                                                    Sales
                                                </DropdownLink>
                                                <div
                                                    class="border-t border-gray-200 dark:border-gray-600"
                                                />
                                                <DropdownLink href="/returns">
                                                    Returns
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        userRole === 'Owner' ||
                                        userRole === 'Admin'
                                    "
                                    class="flex items-center"
                                >
                                    <div class="ml-3 relative">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span
                                                    class="inline-flex rounded-md"
                                                >
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
                                                    >
                                                        Inventory
                                                        <svg
                                                            class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <template #content>
                                                <DropdownLink
                                                    href="/productions"
                                                >
                                                    Production
                                                </DropdownLink>
                                                <DropdownLink
                                                    href="/stock-takes"
                                                >
                                                    Stock Takes
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        userRole === 'Owner' ||
                                        userRole === 'Admin'
                                    "
                                    class="flex items-center"
                                >
                                    <div class="ml-3 relative">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span
                                                    class="inline-flex rounded-md"
                                                >
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
                                                    >
                                                        Accounting
                                                        <svg
                                                            class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <template #content>
                                                <DropdownLink
                                                    v-if="userRole === 'Owner'"
                                                    href="/accounts"
                                                >
                                                    Chart of Accounts
                                                </DropdownLink>
                                                <DropdownLink
                                                    href="/cash-transactions"
                                                >
                                                    Cash Transactions
                                                </DropdownLink>
                                                <DropdownLink
                                                    href="/reports/payable"
                                                >
                                                    Payable
                                                </DropdownLink>
                                                <DropdownLink
                                                    href="/reports/receivable"
                                                >
                                                    Receivable
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>

                                <div
                                    v-if="userRole === 'Owner'"
                                    class="flex items-center"
                                >
                                    <div class="ml-3 relative">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span
                                                    class="inline-flex rounded-md"
                                                >
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
                                                    >
                                                        Settings
                                                        <svg
                                                            class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <template #content>
                                                <DropdownLink href="/users">
                                                    Users
                                                </DropdownLink>
                                                <DropdownLink href="/roles">
                                                    Roles
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative"><ThemeSwitcher /></div>
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            v-if="
                                                $page.props.jetstream
                                                    .managesProfilePhotos
                                            "
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition"
                                        >
                                            <img
                                                class="h-8 w-8 rounded-full object-cover"
                                                :src="
                                                    $page.props.auth.user
                                                        .profile_photo_url
                                                "
                                                :alt="
                                                    $page.props.auth.user.name
                                                "
                                            />
                                        </button>
                                        <span
                                            v-else
                                            class="inline-flex rounded-md"
                                        >
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}
                                                <svg
                                                    class="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div
                                            class="block px-4 py-2 text-xs text-gray-400"
                                        >
                                            Manage Account
                                        </div>
                                        <DropdownLink href="/user/profile">
                                            Profile
                                        </DropdownLink>
                                        <div
                                            class="border-t border-gray-200 dark:border-gray-600"
                                        />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-mr-2 flex items-center sm:hidden"></div>
                    </div>
                </div>

                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                ></div>
            </nav>

            <header
                v-if="$slots.header"
                class="bg-white dark:bg-gray-800 shadow"
            >
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>

        <Toaster />
    </div>
</template>
