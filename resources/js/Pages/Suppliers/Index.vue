<script setup>
import { ref, computed, watch } from "vue";
import { useForm, router, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/Components/ui/alert-dialog";
import { MoreHorizontal, Trash2, FileDown } from "lucide-vue-next";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import * as XLSX from "xlsx";

const props = defineProps({
    suppliers: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const selectedRows = ref([]);

const filteredSuppliers = computed(() => {
    if (!search.value) return props.suppliers.data;
    return props.suppliers.data.filter(
        (s) =>
            s.supplier_name
                .toLowerCase()
                .includes(search.value.toLowerCase()) ||
            s.email.toLowerCase().includes(search.value.toLowerCase()),
    );
});

watch(search, (value) => {
    router.get(
        route("suppliers.index"),
        { search: value },
        { preserveState: true, replace: true },
    );
});

const isAllSelected = computed(
    () =>
        filteredSuppliers.value.length > 0 &&
        selectedRows.value.length === filteredSuppliers.value.length,
);

const selectAllRows = (event) => {
    selectedRows.value = event.target.checked
        ? filteredSuppliers.value.map((s) => s.id)
        : [];
};

const isConfirmDialogOpen = ref(false);
const confirmDialogData = ref({
    title: "",
    description: "",
    onConfirm: () => {},
});

const showConfirmation = (title, description, onConfirm) => {
    confirmDialogData.value = { title, description, onConfirm };
    isConfirmDialogOpen.value = true;
};

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

const closeModal = () => (isModalVisible.value = false);

const saveSupplier = () => {
    const onFinish = { onSuccess: () => closeModal(), preserveScroll: true };
    if (isEditMode.value) {
        form.put(route("suppliers.update", form.id), onFinish);
    } else {
        form.post(route("suppliers.store"), onFinish);
    }
};

const deleteSupplier = (supplier) => {
    showConfirmation(
        "Delete Supplier",
        `Are you sure you want to delete "${supplier.supplier_name}"?`,
        () => {
            router.delete(route("suppliers.destroy", supplier.id), {
                preserveScroll: true,
            });
        },
    );
};

const deleteSelected = () => {
    if (selectedRows.value.length === 0) return;
    showConfirmation(
        "Delete Selected Suppliers",
        `Are you sure you want to delete ${selectedRows.value.length} selected suppliers?`,
        () => {
            router.delete(route("suppliers.destroyMultiple"), {
                data: { ids: selectedRows.value },
                preserveScroll: true,
                onSuccess: () => (selectedRows.value = []),
            });
        },
    );
};

const exportToExcel = () => {
    const dataToExport = filteredSuppliers.value.map((s) => ({
        "Supplier Name": s.supplier_name,
        Email: s.email,
        Phone: s.phone_number,
        Address: s.address,
    }));

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Suppliers");
    XLSX.writeFile(workbook, "Suppliers_List.xlsx");
};
</script>

<template>
    <AppLayout title="Suppliers">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="text-xl font-semibold text-gray-800 dark:text-gray-200"
                >
                    Supplier Management
                </h2>
                <PrimaryButton @click="openModal()">New Supplier</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <Input
                            v-model="search"
                            placeholder="Search supplier..."
                            class="max-w-sm"
                        />
                        <div class="flex items-center space-x-2">
                            <Button
                                variant="destructive"
                                @click="deleteSelected"
                                v-if="selectedRows.length > 0"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete ({{ selectedRows.length }})
                            </Button>
                            <Button @click="exportToExcel">
                                <FileDown class="mr-2 h-4 w-4" />
                                Export
                            </Button>
                        </div>
                    </div>

                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[40px] px-4">
                                        <input
                                            type="checkbox"
                                            :checked="isAllSelected"
                                            @change="selectAllRows"
                                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800 dark:bg-gray-900"
                                        />
                                    </TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Contact</TableHead>
                                    <TableHead>Address</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="supplier in filteredSuppliers"
                                    :key="supplier.id"
                                >
                                    <TableCell class="px-4">
                                        <input
                                            type="checkbox"
                                            :value="supplier.id"
                                            v-model="selectedRows"
                                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800 dark:bg-gray-900"
                                        />
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ supplier.supplier_name }}
                                    </TableCell>
                                    <TableCell>
                                        <div>{{ supplier.email }}</div>
                                        <div
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ supplier.phone_number }}
                                        </div>
                                    </TableCell>
                                    <TableCell>{{
                                        supplier.address
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    class="h-8 w-8 p-0"
                                                >
                                                    <MoreHorizontal
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuLabel
                                                    >Actions</DropdownMenuLabel
                                                >
                                                <DropdownMenuItem
                                                    @click="
                                                        openModal(
                                                            true,
                                                            supplier,
                                                        )
                                                    "
                                                >
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    @click="
                                                        deleteSupplier(supplier)
                                                    "
                                                    class="text-red-600"
                                                >
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredSuppliers.length === 0">
                                    <TableCell
                                        colspan="5"
                                        class="text-center py-4 text-gray-500"
                                    >
                                        No suppliers found
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ suppliers.from }} to
                            {{ suppliers.to }} of
                            {{ suppliers.total }} suppliers
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in suppliers.links"
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

        <!-- Modal -->
        <Modal :show="isModalVisible" @close="closeModal">
            <div class="p-6 bg-white dark:bg-gray-800">
                <h2
                    class="text-lg font-medium text-gray-900 dark:text-gray-100"
                >
                    {{ isEditMode ? "Edit" : "Create" }} Supplier
                </h2>
                <form @submit.prevent="saveSupplier" class="mt-6 space-y-4">
                    <div>
                        <InputLabel
                            for="supplier_name"
                            value="Supplier Name"
                            required
                        />
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
                        <InputLabel for="email" value="Email" required />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel
                            for="phone_number"
                            value="Phone Number"
                            required
                        />
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
                        <SecondaryButton @click="closeModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton class="ml-3" :disabled="form.processing">
                            {{ isEditMode ? "Update" : "Create" }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Confirmation Dialog -->
        <AlertDialog
            :open="isConfirmDialogOpen"
            @update:open="isConfirmDialogOpen = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{
                        confirmDialogData.title
                    }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ confirmDialogData.description }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDialogData.onConfirm">
                        Continue
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
