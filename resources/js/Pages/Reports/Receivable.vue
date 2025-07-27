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
    receivables: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");

const filteredReceivables = computed(() => {
    if (!search.value) {
        return props.receivables.data;
    }
    return props.receivables.data.filter(
        (receivable) =>
            receivable.sale_number
                .toLowerCase()
                .includes(search.value.toLowerCase()) ||
            receivable.customer.customer_name
                .toLowerCase()
                .includes(search.value.toLowerCase()),
    );
});

const totalReceivable = computed(() => {
    return filteredReceivables.value.reduce(
        (total, item) => total + parseFloat(item.remaining_receivable),
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
    const dataToExport = filteredReceivables.value.map((r) => ({
        Date: r.sale_date,
        "Sale No.": r.sale_number,
        Customer: r.customer.customer_name,
        "Total Bill": parseFloat(r.total_price),
        "Remaining Receivable": parseFloat(r.remaining_receivable),
    }));

    dataToExport.push({
        Date: "",
        "Sale No.": "",
        Customer: "TOTAL",
        "Total Bill": "",
        "Remaining Receivable": totalReceivable.value,
    });

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Accounts Receivable");

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
            typeof row["Remaining Receivable"] === "number"
        ) {
            worksheet[cellRefRemaining].z = '"Rp"#,##0';
        }
    });

    XLSX.writeFile(workbook, "Laporan_Piutang_Usaha.xlsx");
};
</script>

<template>
    <AppLayout title="Accounts Receivable Report">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Accounts Receivable (Outstanding Invoice)
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
                                $inertia.get(route('reports.receivable'), {
                                    search,
                                })
                            "
                            class="w-full max-w-sm"
                        >
                            <Input
                                v-model="search"
                                placeholder="Filter by SO number or customer..."
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
                                    <TableHead>Sale No.</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead class="text-right"
                                        >Total Bill</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Remaining Receivable</TableHead
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
                                    v-for="receivable in filteredReceivables"
                                    :key="receivable.id"
                                >
                                    <TableCell>{{
                                        receivable.sale_date
                                    }}</TableCell>
                                    <TableCell class="font-medium">{{
                                        receivable.sale_number
                                    }}</TableCell>
                                    <TableCell>{{
                                        receivable.customer.customer_name
                                    }}</TableCell>
                                    <TableCell class="text-right">{{
                                        formatCurrency(receivable.total_price)
                                    }}</TableCell>
                                    <TableCell
                                        class="text-right text-green-500 font-semibold"
                                        >{{
                                            formatCurrency(
                                                receivable.remaining_receivable,
                                            )
                                        }}</TableCell
                                    >
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
                                                        :href="`/sales/${receivable.id}`"
                                                        >View Details</Link
                                                    >
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-if="filteredReceivables.length === 0"
                                >
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
                    <div
                        class="font-bold text-lg dark:text-white text-right mt-3"
                    >
                        Total Outstanding:
                        {{ formatCurrency(totalReceivable) }}
                    </div>
                    <!-- Pagination -->
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ receivables.from }} to
                            {{ receivables.to }} of
                            {{ receivables.total }} receivables
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in receivables.links"
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
