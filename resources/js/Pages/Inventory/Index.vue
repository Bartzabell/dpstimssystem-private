<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { PhEyeSlash, PhFilePlus, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass } from "@phosphor-icons/vue";
</script>
<template>
    <AppLayout title="Inventory">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Inventory
            </h2>
        </template>
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <ButtonCode 
                    text="Add Item"
                    color="bg-green-500 hover:bg-green-700"
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
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Item Code</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Category</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Material</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Color</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Quantity</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Unit</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Price per Unit</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Min. Stock</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Max. Stock</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Status</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50">
                            <td class="px-2 py-1 border whitespace-nowrap">16oz_PB0091</td>
                            <td class="px-2 py-1 border whitespace-nowrap">Bottle</td>
                            <td class="px-2 py-1 border whitespace-nowrap">Plastic</td>
                            <td class="px-2 py-1 border whitespace-nowrap">Clear</td>
                            <td class="px-2 py-1 border whitespace-nowrap">600</td>
                            <td class="px-2 py-1 border whitespace-nowrap">pcs</td>
                            <td class="px-2 py-1 border whitespace-nowrap">25</td>
                            <td class="px-2 py-1 border whitespace-nowrap">300</td>
                            <td class="px-2 py-1 border whitespace-nowrap">800</td>
                            <td class="px-2 py-1 border whitespace-nowrap">Good</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>