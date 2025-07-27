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
    finished_goods: Object,
    raw_materials: Object,
    assets: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const activeTab = ref("products");
const selectedRows = ref([]);

// Server-side search
watch([search, activeTab], ([searchValue, tab]) => {
    const queryParams = {
        search: searchValue,
        page_type:
            tab === "products"
                ? "fg_page"
                : tab === "materials"
                  ? "rm_page"
                  : "as_page",
    };

    router.get(route("products.index"), queryParams, {
        preserveState: true,
        replace: true,
    });
});

const dataForCurrentTab = computed(() => {
    if (activeTab.value === "products") return props.finished_goods.data;
    if (activeTab.value === "materials") return props.raw_materials.data;
    if (activeTab.value === "assets") return props.assets.data;
    return [];
});

const isAllSelected = computed(
    () =>
        dataForCurrentTab.value.length > 0 &&
        selectedRows.value.length === dataForCurrentTab.value.length,
);

const selectAllRows = (event) => {
    selectedRows.value = event.target.checked
        ? dataForCurrentTab.value.map((p) => p.id)
        : [];
};

// Confirmation dialog
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

// Form handling
const form = useForm({
    id: null,
    product_name: "",
    product_code: "",
    product_type: "Finished Good",
    unit: "Pcs",
    minimum_stock: 0,
    sell_price: 0,
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

const deleteProduct = (product) => {
    showConfirmation(
        `Delete ${product.product_type}`,
        `Are you sure you want to delete "${product.product_name}"?`,
        () => {
            router.delete(route("products.destroy", product.id), {
                preserveScroll: true,
            });
        },
    );
};

const deleteSelected = () => {
    if (selectedRows.value.length === 0) return;
    showConfirmation(
        "Delete Selected Items",
        `Are you sure you want to delete ${selectedRows.value.length} selected items?`,
        () => {
            router.delete(route("products.destroyMultiple"), {
                data: { ids: selectedRows.value },
                preserveScroll: true,
                onSuccess: () => (selectedRows.value = []),
            });
        },
    );
};

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);

const exportToExcel = () => {
    const dataToExport = dataForCurrentTab.value.map((p) => {
        let record = {
            Name: p.product_name,
            Code: p.product_code,
            Type: p.product_type,
            Stock: p.current_stock,
            Unit: p.unit,
        };
        if (p.product_type === "Finished Good") {
            record["Sell Price"] = parseFloat(p.sell_price);
        }
        return record;
    });

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Products");
    XLSX.writeFile(workbook, `Products_Report_${activeTab.value}.xlsx`);
};
</script>

<template>
    <AppLayout title="Products">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="text-xl font-semibold text-gray-800 dark:text-gray-200"
                >
                    Product, Material & Asset Management
                </h2>
                <PrimaryButton @click="openModal()">Create New</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
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
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <form
                                @submit.prevent="
                                    router.get(route('products.index'), {
                                        search,
                                        page_type:
                                            activeTab === 'products'
                                                ? 'fg_page'
                                                : activeTab === 'materials'
                                                  ? 'rm_page'
                                                  : 'as_page',
                                    })
                                "
                                class="w-full max-w-sm"
                            >
                                <Input
                                    v-model="search"
                                    placeholder="Search by name or code..."
                                    class="w-full"
                                />
                            </form>
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
                                        <TableHead>Code</TableHead>
                                        <TableHead>Stock</TableHead>
                                        <TableHead
                                            v-if="activeTab === 'products'"
                                            class="text-right"
                                        >
                                            Sell Price
                                        </TableHead>
                                        <TableHead class="text-right"
                                            >Actions</TableHead
                                        >
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="product in dataForCurrentTab"
                                        :key="product.id"
                                    >
                                        <TableCell class="px-4">
                                            <input
                                                type="checkbox"
                                                :value="product.id"
                                                v-model="selectedRows"
                                                class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800 dark:bg-gray-900"
                                            />
                                        </TableCell>
                                        <TableCell class="font-medium">
                                            {{ product.product_name }}
                                        </TableCell>
                                        <TableCell>{{
                                            product.product_code
                                        }}</TableCell>
                                        <TableCell>
                                            {{ product.current_stock }}
                                            {{ product.unit }}
                                        </TableCell>
                                        <TableCell
                                            v-if="activeTab === 'products'"
                                            class="text-right"
                                        >
                                            {{
                                                formatCurrency(
                                                    product.sell_price,
                                                )
                                            }}
                                        </TableCell>
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
                                                <DropdownMenuContent
                                                    align="end"
                                                >
                                                    <DropdownMenuLabel
                                                        >Actions</DropdownMenuLabel
                                                    >
                                                    <DropdownMenuItem
                                                        @click="
                                                            openModal(
                                                                true,
                                                                product,
                                                            )
                                                        "
                                                    >
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem
                                                        @click="
                                                            deleteProduct(
                                                                product,
                                                            )
                                                        "
                                                        class="text-red-600 focus:text-red-600"
                                                    >
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow
                                        v-if="dataForCurrentTab.length === 0"
                                    >
                                        <TableCell
                                            :colspan="
                                                activeTab === 'products' ? 5 : 4
                                            "
                                            class="text-center py-4 text-gray-500"
                                        >
                                            No items found
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-sm text-muted-foreground">
                                Showing
                                {{
                                    activeTab === "products"
                                        ? finished_goods.from
                                        : activeTab === "materials"
                                          ? raw_materials.from
                                          : assets.from
                                }}
                                to
                                {{
                                    activeTab === "products"
                                        ? finished_goods.to
                                        : activeTab === "materials"
                                          ? raw_materials.to
                                          : assets.to
                                }}
                                of
                                {{
                                    activeTab === "products"
                                        ? finished_goods.total
                                        : activeTab === "materials"
                                          ? raw_materials.total
                                          : assets.total
                                }}
                                items
                            </div>
                            <nav class="flex space-x-2">
                                <Link
                                    v-for="link in activeTab === 'products'
                                        ? finished_goods.links
                                        : activeTab === 'materials'
                                          ? raw_materials.links
                                          : assets.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    class="px-3 py-1 rounded border text-sm"
                                    :class="{
                                        'bg-gray-300 dark:bg-gray-700':
                                            link.active,
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
        </div>

        <!-- Modal -->
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
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"
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
                        <SecondaryButton @click="closeModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton class="ml-3" :disabled="form.processing">
                            Save
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
