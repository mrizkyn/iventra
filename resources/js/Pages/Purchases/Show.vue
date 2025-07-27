<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref } from "vue";
import { usePage, router, Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/Components/ui/alert-dialog";

const props = defineProps({
    purchase: Object,
});

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
const formatDateTime = (value) =>
    new Date(value).toLocaleString("id-ID", {
        dateStyle: "long",
        timeStyle: "short",
    });
const userRole = computed(() => usePage().props.auth.user?.role);

// State & fungsi untuk dialog konfirmasi
const isConfirmDialogOpen = ref(false);
const confirmDialogData = ref({
    title: "",
    description: "",
    onConfirm: () => {},
});
const showConfirmation = (title, description, onConfirm) => {
    confirmDialogData.value = { title, description, onConfirm };
    isConfirmDialogOpen.value = true;
};

// Form untuk pembayaran baru
const paymentForm = useForm({
    payment_date: new Date().toISOString().slice(0, 10),
    amount_paid:
        props.purchase.remaining_debt > 0 ? props.purchase.remaining_debt : 0,
    payment_method: "Transfer",
});
const isPaymentFormVisible = ref(false);
const submitPayment = () => {
    paymentForm.post(`/purchases/${props.purchase.id}/payments`, {
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.reset();
            isPaymentFormVisible.value = false;
        },
    });
};

const approvePurchase = (purchase) => {
    showConfirmation(
        "Approve Purchase",
        `Are you sure you want to approve PO "${purchase.purchase_number}"? Stock will be updated.`,
        () => {
            router.patch(
                `/purchases/${purchase.id}/approve`,
                {},
                { preserveScroll: true },
            );
        },
    );
};

// Fungsi untuk menghitung total qty yang sudah diretur per produk
const getReturnedQty = (productId) => {
    if (!props.purchase.returns || props.purchase.returns.length === 0) {
        return 0;
    }

    let total = 0;
    props.purchase.returns.forEach((ret) => {
        ret.details.forEach((detail) => {
            if (detail.product_id === productId) {
                total += detail.quantity;
            }
        });
    });
    return total;
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
                    @click="approvePurchase(purchase)"
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
                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Product</TableHead>
                                    <TableHead class="text-right"
                                        >Quantity</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Price per Unit</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Subtotal</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="detail in purchase.details"
                                    :key="detail.id"
                                >
                                    <TableCell
                                        class="font-medium text-gray-900 dark:text-white"
                                        >{{
                                            detail.product.product_name
                                        }}</TableCell
                                    >
                                    <TableCell
                                        class="text-right text-gray-500 dark:text-gray-400"
                                    >
                                        {{ detail.quantity }}
                                        <div
                                            v-if="
                                                getReturnedQty(
                                                    detail.product_id,
                                                ) > 0
                                            "
                                            class="text-xs text-orange-500"
                                        >
                                            (Returned:
                                            {{
                                                getReturnedQty(
                                                    detail.product_id,
                                                )
                                            }})
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="text-right text-gray-500 dark:text-gray-400"
                                        >{{
                                            formatCurrency(
                                                detail.buy_price_per_unit,
                                            )
                                        }}</TableCell
                                    >
                                    <TableCell
                                        class="text-right text-gray-500 dark:text-gray-400"
                                        >{{
                                            formatCurrency(detail.subtotal)
                                        }}</TableCell
                                    >
                                </TableRow>
                            </TableBody>
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
                        </Table>
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
                            v-if="
                                purchase.remaining_debt > 0 &&
                                purchase.return_status !== 'Fully Returned'
                            "
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
                                            purchase.remaining_debt,
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
                    <ul
                        v-if="purchase.payments && purchase.payments.length > 0"
                        class="divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <li
                            v-for="payment in purchase.payments"
                            :key="payment.id"
                            class="py-3 flex justify-between items-center"
                        >
                            <div>
                                <p
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{ formatCurrency(payment.amount_paid) }} -
                                    {{ payment.payment_method }}
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    on {{ payment.payment_date }}
                                </p>
                            </div>
                        </li>
                    </ul>
                    <div
                        v-else
                        class="text-center text-gray-500 dark:text-gray-400 py-4"
                    >
                        No payment history.
                    </div>
                </div>

                <div
                    v-if="purchase.returns && purchase.returns.length > 0"
                    class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                >
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                    >
                        Return History
                    </h3>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Return No.</TableHead>
                                    <TableHead>Return Date</TableHead>
                                    <TableHead class="text-right"
                                        >Total Qty Returned</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="ret in purchase.returns"
                                    :key="ret.id"
                                >
                                    <TableCell>
                                        <Link
                                            :href="`/returns/${ret.id}`"
                                            class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
                                        >
                                            {{ ret.return_number }}
                                        </Link>
                                    </TableCell>
                                    <TableCell>{{ ret.return_date }}</TableCell>
                                    <TableCell class="text-right">{{
                                        ret.details.reduce(
                                            (sum, d) => sum + d.quantity,
                                            0,
                                        )
                                    }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>

        <AlertDialog
            :open="isConfirmDialogOpen"
            @update:open="isConfirmDialogOpen = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{
                        confirmDialogData.title
                    }}</AlertDialogTitle>
                    <AlertDialogDescription>{{
                        confirmDialogData.description
                    }}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDialogData.onConfirm"
                        >Continue</AlertDialogAction
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
