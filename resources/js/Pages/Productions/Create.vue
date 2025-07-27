<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    finished_goods: Array,
    raw_materials: Array,
});

const form = useForm({
    production_date: new Date().toISOString().slice(0, 10),
    finished_good_id: "",
    quantity_produced: 1,
    notes: "",
    materials: [],
});

const addMaterial = () => {
    form.materials.push({
        raw_material_id: "",
        quantity_used: 1,
    });
};

const removeMaterial = (index) => {
    form.materials.splice(index, 1);
};
</script>

<template>
    <AppLayout title="New Production">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Record New Production
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="form.post('/productions')">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mb-8"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            Production Result
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel value="Date" required />
                                <TextInput
                                    type="date"
                                    v-model="form.production_date"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="form.errors.production_date"
                                />
                            </div>
                            <div>
                                <InputLabel value="Finished Good" required />
                                <select
                                    v-model="form.finished_good_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                                    <option value="">Select product...</option>
                                    <option
                                        v-for="fg in finished_goods"
                                        :key="fg.id"
                                        :value="fg.id"
                                    >
                                        {{ fg.product_name }}
                                    </option>
                                </select>
                                <InputError
                                    :message="form.errors.finished_good_id"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    value="Quantity Produced"
                                    required
                                />
                                <TextInput
                                    type="number"
                                    min="1"
                                    v-model="form.quantity_produced"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="form.errors.quantity_produced"
                                />
                            </div>
                            <div class="col-span-3">
                                <InputLabel value="Notes" />
                                <TextInput
                                    v-model="form.notes"
                                    class="mt-1 block w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            Raw Materials Used
                        </h3>
                        <InputError
                            :message="form.errors.materials"
                            class="mt-2"
                        />
                        <div
                            v-for="(material, index) in form.materials"
                            :key="index"
                            class="grid grid-cols-12 gap-4 items-center border-t dark:border-gray-700 pt-4 mt-4"
                        >
                            <div class="col-span-7">
                                <InputLabel :value="`Material ${index + 1}`" />
                                <select
                                    v-model="material.raw_material_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                                    <option value="">Select material...</option>
                                    <option
                                        v-for="rm in raw_materials"
                                        :key="rm.id"
                                        :value="rm.id"
                                    >
                                        {{ rm.product_name }} (Stock:
                                        {{ rm.current_stock }})
                                    </option>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <InputLabel value="Qty Used" />
                                <TextInput
                                    type="number"
                                    min="1"
                                    v-model="material.quantity_used"
                                    class="mt-1 w-full"
                                />
                            </div>
                            <div class="col-span-2 flex items-end">
                                <DangerButton
                                    @click="removeMaterial(index)"
                                    class="mt-6"
                                    >Remove</DangerButton
                                >
                            </div>
                        </div>
                        <SecondaryButton
                            type="button"
                            @click="addMaterial"
                            class="mt-4"
                            >+ Add Material</SecondaryButton
                        >
                    </div>
                    <div class="mt-8 flex justify-end">
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Save Production
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
