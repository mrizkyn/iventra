<script setup>
import { ref, computed, watch } from "vue";
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
    returns: Object,
    filters: Object,
});

const search = ref(props.filters.search);

// Debounce search filter
watch(search, (value) => {
    router.get(
        "/returns",
        { search: value },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const exportToExcel = () => {
    const dataToExport = props.returns.data.map((r) => ({
        "Return No.": r.return_number,
        Date: r.return_date,
        Type: r.return_type,
        Reason: r.reason,
        "Original Transaction ID": r.related_transaction_id,
    }));
    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Returns");
    XLSX.writeFile(workbook, "Returns_Report.xlsx");
};
</script>

<template>
    <AppLayout title="Returns">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Return Transactions
                </h2>
                <Link href="/returns/create">
                    <PrimaryButton>New Return</PrimaryButton>
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
                            placeholder="Filter by return number or type..."
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
                                    <TableHead>Return No.</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Reason</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="ret in returns.data"
                                    :key="ret.id"
                                >
                                    <TableCell class="font-medium">{{
                                        ret.return_number
                                    }}</TableCell>
                                    <TableCell>{{ ret.return_date }}</TableCell>
                                    <TableCell>
                                        <span
                                            class="font-semibold"
                                            :class="
                                                ret.return_type === 'Purchase'
                                                    ? 'text-red-500'
                                                    : 'text-green-500'
                                            "
                                        >
                                            {{ ret.return_type }} Return
                                        </span>
                                    </TableCell>
                                    <TableCell
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                        >{{ ret.reason }}</TableCell
                                    >
                                    <TableCell class="text-right">
                                        <Button
                                            as-child
                                            variant="outline"
                                            size="sm"
                                        >
                                            <Link :href="`/returns/${ret.id}`"
                                                >View</Link
                                            >
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="returns.data.length === 0">
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
                            Showing {{ returns.from }} to {{ returns.to }} of
                            {{ returns.total }} returns
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in returns.links"
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
