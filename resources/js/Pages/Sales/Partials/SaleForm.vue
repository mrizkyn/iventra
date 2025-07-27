<script setup>
import { computed } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    form: Object,
    customers: Array,
    products: Array,
});

const addProduct = () => {
    props.form.details.push({
        product_id: "",
        quantity: 1,
        sell_price_per_unit: 0,
        subtotal: 0,
    });
};

const removeProduct = (index) => {
    props.form.details.splice(index, 1);
};

const updateProductDetails = (index) => {
    const detail = props.form.details[index];
    const selectedProduct = props.products.find(
        (p) => p.id === detail.product_id
    );
    if (selectedProduct) {
        detail.sell_price_per_unit = selectedProduct.sell_price || 0;
    }
};

const totalPrice = computed(() => {
    return props.form.details.reduce((total, detail) => {
        const subtotal =
            (detail.quantity || 0) * (detail.sell_price_per_unit || 0);
        detail.subtotal = subtotal;
        return total + subtotal;
    }, 0);
});

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
</script>

<template>
    <div
        class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-white dark:bg-gray-800 rounded-lg shadow"
    >
        <div>
            <InputLabel for="customer_id" value="Customer" required />
            <select
                id="customer_id"
                v-model="form.customer_id"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
            >
                <option value="">Select a customer</option>
                <option
                    v-for="customer in customers"
                    :key="customer.id"
                    :value="customer.id"
                >
                    {{ customer.customer_name }}
                </option>
            </select>
            <InputError :message="form.errors.customer_id" class="mt-2" />
        </div>
        <div>
            <InputLabel for="sale_date" value="Sale Date" required />
            <TextInput
                id="sale_date"
                type="date"
                class="mt-1 block w-full"
                v-model="form.sale_date"
            />
            <InputError :message="form.errors.sale_date" class="mt-2" />
        </div>
    </div>
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Products
        </h3>
        <InputError :message="form.errors.details" class="mt-2" />
        <div
            v-for="(detail, index) in form.details"
            :key="index"
            class="grid grid-cols-12 gap-4 items-start border-t dark:border-gray-700 pt-4 mt-4"
        >
            <div class="col-span-12 md:col-span-5">
                <InputLabel :for="'product_' + index" value="Product" />
                <select
                    :id="'product_' + index"
                    v-model="detail.product_id"
                    @change="updateProductDetails(index)"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                >
                    <option value="">Select a product</option>
                    <option
                        v-for="product in products"
                        :key="product.id"
                        :value="product.id"
                    >
                        {{ product.product_name }} (Stock:
                        {{ product.current_stock }})
                    </option>
                </select>
            </div>
            <div class="col-span-4 md:col-span-2">
                <InputLabel :for="'quantity_' + index" value="Qty" />
                <TextInput
                    :id="'quantity_' + index"
                    type="number"
                    v-model="detail.quantity"
                    min="1"
                    class="mt-1 w-full"
                />
            </div>
            <div class="col-span-8 md:col-span-2">
                <InputLabel :for="'price_' + index" value="Price" />
                <TextInput
                    :id="'price_' + index"
                    type="number"
                    v-model="detail.sell_price_per_unit"
                    class="mt-1 w-full"
                />
            </div>
            <div class="col-span-8 md:col-span-2">
                <InputLabel value="Subtotal" />
                <p class="mt-3 text-gray-700 dark:text-gray-300">
                    {{ formatCurrency(detail.subtotal) }}
                </p>
            </div>
            <div class="col-span-4 md:col-span-1 flex items-end">
                <DangerButton @click="removeProduct(index)" class="mt-6"
                    >X</DangerButton
                >
            </div>
        </div>
        <SecondaryButton @click="addProduct" class="mt-4"
            >+ Add Product</SecondaryButton
        >
    </div>
    <div class="mt-8 flex justify-end text-right">
        <div>
            <p class="text-gray-500 dark:text-gray-400">Total Price</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ formatCurrency(totalPrice) }}
            </p>
        </div>
    </div>
</template>
