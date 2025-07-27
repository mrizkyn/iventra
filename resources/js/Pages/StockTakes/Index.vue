<script setup>
import { ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
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
import { FileDown } from "lucide-vue-next";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import * as XLSX from "xlsx";

const props = defineProps({
    stock_takes: Object,
    filters: Object,
});

const search = ref(props.filters.search);

// Debounce search filter
watch(search, (value) => {
    router.get(
        "/stock-takes",
        { search: value },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const exportToExcel = () => {
    const rows = [];

    props.stock_takes.data.forEach((st) => {
        st.details.forEach((detail) => {
            rows.push({
                Date: st.take_date,
                "Recorded By": st.user.name,
                Notes: st.notes || "-",
                "Product Name": detail.product.product_name,
                "System Stock": detail.system_stock,
                "Physical Stock": detail.physical_stock,
                Difference: detail.difference,
            });
        });
    });

    const worksheet = XLSX.utils.json_to_sheet(rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Stock Take Details");
    XLSX.writeFile(workbook, "Stock_Take_Details.xlsx");
};
</script>

<template>
    <AppLayout title="Stock Takes">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Stock Take History
                </h2>
                <Link href="/stock-takes/create">
                    <PrimaryButton>New Stock Take</PrimaryButton>
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
                            placeholder="Filter by date or user..."
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
                                    <TableHead>Date</TableHead>
                                    <TableHead>Recorded By</TableHead>
                                    <TableHead>Notes</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="stock_take in stock_takes.data"
                                    :key="stock_take.id"
                                >
                                    <TableCell class="font-medium">{{
                                        stock_take.take_date
                                    }}</TableCell>
                                    <TableCell>{{
                                        stock_take.user.name
                                    }}</TableCell>
                                    <TableCell
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                        >{{ stock_take.notes }}</TableCell
                                    >
                                    <TableCell class="text-right">
                                        <Button
                                            as-child
                                            variant="outline"
                                            size="sm"
                                        >
                                            <Link
                                                :href="`/stock-takes/${stock_take.id}`"
                                                >View</Link
                                            >
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="stock_takes.data.length === 0">
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
                            Showing {{ stock_takes.from }} to
                            {{ stock_takes.to }} of
                            {{ stock_takes.total }} stock takes
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in stock_takes.links"
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
