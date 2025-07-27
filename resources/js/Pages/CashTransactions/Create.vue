<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

defineProps({ accounts: Array });

const form = useForm({
    transaction_date: new Date().toISOString().slice(0, 10),
    account_id: "",
    type: "Out",
    amount: 0,
    description: "",
});
</script>

<template>
    <AppLayout title="New Cash Transaction">
        <template #header>
            <h2 class="font-semibold text-xl ...">Record Cash Transaction</h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <form @submit.prevent="form.post('/cash-transactions')">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <InputLabel value="Date" required />
                                <TextInput
                                    type="date"
                                    v-model="form.transaction_date"
                                    class="mt-1 w-full"
                                />
                                <InputError
                                    :message="form.errors.transaction_date"
                                />
                            </div>
                            <div>
                                <InputLabel value="Transaction Type" required />
                                <select
                                    v-model="form.type"
                                    class="mt-1 w-full ..."
                                >
                                    <option value="Out">Expense (Out)</option>
                                    <option value="In">Income (In)</option>
                                </select>
                                <InputError :message="form.errors.type" />
                            </div>
                            <div class="col-span-2">
                                <InputLabel value="Account" required />
                                <select
                                    v-model="form.account_id"
                                    class="mt-1 w-full ..."
                                >
                                    <option value="">Select account...</option>
                                    <option
                                        v-for="acc in accounts"
                                        :key="acc.id"
                                        :value="acc.id"
                                    >
                                        {{ acc.account_name }} ({{
                                            acc.account_code
                                        }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.account_id" />
                            </div>
                            <div class="col-span-2">
                                <InputLabel value="Amount" required />
                                <TextInput
                                    type="number"
                                    v-model="form.amount"
                                    class="mt-1 w-full"
                                />
                                <InputError :message="form.errors.amount" />
                            </div>
                            <div class="col-span-2">
                                <InputLabel value="Description" required />
                                <TextInput
                                    v-model="form.description"
                                    class="mt-1 w-full"
                                />
                                <InputError
                                    :message="form.errors.description"
                                />
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <PrimaryButton :disabled="form.processing"
                                >Save Transaction</PrimaryButton
                            >
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
