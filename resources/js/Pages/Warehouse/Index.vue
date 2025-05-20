<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { PhX, PhFilePlus, PhFloppyDisk, PhTrash, PhPencil, PhCaretUp, PhCaretDown, PhDownloadSimple, PhListMagnifyingGlass, PhWarning } from "@phosphor-icons/vue";

const props = defineProps({
    warehouseStocks: Object,
    inventories: Array,
    filters: Object,
    warehouse: Object,
    flash: Object // Add this to receive flash messages
});

const search = ref(props.filters?.search || '');
const sortField = ref(props.filters.sort_field || 'id');
const sortDirection = ref(props.filters.sort_direction || 'asc');
watch(search, (value) => {
    router.get(route('warehouse.index'), { search: value, sort_field: sortField.value, sort_direction: sortDirection.value }, { preserveState: true, replace: true });
}, { deep: true });

const sort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route('warehouse.index'), { search: search.value, sort_field: sortField.value, sort_direction: sortDirection.value }, { preserveState: true, replace: true });
};

const isFormVisible = ref(false);
const topToast = ref(null);
const selectedInventory = ref(null);
const form = useForm({
    id: null,
    inventory_id: '',
    item_qty: '',
    price: '',
    min_stock: '',
    max_stock: '',
    status: '',
});

// Display flash messages when component mounts
watch(() => props.flash, (newFlash) => {
    if (newFlash?.error) {
        topToast.value.showToast(newFlash.error, 'error');
    }
    if (newFlash?.success) {
        topToast.value.showToast(newFlash.success, 'success');
    }
}, { immediate: true, deep: true });

watch(() => form.errors, (errors) => {
    if (errors.error) {
        topToast.value.showToast(errors.error, 'error');
    }
}, { deep: true });

function toggleFormVisibility() {
    if (isFormVisible.value) {
        // Form is currently visible, so we're closing it
        resetForm();
        editing.value = false;
    }
    isFormVisible.value = !isFormVisible.value;
}

const edit = (warehouse) => {
    if (!isFormVisible.value) {
        isFormVisible.value = true;
    }
    form.id = warehouse.id;
    form.inventory_id = warehouse.inventory_id;
    form.item_qty = warehouse.item_qty;
    form.price = warehouse.price;
    form.min_stock = warehouse.min_stock;
    form.max_stock = warehouse.max_stock;
    form.status = warehouse.status;

    selectedInventory.value = warehouse.inventory_id;

    editing.value = true;
};

const confirmDelete = (id) => {
    itemToDelete.value = id;
    showDeleteConfirmation.value = true;
};

const deleteItem = () => {
    form.delete(route('warehouse.destroy', itemToDelete.value), {
        onSuccess: () => {
            if (isFormVisible.value) {
                isFormVisible.value = false;
            }
            showDeleteConfirmation.value = false;
            // Toast will be handled by the flash message
        },
        onError: () => {
            topToast.value.showToast('Failed to delete item', 'error');
            showDeleteConfirmation.value = false;
        }
    });
};

const resetForm = () => {
    form.id = null;
    form.inventory_id = '';
    form.item_qty = '';
    form.price = '';
    form.min_stock = '';
    form.max_stock = '';
    form.status = '';
    selectedInventory.value = null;
    form.clearErrors();
};

const editing = ref(false);

const submit = () => {
    dialogAction.value = editing.value ? 'update' : 'add';
    showConfirmDialog.value = true;
};

const confirmSubmit = () => {
    if (editing.value) {
        form.put(route('warehouse.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                isFormVisible.value = false;
                resetForm();
                editing.value = false;
                showConfirmDialog.value = false;
            },
            onError: () => {
                showConfirmDialog.value = false;
                // The error will be handled by the form.errors watcher
            }
        });
    } else {
        form.post(route('warehouse.store'), {
            preserveScroll: true,
            onSuccess: () => {
                if (isFormVisible.value) {
                    isFormVisible.value = false;
                }
                resetForm();
                showConfirmDialog.value = false;
            },
            onError: (errors) => {
                showConfirmDialog.value = false;
                if (errors.error) {
                    topToast.value.showToast(errors.error, 'error');
                } else {
                    topToast.value.showToast('Failed to add item', 'error');
                }
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
    <AppLayout title="Warehouse Inventory">
        <template #header :warehouse-name="warehouseObject.name">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-20 0">
                <span class="p-2 text-white rounded-l-md bg-emerald-800">{{ warehouse?.name ?? 'Warehouse' }} </span> Inventory
            </h2>
        </template>
        <Modal :show="isFormVisible" @close="!isFormVisible" class="fixed inset-0 z-50">
            <div class="w-[50vw]" v-if="isFormVisible">
                <div
                    class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black dark:border-gray-500 dark:bg-gray-800">
                    <div>
                        <h1 class="text-2xl font-extrabold dark:text-gray-200">{{ editing ? 'Edit Product' : 'Add Product' }}</h1>
                    </div>
                    <button @click="toggleFormVisibility"
                        class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 !text-[10px] 2xl:!text-sm gap-2 p-5 mt-10 lg:grid-cols-2">
                        <div>
                            <Label class="font-medium">Product Name</Label>
                            <SearchableDropdown
                                v-model="selectedInventory"
                                :items="inventories"
                                value-field="id"
                                label-field="name"
                                @change="form.inventory_id = $event.id"
                                :disabled="editing"
                                />
                        </div>
                        <CustomInput name="Product Quantity" v-model="form.item_qty" />
                        <CustomInput name="Product Price" v-model="form.price" />
                        <CustomInput name="Minimum Stock" v-model="form.min_stock" />
                        <CustomInput name="Maximum Stock" v-model="form.max_stock" />
                    </div>
                    <div class="flex items-center justify-center gap-2 p-2 mt-2">
                        <ButtonCode type="submit" :icon="editing ? PhFloppyDisk : PhFilePlus"
                            color="bg-emerald-700 hover:bg-emerald-900"
                            :text="editing ? 'Update Product' : 'Save Product'" />
                        <ButtonCode v-if="editing" type="button" @click="cancelForm"
                            color="bg-gray-500 hover:bg-gray-700" text="Cancel" />
                    </div>
                </form>
            </div>
        </Modal>
        <div class="p-5">
            <div class="p-6 mt-2 bg-white rounded shadow dark:bg-gray-700">
                <!-- Search Bar -->
                <div class="flex flex-col items-end justify-end gap-2 mb-4 md:items-center md:flex-row">
                    <ButtonCode @click="toggleFormVisibility" text="Add Product" :icon="PhFilePlus"
                        color="bg-emerald-700 hover:bg-emerald-900" />
                    <div class="relative">
                        <PhListMagnifyingGlass
                            class="absolute text-gray-400 transform -translate-y-1/2 dark:text-gray-500 left-2 top-1/2"
                            :size="20" />
                        <input type="text" v-model="search" placeholder="Search..."
                            class="py-1 pl-8 pr-2 text-sm border dark:bg-gray-300 dark:text-gray-500 rounded-2xl" />
                    </div>
                </div>

                <div v-if="warehouseStocks && warehouseStocks.data && warehouseStocks.data.length > 0">
                    <div class="overflow-x-auto border rounded-lg dark:border-gray-600">
                        <table
                            class="min-w-full divide-y divide-gray-200 rounded-lg dark:border-gray-600 dark:divide-gray-600">
                            <thead>
                                <tr class="text-xs text-center text-white bg-gray-100 md:text-base">
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('id')" class="flex items-center justify-center w-full">
                                            ID
                                            <PhCaretUp v-if="sortField === 'id' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'id' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('name')" class="flex items-center justify-center w-full">
                                            NAME
                                            <PhCaretUp v-if="sortField === 'inventory.name' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'inventory.name' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('item_code')"
                                            class="flex items-center justify-center w-full">
                                            PRODUCT CODE
                                            <PhCaretUp v-if="sortField === 'inventory.item_code' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'inventory.item_code' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('item_qty')"
                                            class="flex items-center justify-center w-full">
                                            QUANTITY
                                            <PhCaretUp v-if="sortField === 'item_qty' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'item_qty' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('category')"
                                            class="flex items-center justify-center w-full">
                                            CATEGORY
                                            <PhCaretUp v-if="sortField === 'inventory.category' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'inventory.category' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('material')"
                                            class="flex items-center justify-center w-full">
                                            MATERIAL
                                            <PhCaretUp v-if="sortField === 'inventory.material' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'inventory.material' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('color')" class="flex items-center justify-center w-full">
                                            COLOR
                                            <PhCaretUp v-if="sortField === 'inventory.color' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'inventory.color' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('size')" class="flex items-center justify-center w-full">
                                            SIZE
                                            <PhCaretUp v-if="sortField === 'inventory.size' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'inventory.size' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('price')" class="flex items-center justify-center w-full">
                                            PRICE
                                            <PhCaretUp v-if="sortField === 'price' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'price' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('min_stock')"
                                            class="flex items-center justify-center w-full">
                                            MIN STOCK
                                            <PhCaretUp v-if="sortField === 'min_stock' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'min_stock' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('max_stock')"
                                            class="flex items-center justify-center w-full">
                                            MAX STOCK
                                            <PhCaretUp v-if="sortField === 'max_stock' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'max_stock' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('status')" class="flex items-center justify-center w-full">
                                            STATUS
                                            <PhCaretUp v-if="sortField === 'status' && sortDirection === 'asc'"
                                                class="ml-1" :size="16" />
                                            <PhCaretDown v-if="sortField === 'status' && sortDirection === 'desc'"
                                                class="ml-1" :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-xs text-gray-600 dark:text-gray-50 dark:bg-gray-500 dark:even:bg-gray-600 dark:hover:bg-gray-800 md:text-base hover:bg-blue-100 even:bg-gray-50"
                                    v-for="warehouse in warehouseStocks.data" :key="warehouse.id">
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.id }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.inventory.name }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.inventory.item_code }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.item_qty }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.inventory.category }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.inventory.material }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.inventory.color }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.inventory.size }}{{ warehouse.inventory.uom
                                        }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.inventory.price }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.min_stock }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.max_stock }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ warehouse.status }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                        <div class="inline-flex justify-center w-full h-full gap-2 ">
                                            <button @click="edit(warehouse)"
                                                class="p-2 text-white bg-blue-500 rounded-full">
                                                <PhPencil :size="16" />
                                            </button>
                                            <button @click="confirmDelete(warehouse.id)"
                                                class="p-2 text-white bg-red-500 rounded-full">
                                                <PhTrash :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <PaginationButton :data="warehouseStocks" />
                </div>
                <div v-else class="p-4 text-center">
                    <p>No items found. Create your first item above.</p>
                </div>
            </div>
        </div>
        <!-- Delete Confirmation Modal -->
        <SimpleDialog v-model="showDeleteConfirmation" theme="red" :icon="PhWarning" title="Confirm Delete"
            description="Are you sure you want to delete this item? This action cannot be undone."
            confirmText="Yes, Delete" @confirm="deleteItem" @cancel="cancelDelete" />

        <!-- Confirm Dialog for Add/Update/Cancel -->
        <SimpleDialog v-model="showConfirmDialog"
            :theme="dialogAction === 'add' || dialogAction === 'update' ? 'blue' : 'yellow'"
            :icon="dialogAction === 'add' || dialogAction === 'update' ? PhDownloadSimple : PhWarning"
            :title="dialogAction === 'add' ? 'Confirm Add' : dialogAction === 'update' ? 'Confirm Update' : 'Confirm Cancel'"
            :description="dialogAction === 'add' ? 'Are you sure you want to add this item?' :
                dialogAction === 'update' ? 'Are you sure you want to update this item?' :
                    'Are you sure you want to cancel? Any unsaved changes will be lost.'"
            :confirmText="dialogAction === 'add' ? 'Yes, Add' : dialogAction === 'update' ? 'Yes, Update' : 'Yes, Cancel'"
            cancelText="No"
            @confirm="dialogAction === 'add' || dialogAction === 'update' ? confirmSubmit() : confirmCancel()"
            @cancel="cancelConfirmDialog" />

        <!-- Toast Notification -->
        <TopToast ref="topToast" />
    </AppLayout>
</template>
