<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

defineProps({ suppliers: Object });

const form = useForm({
    id: null,
    supplier_name: "",
    address: "",
    phone_number: "",
    email: "",
});

const isModalVisible = ref(false);
const isEditMode = ref(false);

const openModal = (isEdit = false, supplier = null) => {
    isEditMode.value = isEdit;
    form.reset();
    if (isEdit && supplier) {
        form.id = supplier.id;
        form.supplier_name = supplier.supplier_name;
        form.address = supplier.address;
        form.phone_number = supplier.phone_number;
        form.email = supplier.email;
    }
    isModalVisible.value = true;
};

const closeModal = () => {
    isModalVisible.value = false;
};

const saveSupplier = () => {
    const onFinish = { onSuccess: () => closeModal(), preserveScroll: true };
    if (isEditMode.value) {
        form.put(route("suppliers.update", form.id), onFinish);
    } else {
        form.post(route("suppliers.store"), onFinish);
    }
};

const deleteSupplier = (id) => {
    if (confirm("Are you sure you want to delete this supplier?")) {
        useForm({}).delete(route("suppliers.destroy", id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Suppliers">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Supplier Management
                </h2>
                <PrimaryButton @click="openModal()"
                    >Create Supplier</PrimaryButton
                >
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
                                <th class="px-6 py-3 text-left ...">Name</th>
                                <th class="px-6 py-3 text-left ...">Contact</th>
                                <th class="px-6 py-3 text-left ...">Address</th>
                                <th class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="supplier in suppliers.data"
                                :key="supplier.id"
                            >
                                <td class="px-6 py-4 ...">
                                    {{ supplier.supplier_name }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    <div class="text-sm font-medium ...">
                                        {{ supplier.email }}
                                    </div>
                                    <div class="text-sm text-gray-500 ...">
                                        {{ supplier.phone_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ supplier.address }}
                                </td>
                                <td class="px-6 py-4 text-right ... space-x-2">
                                    <SecondaryButton
                                        @click="openModal(true, supplier)"
                                        >Edit</SecondaryButton
                                    >
                                    <DangerButton
                                        @click="deleteSupplier(supplier.id)"
                                        >Delete</DangerButton
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="isModalVisible" @close="closeModal">
            <div class="p-6 bg-white dark:bg-gray-800">
                <h2
                    class="text-lg font-medium text-gray-900 dark:text-gray-100"
                >
                    {{ isEditMode ? "Edit" : "Create" }} Supplier
                </h2>
                <form @submit.prevent="saveSupplier" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="supplier_name" value="Supplier Name" />
                        <TextInput
                            id="supplier_name"
                            v-model="form.supplier_name"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.supplier_name"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="phone_number" value="Phone Number" />
                        <TextInput
                            id="phone_number"
                            v-model="form.phone_number"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.phone_number"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="address" value="Address" />
                        <TextInput
                            id="address"
                            v-model="form.address"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.address"
                            class="mt-2"
                        />
                    </div>
                    <div class="flex justify-end pt-4">
                        <SecondaryButton @click="closeModal"
                            >Cancel</SecondaryButton
                        >
                        <PrimaryButton
                            class="ml-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            >Save</PrimaryButton
                        >
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
