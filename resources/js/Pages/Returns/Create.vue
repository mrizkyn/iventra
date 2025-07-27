<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    purchases: Array,
    sales: Array,
});

const transactionItems = ref([]);
const isLoadingItems = ref(false);

const form = useForm({
    return_date: new Date().toISOString().slice(0, 10),
    return_type: "Sale",
    transaction_id: "",
    reason: "",
    details: [],
});

const fetchTransactionItems = async () => {
    if (!form.transaction_id) {
        transactionItems.value = [];
        form.details = [];
        return;
    }

    isLoadingItems.value = true;
    try {
        const response = await fetch(
            `/transactions/${form.return_type}/${form.transaction_id}`,
        );
        if (!response.ok) throw new Error("Failed to fetch items");
        const data = await response.json();
        transactionItems.value = data;
        form.details = [];
    } catch (error) {
        console.error("Error fetching transaction items:", error);
        transactionItems.value = [];
    } finally {
        isLoadingItems.value = false;
    }
};

watch(() => form.transaction_id, fetchTransactionItems);
watch(
    () => form.return_type,
    () => {
        form.transaction_id = "";
        transactionItems.value = [];
        form.details = [];
    },
);

const addDetail = () => {
    form.details.push({ product_id: "", quantity: 1, max_quantity: 0 });
};

const removeDetail = (index) => {
    form.details.splice(index, 1);
};

const updateDetailMaxQuantity = (detailIndex) => {
    const selectedItem = transactionItems.value.find(
        (item) => item.product_id === form.details[detailIndex].product_id,
    );
    if (selectedItem) {
        form.details[detailIndex].max_quantity = selectedItem.max_quantity;
        form.details[detailIndex].quantity = 1;
    }
};
</script>

<template>
    <AppLayout title="New Return">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Record New Return
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="form.post('/returns')">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mb-8"
                    >
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <InputLabel value="Return Date" required />
                                <TextInput
                                    type="date"
                                    v-model="form.return_date"
                                    class="mt-1 w-full"
                                />
                                <InputError
                                    :message="form.errors.return_date"
                                />
                            </div>
                            <div>
                                <InputLabel value="Return Type" required />
                                <select
                                    v-model="form.return_type"
                                    class="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"
                                >
                                    <option>Sale</option>
                                    <option>Purchase</option>
                                </select>
                                <InputError
                                    :message="form.errors.return_type"
                                />
                            </div>
                            <div class="col-span-2">
                                <InputLabel
                                    value="Related Transaction"
                                    required
                                />
                                <select
                                    v-model="form.transaction_id"
                                    class="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">
                                        Select transaction...
                                    </option>
                                    <template
                                        v-if="form.return_type === 'Sale'"
                                    >
                                        <option
                                            v-for="s in sales"
                                            :key="s.id"
                                            :value="s.id"
                                        >
                                            {{ s.sale_number }}
                                        </option>
                                    </template>
                                    <template v-else>
                                        <option
                                            v-for="p in purchases"
                                            :key="p.id"
                                            :value="p.id"
                                        >
                                            {{ p.purchase_number }}
                                        </option>
                                    </template>
                                </select>
                                <InputError
                                    :message="form.errors.transaction_id"
                                />
                            </div>
                            <div class="col-span-2">
                                <InputLabel value="Reason" />
                                <TextInput
                                    v-model="form.reason"
                                    class="mt-1 w-full"
                                />
                                <InputError :message="form.errors.reason" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            Items to Return
                        </h3>
                        <div
                            v-if="isLoadingItems"
                            class="text-center py-4 text-gray-500"
                        >
                            Loading items...
                        </div>
                        <div
                            v-if="
                                !isLoadingItems &&
                                form.transaction_id &&
                                transactionItems.length === 0
                            "
                            class="text-center py-4 text-red-500"
                        >
                            Could not load items for this transaction.
                        </div>

                        <div
                            v-for="(detail, index) in form.details"
                            :key="index"
                            class="grid grid-cols-12 gap-4 items-center border-t dark:border-gray-700 pt-4 mt-4"
                        >
                            <div class="col-span-8">
                                <InputLabel :value="`Item ${index + 1}`" />
                                <select
                                    v-model="detail.product_id"
                                    @change="updateDetailMaxQuantity(index)"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Select item...</option>
                                    <option
                                        v-for="item in transactionItems"
                                        :key="item.product_id"
                                        :value="item.product_id"
                                    >
                                        {{ item.product_name }} (Max:
                                        {{ item.max_quantity }})
                                    </option>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <InputLabel value="Qty" />
                                <TextInput
                                    type="number"
                                    min="1"
                                    :max="detail.max_quantity"
                                    v-model="detail.quantity"
                                    class="mt-1 w-full"
                                />
                            </div>
                            <div class="col-span-1 flex items-end">
                                <DangerButton
                                    type="button"
                                    @click="removeDetail(index)"
                                    class="mt-6"
                                    >X</DangerButton
                                >
                            </div>
                        </div>
                        <SecondaryButton
                            type="button"
                            @click="addDetail"
                            class="mt-4"
                            :disabled="!form.transaction_id || isLoadingItems"
                            >+ Add Item</SecondaryButton
                        >
                    </div>

                    <div class="mt-8 flex justify-end">
                        <PrimaryButton :disabled="form.processing"
                            >Save Return</PrimaryButton
                        >
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
