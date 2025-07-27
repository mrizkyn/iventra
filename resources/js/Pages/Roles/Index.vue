<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/vue3";

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

const props = defineProps({
    roles: Object,
    filters: Object,
    flash: Object,
});

// Search filter
const search = ref(props.filters.search || "");
watch(search, (value) => {
    router.get(
        route("roles.index"),
        { search: value },
        { preserveState: true, replace: true },
    );
});

// Export to Excel
const exportToExcel = () => {
    const rows = props.roles.data.map((role) => ({
        "Role Name": role.role_name,
        Description: role.description || "-",
        "Users Count": role.users_count,
    }));
    const worksheet = XLSX.utils.json_to_sheet(rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Roles");
    XLSX.writeFile(workbook, "Roles.xlsx");
};

// const saveRole = () => {
//     if (isEditMode.value) {
//         form.put(route("roles.update", form.id), {
//             onSuccess: () => closeModal(),
//         });
//     } else {
//         form.post(route("roles.store"), {
//             onSuccess: () => closeModal(),
//         });
//     }
// };
</script>

<template>
    <AppLayout title="Roles">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200"
                >
                    Role Management
                </h2>
                <!-- <PrimaryButton
                    @click="openModal()"
                    class="flex items-center gap-2"
                >
                    <PlusCircle class="w-4 h-4" /> Create Role
                </PrimaryButton> -->
            </div>
        </template>

        <!-- Flash Messages -->
        <div
            v-if="flash.success"
            class="bg-green-100 text-green-800 p-3 rounded mb-4"
        >
            {{ flash.success }}
        </div>
        <div
            v-if="flash.error"
            class="bg-red-100 text-red-800 p-3 rounded mb-4"
        >
            {{ flash.error }}
        </div>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-6"
                >
                    <!-- Search & Export -->
                    <div class="flex justify-between mb-4">
                        <Input
                            v-model="search"
                            placeholder="Search roles..."
                            class="max-w-sm"
                        />
                        <Button @click="exportToExcel">
                            <FileDown class="mr-2 h-4 w-4" /> Export
                        </Button>
                    </div>

                    <!-- Table Roles -->
                    <div class="rounded-md border overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Role Name</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>User Counts</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="role in roles.data"
                                    :key="role.id"
                                >
                                    <TableCell>{{ role.role_name }}</TableCell>
                                    <TableCell>{{
                                        role.description
                                    }}</TableCell>
                                    <TableCell>{{
                                        role.users_count
                                    }}</TableCell>
                                </TableRow>
                                <TableRow v-if="roles.data.length === 0">
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
                            Showing {{ roles.from }} to {{ roles.to }} of
                            {{ roles.total }} roles
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in roles.links"
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

        <!-- Modal
        <Modal :show="isModalVisible" @close="closeModal">
            <div class="p-6 bg-white dark:bg-gray-800">
                <h2 class="text-lg font-medium">
                    {{ isEditMode ? "Edit Role" : "Create Role" }}
                </h2>
                <form @submit.prevent="saveRole" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="role_name" value="Role Name" />
                        <TextInput
                            id="role_name"
                            v-model="form.role_name"
                            class="w-full"
                        />
                        <InputError
                            :message="form.errors.role_name"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="description" value="Description" />
                        <TextInput
                            id="description"
                            v-model="form.description"
                            class="w-full"
                        />
                        <InputError
                            :message="form.errors.description"
                            class="mt-2"
                        />
                    </div>
                    <div class="flex justify-end">
                        <SecondaryButton @click="closeModal"
                            >Cancel</SecondaryButton
                        >
                        <PrimaryButton class="ml-3" :disabled="form.processing">
                            {{ isEditMode ? "Update" : "Save" }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal> -->
    </AppLayout>
</template>
