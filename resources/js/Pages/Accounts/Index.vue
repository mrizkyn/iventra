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

defineProps({ accounts: Object });

const form = useForm({
    id: null,
    account_code: "",
    account_name: "",
    category: "Expense",
});

const isModalVisible = ref(false);
const isEditMode = ref(false);

const openModal = (isEdit = false, account = null) => {
    isEditMode.value = isEdit;
    form.reset();
    if (isEdit && account) {
        form.id = account.id;
        form.account_code = account.account_code;
        form.account_name = account.account_name;
        form.category = account.category;
    }
    isModalVisible.value = true;
};

const closeModal = () => {
    isModalVisible.value = false;
};

const saveAccount = () => {
    const onFinish = { onSuccess: () => closeModal(), preserveScroll: true };
    if (isEditMode.value) {
        form.put(route("accounts.update", form.id), onFinish);
    } else {
        form.post(route("accounts.store"), onFinish);
    }
};

const deleteAccount = (id) => {
    if (confirm("Are you sure?")) {
        useForm({}).delete(route("accounts.destroy", id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Chart of Accounts">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Chart of Accounts
                </h2>
                <PrimaryButton @click="openModal()">New Account</PrimaryButton>
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
                                <th class="px-6 py-3 text-left ...">Code</th>
                                <th class="px-6 py-3 text-left ...">
                                    Account Name
                                </th>
                                <th class="px-6 py-3 text-left ...">
                                    Category
                                </th>
                                <th class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 ...">
                            <tr
                                v-for="account in accounts.data"
                                :key="account.id"
                            >
                                <td class="px-6 py-4 ...">
                                    {{ account.account_code }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ account.account_name }}
                                </td>
                                <td class="px-6 py-4 ...">
                                    {{ account.category }}
                                </td>
                                <td class="px-6 py-4 text-right ... space-x-2">
                                    <SecondaryButton
                                        @click="openModal(true, account)"
                                        >Edit</SecondaryButton
                                    >
                                    <DangerButton
                                        @click="deleteAccount(account.id)"
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
                <h2 class="text-lg ...">
                    {{ isEditMode ? "Edit" : "Create" }} Account
                </h2>
                <form @submit.prevent="saveAccount" class="mt-6 space-y-4">
                    <div>
                        <InputLabel value="Category" required />
                        <select
                            v-model="form.category"
                            class="mt-1 block w-full ..."
                        >
                            <option>Asset</option>
                            <option>Liability</option>
                            <option>Equity</option>
                            <option>Revenue</option>
                            <option>Expense</option>
                        </select>
                        <InputError :message="form.errors.category" />
                    </div>
                    <div>
                        <InputLabel value="Account Code" required />
                        <TextInput
                            v-model="form.account_code"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.account_code" />
                    </div>
                    <div>
                        <InputLabel value="Account Name" required />
                        <TextInput
                            v-model="form.account_name"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.account_name" />
                    </div>
                    <div class="flex justify-end pt-4">
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
