<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    productions: Object,
});
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
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left ...">
                                    Production No.
                                </th>
                                <th scope="col" class="px-6 py-3 text-left ...">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left ...">
                                    Finished Good
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-right ..."
                                >
                                    Quantity
                                </th>
                                <th scope="col" class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="production in productions.data"
                                :key="production.id"
                            >
                                <td class="px-6 py-4 ...">
                                    {{ production.production_number }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ production.production_date }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ production.finished_good.product_name }}
                                </td>
                                <td class="px-6 py-4 text-right ...">
                                    {{ production.quantity_produced }}
                                </td>
                                <td class="px-6 py-4 text-right ...">
                                    <Link
                                        :href="`/productions/${production.id}`"
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
