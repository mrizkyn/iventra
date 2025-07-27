<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({ returns: Object });
</script>
<template>
    <AppLayout title="Returns">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl ...">Return Transactions</h2>
                <Link href="/returns/create"
                    ><PrimaryButton>New Return</PrimaryButton></Link
                >
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <table class="min-w-full ...">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 ...">Return No.</th>
                                <th class="px-6 py-3 ...">Date</th>
                                <th class="px-6 py-3 ...">Type</th>
                                <th class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 ...">
                            <tr v-for="ret in returns.data" :key="ret.id">
                                <td class="px-6 py-4 ...">
                                    {{ ret.return_number }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ ret.return_date }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    <span
                                        :class="
                                            ret.return_type === 'Purchase'
                                                ? 'text-red-500'
                                                : 'text-green-500'
                                        "
                                    >
                                        {{ ret.return_type }} Return
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right ...">
                                    <Link
                                        :href="`/returns/${ret.id}`"
                                        class="text-indigo-600 ..."
                                        >View Details</Link
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
