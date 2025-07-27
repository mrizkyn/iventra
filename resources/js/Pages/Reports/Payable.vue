<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { MoreHorizontal, FileDown } from "lucide-vue-next";
import * as XLSX from "xlsx";

const props = defineProps({
    payables: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");

const filteredPayables = computed(() => {
    if (!search.value) {
        return props.payables.data;
    }
    return props.payables.data.filter(
        (payable) =>
            payable.purchase_number
                .toLowerCase()
                .includes(search.value.toLowerCase()) ||
            payable.supplier.supplier_name
                .toLowerCase()
                .includes(search.value.toLowerCase()),
    );
});

const totalPayable = computed(() => {
    return filteredPayables.value.reduce(
        (total, item) => total + parseFloat(item.remaining_debt),
        0,
    );
});

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);

const exportToExcel = () => {
    const dataToExport = filteredPayables.value.map((p) => ({
        Date: p.purchase_date,
        "Purchase No.": p.purchase_number,
        Supplier: p.supplier.supplier_name,
        "Total Bill": parseFloat(p.total_price),
        "Remaining Debt": parseFloat(p.remaining_debt),
    }));

    dataToExport.push({
        Date: "",
        "Purchase No.": "",
        Supplier: "TOTAL",
        "Total Bill": "",
        "Remaining Debt": totalPayable.value,
    });

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Accounts Payable");

    worksheet["!cols"] = [
        { wch: 12 },
        { wch: 20 },
        { wch: 30 },
        { wch: 15 },
        { wch: 15 },
    ];
    dataToExport.forEach((row, index) => {
        const rowIndex = index + 2;
        const cellRefTotal = `D${rowIndex}`;
        const cellRefRemaining = `E${rowIndex}`;
        if (worksheet[cellRefTotal] && typeof row["Total Bill"] === "number") {
            worksheet[cellRefTotal].z = '"Rp"#,##0';
        }
        if (
            worksheet[cellRefRemaining] &&
            typeof row["Remaining Debt"] === "number"
        ) {
            worksheet[cellRefRemaining].z = '"Rp"#,##0';
        }
    });

    XLSX.writeFile(workbook, "Laporan_Hutang_Usaha.xlsx");
};
</script>

<template>
    <AppLayout title="Accounts Payable Report">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Accounts Payable (Outstanding Debt)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <div class="flex items-center justify-between mb-4">
                        <form
                            @submit.prevent="
                                $inertia.get(route('reports.payable'), {
                                    search,
                                })
                            "
                            class="w-full max-w-sm"
                        >
                            <Input
                                v-model="search"
                                placeholder="Filter by PO number or supplier..."
                                class="w-full"
                            />
                        </form>
                        <Button @click="exportToExcel">
                            <FileDown class="mr-2 h-4 w-4" />
                            Export
                        </Button>
                    </div>

                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Purchase No.</TableHead>
                                    <TableHead>Supplier</TableHead>
                                    <TableHead class="text-right"
                                        >Total Bill</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Remaining Debt</TableHead
                                    >
                                    <TableHead
                                        ><span class="sr-only"
                                            >Actions</span
                                        ></TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="payable in filteredPayables"
                                    :key="payable.id"
                                >
                                    <TableCell>{{
                                        payable.purchase_date
                                    }}</TableCell>
                                    <TableCell class="font-medium">{{
                                        payable.purchase_number
                                    }}</TableCell>
                                    <TableCell>{{
                                        payable.supplier.supplier_name
                                    }}</TableCell>
                                    <TableCell class="text-right">{{
                                        formatCurrency(payable.total_price)
                                    }}</TableCell>
                                    <TableCell
                                        class="text-right text-red-500 font-semibold"
                                    >
                                        {{
                                            formatCurrency(
                                                payable.remaining_debt,
                                            )
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    class="h-8 w-8 p-0"
                                                >
                                                    <span class="sr-only"
                                                        >Open menu</span
                                                    >
                                                    <MoreHorizontal
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuLabel
                                                    >Actions</DropdownMenuLabel
                                                >
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="`/purchases/${payable.id}`"
                                                        >View Details</Link
                                                    >
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredPayables.length === 0">
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
                    <div
                        class="font-bold text-lg dark:text-white text-right mt-3"
                    >
                        Total Outstanding:
                        {{ formatCurrency(totalPayable) }}
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ payables.from }} to {{ payables.to }} of
                            {{ payables.total }} payables
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in payables.links"
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
    </AppLayout>
</template>
