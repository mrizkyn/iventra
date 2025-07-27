<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";

const props = defineProps({
    stats: Object,
    recent_sales: Array,
    low_stock_products: Array,
});

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <div
                        class="bg-indigo-600 dark:bg-indigo-500 p-6 rounded-lg shadow-lg flex flex-col justify-center items-center text-center text-white"
                    >
                        <h3 class="font-semibold mb-4">Quick Actions</h3>
                        <div class="flex flex-col space-y-2 w-full">
                            <Button as-child variant="secondary">
                                <Link href="/sales/create">New Sale</Link>
                            </Button>
                            <Button as-child variant="secondary">
                                <Link href="/purchases/create"
                                    >New Purchase</Link
                                >
                            </Button>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Pending Sales Approval
                        </h3>
                        <p class="text-3xl font-bold text-yellow-500 mt-1">
                            {{ stats.pending_sales }}
                        </p>
                        <Link
                            href="/sales"
                            class="text-xs text-indigo-500 hover:underline mt-2 inline-block"
                            >View Sales</Link
                        >
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Pending Purchases Approval
                        </h3>
                        <p class="text-3xl font-bold text-yellow-500 mt-1">
                            {{ stats.pending_purchases }}
                        </p>
                        <Link
                            href="/purchases"
                            class="text-xs text-indigo-500 hover:underline mt-2 inline-block"
                            >View Purchases</Link
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Recent Sales Activity
                        </h3>
                        <ul
                            class="divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <li
                                v-for="sale in recent_sales"
                                :key="sale.id"
                                class="py-3 flex justify-between items-center"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900 dark:text-gray-200"
                                    >
                                        {{ sale.sale_number }}
                                    </p>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ sale.customer.customer_name }}
                                    </p>
                                </div>
                                <div
                                    class="text-sm text-right text-gray-500 dark:text-gray-400"
                                >
                                    {{ formatCurrency(sale.total_price) }}
                                </div>
                            </li>
                            <li
                                v-if="recent_sales.length === 0"
                                class="text-center text-sm text-gray-500 dark:text-gray-400 py-4"
                            >
                                No recent sales.
                            </li>
                        </ul>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Low Stock Products
                        </h3>
                        <ul
                            class="divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <li
                                v-for="product in low_stock_products"
                                :key="product.id"
                                class="py-3"
                            >
                                <p
                                    class="text-sm font-medium text-gray-900 dark:text-gray-200"
                                >
                                    {{ product.product_name }}
                                </p>
                                <p class="text-sm text-red-500">
                                    Current Stock:
                                    {{ product.current_stock }} (Min:
                                    {{ product.minimum_stock }})
                                </p>
                            </li>
                            <li
                                v-if="low_stock_products.length === 0"
                                class="text-center text-sm text-gray-500 dark:text-gray-400 py-4"
                            >
                                All products have sufficient stock.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
