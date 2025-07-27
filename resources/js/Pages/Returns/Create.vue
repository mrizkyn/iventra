<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
// ... import komponen form lainnya

const props = defineProps({
    purchases: Array,
    sales: Array,
});

const form = useForm({
    return_date: new Date().toISOString().slice(0, 10),
    return_type: "Sale",
    transaction_id: "",
    reason: "",
    details: [], // Akan diisi setelah transaksi dipilih
});

// Anda akan memerlukan fungsi untuk mengambil detail item dari transaksi yang dipilih via AJAX/axios
// Untuk kesederhanaan, kita akan membiarkan user mengisi manual untuk saat ini
const addDetail = () => {
    form.details.push({ product_id: "", quantity: 1 });
};
const removeDetail = (index) => {
    form.details.splice(index, 1);
};
</script>

<template>
    <AppLayout title="New Return">
        <template #header>
            <h2 class="font-semibold text-xl ...">Record New Return</h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="form.post('/returns')">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mb-8"
                    >
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <InputLabel value="Return Date" required />
                                <TextInput
                                    type="date"
                                    v-model="form.return_date"
                                    class="mt-1 w-full"
                                />
                            </div>
                            <div>
                                <InputLabel value="Return Type" required />
                                <select
                                    v-model="form.return_type"
                                    class="mt-1 w-full ..."
                                >
                                    <option>Sale</option>
                                    <option>Purchase</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <InputLabel
                                    value="Related Transaction"
                                    required
                                />
                                <select
                                    v-model="form.transaction_id"
                                    class="mt-1 w-full ..."
                                >
                                    <option value="">
                                        Select transaction...
                                    </option>
                                    <template
                                        v-if="form.return_type === 'Sale'"
                                    >
                                        <option
                                            v-for="s in sales"
                                            :key="s.id"
                                            :value="s.id"
                                        >
                                            {{ s.sale_number }}
                                        </option>
                                    </template>
                                    <template v-else>
                                        <option
                                            v-for="p in purchases"
                                            :key="p.id"
                                            :value="p.id"
                                        >
                                            {{ p.purchase_number }}
                                        </option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <InputLabel value="Reason" />
                                <TextInput
                                    v-model="form.reason"
                                    class="mt-1 w-full"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <PrimaryButton :disabled="form.processing"
                            >Save Return</PrimaryButton
                        >
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
