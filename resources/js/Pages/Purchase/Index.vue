<script setup>
    import { ref, watch } from 'vue';
    import { useForm, router, usePage } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { PhRowsPlusBottom, PhEyeSlash, PhFilePlus, PhDownloadSimple, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass, PhWarning } from "@phosphor-icons/vue";

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
    const selectedSupplier = ref(null);

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
        router.get(route('purchase.index'), { search: value }, { preserveState: true, replace: true });
    }, { deep: true });

    const edit = (purchase_form) => {
        if (!isFormVisible.value) {
            isFormVisible.value = true;
        }
        form.id = purchase_form.id;
        form.supplier_id = purchase_form.supplier_id;
        form.date_purchased = purchase_form.date_purchased;

        if (purchase_form.date_purchased) {
            // Parse the date as UTC and convert it to local time
            const dateObj = new Date(purchase_form.date_purchased + 'Z'); // Append 'Z' to treat it as UTC
            form.date_purchased = dateObj.toISOString().split('T')[0]; // Format as YYYY-MM-DD
        } else {
            form.date_purchased= '';
        }

        // Load items
        form.items = purchase_form.items ?
            [...purchase_form.items] : [];

        // Fixed: Initialize selectedItems array with the correct number of elements
        selectedItems.value = form.items.map(item => item.stock_id || null);
        selectedSupplier.value = purchase_form.supplier_id;

        editing.value = true;
    };

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
        form.items = [];
        selectedItems.value = [];
        selectedSupplier.value = null;
    };

    // Add a new empty items
    const addItem = () => {
        form.items.push({
            id: null,
            tpb_id: '',
            stock_id: '',
            item_qty: '',
            per_piece: '',
            total_price: '',
            bill_no: '',
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

    const handleInventoryChange = (event, index) => {
        form.items[index].stock_id = event.id;
        selectedItems.value[index] = event.id;
    };

    const submit = () => {
        dialogAction.value = editing.value ? 'update' : 'add';
        showConfirmDialog.value = true;
    };

    const confirmSubmit = () => {
        if (editing.value) {
            form.put(route('purchase.update', form.id), {
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
                <div class="absolute top-0 flex justify-end w-full">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Close"
                        color="bg-red-500 hover:bg-red-700"
                    />
                </div>
                <form @submit.prevent="submit" class="pb-4 m-3 bg-white rounded shadow">
                    <h1 class="px-6 py-2 text-2xl font-extrabold">Purchase Form</h1>
                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                        <div class="spanlabel">
                            <label class="block mb-1 text-sm font-medium">Supplier</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedSupplier"
                                :items="suppliers"
                                placeholder="Search Supplier..."
                                @change="form.supplier_id = $event.id"
                            />
                        </div>
                        <div>
                            <CustomInput name="Date Purchased" type="date" v-model="form.date_purchased" :message="form.errors.date_purchased" />
                        </div>

                        <div class="col-span-1 mt-6 md:col-span-2">
                            <div class="p-5 mt-6">
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
                                                <th class="w-3/5 px-2 py-1 border whitespace-nowrap">ITEM CODE</th>
                                                <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Quantity</th>
                                                <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Price Per Unit</th>
                                                <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Total Price</th>
                                                <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Bill No</th>
                                                <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                                                <td class="px-2 py-1 border whitespace-nowrap">
                                                    <!-- Fixed: Use index-specific v-model binding -->
                                                    <SearchableDropdown
                                                        class="border rounded-lg border-slate-600"
                                                        v-model="selectedItems[index]"
                                                        :items="inventories"
                                                        placeholder="Search Item..."
                                                        @change="handleInventoryChange($event, index)"
                                                    />
                                                </td>
                                                <td class="px-2 py-1 border whitespace-nowrap"><CustomInput v-model="item.item_qty" min="1" /></td>
                                                <td class="px-2 py-1 border whitespace-nowrap"><CustomInput v-model="item.per_piece" min="1" /></td>
                                                <td class="px-2 py-1 border whitespace-nowrap"><CustomInput v-model="item.total_price" min="1" /></td>
                                                <td class="px-2 py-1 border whitespace-nowrap"><CustomInput v-model="item.bill_no" min="1" /></td>

                                                <td class="px-2 py-1 border whitespace-nowrap">
                                                    <div class="inline-flex justify-center w-full h-full gap-2 ">
                                                        <button type="button" @click="removeItem(index)" class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="flex justify-end w-full py-2">
                                    <ButtonCode :icon="PhRowsPlusBottom" color="bg-emerald-700 hover:bg-emerald-900" @click="addItem" text="Add Item" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2 mt-6">
                        <ButtonCode type="submit" :icon="editing ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing ? 'Update' : 'Add'" />
                        <ButtonCode v-if="editing" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel" @click="cancelForm" />
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
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">ID</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">SUPPLIER</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">DATE PURCHASED</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">CREATED BY</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">DATE CREATED</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50" v-for="form in forms.data" :key="form.id">
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.id }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.supplier?.name }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(form.date_purchased) }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.creator?.name }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(form.created_at) }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">
                                    <div class="inline-flex justify-center w-full h-full gap-2 ">
                                        <button class="p-3 text-white bg-green-700 rounded-full hover:bg-green-900"><PhPrinter :size="16" /></button>
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
