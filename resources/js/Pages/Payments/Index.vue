<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

defineProps({
    payments: Object,
});

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);

const getTransactionLink = (payment) => {
    if (payment.transaction_type === "Debt") {
        return {
            url: `/purchases/${payment.transaction_id}`,
            number: payment.purchase?.purchase_number || "N/A",
        };
    }
    if (payment.transaction_type === "Receivable") {
        return {
            url: `/sales/${payment.transaction_id}`,
            number: payment.sale?.sale_number || "N/A",
        };
    }
    return { url: "#", number: "N/A" };
};
</script>

<template>
    <AppLayout title="Payments">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Payment History
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left ...">Date</th>
                                <th class="px-6 py-3 text-left ...">Type</th>
                                <th class="px-6 py-3 text-right ...">Amount</th>
                                <th class="px-6 py-3 text-left ...">Method</th>
                                <th class="px-6 py-3 text-left ...">
                                    Related Transaction
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="payment in payments.data"
                                :key="payment.id"
                            >
                                <td class="px-6 py-4 ...">
                                    {{ payment.payment_date }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    <span
                                        :class="
                                            payment.transaction_type === 'Debt'
                                                ? 'text-red-500'
                                                : 'text-green-500'
                                        "
                                    >
                                        {{ payment.transaction_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right ...">
                                    {{ formatCurrency(payment.amount_paid) }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ payment.payment_method }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    <Link
                                        :href="getTransactionLink(payment).url"
                                        class="text-indigo-600 dark:text-indigo-400 hover:underline"
                                    >
                                        {{ getTransactionLink(payment).number }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
