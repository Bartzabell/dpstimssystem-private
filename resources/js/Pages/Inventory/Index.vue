<script setup>
    import { ref, watch, computed } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { PhX, PhFilePlus, PhFloppyDisk, PhTrash, PhPencil, PhCaretUp, PhCaretDown, PhDownloadSimple, PhListMagnifyingGlass, PhWarning } from "@phosphor-icons/vue";

    const props = defineProps({
        inventories: Object,
        categories: Array,
        materials: Array,
        colors: Array,
        uoms: Array,
        filters: Object
    });

    const search = ref(props.filters?.search || '');
    const sortField = ref(props.filters.sort_field || 'id');
    const sortDirection = ref(props.filters.sort_direction || 'asc');
    watch(search, (value) => {
        router.get(route('inventory.index'), { search: value, sort_field: sortField.value, sort_direction: sortDirection.value }, { preserveState: true, replace: true });
    }, { deep: true });

    const sort = (field) => {
        if (sortField.value === field) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortField.value = field;
            sortDirection.value = 'asc';
        }
        router.get(route('inventory.index'), { search: search.value, sort_field: sortField.value, sort_direction: sortDirection.value }, { preserveState: true, replace: true });
    };

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
        category: '',
        material: '',
        color: '',
        uom: '',
        type: '',
        size: '',
        price: '',
        min_stock: '',
        max_stock: '',
        status: '',
    });

    // Watchers for fields that affect item_code
    watch([selectedColor, form.type, selectedMaterial, selectedCategory, form.size, selectedUom], () => {
        form.item_code = generateItemCode();
    }, { deep: true });

    // Method to generate item_code
    const generateItemCode = () => {
        const color = form.color || '';
        const type = form.type || '';
        const material = form.material || '';
        const category = form.category || '';
        const size = form.size || '';
        const uom = form.uom || '';
        return `${color}${type}${material}${category}${size}${uom}`;
    };

    function toggleFormVisibility() {
        if (isFormVisible.value) {
            // Form is currently visible, so we're closing it
            resetForm();
            editing.value = false;
        }
        isFormVisible.value = !isFormVisible.value;
    }

    const edit = (inventory) => {
        if (!isFormVisible.value) {
            isFormVisible.value = true;
        }
        form.id = inventory.id;
        form.name = inventory.name;
        form.item_code = inventory.item_code;
        form.item_qty = inventory.item_qty;
        form.category = inventory.category;
        form.material = inventory.material;
        form.color = inventory.color;
        form.uom = inventory.uom;
        form.type = inventory.type;
        form.size = inventory.size;
        form.price = inventory.price;
        form.min_stock = inventory.min_stock;
        form.max_stock = inventory.max_stock;
        form.status = inventory.status;

        selectedCategory.value = inventory.category;
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
        form.delete(route('inventory.destroy', itemToDelete.value), {
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
        form.category = '';
        form.material = '';
        form.color = '';
        form.uom = '';
        form.type = '';
        form.size = '';
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
            form.post(route('inventory.update', form.id), {
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
                    if (isFormVisible.value) {
                        isFormVisible.value = false;
                    }
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
                <div class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black dark:border-gray-500 dark:bg-gray-800">
                    <div>
                        <h1 class="text-2xl font-extrabold dark:text-gray-200">Inventory Form</h1>
                    </div>
                    <button @click="toggleFormVisibility" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-5 p-5 mt-10 md:grid-cols-2">
                        <CustomInput name="Name" v-model="form.name"/>
                        <CustomInput name="Item Code" v-model="form.item_code" disabled/>
                        <div>
                            <label class="text-sm font-medium dark:text-gray-200">Color</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedColor"
                                :items="colors"
                                placeholder="Search Colors..."
                                @change="form.color = $event.name"
                            />
                        </div>
                        <CustomInput name="Type" v-model="form.type"/>
                        <div>
                            <label class="text-sm font-medium dark:text-gray-200">Material</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedMaterial"
                                :items="materials"
                                placeholder="Search Material..."
                                @change="form.material = $event.name"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium dark:text-gray-200">Category</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedCategory"
                                :items="categories"
                                placeholder="Search category"
                                @change="form.category = $event.name"
                            />
                        </div>
                        <CustomInput name="Size" v-model="form.size"/>
                        <div>
                            <label class="text-sm font-medium dark:text-gray-200">UOM</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedUom"
                                :items="uoms"
                                placeholder="Search UOM..."
                                @change="form.uom = $event.name"
                            />
                        </div>
                        <CustomInput name="Item Quantity" v-model="form.item_qty"/>
                        <CustomInput name="Item Price" v-model="form.price"/>
                        <CustomInput name="Minimum Stock" v-model="form.min_stock"/>
                        <CustomInput name="Maximum Stock" v-model="form.max_stock"/>
                    </div>
                    <div class="flex items-center justify-center gap-2 p-2 mt-2">
                        <ButtonCode type="submit" :icon="editing ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing ? 'Update Item' : 'Add Item'" />
                        <ButtonCode
                            v-if="editing"
                            type="button"
                            @click="cancelForm"
                            color="bg-gray-500 hover:bg-gray-700"
                            text="Cancel"
                        />
                    </div>
                </form>
            </div>
        </Modal>
        <div class="p-5">
            <div class="p-6 mt-2 bg-white rounded shadow dark:bg-gray-700">
                <!-- Search Bar -->
                <div class="flex flex-col items-end justify-end gap-2 mb-4 md:items-center md:flex-row">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Add Item"
                        :icon="PhFilePlus"
                        color="bg-emerald-700 hover:bg-emerald-900"
                    />
                    <div class="relative">
                        <PhListMagnifyingGlass class="absolute text-gray-400 transform -translate-y-1/2 dark:text-gray-500 left-2 top-1/2" :size="20" />
                        <input
                        type="text"
                        v-model="search"
                        placeholder="Search..."
                        class="py-1 pl-8 pr-2 text-sm border dark:bg-gray-300 dark:text-gray-500 rounded-2xl"
                        />
                    </div>
                </div>

                <div v-if="inventories && inventories.data && inventories.data.length > 0">
                    <div class="overflow-x-auto border rounded-lg dark:border-gray-600">
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg dark:border-gray-600 dark:divide-gray-600">
                            <thead>
                                <tr class="text-xs text-center text-white bg-gray-100 md:text-base">
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('id')" class="flex items-center justify-center w-full">
                                            ID
                                            <PhCaretUp v-if="sortField === 'id' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'id' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('name')" class="flex items-center justify-center w-full">
                                            NAME
                                            <PhCaretUp v-if="sortField === 'name' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'name' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('item_code')" class="flex items-center justify-center w-full">
                                            ITEM CODE
                                            <PhCaretUp v-if="sortField === 'item_code' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'item_code' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('item_qty')" class="flex items-center justify-center w-full">
                                            ITEM QUANTITY
                                            <PhCaretUp v-if="sortField === 'item_qty' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'item_qty' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('category')" class="flex items-center justify-center w-full">
                                            CATEGORY
                                            <PhCaretUp v-if="sortField === 'category' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'category' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('material')" class="flex items-center justify-center w-full">
                                            MATERIAL
                                            <PhCaretUp v-if="sortField === 'material' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'material' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('color')" class="flex items-center justify-center w-full">
                                            COLOR
                                            <PhCaretUp v-if="sortField === 'color' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'color' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('size')" class="flex items-center justify-center w-full">
                                           SIZE
                                            <PhCaretUp v-if="sortField === 'size' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'size' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('price')" class="flex items-center justify-center w-full">
                                            PRICE
                                            <PhCaretUp v-if="sortField === 'price' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'price' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('min_stock')" class="flex items-center justify-center w-full">
                                            MIN STOCK
                                            <PhCaretUp v-if="sortField === 'min_stock' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'min_stock' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('max_stock')" class="flex items-center justify-center w-full">
                                            MAX STOCK
                                            <PhCaretUp v-if="sortField === 'max_stock' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'max_stock' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                        <button @click="sort('status')" class="flex items-center justify-center w-full">
                                            STATUS
                                            <PhCaretUp v-if="sortField === 'status' && sortDirection === 'asc'" class="ml-1"
                                                :size="16" />
                                            <PhCaretDown v-if="sortField === 'status' && sortDirection === 'desc'" class="ml-1"
                                                :size="16" />
                                        </button>
                                    </th>
                                    <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-xs text-gray-600 dark:text-gray-50 dark:bg-gray-500 dark:even:bg-gray-600 dark:hover:bg-gray-800 md:text-base hover:bg-blue-100 even:bg-gray-50" v-for="inventory in inventories.data" :key="inventory.id">
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.id }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.name }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.item_code }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.item_qty }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.category }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.material }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.color }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.size }}{{ inventory.uom }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.price }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.min_stock }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.max_stock }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.status }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                        <div class="inline-flex justify-center w-full h-full gap-2 ">
                                            <button @click="edit(inventory)" class="p-2 text-white bg-blue-500 rounded-full"><PhPencil :size="16" /></button>
                                            <button @click="confirmDelete(inventory.id)" class="p-2 text-white bg-red-500 rounded-full"><PhTrash :size="16" /></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <PaginationButton :data="inventories" />
                </div>
                <div v-else class="p-4 text-center">
                    <p>No items found. Create your first item above.</p>
                </div>
            </div>
        </div>
        <!-- Delete Confirmation Modal -->
        <SimpleDialog
            v-model="showDeleteConfirmation"
            theme="red"
            :icon="PhWarning"
            title="Confirm Delete"
            description="Are you sure you want to delete this item? This action cannot be undone."
            confirmText="Yes, Delete"
            @confirm="deleteItem"
            @cancel="cancelDelete"
        />

        <!-- Confirm Dialog for Add/Update/Cancel -->
        <SimpleDialog
            v-model="showConfirmDialog"
            :theme="dialogAction === 'add' || dialogAction === 'update' ? 'blue' : 'yellow'"
            :icon="dialogAction === 'add' || dialogAction === 'update' ? PhDownloadSimple : PhWarning"
            :title="dialogAction === 'add' ? 'Confirm Add' : dialogAction === 'update' ? 'Confirm Update' : 'Confirm Cancel'"
            :description="dialogAction === 'add' ? 'Are you sure you want to add this item?' :
                        dialogAction === 'update' ? 'Are you sure you want to update this item?' :
                        'Are you sure you want to cancel? Any unsaved changes will be lost.'"
            :confirmText="dialogAction === 'add' ? 'Yes, Add' : dialogAction === 'update' ? 'Yes, Update' : 'Yes, Cancel'"
            cancelText="No"
            @confirm="dialogAction === 'add' || dialogAction === 'update' ? confirmSubmit() : confirmCancel()"
            @cancel="cancelConfirmDialog"
        />

        <!-- Toast Notification -->
        <TopToast ref="topToast" />
    </AppLayout>
</template>
