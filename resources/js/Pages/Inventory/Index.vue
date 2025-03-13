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
            <h2 class="text-sm font-bold md:text-xl">Inventory</h2>
        </template>
        <Modal :show="isFormVisible" @close="!isFormVisible" class="fixed inset-0 z-50">
            <div v-if="isFormVisible">
                <div class="absolute top-0 flex justify-end w-full">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Close"
                        color="bg-red-500 hover:bg-red-700"
                    />
                </div>
                <form @submit.prevent="submit" class="pb-4 m-3 bg-white rounded shadow">
                    <div class="grid w-full grid-cols-1 gap-4 p-6 md:grid-cols-3">
                        <div class="col-span-1 md:col-span-3">
                            <h1 class="text-lg font-bold md:text-xl">Inventory Form</h1>
                            <hr class="my-4">
                        </div>
                        <CustomInput name="Name" v-model="form.name"/>
                        <CustomInput name="Item Code" v-model="form.item_code"/>
                        <CustomInput name="Item Quantity" v-model="form.item_qty"/>
                        <CustomInput name="Item Price" v-model="form.price"/>
                        <CustomInput name="Minimum Stock" v-model="form.min_stock"/>
                        <CustomInput name="Maximum Stock" v-model="form.max_stock"/>
                        <div>
                            <label class="text-sm font-medium">Category</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedCategory"
                                :items="categories"
                                placeholder="Search category"
                                @change="form.category_id = $event.id"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium">Material</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedMaterial"
                                :items="materials"
                                placeholder="Search Material..."
                                @change="form.material = $event.name"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium">Color</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedColor"
                                :items="colors"
                                placeholder="Search Colors..."
                                @change="form.color = $event.name"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium">UOM</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedUom"
                                :items="uoms"
                                placeholder="Search UOM..."
                                @change="form.uom = $event.name"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-2 mt-6">
                        <ButtonCode type="submit" :icon="editing ? PhFloppyDisk : PhFilePlus" color="bg-blue-500 hover:bg-blue-700" :text="editing ? 'Update Item' : 'Add Item'" />
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
            <div class="p-6 mt-2 bg-white rounded shadow">
                <!-- Search Bar -->
                <div class="flex items-center justify-between mb-4">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Create Item FOrm"
                        :icon="PhFilePlus"
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

                <div v-if="inventories && inventories.data && inventories.data.length > 0">
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-xs bg-gray-100 md:text-base">
                                    <th class="px-2 py-1 border whitespace-nowrap">ID</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">NAME</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">ITEM CODE</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">ITEM QUANTITY</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">CATEGORY</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">MATERIAL</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">COLOR</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">UNIT</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">PRICE</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">MIN STOCK</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">MAX STOCK</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">STATUS</th>
                                    <th class="px-2 py-1 border whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50" v-for="inventory in inventories.data" :key="inventory.id">
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.id }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.name }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.item_code }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.item_qty }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.category?.name }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.material }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.color }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ inventory.uom }}</td>
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

