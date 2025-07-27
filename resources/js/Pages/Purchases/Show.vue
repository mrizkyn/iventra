<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import { computed, ref } from "vue";
import { usePage, router, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    purchase: Object,
});

// Helper untuk format mata uang
const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);

// Helper untuk format tanggal dan waktu
const formatDateTime = (value) =>
    new Date(value).toLocaleString("id-ID", {
        dateStyle: "long",
        timeStyle: "short",
    });

// Mengambil role pengguna saat ini dari props halaman
const userRole = computed(() => usePage().props.auth.user?.role);

// Form untuk pembayaran baru
const paymentForm = useForm({
    payment_date: new Date().toISOString().slice(0, 10),
    amount_paid:
        props.purchase.remaining_debt > 0 ? props.purchase.remaining_debt : 0,
    payment_method: "Transfer",
});

const isPaymentFormVisible = ref(false);

const submitPayment = () => {
    paymentForm.post(route("payments.purchase.store", props.purchase.id), {
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.reset();
            isPaymentFormVisible.value = false;
        },
    });
};

// Fungsi approve yang sudah ada
const approvePurchase = (id) => {
    if (
        confirm(
            "Are you sure you want to approve this purchase? Stock will be updated."
        )
    ) {
        router.patch(
            route("purchases.approve", id),
            {},
            {
                preserveScroll: true,
            }
        );
    }
};
</script>

<template>
    <AppLayout title="Purchase Detail">
        <template #header>
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center"
            >
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Detail: {{ purchase.purchase_number }}
                </h2>
                <PrimaryButton
                    v-if="
                        userRole === 'Owner' &&
                        purchase.approval_status === 'Pending'
                    "
                    @click="approvePurchase(purchase.id)"
                    class="mt-4 md:mt-0"
                >
                    Approve Purchase
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div
                        class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div>
                            <h3
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Supplier
                            </h3>
                            <p
                                class="mt-1 text-lg text-gray-900 dark:text-white"
                            >
                                {{ purchase.supplier.supplier_name }}
                            </p>
                        </div>
                        <div>
                            <h3
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Date
                            </h3>
                            <p
                                class="mt-1 text-lg text-gray-900 dark:text-white"
                            >
                                {{ purchase.purchase_date }}
                            </p>
                        </div>
                        <div>
                            <h3
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Recorded By
                            </h3>
                            <p
                                class="mt-1 text-lg text-gray-900 dark:text-white"
                            >
                                {{ purchase.user.name }}
                            </p>
                        </div>
                        <div>
                            <h3
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Approval Status
                            </h3>
                            <p
                                class="mt-1 text-lg font-bold"
                                :class="{
                                    'text-green-500':
                                        purchase.approval_status === 'Approved',
                                    'text-yellow-500':
                                        purchase.approval_status === 'Pending',
                                    'text-red-500':
                                        purchase.approval_status === 'Rejected',
                                }"
                            >
                                {{ purchase.approval_status }}
                            </p>
                            <p
                                v-if="purchase.approved_at && purchase.user"
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                by {{ purchase.user.name }} on
                                {{ formatDateTime(purchase.approved_at) }}
                            </p>
                        </div>
                    </div>

                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                    >
                        Products Purchased
                    </h3>
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Product
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Quantity
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Price per Unit
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="detail in purchase.details"
                                    :key="detail.id"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                                    >
                                        {{ detail.product.product_name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ detail.quantity }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            formatCurrency(
                                                detail.buy_price_per_unit
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ formatCurrency(detail.subtotal) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-100 dark:bg-gray-900">
                                <tr>
                                    <td
                                        colspan="3"
                                        class="px-6 py-3 text-right text-sm font-bold text-gray-700 dark:text-gray-200 uppercase"
                                    >
                                        Total
                                    </td>
                                    <td
                                        class="px-6 py-3 text-right text-sm font-bold text-gray-700 dark:text-gray-200"
                                    >
                                        {{
                                            formatCurrency(purchase.total_price)
                                        }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            Payment Details
                        </h3>
                        <SecondaryButton
                            v-if="purchase.remaining_debt > 0"
                            @click="
                                isPaymentFormVisible = !isPaymentFormVisible
                            "
                        >
                            {{
                                isPaymentFormVisible
                                    ? "Cancel"
                                    : "+ Add Payment"
                            }}
                        </SecondaryButton>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div
                            class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg"
                        >
                            <h4
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Total Bill
                            </h4>
                            <p
                                class="text-xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ formatCurrency(purchase.total_price) }}
                            </p>
                        </div>
                        <div
                            class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg"
                        >
                            <h4
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Paid Amount
                            </h4>
                            <p class="text-xl font-bold text-green-500">
                                {{
                                    formatCurrency(
                                        purchase.total_price -
                                            purchase.remaining_debt
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg"
                        >
                            <h4
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Remaining Debt
                            </h4>
                            <p class="text-xl font-bold text-red-500">
                                {{ formatCurrency(purchase.remaining_debt) }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="isPaymentFormVisible"
                        class="border-t border-gray-200 dark:border-gray-700 pt-6"
                    >
                        <form
                            @submit.prevent="submitPayment"
                            class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end"
                        >
                            <div>
                                <InputLabel
                                    for="payment_date"
                                    value="Payment Date"
                                />
                                <TextInput
                                    id="payment_date"
                                    type="date"
                                    v-model="paymentForm.payment_date"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="paymentForm.errors.payment_date"
                                    class="mt-2"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    for="amount_paid"
                                    value="Amount Paid"
                                />
                                <TextInput
                                    id="amount_paid"
                                    type="number"
                                    v-model="paymentForm.amount_paid"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="paymentForm.errors.amount_paid"
                                    class="mt-2"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    for="payment_method"
                                    value="Method"
                                />
                                <select
                                    id="payment_method"
                                    v-model="paymentForm.payment_method"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                                    <option>Transfer</option>
                                    <option>Cash</option>
                                </select>
                                <InputError
                                    :message="paymentForm.errors.payment_method"
                                    class="mt-2"
                                />
                            </div>
                            <div class="flex justify-end">
                                <PrimaryButton
                                    :disabled="paymentForm.processing"
                                    >Record Payment</PrimaryButton
                                >
                            </div>
                        </form>
                    </div>

                    <h4
                        class="text-md font-medium text-gray-800 dark:text-gray-200 mt-8 mb-4"
                    >
                        Payment History
                    </h4>

                    <div
                        v-if="purchase.payments && purchase.payments.length > 0"
                        class="space-y-4"
                    >
                        <div
                            v-for="payment in purchase.payments"
                            :key="payment.id"
                            class="flex items-center justify-between bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md px-4 py-3"
                        >
                            <div class="flex items-start space-x-3">
                                <div
                                    class="text-indigo-500 dark:text-indigo-400"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c.828 0 1.5-.672 1.5-1.5S12.828 5 12 5s-1.5.672-1.5 1.5S11.172 8 12 8zM12 12v5m0-5a5 5 0 110-10 5 5 0 010 10z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-900 dark:text-white font-medium"
                                    >
                                        {{
                                            formatCurrency(payment.amount_paid)
                                        }}
                                        <span
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            ({{ payment.payment_method }})
                                        </span>
                                    </p>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        on
                                        {{
                                            formatDateTime(payment.payment_date)
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="text-center text-gray-500 dark:text-gray-400 py-4"
                    >
                        No payment history available.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
