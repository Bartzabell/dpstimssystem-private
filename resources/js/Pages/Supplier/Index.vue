<script setup>
import { ref, watch, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { PhListMagnifyingGlass, PhFilePlus, PhPrinter, PhTrash, PhRowsPlusBottom, PhPencilLine } from "@phosphor-icons/vue";

// For displaying the modal
const isFormVisible = ref(false);
function toggleFormVisibility() {
        isFormVisible.value = !isFormVisible.value;
    };
</script>
<template>
    <AppLayout title="Suppliers">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Suppliers
            </h2>
        </template>
        <Modal :show="isFormVisible" @close="!isFormVisible" class="fixed inset-0 z-50">
            <div v-if="isFormVisible">
                <div class="absolute flex justify-end w-full right-1 top-1">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Close"
                        color="bg-red-500 hover:bg-red-700"
                    />
                </div>
                <h1 class="px-6 py-2 text-2xl font-extrabold">Supplier Form</h1>
                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-4">
                    <CustomInput name="Supplier Name:" />
                    <CustomInput name="Phone No.:" />
                    <CustomInput name="Email:" />
                    <CustomInput name="TIN.:" />
                    <CustomInput name="Blk/Lot/Street/No:" />
                    <CustomInput name="Barangay:" />
                    <CustomInput name="Province:" />
                    <CustomInput name="City:" />
                </div>
                <div class="flex items-center justify-center w-full p-5">
                    <div class="w-max">
                        <ButtonCode
                            text="Add Supplier"
                            :icon="PhFilePlus"
                            color="bg-emerald-700 hover:bg-emerald-900"
                        />
                    </div>
                </div>
            </div>
        </Modal>
        <div class="p-5">
            <div class="flex items-center justify-end gap-2 mb-4">
                <ButtonCode 
                    @click="toggleFormVisibility"
                    text="Add Suppliers"
                    :icon="PhFilePlus"
                    color="bg-emerald-700 hover:bg-emerald-900"
                />
                <div class="relative">
                    <PhListMagnifyingGlass class="absolute text-gray-400 transform -translate-y-1/2 left-2 top-1/2" :size="20" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search..."
                        class="py-1 pl-8 pr-2 text-sm border rounded-2xl"
                    />
                </div>
            </div>
            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-xs text-center text-white bg-gray-100 md:text-base">
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Name</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Contact</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">TIN</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Date of Latest Purchase</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50">
                            <td class="px-2 py-1 border whitespace-nowrap">Filpet Bottles</td>
                            <td class="px-2 py-1 border whitespace-nowrap">0909-456-8213</td>
                            <td class="px-2 py-1 border whitespace-nowrap">123-456-789-00</td>
                            <td class="px-2 py-1 border whitespace-nowrap">05 January 2025</td>
                            <td class="px-2 py-1 border whitespace-nowrap">
                                <div class="flex items-center justify-center w-full gap-2">
                                    <button class="p-3 text-white bg-green-700 rounded-full hover:bg-green-900">
                                        <PhPrinter :size="16" />
                                    </button>
                                    <button class="p-3 text-white bg-blue-700 rounded-full hover:bg-blue-900">
                                        <PhPencilLine :size="16" />
                                    </button>
                                    <button class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                                        <PhTrash :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>