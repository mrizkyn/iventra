<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    products: Array,
});

// Inisialisasi form dengan semua produk
const form = useForm({
    take_date: new Date().toISOString().slice(0, 10),
    notes: "",
    details: props.products.map((product) => ({
        product_id: product.id,
        product_name: product.product_name,
        product_code: product.product_code,
        system_stock: product.current_stock,
        physical_stock: product.current_stock, // Default ke stok sistem
    })),
});

const getDifference = (detail) => {
    return detail.physical_stock - detail.system_stock;
};
</script>

<template>
    <AppLayout title="New Stock Take">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Record New Stock Opname
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="form.post('/stock-takes')">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mb-8"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel
                                    for="take_date"
                                    value="Date"
                                    required
                                />
                                <TextInput
                                    id="take_date"
                                    type="date"
                                    v-model="form.take_date"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="form.errors.take_date"
                                    class="mt-2"
                                />
                            </div>
                            <div>
                                <InputLabel for="notes" value="Notes" />
                                <TextInput
                                    id="notes"
                                    type="text"
                                    v-model="form.notes"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="form.errors.notes"
                                    class="mt-2"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                    >
                        <table class="min-w-full ...">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left ...">
                                        Product
                                    </th>
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
                                    v-for="(detail, index) in form.details"
                                    :key="detail.product_id"
                                    class="border-b dark:border-gray-700"
                                >
                                    <td class="px-6 py-4 ...">
                                        {{ detail.product_name }} ({{
                                            detail.product_code
                                        }})
                                    </td>
                                    <td class="px-6 py-4 text-center ...">
                                        {{ detail.system_stock }}
                                    </td>
                                    <td class="px-6 py-4 ...">
                                        <TextInput
                                            type="number"
                                            v-model="detail.physical_stock"
                                            class="w-24 text-center mx-auto"
                                        />
                                    </td>
                                    <td
                                        class="px-6 py-4 text-center font-bold ..."
                                        :class="{
                                            'text-red-500':
                                                getDifference(detail) !== 0,
                                            'text-green-500':
                                                getDifference(detail) === 0,
                                        }"
                                    >
                                        {{ getDifference(detail) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Save Record
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
