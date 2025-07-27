<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import PurchaseForm from "./Partials/PurchaseForm.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    suppliers: Array,
    products: Array,
});

const form = useForm({
    supplier_id: "",
    purchase_date: new Date().toISOString().slice(0, 10),
    details: [],
    // Field untuk pembayaran awal
    down_payment: 0,
    payment_method: "Transfer",
});
</script>

<template>
    <AppLayout title="New Purchase">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Create New Purchase
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="form.post('/purchases')">
                    <PurchaseForm
                        :form="form"
                        :suppliers="suppliers"
                        :products="products"
                    />

                    <div
                        class="mt-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            Initial Payment
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Down Payment Input -->
                            <div>
                                <InputLabel
                                    for="down_payment"
                                    value="(Down Payment / Full Payment)"
                                />
                                <TextInput
                                    id="down_payment"
                                    type="number"
                                    v-model="form.down_payment"
                                    placeholder="Enter amount"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="form.errors.down_payment"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Payment Method Select -->
                            <div>
                                <InputLabel
                                    for="payment_method"
                                    value="Payment Method"
                                />
                                <select
                                    id="payment_method"
                                    v-model="form.payment_method"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                                    <option disabled value="">
                                        -- Select Method --
                                    </option>
                                    <option>Transfer</option>
                                    <option>Cash</option>
                                </select>
                                <InputError
                                    :message="form.errors.payment_method"
                                    class="mt-2"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            >Record Purchase</PrimaryButton
                        >
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
