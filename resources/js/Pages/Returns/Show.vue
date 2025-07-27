<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

const props = defineProps({
    // Terima prop dengan nama baru
    returnData: Object,
});

const originalTransaction = {
    number: props.returnData.purchase
        ? props.returnData.purchase.purchase_number
        : props.returnData.sale.sale_number,
    partner: props.returnData.purchase
        ? `Supplier: ${props.returnData.purchase.supplier.supplier_name}`
        : `Customer: ${props.returnData.sale.customer.customer_name}`,
    url:
        props.returnData.return_type === "Purchase"
            ? `/purchases/${props.returnData.related_transaction_id}`
            : `/sales/${props.returnData.related_transaction_id}`,
};
</script>

<template>
    <AppLayout title="Return Detail">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Return Detail: {{ returnData.return_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div
                        class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Return Date
                            </p>
                            <p
                                class="mt-1 text-lg text-gray-900 dark:text-white"
                            >
                                {{ returnData.return_date }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Return Type
                            </p>
                            <p
                                class="mt-1 text-lg font-semibold"
                                :class="
                                    returnData.return_type === 'Purchase'
                                        ? 'text-red-500'
                                        : 'text-green-500'
                                "
                            >
                                {{ returnData.return_type }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Original Transaction
                            </p>
                            <p
                                class="mt-1 text-lg text-gray-900 dark:text-white"
                            >
                                <Link
                                    :href="originalTransaction.url"
                                    class="text-indigo-600 dark:text-indigo-400 hover:underline"
                                >
                                    {{ originalTransaction.number }}
                                </Link>
                                <span
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    ({{ originalTransaction.partner }})</span
                                >
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Reason
                            </p>
                            <p
                                class="mt-1 text-base text-gray-900 dark:text-white"
                            >
                                {{ returnData.reason || "-" }}
                            </p>
                        </div>
                    </div>

                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                    >
                        Returned Items
                    </h3>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Product Name</TableHead>
                                    <TableHead class="text-right"
                                        >Quantity Returned</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="detail in returnData.details"
                                    :key="detail.id"
                                >
                                    <TableCell class="font-medium">{{
                                        detail.product.product_name
                                    }}</TableCell>
                                    <TableCell
                                        class="text-right font-semibold"
                                        >{{ detail.quantity }}</TableCell
                                    >
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
