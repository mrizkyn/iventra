<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    roles: Object,
});

const form = useForm({
    id: null,
    role_name: '',
    description: '',
});

const isModalVisible = ref(false);
const isEditMode = ref(false);

const openModal = (isEdit = false, role = null) => {
    isEditMode.value = isEdit;
    if (isEdit && role) {
        form.id = role.id;
        form.role_name = role.role_name;
        form.description = role.description;
    } else {
        form.reset();
    }
    isModalVisible.value = true;
};

const closeModal = () => {
    isModalVisible.value = false;
    form.reset();
};

const saveRole = () => {
    if (isEditMode.value) {
        form.put(route('roles.update', form.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('roles.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteRole = (role) => {
    if (confirm(`Are you sure you want to delete the role "${role.role_name}"?`)) {
        useForm({}).delete(route('roles.destroy', role.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Roles">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Role Management</h2>
                <PrimaryButton @click="openModal()">Create Role</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Role Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Users</th>
                                <th class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="role in roles.data" :key="role.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ role.role_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ role.description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ role.users_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <SecondaryButton @click="openModal(true, role)">Edit</SecondaryButton>
                                    <DangerButton @click="deleteRole(role)">Delete</DangerButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="isModalVisible" @close="closeModal">
            <div class="p-6 bg-white dark:bg-gray-800">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ isEditMode ? 'Edit' : 'Create' }} Role</h2>
                <form @submit.prevent="saveRole" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="role_name" value="Role Name" />
                        <TextInput id="role_name" type="text" v-model="form.role_name" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.role_name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="description" value="Description" />
                        <TextInput id="description" type="text" v-model="form.description" class="mt-1 block w-full" />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>
                    <div class="flex justify-end">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ isEditMode ? 'Update' : 'Save' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>