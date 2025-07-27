<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

// ✅ Ganti 'return' jadi 'returnData' agar tidak tabrakan dengan keyword JS
defineProps({ returnData: Object });
</script>

<template>
    <AppLayout title="Return Detail">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Return Detail: {{ returnData.return_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div class="grid grid-cols-2 gap-6 mb-4">
                        <p>
                            <strong>Date:</strong> {{ returnData.return_date }}
                        </p>
                        <p>
                            <strong>Type:</strong> {{ returnData.return_type }}
                        </p>
                        <p class="col-span-2">
                            <strong>Reason:</strong> {{ returnData.reason }}
                        </p>
                        <p class="col-span-2">
                            <strong>Original Transaction:</strong>
                            <span v-if="returnData.purchase">
                                {{
                                    returnData.purchase.purchase_number
                                }}
                                (Supplier:
                                {{
                                    returnData.purchase.supplier.supplier_name
                                }})
                            </span>
                            <span v-if="returnData.sale">
                                {{ returnData.sale.sale_number }} (Customer:
                                {{ returnData.sale.customer.customer_name }})
                            </span>
                        </p>
                    </div>

                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-white mt-6"
                    >
                        Returned Items
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="detail in returnData.details"
                            :key="detail.id"
                            class="py-3 flex justify-between"
                        >
                            <span>{{ detail.product.product_name }}</span>
                            <span class="font-semibold">{{
                                detail.quantity
                            }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
