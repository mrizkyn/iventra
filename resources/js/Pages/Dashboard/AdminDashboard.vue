<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

defineProps({
    recent_sales: Array,
    low_stock_products: Array,
});
</script>

<template>
    <AppLayout title="Admin Dashboard">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8"
            >
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Recent Sales
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="sale in recent_sales"
                            :key="sale.id"
                            class="py-3"
                        >
                            <p class="text-sm font-medium">
                                {{ sale.sale_number }} -
                                {{ sale.customer.customer_name }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{
                                    new Date(
                                        sale.sale_date
                                    ).toLocaleDateString()
                                }}
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Low Stock Products
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="product in low_stock_products"
                            :key="product.id"
                            class="py-3"
                        >
                            <p class="text-sm font-medium">
                                {{ product.product_name }}
                            </p>
                            <p class="text-sm text-red-500">
                                Stock: {{ product.current_stock }} (Min:
                                {{ product.minimum_stock }})
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
