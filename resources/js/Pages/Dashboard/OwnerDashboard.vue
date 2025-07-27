<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    stats: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};
</script>

<template>
    <AppLayout title="Owner Dashboard">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Owner Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
                >
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3 class="text-gray-500 dark:text-gray-400">
                            Total Users
                        </h3>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ stats.total_users }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3 class="text-gray-500 dark:text-gray-400">
                            Total Products
                        </h3>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ stats.total_products }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3 class="text-gray-500 dark:text-gray-400">
                            Total Sales
                        </h3>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ formatCurrency(stats.total_sales) }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3 class="text-gray-500 dark:text-gray-400">
                            Total Purchases
                        </h3>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ formatCurrency(stats.total_purchases) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
