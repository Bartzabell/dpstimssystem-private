<script setup>
    import { ref, watch, computed } from 'vue';
    import { useForm, router, usePage } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { PhRowsPlusBottom, PhX, PhPrinter, PhFilePlus, PhDownloadSimple, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass, PhWarning, PhCaretUp, PhCaretDown } from "@phosphor-icons/vue";

    const props = defineProps({
        forms: Object,
        suppliers: Array,
        inventories: Array,
        filters: Object
    });

    const search = ref(props.filters.search || '');
    const sortField = ref(props.filters.sort_field || 'id');
    const sortDirection = ref(props.filters.sort_direction || 'asc');
    const isFormVisible = ref(false);
    const editing = ref(false);
    const topToast = ref(null);

    const selectedItems = ref([]);
    const selectedSupplier = ref(null);

    function toggleFormVisibility() {
        if (isFormVisible.value) {
            // Form is currently visible, so we're closing it
            resetForm();
            editing.value = false;
        }
        isFormVisible.value = !isFormVisible.value;
    }

    const showDeleteConfirmation = ref(false);
    const showConfirmDialog = ref(false);
    const itemToDelete = ref(null);
    const dialogAction = ref('');

    const toast = ref({
        show: false,
        message: '',
        type: 'success',
    });

    const form = useForm({
        id: null,
        supplier_id: '',
        date_purchased: '',
        phone_no: '',
        tin_no: '',
        items: [],
    });

    const total_price = computed(() => {
        return form.items.reduce((total, item) => {
            return total + (Number(item.item_price) || 0); // Ensure item_price is a number
        }, 0);
    });

    watch(search, (value) => {
        router.get(route('purchase.index'), { search: value, sort_field: sortField.value, sort_direction: sortDirection.value }, { preserveState: true, replace: true });
    }, { deep: true });

    const sort = (field) => {
        if (sortField.value === field) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortField.value = field;
            sortDirection.value = 'asc';
        }
        router.get(route('purchase.index'), { search: search.value, sort_field: sortField.value, sort_direction: sortDirection.value }, { preserveState: true, replace: true });
    };

    const edit = (purchase_form) => {
        if (!isFormVisible.value) {
            isFormVisible.value = true;
        }
        form.id = purchase_form.id;
        form.supplier_id = purchase_form.supplier_id;
        form.date_purchased = purchase_form.date_purchased;
        form.phone_no = purchase_form.supplier?.phone_no || '';
        form.tin_no = purchase_form.supplier?.tin_no || '';

        if (purchase_form.date_purchased) {
            const dateObj = new Date(purchase_form.date_purchased + 'Z');
            form.date_purchased = dateObj.toISOString().split('T')[0];
        } else {
            form.date_purchased= '';
        }

        form.items = purchase_form.items ?
            [...purchase_form.items] : [];

        selectedItems.value = form.items.map(item => item.stock_id || null);
        selectedSupplier.value = purchase_form.supplier_id;

        editing.value = true;
    };

    watch(selectedSupplier, (newSupplierId) => {
        if (newSupplierId) {
            const selectedSupplier = props.suppliers.find(supplier => supplier.id === newSupplierId);
            if (selectedSupplier) {
                form.phone_no = selectedSupplier.phone_no;
                form.tin_no = selectedSupplier.tin_no;
            }
        } else {
            form.phone_no = '';
            form.tin_no = '';
        }
    });

    const confirmDelete = (id) => {
        itemToDelete.value = id;
        showDeleteConfirmation.value = true;
    };

    const deleteItem = () => {
        form.delete(route('purchase.destroy', itemToDelete.value), {
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
        form.supplier_id = '';
        form.date_purchased = '';
        form.phone_no = '';
        form.tin_no = '';
        form.items = [];
        selectedItems.value = [];
        selectedSupplier.value = null;
    };

    const addItem = () => {
        form.items.push({
            id: null,
            tpb_id: '',
            stock_id: '',
            item_qty: 0, // Initialize with 0
            item_price: 0, // Initialize with 0
        });
        selectedItems.value.push(null);
    };

    const removeItem = (index) => {
        form.items.splice(index, 1);
        selectedItems.value.splice(index, 1);
    };

    const formatDate = (date) => {
        if (!date) return "";
        return new Date(date).toLocaleDateString("en-GB", {
            day: "numeric",
            month: "long",
            year: "numeric"
        });
    };

    const handleInventoryChange = (event, index) => {
        form.items[index].stock_id = event.id;
        selectedItems.value[index] = event.id;
        updatePrice(index);
    };

    const updatePrice = (index) => {
        const item = form.items[index];
        const inventory = props.inventories.find(inv => inv.id === item.stock_id);
        if (inventory && item.item_qty) {
            item.item_price = inventory.price * item.item_qty;
        }
    };

    watch(() => form.items, (newItems) => {
        newItems.forEach((item, index) => {
            watch(() => item.item_qty, () => {
                updatePrice(index);
            });
        });
    }, { deep: true });

    const submit = () => {
        dialogAction.value = editing.value ? 'update' : 'add';
        showConfirmDialog.value = true;
    };

    const confirmSubmit = () => {
        if (editing.value) {
            form.post(route('purchase.update', form.id), {
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
            form.post(route('purchase.store'), {
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
    <AppLayout title="Transaction Purchase Form">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Transaction Purchase Forms</h2>
        </template>
        <Modal :show="isFormVisible" @close="!isFormVisible" class="fixed inset-0 z-50">
            <div v-if="isFormVisible">
                <div class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black">
                    <div>
                        <h1 class="text-2xl font-extrabold">Purchase Form</h1>
                    </div>
                    <button @click="toggleFormVisibility" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>
                <form @submit.prevent="submit" class="pt-10 pb-4 m-3 bg-white rounded shadow">
                    <div class="grid grid-cols-1 gap-5 px-3 py-1 md:grid-cols-2">
                        <div>
                            <div class="mb-5 spanlabel">
                                <label class="block mb-1 text-sm font-medium">Supplier</label>
                                <SearchableDropdown
                                    class="border rounded-lg border-slate-600"
                                    v-model="selectedSupplier"
                                    :items="suppliers"
                                    placeholder="Search Supplier..."
                                    @change="form.supplier_id = $event.id"
                                />
                            </div>
                            <div class="mb-5">
                                <CustomInput name="Date Purchased" type="date" v-model="form.date_purchased" :message="form.errors.date_purchased" />
                            </div>
                            <div class="mb-5">
                                <CustomInput name="Phone Number" type="text" v-model="form.phone_no" disabled/>
                            </div>
                            <div>
                                <CustomInput name="Tax Identification Number" type="text" v-model="form.tin_no" disabled/>
                            </div>
                        </div>

                        <div class="overflow-y-auto max-h-[90vh] md:max-h-[50vh]">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-bold">Items</h3>
                            </div>
                            <div v-if="form.items.length === 0" class="py-4 text-center rounded bg-gray-50">
                                <p>No items added yet. Click 'Add Item' to start.</p>
                            </div>
                            <div v-else class="overflow-visible border rounded-lg">
                                <table class="w-full">
                                    <thead>
                                        <tr class="text-left bg-gray-100">
                                            <th class="w-3/5 px-2 py-1 border whitespace-nowrap">Item Code</th>
                                            <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Quantity</th>
                                            <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Price</th>
                                            <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                                            <td class="px-2 py-1 border whitespace-nowrap">
                                                <SearchableDropdown
                                                    class="border rounded-lg border-slate-600"
                                                    v-model="selectedItems[index]"
                                                    :items="inventories"
                                                    placeholder="Search Item..."
                                                    @change="handleInventoryChange($event, index)"
                                                />
                                            </td>
                                            <td class="px-2 py-1 border whitespace-nowrap"><CustomInput v-model="item.item_qty" min="1" /></td>
                                            <td class="px-2 py-1 border whitespace-nowrap"><CustomInput v-model="item.item_price" min="1" /></td>
                                            <td class="px-2 py-1 border whitespace-nowrap">
                                                <div class="inline-flex justify-center w-full h-full gap-2 ">
                                                    <ButtonCode @click="removeItem(index)" color="bg-red-700 hover:bg-red-900" text="Remove" />
                                                    <!-- <button type="button" @click="removeItem(index)" class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                                                        Remove
                                                    </button> -->
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Display Total Price -->
                            <div class="flex justify-end w-full">
                                <div class="flex items-center gap-4 px-1 py-2 bg-gray-300 rounded-bl-lg">
                                    <span class="text-lg font-bold">Total Price:</span>
                                    <span class="text-lg">{{ total_price.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end w-full col-span-1 pt-2 md:col-span-2">
                                <ButtonCode :icon="PhRowsPlusBottom" color="bg-emerald-700 hover:bg-emerald-900" @click="addItem" text="Add Item" />
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <ButtonCode type="submit" :icon="editing ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing ? 'Update' : 'Add'" />
                        <ButtonCode v-if="editing" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel" @click="cancelForm" />
                    </div>
                </form>
            </div>
        </Modal>
        <div class="p-5">
            <div class="p-6 mt-2 bg-white rounded shadow">
                <!-- Search Bar -->
                <div class="flex flex-col items-end justify-end gap-2 mb-4 md:items-center md:flex-row">
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
                <!-- items Table -->
                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-xs text-center text-white bg-gray-100 md:text-base">
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('id')" class="flex items-center justify-center w-full">
                                        ID
                                        <PhCaretUp v-if="sortField === 'id' && sortDirection === 'asc'" class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'id' && sortDirection === 'desc'" class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('supplier_id')" class="flex items-center justify-center w-full">
                                        SUPPLIER
                                        <PhCaretUp v-if="sortField === 'supplier_id' && sortDirection === 'asc'" class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'supplier_id' && sortDirection === 'desc'" class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('date_purchased')" class="flex items-center justify-center w-full">
                                        DATE PURCHASED
                                        <PhCaretUp v-if="sortField === 'date_purchased' && sortDirection === 'asc'" class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'date_purchased' && sortDirection === 'desc'" class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('total_price')" class="flex items-center justify-center w-full">
                                        TOTAL PRICE
                                        <PhCaretUp v-if="sortField === 'total_price' && sortDirection === 'asc'" class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'total_price' && sortDirection === 'desc'" class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('created_by')" class="flex items-center justify-center w-full">
                                        CREATED BY
                                        <PhCaretUp v-if="sortField === 'created_by' && sortDirection === 'asc'" class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'created_by' && sortDirection === 'desc'" class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('created_at')" class="flex items-center justify-center w-full">
                                        DATE CREATED
                                        <PhCaretUp v-if="sortField === 'created_at' && sortDirection === 'asc'" class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'created_at' && sortDirection === 'desc'" class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50" v-for="form in forms.data" :key="form.id">
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.id }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.supplier?.name }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(form.date_purchased) }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.total_price }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.creator?.name }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(form.created_at) }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">
                                    <div class="inline-flex justify-center w-full h-full gap-2 ">
                                        <button @click="edit(form)" class="p-3 text-white bg-blue-700 rounded-full hover:bg-blue-900"><PhPencil :size="16" /></button>
                                        <button @click="confirmDelete(form.id)" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900"><PhTrash :size="16" /></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <PaginationButton :data="forms" />
            </div>
        </div>
        <SimpleDialog
            v-model="showDeleteConfirmation"
            theme="red"
            :icon="PhWarning"
            title="Confirm Delete"
            description="Are you sure you want to delete this? This action cannot be undone."
            confirmText="Yes, Delete"
            @confirm="deleteItem"
            @cancel="cancelDelete"
        />
        <SimpleDialog
            v-model="showConfirmDialog"
            :theme="dialogAction === 'add' || dialogAction === 'update' ? 'blue' : 'yellow'"
            :icon="dialogAction === 'add' || dialogAction === 'update' ? PhDownloadSimple : PhWarning"
            :title="dialogAction === 'add' ? 'Confirm Add' : dialogAction === 'update' ? 'Confirm Update' : 'Confirm Cancel'"
            :description="dialogAction === 'add' ? 'Are you sure you want to add this?' :
                        dialogAction === 'update' ? 'Are you sure you want to update this?' :
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
