<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    stock_takes: Object,
});
</script>

<template>
    <AppLayout title="Stock Takes">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Stock Opname History
                </h2>
                <Link href="/stock-takes/create">
                    <PrimaryButton>New Stock Take</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left ...">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left ...">
                                    Recorded By
                                </th>
                                <th scope="col" class="px-6 py-3 text-left ...">
                                    Notes
                                </th>
                                <th scope="col" class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="stock_take in stock_takes.data"
                                :key="stock_take.id"
                            >
                                <td class="px-6 py-4 ...">
                                    {{ stock_take.take_date }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ stock_take.user.name }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ stock_take.notes }}
                                </td>
                                <td class="px-6 py-4 text-right ...">
                                    <Link
                                        :href="`/stock-takes/${stock_take.id}`"
                                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
