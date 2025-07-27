<script setup>
import { ref, computed, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
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
import { MoreHorizontal, Trash2 } from "lucide-vue-next";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    accounts: Object,
});

const search = ref("");
const selectedRows = ref([]);

const filteredAccounts = computed(() => {
    if (!search.value) return props.accounts.data;
    return props.accounts.data.filter((a) =>
        a.account_name.toLowerCase().includes(search.value.toLowerCase()),
    );
});

watch(search, () => {
    selectedRows.value = [];
});

const isAllSelected = computed(
    () =>
        filteredAccounts.value.length > 0 &&
        selectedRows.value.length === filteredAccounts.value.length,
);

const selectAllRows = (event) => {
    selectedRows.value = event.target.checked
        ? filteredAccounts.value.map((a) => a.id)
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

const closeModal = () => (isModalVisible.value = false);

const saveAccount = () => {
    const onFinish = { onSuccess: () => closeModal(), preserveScroll: true };
    if (isEditMode.value) {
        form.put(route("accounts.update", form.id), onFinish);
    } else {
        form.post(route("accounts.store"), onFinish);
    }
};

const deleteAccount = (account) => {
    showConfirmation(
        "Delete Account",
        `Are you sure you want to delete "${account.account_name}"?`,
        () => {
            router.delete(route("accounts.destroy", account.id), {
                preserveScroll: true,
            });
        },
    );
};

const deleteSelected = () => {
    if (selectedRows.value.length === 0) return;
    showConfirmation(
        "Delete Selected Accounts",
        `Are you sure you want to delete ${selectedRows.value.length} selected accounts?`,
        () => {
            router.delete(route("accounts.destroyMultiple"), {
                data: { ids: selectedRows.value },
                preserveScroll: true,
                onSuccess: () => {
                    selectedRows.value = [];
                },
            });
        },
    );
};
</script>

<template>
    <AppLayout title="Chart of Accounts">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="text-xl font-semibold text-gray-800 dark:text-gray-200"
                >
                    Chart of Accounts
                </h2>
                <PrimaryButton @click="openModal()">New Account</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <Input
                            v-model="search"
                            placeholder="Search account name..."
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
                                    <TableHead>Code</TableHead>
                                    <TableHead>Account Name</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="account in filteredAccounts"
                                    :key="account.id"
                                >
                                    <TableCell class="px-4">
                                        <input
                                            type="checkbox"
                                            :value="account.id"
                                            v-model="selectedRows"
                                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800 dark:bg-gray-900"
                                        />
                                    </TableCell>
                                    <TableCell>{{
                                        account.account_code
                                    }}</TableCell>
                                    <TableCell>{{
                                        account.account_name
                                    }}</TableCell>
                                    <TableCell>{{
                                        account.category
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
                                                        openModal(true, account)
                                                    "
                                                >
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    @click="
                                                        deleteAccount(account)
                                                    "
                                                    class="text-red-600"
                                                >
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <!-- Pagination -->
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ accounts.from }} to {{ accounts.to }} of
                            {{ accounts.total }} charts of accounts
                        </div>
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in accounts.links"
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
                    {{ isEditMode ? "Edit" : "Create" }} Account
                </h2>
                <form @submit.prevent="saveAccount" class="mt-6 space-y-4">
                    <div>
                        <InputLabel value="Category" required />
                        <select
                            v-model="form.category"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                        >
                            <option>Asset</option>
                            <option>Liability</option>
                            <option>Equity</option>
                            <option>Revenue</option>
                            <option>Expense</option>
                        </select>
                        <InputError
                            :message="form.errors.category"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel value="Account Code" required />
                        <TextInput
                            v-model="form.account_code"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.account_code"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel value="Account Name" required />
                        <TextInput
                            v-model="form.account_name"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.account_name"
                            class="mt-2"
                        />
                    </div>
                    <div class="flex justify-end pt-4">
                        <SecondaryButton @click="closeModal"
                            >Cancel</SecondaryButton
                        >
                        <PrimaryButton class="ml-3" :disabled="form.processing">
                            Save
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Dialog Konfirmasi -->
        <AlertDialog
            :open="isConfirmDialogOpen"
            @update:open="isConfirmDialogOpen = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{
                        confirmDialogData.title
                    }}</AlertDialogTitle>
                    <AlertDialogDescription>{{
                        confirmDialogData.description
                    }}</AlertDialogDescription>
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
