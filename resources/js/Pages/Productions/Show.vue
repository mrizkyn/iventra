<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    production: Object,
});
</script>

<template>
    <AppLayout title="Production Detail">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Production Detail - {{ production.production_number }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div
                        class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b dark:border-gray-700"
                    >
                        <div>
                            <p class="text-sm text-gray-500">
                                Production Date:
                            </p>
                            <p class="font-semibold">
                                {{ production.production_date }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status:</p>
                            <p class="font-semibold">{{ production.status }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Notes:</p>
                            <p>{{ production.notes || "-" }}</p>
                        </div>
                    </div>
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                    >
                        Result
                    </h3>
                    <div
                        class="flex justify-between items-center bg-green-100 dark:bg-green-900 p-4 rounded-md"
                    >
                        <span
                            class="font-semibold text-green-800 dark:text-green-200"
                            >{{ production.finished_good.product_name }}</span
                        >
                        <span
                            class="font-bold text-green-800 dark:text-green-200"
                            >+{{ production.quantity_produced }}</span
                        >
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                    >
                        Materials Consumed
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="material in production.materials"
                            :key="material.id"
                            class="py-3 flex justify-between items-center"
                        >
                            <span class="text-gray-800 dark:text-gray-200">{{
                                material.raw_material.product_name
                            }}</span>
                            <span class="font-semibold text-red-500"
                                >-{{ material.quantity_used }}</span
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
