<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed } from "vue";
import { Bar } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from "chart.js";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
);

const props = defineProps({
    stats: Object,
    chartData: Object,
    topSellingProducts: Array,
    recentActivities: Array,
});

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);

// --- DATA UNTUK GRAFIK ---
const monthlyChartData = computed(() => {
    const labels = [];
    const salesData = [];
    const purchasesData = [];

    // Buat label untuk 6 bulan terakhir
    for (let i = 5; i >= 0; i--) {
        const d = new Date();
        d.setMonth(d.getMonth() - i);
        labels.push(d.toLocaleString("default", { month: "short" }));
    }

    // Peta data dari backend ke bulan yang sesuai
    const salesMap = new Map(
        props.chartData.monthly_sales.map((item) => [
            new Date(item.month + "-02").toLocaleString("default", {
                month: "short",
            }),
            item.total,
        ]),
    );
    const purchasesMap = new Map(
        props.chartData.monthly_purchases.map((item) => [
            new Date(item.month + "-02").toLocaleString("default", {
                month: "short",
            }),
            item.total,
        ]),
    );

    labels.forEach((label) => {
        salesData.push(salesMap.get(label) || 0);
        purchasesData.push(purchasesMap.get(label) || 0);
    });

    return {
        labels: labels,
        datasets: [
            {
                label: "Sales",
                backgroundColor: "#4ade80", // green-400
                borderColor: "#22c55e", // green-500
                borderWidth: 1,
                data: salesData,
            },
            {
                label: "Purchases",
                backgroundColor: "#f87171", // red-400
                borderColor: "#ef4444", // red-500
                borderWidth: 1,
                data: purchasesData,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => formatCurrency(value),
            },
        },
    },
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
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Total Revenue
                        </h3>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                        >
                            {{ formatCurrency(stats.total_revenue) }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Total Expense
                        </h3>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                        >
                            {{ formatCurrency(stats.total_expense) }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Total Customers
                        </h3>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                        >
                            {{ stats.total_customers }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Total Suppliers
                        </h3>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                        >
                            {{ stats.total_suppliers }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Pending Purchases
                        </h3>
                        <p class="text-3xl font-bold text-yellow-500 mt-1">
                            {{ stats.pending_purchases }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Pending Sales
                        </h3>
                        <p class="text-3xl font-bold text-yellow-500 mt-1">
                            {{ stats.pending_sales }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Sale Returns
                        </h3>
                        <p class="text-3xl font-bold text-green-500 mt-1">
                            {{ stats.total_sale_returns }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
                        >
                            Purchase Returns
                        </h3>
                        <p class="text-3xl font-bold text-red-500 mt-1">
                            {{ stats.total_purchase_returns }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div
                        class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Sales & Purchases (Last 6 Months)
                        </h3>
                        <div class="h-80">
                            <Bar
                                :data="monthlyChartData"
                                :options="chartOptions"
                            />
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Top 5 Selling Products
                        </h3>
                        <ul class="space-y-4">
                            <li
                                v-for="product in topSellingProducts"
                                :key="product.id"
                                class="flex justify-between items-center"
                            >
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-300"
                                    >{{ product.product_name }}</span
                                >
                                <span
                                    class="font-semibold text-gray-900 dark:text-white"
                                    >{{ product.quantity_sold }} Pcs</span
                                >
                            </li>
                            <li
                                v-if="topSellingProducts.length === 0"
                                class="text-center text-sm text-gray-500 dark:text-gray-400 py-4"
                            >
                                No sales data yet.
                            </li>
                        </ul>
                    </div>

                    <div
                        class="lg:col-span-3 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
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
                                v-for="activity in recentActivities"
                                :key="activity.id"
                                class="py-3 flex justify-between items-center"
                            >
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-300"
                                >
                                    New sale
                                    <span
                                        class="font-semibold text-indigo-500 dark:text-indigo-400"
                                        >{{ activity.sale_number }}</span
                                    >
                                    to {{ activity.customer.customer_name }}
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ formatCurrency(activity.total_price) }}
                                </p>
                            </li>
                            <li
                                v-if="recentActivities.length === 0"
                                class="text-center text-sm text-gray-500 dark:text-gray-400 py-4"
                            >
                                No recent activities.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
