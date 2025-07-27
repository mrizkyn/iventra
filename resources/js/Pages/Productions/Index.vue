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
    productions: Object,
    filters: Object,
});

const search = ref(props.filters.search);

// Debounce search filter
watch(search, (value) => {
    router.get(
        "/productions",
        { search: value },
        {
            preserveState: true,
            replace: true,
        },
    );
});
const exportToExcel = () => {
    const rows = props.productions.data.map((p) => {
        const materialsText = (p.materials || [])
            .map((m) => {
                const name = m.raw_material?.product_name ?? "-";
                const qty = m.quantity_used ?? 0;
                const unit = m.raw_material?.unit ?? "";
                return `${name}: ${qty} ${unit}`.trim();
            })
            .join(", ");

        return {
            "Production No.": p.production_number,
            Date: p.production_date,
            "Finished Good": p.finished_good?.product_name ?? "-",
            "Quantity Produced": p.quantity_produced,
            Status: p.status,
            "Raw Materials Used": materialsText || "-",
        };
    });

    const worksheet = XLSX.utils.json_to_sheet(rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Productions");
    XLSX.writeFile(workbook, "Production_Report.xlsx");
};
</script>

<template>
    <AppLayout title="Production">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Production History
                </h2>
                <Link href="/productions/create">
                    <PrimaryButton>New Production</PrimaryButton>
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
                            placeholder="Filter by production number or product..."
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
                                    <TableHead>Production No.</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Finished Good</TableHead>
                                    <TableHead class="text-right"
                                        >Quantity</TableHead
                                    >
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="production in productions.data"
                                    :key="production.id"
                                >
                                    <TableCell class="font-medium">{{
                                        production.production_number
                                    }}</TableCell>
                                    <TableCell>{{
                                        production.production_date
                                    }}</TableCell>
                                    <TableCell>{{
                                        production.finished_good.product_name
                                    }}</TableCell>
                                    <TableCell class="text-right">{{
                                        production.quantity_produced
                                    }}</TableCell>
                                    <TableCell>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >
                                            {{ production.status }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button
                                            as-child
                                            variant="outline"
                                            size="sm"
                                        >
                                            <Link
                                                :href="`/productions/${production.id}`"
                                                >View</Link
                                            >
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="productions.data.length === 0">
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
                            Showing {{ productions.from }} to
                            {{ productions.to }} of
                            {{ productions.total }} productions
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in productions.links"
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
