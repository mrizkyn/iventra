<script setup>
import { ref, computed, watch } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";

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
import { FileDown } from "lucide-vue-next";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import * as XLSX from "xlsx";

const props = defineProps({
    purchases: Object,
    filters: Object,
});

const search = ref(props.filters.search);
const userRole = computed(() => usePage().props.auth.user?.role);

// Debounce search filter
watch(search, (value) => {
    router.get(
        "/purchases",
        { search: value },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);

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
const exportToExcel = () => {
    const dataToExport = props.purchases.data.map((p) => ({
        "PO Number": p.purchase_number,
        Date: p.purchase_date,
        Supplier: p.supplier.supplier_name,
        Total: parseFloat(p.total_price),
        "Payment Status": p.payment_status,
        "Approval Status": p.approval_status,
        "Return Status": p.return_status,
    }));

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Purchases");

    worksheet["!cols"] = [
        { wch: 20 },
        { wch: 12 },
        { wch: 30 },
        { wch: 15 },
        { wch: 15 },
        { wch: 15 },
        { wch: 15 },
    ];

    dataToExport.forEach((row, index) => {
        const rowIndex = index + 2;
        const cellRefTotal = `D${rowIndex}`;
        if (worksheet[cellRefTotal] && typeof row["Total"] === "number") {
            worksheet[cellRefTotal].z = '"Rp"#,##0';
        }
    });

    XLSX.writeFile(workbook, "Purchases_Report.xlsx");
};
</script>

<template>
    <AppLayout title="Purchases">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Purchase Transactions
                </h2>
                <Link href="/purchases/create">
                    <PrimaryButton>New Purchase</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <div class="flex items-center justify-between mb-4">
                        <Input
                            v-model="search"
                            placeholder="Filter by PO number or supplier..."
                            class="max-w-sm"
                        />
                        <Button @click="exportToExcel">
                            <FileDown class="mr-2 h-4 w-4" />
                            Export
                        </Button>
                    </div>

                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>PO Number</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Supplier</TableHead>
                                    <TableHead class="text-right"
                                        >Total</TableHead
                                    >
                                    <TableHead>Payment</TableHead>
                                    <TableHead>Approval</TableHead>
                                    <TableHead>Return</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="purchase in purchases.data"
                                    :key="purchase.id"
                                >
                                    <TableCell class="font-medium">{{
                                        purchase.purchase_number
                                    }}</TableCell>
                                    <TableCell>{{
                                        purchase.purchase_date
                                    }}</TableCell>
                                    <TableCell>{{
                                        purchase.supplier.supplier_name
                                    }}</TableCell>
                                    <TableCell class="text-right">{{
                                        formatCurrency(purchase.total_price)
                                    }}</TableCell>
                                    <TableCell>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300':
                                                    purchase.payment_status ===
                                                    'Paid',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300':
                                                    purchase.payment_status ===
                                                    'Unpaid',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300':
                                                    purchase.payment_status ===
                                                    'DP',
                                            }"
                                            >{{ purchase.payment_status }}</span
                                        >
                                    </TableCell>

                                    <TableCell>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300':
                                                    purchase.approval_status ===
                                                    'Approved',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300':
                                                    purchase.approval_status ===
                                                    'Pending',
                                            }"
                                            >{{
                                                purchase.approval_status
                                            }}</span
                                        >
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            v-if="
                                                purchase.return_status !==
                                                'No Return'
                                            "
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300"
                                        >
                                            {{ purchase.return_status }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-gray-500"
                                            >-</span
                                        >
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div
                                            class="flex items-center justify-end space-x-2"
                                        >
                                            <Button
                                                v-if="
                                                    userRole === 'Owner' &&
                                                    purchase.approval_status ===
                                                        'Pending'
                                                "
                                                @click="
                                                    approvePurchase(purchase)
                                                "
                                                size="sm"
                                            >
                                                Approve
                                            </Button>
                                            <Button
                                                as-child
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link
                                                    :href="`/purchases/${purchase.id}`"
                                                    >View</Link
                                                >
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="purchases.data.length === 0">
                                    <TableCell
                                        colspan="6"
                                        class="text-center py-4 text-gray-500"
                                    >
                                        No data available.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <!-- Pagination -->
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ purchases.from }} to
                            {{ purchases.to }} of
                            {{ purchases.total }} purchases
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in purchases.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                class="px-3 py-1 rounded border"
                                :class="{
                                    'bg-gray-300 dark:bg-gray-700': link.active,
                                    'text-gray-400 cursor-not-allowed':
                                        !link.url,
                                }"
                                v-html="link.label"
                            />
                        </nav>
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
