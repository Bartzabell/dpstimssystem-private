<script setup>
    import { ref, watch } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { PhEyeSlash, PhFilePlus, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass } from "@phosphor-icons/vue";

    const props = defineProps({
        inventories: Object,
        categories: Array,
        materials: Array,
        colors: Array,
        uoms: Array,
        filters: Object
    });

    const search = ref(props.filters?.search || '');
    watch(search, (value) => {
        router.get(route('inventory.index'), { search: value }, { preserveState: true, replace: true });
    }, { deep: true });

    const isFormVisible = ref(false);
    const selectedCategory = ref(null);
    const selectedMaterial = ref(null);
    const selectedColor = ref(null);
    const selectedUom = ref(null);
    const topToast = ref(null);
    const form = useForm({
        id: null,
        name: '',
        item_code: '',
        item_qty: '',
        category_id: '',
        material: '',
        color: '',
        uom: '',
        price: '',
        min_stock: '',
        max_stock: '',
        status: '',
    });

    function toggleFormVisibility() {
        isFormVisible.value = !isFormVisible.value;
    };

    const edit = (inventory) => {
        if (!isFormVisible.value) {
            isFormVisible.value = true;
        }
        form.id = inventory.id;
        form.name = inventory.name;
        form.item_code = inventory.item_code;
        form.item_qty = inventory.item_qty;
        form.category_id = inventory.category_id;
        form.material = inventory.material;
        form.color = inventory.color;
        form.uom = inventory.uom;
        form.price = inventory.price;
        form.min_stock = inventory.min_stock;
        form.max_stock = inventory.max_stock;
        form.status = inventory.status;

        selectedCategory.value = inventory.category_id;
        selectedMaterial.value = inventory.material;
        selectedColor.value = inventory.color;
        selectedUom.value = inventory.uom;


        editing.value = true;
    };

    const confirmDelete = (id) => {
        itemToDelete.value = id;
        showDeleteConfirmation.value = true;
    };

    const deleteItem = () => {
        form.delete(route('user-request.destroy', itemToDelete.value), {
            onSuccess: () => {
                if (isFormVisible.value) {
                    isFormVisible.value = false;
                }
                topToast.value.showToast('Item deleted successfully', 'success');
                showDeleteConfirmation.value = false;
            },
            onError: () => {
                topToast.value.showToast('Failed to delete item', 'error');
                showDeleteConfirmation.value = false;
            }
        });
    };

    const resetForm = () => {
        form.id = null;
        form.name = '';
        form.item_code = '';
        form.item_qty = '';
        form.category_id = '';
        form.material = '';
        form.color = '';
        form.uom = '';
        form.price = '';
        form.min_stock = '';
        form.max_stock = '';
        form.status = '';
        selectedCategory.value = null;
        selectedMaterial.value = null;
        selectedColor.value = null;
        selectedUom.value = null;
    };

    const editing = ref(false);

    const submit = () => {
        dialogAction.value = editing.value ? 'update' : 'add';
        showConfirmDialog.value = true;
    };

    const confirmSubmit = () => {
        if (editing.value) {
            form.put(route('inventory.update', form.id), {
                onSuccess: () => {
                    if (isFormVisible.value) {
                        isFormVisible.value = false;
                    }
                    resetForm();
                    editing.value = false;
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Item updated successfully', 'success');
                },
                onError: () => {
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Failed to update item', 'error');
                }
            });
        } else {
            form.post(route('inventory.store'), {
                onSuccess: () => {
                    resetForm();
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Item added successfully', 'success');
                },
                onError: () => {
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Failed to add item', 'error');
                }
            });
        }
    };

    const cancelForm = () => {
        dialogAction.value = 'cancel';
        showConfirmDialog.value = true;
    };

    const confirmCancel = () => {
        if (isFormVisible.value) {
            isFormVisible.value = false;
        }
        resetForm();
        editing.value = false;
        showConfirmDialog.value = false;
        topToast.value.showToast('Operation cancelled', 'info');
    };

    const cancelDelete = () => {
        showDeleteConfirmation.value = false;
        topToast.value.showToast('Delete operation cancelled', 'info');
    };

    const cancelConfirmDialog = () => {
        showConfirmDialog.value = false;
    };

    // Modal states
    const showDeleteConfirmation = ref(false);
    const showConfirmDialog = ref(false);
    const itemToDelete = ref(null);
    const dialogAction = ref('');
</script>
<template>
    <AppLayout title="Inventory">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Inventory
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
                <h1 class="px-6 py-2 text-2xl font-extrabold">Inventory Form</h1>
                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                    <CustomInput name="Assign an Item Code:" />
                    <CustomInput name="Quantity:" type="number" />
                    <CustomSelect label="Category" name="category"
                                :options="[ { value: 'bottle', label: 'Bottle' },
                                            { value: 'cap', label: 'Cap' } ]" />
                    <CustomInput name="Minimum Stock Level:" type="number" />
                    <CustomSelect label="Material" name="material"
                                :options="[ { value: 'plastic', label: 'Plastic' },
                                            { value: 'sheet', label: 'Sheet' } ]" />
                    <CustomInput name="Maximum Stock Level:" type="number" />
                    <CustomSelect label="Color:" name="color"
                                :options="[ { value: 'red', label: 'Red' },
                                            { value: 'blue', label: 'Blue' },
                                            { value: 'yellow', label: 'Yellow' } ]" />
                    <CustomInput name="Price per Unit:" type="number" />
                    <CustomSelect label="Unit of Measurement:" name="uom"
                                :options="[ { value: 'pcs', label: 'Pieces' },
                                            { value: 'pck', label: 'Pack' },
                                            { value: 'bg', label: 'Bag' } ]" />
                    <div class="flex items-center justify-end w-full">
                        <div class="w-max">
                            <ButtonCode
                                text="Add Item"
                                :icon="PhFilePlus"
                                color="bg-emerald-700 hover:bg-emerald-900"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
        <div class="p-5">
            <div class="flex items-center justify-end gap-2 mb-4">
                <ButtonCode
                    @click="toggleFormVisibility"
                    text="Add Item"
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
