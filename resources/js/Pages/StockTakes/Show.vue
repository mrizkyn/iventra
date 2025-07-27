<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    stockTake: Object,
});
</script>

<template>
    <AppLayout title="Stock Take Detail">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Stock Opname Detail - {{ stockTake.take_date }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mb-8"
                >
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Date:</p>
                            <p>{{ stockTake.take_date }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Recorded By:</p>
                            <p>{{ stockTake.user.name }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Notes:</p>
                            <p>{{ stockTake.notes || "-" }}</p>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <table class="min-w-full ...">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left ...">Product</th>
                                <th class="px-6 py-3 text-center ...">
                                    System Stock
                                </th>
                                <th class="px-6 py-3 text-center ...">
                                    Physical Stock
                                </th>
                                <th class="px-6 py-3 text-center ...">
                                    Difference
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="detail in stockTake.details"
                                :key="detail.id"
                                class="border-b dark:border-gray-700"
                            >
                                <td class="px-6 py-4 ...">
                                    {{ detail.product.product_name }}
                                </td>
                                <td class="px-6 py-4 text-center ...">
                                    {{ detail.system_stock }}
                                </td>
                                <td class="px-6 py-4 text-center ...">
                                    {{ detail.physical_stock }}
                                </td>
                                <td
                                    class="px-6 py-4 text-center font-bold ..."
                                    :class="{
                                        'text-red-500': detail.difference !== 0,
                                        'text-green-500':
                                            detail.difference === 0,
                                    }"
                                >
                                    {{ detail.difference }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
