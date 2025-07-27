<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    finished_goods: Object,
    raw_materials: Object,
    assets: Object,
});

const form = useForm({
    id: null,
    product_name: "",
    product_code: "",
    product_type: "Finished Good",
    unit: "Pcs",
    minimum_stock: 0,
    sell_price: 0,
});

const activeTab = ref("products"); // 'products', 'materials', atau 'assets'

const productsToShow = computed(() => {
    if (activeTab.value === "products") return props.finished_goods.data;
    if (activeTab.value === "materials") return props.raw_materials.data;
    if (activeTab.value === "assets") return props.assets.data;
    return [];
});

const paginationData = computed(() => {
    if (activeTab.value === "products") return props.finished_goods.links;
    if (activeTab.value === "materials") return props.raw_materials.links;
    if (activeTab.value === "assets") return props.assets.links;
    return [];
});

const isModalVisible = ref(false);
const isEditMode = ref(false);

const openModal = (isEdit = false, product = null) => {
    isEditMode.value = isEdit;
    form.reset();
    if (isEdit && product) {
        form.id = product.id;
        form.product_name = product.product_name;
        form.product_code = product.product_code;
        form.product_type = product.product_type;
        form.unit = product.unit;
        form.minimum_stock = product.minimum_stock;
        form.sell_price = product.sell_price;
    } else {
        if (activeTab.value === "products") form.product_type = "Finished Good";
        if (activeTab.value === "materials") form.product_type = "Raw Material";
        if (activeTab.value === "assets") form.product_type = "Asset";
    }
    isModalVisible.value = true;
};

const closeModal = () => (isModalVisible.value = false);

const saveProduct = () => {
    const onFinish = { onSuccess: () => closeModal(), preserveScroll: true };
    if (isEditMode.value) {
        form.put(route("products.update", form.id), onFinish);
    } else {
        form.post(route("products.store"), onFinish);
    }
};

const deleteProduct = (id) => {
    if (confirm("Are you sure you want to delete this item?")) {
        useForm({}).delete(route("products.destroy", id), {
            preserveScroll: true,
        });
    }
};

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
</script>

<template>
    <AppLayout title="Products">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Product, Material, & Asset Management
                </h2>
                <PrimaryButton @click="openModal()">Create New</PrimaryButton>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div
                        class="p-6 border-b border-gray-200 dark:border-gray-700"
                    >
                        <nav class="flex space-x-4">
                            <button
                                @click="activeTab = 'products'"
                                :class="[
                                    activeTab === 'products'
                                        ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200',
                                ]"
                                class="px-3 py-2 font-medium text-sm rounded-md"
                            >
                                Finished Goods
                            </button>
                            <button
                                @click="activeTab = 'materials'"
                                :class="[
                                    activeTab === 'materials'
                                        ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200',
                                ]"
                                class="px-3 py-2 font-medium text-sm rounded-md"
                            >
                                Raw Materials
                            </button>
                            <button
                                @click="activeTab = 'assets'"
                                :class="[
                                    activeTab === 'assets'
                                        ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200',
                                ]"
                                class="px-3 py-2 font-medium text-sm rounded-md"
                            >
                                Assets
                            </button>
                        </nav>
                    </div>

                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Name
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Code
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Stock
                                    </th>
                                    <th
                                        v-if="activeTab === 'products'"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Sell Price
                                    </th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="product in productsToShow"
                                    :key="product.id"
                                >
                                    <td class="px-6 py-4 ...">
                                        {{ product.product_name }}
                                    </td>
                                    <td class="px-6 py-4 ...">
                                        {{ product.product_code }}
                                    </td>
                                    <td class="px-6 py-4 ...">
                                        {{ product.current_stock }}
                                        {{ product.unit }}
                                    </td>
                                    <td
                                        v-if="activeTab === 'products'"
                                        class="px-6 py-4 text-right ..."
                                    >
                                        {{ formatCurrency(product.sell_price) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right ... space-x-2"
                                    >
                                        <SecondaryButton
                                            @click="openModal(true, product)"
                                            >Edit</SecondaryButton
                                        >
                                        <DangerButton
                                            @click="deleteProduct(product.id)"
                                            >Delete</DangerButton
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="isModalVisible" @close="closeModal">
            <div class="p-6 bg-white dark:bg-gray-800">
                <h2
                    class="text-lg font-medium text-gray-900 dark:text-gray-100"
                >
                    {{ isEditMode ? "Edit" : "Create" }} {{ form.product_type }}
                </h2>
                <form
                    @submit.prevent="saveProduct"
                    class="mt-6 grid grid-cols-2 gap-6"
                >
                    <div>
                        <InputLabel value="Name" required />
                        <TextInput
                            v-model="form.product_name"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.product_name" />
                    </div>
                    <div>
                        <InputLabel value="Code" required />
                        <TextInput
                            v-model="form.product_code"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.product_code" />
                    </div>
                    <div>
                        <InputLabel value="Type" required />
                        <select
                            v-model="form.product_type"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 ..."
                        >
                            <option>Finished Good</option>
                            <option>Raw Material</option>
                            <option>Asset</option>
                        </select>
                        <InputError :message="form.errors.product_type" />
                    </div>
                    <div>
                        <InputLabel value="Unit (Pcs, Kg)" required />
                        <TextInput
                            v-model="form.unit"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.unit" />
                    </div>
                    <div v-if="form.product_type === 'Finished Good'">
                        <InputLabel value="Sell Price" required />
                        <TextInput
                            type="number"
                            v-model="form.sell_price"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.sell_price" />
                    </div>
                    <div>
                        <InputLabel value="Minimum Stock" required />
                        <TextInput
                            type="number"
                            v-model="form.minimum_stock"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.minimum_stock" />
                    </div>
                    <div class="col-span-2 flex justify-end pt-4">
                        <SecondaryButton @click="closeModal"
                            >Cancel</SecondaryButton
                        >
                        <PrimaryButton class="ml-3" :disabled="form.processing"
                            >Save</PrimaryButton
                        >
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
