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

defineProps({
    customers: Object,
});

const form = useForm({
    id: null,
    customer_name: "",
    address: "",
    phone_number: "",
    email: "",
});

const isModalVisible = ref(false);
const isEditMode = ref(false);

const openModal = (isEdit = false, customer = null) => {
    isEditMode.value = isEdit;
    form.reset(); // Selalu reset form saat modal dibuka
    if (isEdit && customer) {
        form.id = customer.id;
        form.customer_name = customer.customer_name;
        form.address = customer.address;
        form.phone_number = customer.phone_number;
        form.email = customer.email;
    }
    isModalVisible.value = true;
};

const closeModal = () => {
    isModalVisible.value = false;
};

const saveCustomer = () => {
    const onFinish = {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    };
    if (isEditMode.value) {
        form.put(route("customers.update", form.id), onFinish);
    } else {
        form.post(route("customers.store"), onFinish);
    }
};

const deleteCustomer = (id) => {
    if (confirm("Are you sure you want to delete this customer?")) {
        useForm({}).delete(route("customers.destroy", id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Customers">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Customer Management
                </h2>
                <PrimaryButton @click="openModal()"
                    >Create Customer</PrimaryButton
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
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    Name
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    Contact
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    Address
                                </th>
                                <th class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="customer in customers.data"
                                :key="customer.id"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    {{ customer.customer_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm text-gray-900 dark:text-white"
                                    >
                                        {{ customer.email }}
                                    </div>
                                    <div
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ customer.phone_number }}
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ customer.address }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2"
                                >
                                    <SecondaryButton
                                        @click="openModal(true, customer)"
                                        >Edit</SecondaryButton
                                    >
                                    <DangerButton
                                        @click="deleteCustomer(customer.id)"
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
                    {{ isEditMode ? "Edit" : "Create" }} Customer
                </h2>
                <form @submit.prevent="saveCustomer" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="customer_name" value="Customer Name" />
                        <TextInput
                            id="customer_name"
                            v-model="form.customer_name"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            :message="form.errors.customer_name"
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
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="phone_number" value="Phone Number" />
                        <TextInput
                            id="phone_number"
                            v-model="form.phone_number"
                            class="mt-1 block w-full"
                            required
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
                            required
                        />
                        <InputError
                            :message="form.errors.address"
                            class="mt-2"
                        />
                    </div>
                    <div
                        class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700"
                    >
                        <SecondaryButton @click="closeModal"
                            >Cancel</SecondaryButton
                        >
                        <PrimaryButton
                            class="ml-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{
                                isEditMode ? "Update Customer" : "Save Customer"
                            }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
