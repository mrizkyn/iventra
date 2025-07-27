<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, watch } from "vue";
import { router, Link } from "@inertiajs/vue3";
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
import { toast } from "vue-sonner";
import * as XLSX from "xlsx";

const props = defineProps({
    transactions: Object,
    filters: Object,
});

// Search
const search = ref(props.filters.search || "");

watch(search, (value) => {
    router.get(
        "/cash-transactions", // ✅ ini sesuai dengan route Laravel
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

const transactionsWithLedger = computed(() => {
    const reversedData = [...props.transactions.data].reverse();

    let startingBalance = reversedData[0]
        ? reversedData[0].balance -
          (reversedData[0].debit - reversedData[0].credit)
        : 0;

    const calculated = reversedData.map((tx) => {
        const initialBalance = startingBalance;
        const finalBalance = initialBalance + tx.debit - tx.credit;
        startingBalance = finalBalance;

        return {
            ...tx,
            initial_balance: initialBalance,
            final_balance: finalBalance,
        };
    });

    return calculated.reverse();
});

const exportToExcel = () => {
    try {
        const rows = transactionsWithLedger.value.map((tx) => ({
            Date: tx.transaction_date,
            Description: tx.description,
            "Initial Balance": tx.initial_balance,
            Debit: tx.debit,
            Credit: tx.credit,
            "Final Balance": tx.final_balance,
            "Noted By": tx.user.name,
        }));

        const worksheet = XLSX.utils.json_to_sheet(rows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Cash Ledger");
        XLSX.writeFile(workbook, "Cash_Ledger.xlsx");

        toast.success("Exported successfully!");
    } catch (error) {
        toast.error("Failed to export to Excel.");
    }
};
</script>

<template>
    <AppLayout title="Cash Ledger">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Cash Ledger (Kas & Bank)
                </h2>
                <Link href="/cash-transactions/create">
                    <PrimaryButton>New Transaction</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-6"
                >
                    <div class="flex items-center justify-between mb-4">
                        <Input
                            v-model="search"
                            placeholder="Search by date, description, or user..."
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
                                    <TableHead>Description</TableHead>
                                    <TableHead class="text-right"
                                        >Initial Balance</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Debit</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Credit</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Current Balance</TableHead
                                    >
                                    <TableHead>Noted by</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="tx in transactionsWithLedger"
                                    :key="tx.id"
                                >
                                    <TableCell>{{
                                        tx.transaction_date
                                    }}</TableCell>
                                    <TableCell>{{ tx.description }}</TableCell>
                                    <TableCell class="text-right">
                                        {{ formatCurrency(tx.initial_balance) }}
                                    </TableCell>
                                    <TableCell
                                        class="text-right text-green-600"
                                    >
                                        {{
                                            tx.debit > 0
                                                ? formatCurrency(tx.debit)
                                                : "-"
                                        }}
                                    </TableCell>
                                    <TableCell class="text-right text-red-600">
                                        {{
                                            tx.credit > 0
                                                ? formatCurrency(tx.credit)
                                                : "-"
                                        }}
                                    </TableCell>
                                    <TableCell class="text-right font-semibold">
                                        {{ formatCurrency(tx.final_balance) }}
                                    </TableCell>
                                    <TableCell>{{ tx.user.name }}</TableCell>
                                </TableRow>
                                <TableRow v-if="transactions.data.length === 0">
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
                            Showing {{ transactions.from }} to
                            {{ transactions.to }} of
                            {{ transactions.total }} transactions
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in transactions.links"
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
