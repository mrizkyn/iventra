<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { FileDown, PlusCircle } from "lucide-vue-next";
import * as XLSX from "xlsx";

// Props dari Inertia
const props = defineProps({
    users: Object, // data users dengan pagination
    roles: Array,
    filters: Object,
    flash: Object, // untuk alert flash message
});

// Search filter
const search = ref(props.filters.search || "");

// Debounce search
watch(search, (value) => {
    router.get(
        route("users.index"),
        { search: value },
        { preserveState: true, replace: true },
    );
});

// Export ke Excel
const exportToExcel = () => {
    const rows = props.users.data.map((user) => ({
        Name: user.name,
        Email: user.email,
        Role: user.role ? user.role.role_name : "-",
    }));

    const worksheet = XLSX.utils.json_to_sheet(rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Users");
    XLSX.writeFile(workbook, "Users.xlsx");
};

// Modal Create User
const isModalVisible = ref(false);
const openModal = () => {
    form.reset();
    isModalVisible.value = true;
};
const closeModal = () => (isModalVisible.value = false);

// Form Create User
const form = useForm({
    name: "",
    email: "",
    role_id: "",
    password: "",
    password_confirmation: "",
});

const saveUser = () => {
    form.post(route("users.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

// Update Role
const updateRole = (user, newRoleId) => {
    router.patch(route("users.update", user.id), { role_id: newRoleId });
};

const isOwner = (user) => user.role && user.role.role_name === "Owner";
</script>

<template>
    <AppLayout title="User Management">
        <!-- Header -->
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200"
                >
                    User Management
                </h2>
                <PrimaryButton
                    @click="openModal"
                    class="flex items-center gap-2"
                >
                    <PlusCircle class="w-4 h-4" /> Create User
                </PrimaryButton>
            </div>
        </template>

        <!-- Flash Message -->
        <div
            v-if="flash.success"
            class="bg-green-100 text-green-800 p-3 rounded mb-4"
        >
            {{ flash.success }}
        </div>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-6"
                >
                    <!-- Filter & Export -->
                    <div class="flex items-center justify-between mb-4">
                        <Input
                            v-model="search"
                            placeholder="Search name or email..."
                            class="max-w-sm"
                        />
                        <Button @click="exportToExcel">
                            <FileDown class="mr-2 h-4 w-4" /> Export
                        </Button>
                    </div>

                    <!-- Table Users -->
                    <div class="rounded-md border overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Role</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="user in users.data"
                                    :key="user.id"
                                >
                                    <TableCell>{{ user.name }}</TableCell>
                                    <TableCell>{{ user.email }}</TableCell>
                                    <TableCell>
                                        <select
                                            :disabled="isOwner(user)"
                                            @change="
                                                updateRole(
                                                    user,
                                                    $event.target.value,
                                                )
                                            "
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 ..."
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
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="users.data.length === 0">
                                    <TableCell
                                        colspan="6"
                                        class="text-center py-4 text-gray-500"
                                    >
                                        No data available.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ users.from }} to {{ users.to }} of
                            {{ users.total }} users
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in users.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                class="px-3 py-1 rounded border"
                                :class="{
                                    'bg-gray-300 dark:bg-gray-700': link.active,
                                    'text-gray-400 cursor-not-allowed':
                                        !link.url,
                                }"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create User -->
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
                            class="w-full"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="w-full"
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="role_id" value="Role" />
                        <select
                            v-model="form.role_id"
                            id="role_id"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option value="">Select Role</option>
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
                            class="w-full"
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
                            class="w-full"
                        />
                        <InputError
                            :message="form.errors.password_confirmation"
                            class="mt-2"
                        />
                    </div>
                    <div class="flex justify-end">
                        <SecondaryButton @click="closeModal"
                            >Cancel</SecondaryButton
                        >
                        <PrimaryButton class="ml-3" :disabled="form.processing">
                            Create User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
