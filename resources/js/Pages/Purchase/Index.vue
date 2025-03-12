<script setup>
    import { ref, watch, onMounted } from 'vue';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { PhListMagnifyingGlass, PhFilePlus, PhPrinter, PhTrash, PhRowsPlusBottom, PhPencilLine } from "@phosphor-icons/vue";

    const props = defineProps({
        forms: Object,
        suppliers: Array,
        inventories: Array,
        filters: Object
    });

    const search = ref(props.filters.search || '');
    const isFormVisible = ref(false);
    const editing = ref(false);
    const topToast = ref(null);

    // Fixed: Changed from single value to an array of selected values
    const selectedItems = ref([]);
    const selectedEmployee = ref(null);

    function toggleFormVisibility() {
        isFormVisible.value = !isFormVisible.value;
    };

    // Modal states
    const showDeleteConfirmation = ref(false);
    const showConfirmDialog = ref(false);
    const itemToDelete = ref(null);
    const dialogAction = ref(''); // 'add', 'update', or 'cancel'

    // Toast states
    const toast = ref({
        show: false,
        message: '',
        type: 'success', // success, error, info
    });

    const form = useForm({
        id: null,
        supplier_id: '',
        date_purchased: '',
        items: [],
    });

    // Watch for search input and debounce API call
    watch(search, (value) => {
        router.get(route('receiving-form.index'), { search: value }, { preserveState: true, replace: true });
    }, { deep: true });

    const edit = (purchased_form) => {
        if (!isFormVisible.value) {
            isFormVisible.value = true;
        }
        form.id = purchased_form.id;
        form.supplier_id = purchased_form.supplier_id;
        form.date_purchased = purchased_form.date_purchased;


        if (purchased_form.date_purchased) {
            // Parse the date as UTC and convert it to local time
            const dateObj = new Date(purchased_form.date_purchased + 'Z'); // Append 'Z' to treat it as UTC
            form.date_purchased = dateObj.toISOString().split('T')[0]; // Format as YYYY-MM-DD
        } else {
            form.date_purchased= '';
        }

        // Load items
        form.items = purchased_form.items ?
            [...purchased_form.items] : [];

        // Fixed: Initialize selectedItems array with the correct number of elements
        selectedItems.value = form.items.map(item => item.stock_id || null);
        selectedEmployee.value = purchased_form.receiver_id;

        editing.value = true;
    };

    const confirmDelete = (id) => {
        itemToDelete.value = id;
        showDeleteConfirmation.value = true;
    };

    const deleteItem = () => {
        form.delete(route('receiving-form.destroy', itemToDelete.value), {
            onSuccess: () => {
                if (isFormVisible.value) {
                    isFormVisible.value = false;
                }
                topToast.value.showToast('Form deleted successfully', 'success');
                showDeleteConfirmation.value = false;
            },
            onError: () => {
                topToast.value.showToast('Failed to delete form', 'error');
                showDeleteConfirmation.value = false;
            }
        });
    };

    const resetForm = () => {
        form.id = '';
        form.location = '';
        form.note = '';
        form.date_received = '';
        form.status = '';
        form.received_from = '';
        form.receiver_id = '';
        form.items = [];
        selectedItems.value = [];
        selectedEmployee.value = null;
    };

    // Add a new empty items
    const addItem = () => {
        form.items.push({
            id: null,
            receiving_form_id: '',
            inventory_id: '',
            item_qty: '',
        });
        // Fixed: Add a corresponding null entry to selectedItems
        selectedItems.value.push(null);
    };

    // Remove a items at the specified index
    const removeItem = (index) => {
        form.items.splice(index, 1);
        // Fixed: Remove the corresponding entry from selectedItems
        selectedItems.value.splice(index, 1);
    };

    const formatDate = (date_received) => {
        if (!date_received) return "";
        return new Date(date_received).toLocaleDateString("en-GB", {
            day: "numeric",
            month: "long",
            year: "numeric"
        });
    };

    // Fixed: Handler for inventory item selection
    const handleInventoryChange = (event, index) => {
        // Update the inventory_id in the form items
        form.items[index].inventory_id = event.id;
        // Update just the selected item at the specific index
        selectedItems.value[index] = event.id;
    };

    // New functions - placed at the end for organization
    const submit = () => {
        dialogAction.value = editing.value ? 'update' : 'add';
        showConfirmDialog.value = true;
    };

    const confirmSubmit = () => {
        if (editing.value) {
            form.put(route('receiving-form.update', form.id), {
                onSuccess: () => {
                    if (isFormVisible.value) {
                        isFormVisible.value = false;
                    }
                    resetForm();
                    editing.value = false;
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Form updated successfully', 'success');
                },
                onError: () => {
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Failed to update form', 'error');
                }
            });
        } else {
            form.post(route('receiving-form.store'), {
                onSuccess: () => {
                    resetForm();
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Form added successfully', 'success');
                },
                onError: () => {
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Failed to add form', 'error');
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
</script>
<template>
    <AppLayout title="Purchase">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Purchase
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
                <h1 class="px-6 py-2 text-2xl font-extrabold">Purchase Form</h1>
                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-3">
                    <CustomSelect label="Supplier Name:" name="suppplier_name"
                                :options="[ { value: 'Filpet Bottles', label: 'Filpet Bottles' },
                                            { value: 'BottleShop', label: 'BottleShop' } ]" />
                    <CustomInput name="Phone No.:" readonly />
                    <CustomInput name="TIN.:" readonly />
                    <div class="col-span-1 mt-6 md:col-span-3">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold">Items</h3>
                        </div>

                        <!-- Conditional display based on items length -->
                        <div v-if="items.length === 0" class="py-4 text-center rounded bg-gray-50">
                            <p>No items added yet. Click 'Add Item' to start.</p>
                        </div>

                        <!-- Table displays when we have items -->
                        <div v-else class="overflow-visible border rounded-lg">
                            <table class="w-full">
                                <thead>
                                <tr class="text-left bg-gray-100">
                                    <th class="w-2/6 px-2 py-1 border whitespace-nowrap">Product</th>
                                    <th class="w-1/6 px-2 py-1 border whitespace-nowrap">Quantity</th>
                                    <th class="w-1/6 px-2 py-1 border whitespace-nowrap">Price Per Unit</th>
                                    <th class="w-1/6 px-2 py-1 border whitespace-nowrap">Total Cost</th>
                                    <th class="w-1/6 px-2 py-1 border whitespace-nowrap">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item, index) in items" :key="index" class="hover:bg-gray-50">
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                    <CustomSelect
                                        name="product"
                                        :options="[ { value: 'bottle', label: 'Bottle' },
                                                    { value: 'cap', label: 'Cap' } ]" />
                                    </td>
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                    <CustomInput
                                        type="number"
                                        v-model="item.quantity"
                                        @update:modelValue="updateTotalCost(index)"
                                    />
                                    </td>
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                    <CustomInput
                                        type="number"
                                        v-model="item.pricePerUnit"
                                        @update:modelValue="updateTotalCost(index)"
                                    />
                                    </td>
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                    <CustomInput
                                        type="number"
                                        v-model="item.totalCost"
                                        readonly
                                    />
                                    </td>
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                    <div class="inline-flex justify-center w-full h-full gap-2">
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                            @click="removeItem(index)"
                                            >
                                            Remove
                                        </button>
                                    </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end w-full py-2">
                            <ButtonCode
                                :icon="PhRowsPlusBottom"
                                color="bg-green-500 hover:bg-green-700"
                                text="Add Row"
                                @click="addItem"
                            />
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-center w-full p-5">
                    <div class="w-max">
                        <ButtonCode
                            text="Add Item"
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
                    text="Add Purchase"
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
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Bill No.</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Supplier</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Purchase Item</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Quantity</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Price Per Unit</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Total Price Purchased</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Date Purchased</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50">
                            <td class="px-2 py-1 border whitespace-nowrap">000014326</td>
                            <td class="px-2 py-1 border whitespace-nowrap">Filpet Bottles</td>
                            <td class="px-2 py-1 border whitespace-nowrap">16oz_PB0091</td>
                            <td class="px-2 py-1 border whitespace-nowrap">50</td>
                            <td class="px-2 py-1 border whitespace-nowrap">307</td>
                            <td class="px-2 py-1 border whitespace-nowrap">15350.00</td>
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
