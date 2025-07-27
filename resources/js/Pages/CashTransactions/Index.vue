<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({ transactions: Object });
const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
</script>

<template>
    <AppLayout title="Cash Transactions">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl ...">Cash Transactions</h2>
                <Link href="/cash-transactions/create"
                    ><PrimaryButton>New Transaction</PrimaryButton></Link
                >
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <table class="min-w-full ...">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 ...">Date</th>
                                <th class="px-6 py-3 ...">Description</th>
                                <th class="px-6 py-3 ...">Account</th>
                                <th class="px-6 py-3 text-right ...">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tx in transactions.data" :key="tx.id">
                                <td class="px-6 py-4 ...">
                                    {{ tx.transaction_date }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ tx.description }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ tx.account.account_name }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right ..."
                                    :class="
                                        tx.type === 'In'
                                            ? 'text-green-500'
                                            : 'text-red-500'
                                    "
                                >
                                    {{ tx.type === "In" ? "+" : "-" }}
                                    {{ formatCurrency(tx.amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
