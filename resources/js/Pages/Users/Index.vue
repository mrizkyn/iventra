<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

defineProps({
    users: Object,
    roles: Array,
});

// Form untuk user baru
const form = useForm({
    name: "",
    email: "",
    role_id: "",
    password: "",
    password_confirmation: "",
});

const isModalVisible = ref(false);

const openModal = () => {
    form.reset();
    isModalVisible.value = true;
};

const closeModal = () => {
    isModalVisible.value = false;
};

const saveUser = () => {
    form.post(route("users.store"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

// Fungsi untuk update role yang sudah ada
const updateRole = (user, newRoleId) => {
    router.patch(
        route("users.update", user.id),
        { role_id: newRoleId },
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <AppLayout title="Users">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    User Management
                </h2>
                <PrimaryButton @click="openModal">Create User</PrimaryButton>
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
                                <th class="px-6 py-3 text-left ...">Email</th>
                                <th class="px-6 py-3 text-left ...">Role</th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="px-6 py-4 ...">{{ user.name }}</td>
                                <td class="px-6 py-4 ...">{{ user.email }}</td>
                                <td class="px-6 py-4 ...">
                                    <select
                                        @change="
                                            updateRole(
                                                user,
                                                $event.target.value
                                            )
                                        "
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 ... rounded-md shadow-sm"
                                    >
                                        <option
                                            v-for="role in roles"
                                            :key="role.id"
                                            :value="role.id"
                                            :selected="
                                                user.role &&
                                                user.role.id === role.id
                                            "
                                        >
                                            {{ role.role_name }}
                                        </option>
                                    </select>
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
                    Create New User
                </h2>
                <form @submit.prevent="saveUser" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            type="text"
                            v-model="form.name"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
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
                        <InputLabel for="role_id" value="Role" />
                        <select
                            v-model="form.role_id"
                            id="role_id"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 ... rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select a role</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.role_name }}
                            </option>
                        </select>
                        <InputError
                            :message="form.errors.role_id"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            :message="form.errors.password"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirm Password"
                        />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            :message="form.errors.password_confirmation"
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
                        >
                            Create User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
