<script setup>
import { ref, watch, computed } from 'vue';
import Print from './Print.vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { PhRowsPlusBottom, PhX, PhPrinter, PhFilePlus, PhCaretUp, PhCaretDown, PhDownloadSimple, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass, PhWarning } from "@phosphor-icons/vue";

const props = defineProps({
    forms: Object,
    customers: Array,
    inventories: Array,
    discounts: Array,
    filters: Object,
    categories: Array
});

const printRef = ref(null);
function handlePrint(formId) {
    printRef.value.printTest(formId);
}

const search = ref(props.filters.search || '');
const sortField = ref(props.filters.sort_field || 'id');
const sortDirection = ref(props.filters.sort_direction || 'asc');
const isFormVisible = ref(false);
const editing = ref(false);
const topToast = ref(null);

const selectedItems = ref([]);
const selectedCustomer = ref(null);
const selectedDiscount = ref(null);

// State for categories and filtered items
const selectedCategory = ref(null);
const filteredInventories = computed(() => {
    if (!selectedCategory.value) return props.inventories;
    return props.inventories.filter(item => item.category === selectedCategory.value);
});

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

const lastItemQty = computed({
    get() {
        return form.items.length > 0 ? form.items[form.items.length - 1].item_qty : '';
    },
    set(value) {
        if (form.items.length > 0) {
            form.items[form.items.length - 1].item_qty = value;
        }
    }
});

const form = useForm({
    id: null,
    customer_id: '',
    discount_id: '',
    date_sold: '',
    phone_no: '',
    tin_no: '',
    address: '',
    email: '',
    items: [],
    total_price: 0,
});

const getSubtotal = () => {
    return form.items.reduce((total, item) => {
        return total + (Number(item.item_price) || 0);
    }, 0);
};

const getDiscountInfo = () => {
    const result = {
        applied: false,
        name: '',
        description: '',
        amount: 0
    };

    if (selectedDiscount.value) {
        const discount = props.discounts.find(d => d.id === selectedDiscount.value);
        if (discount) {
            const subtotal = getSubtotal();
            result.applied = true;
            result.name = discount.name;

            if (discount.type === "Percentage") {
                result.description = `${discount.amount}%`;
                result.amount = subtotal * (discount.amount / 100);
            } else if (discount.type === "Fixed Amount") {
                result.description = 'Fixed Amount';
                // Ensure discount.amount is a number
                result.amount = parseFloat(discount.amount);
            }
        }
    }

    // Ensure result.amount is a number
    if (typeof result.amount !== 'number' || isNaN(result.amount)) {
        result.amount = 0;
    }

    return result;
};

const total_price = computed(() => {
    const subtotal = getSubtotal();
    const discountInfo = getDiscountInfo();

    if (discountInfo.applied) {
        return subtotal - discountInfo.amount;
    }

    return subtotal;
});

watch(search, (value) => {
    router.get(route('sales.index'), { search: value, sort_field: sortField.value, sort_direction: sortDirection.value }, { preserveState: true, replace: true });
}, { deep: true });

const sort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route('sales.index'), { search: search.value, sort_field: sortField.value, sort_direction: sortDirection.value }, { preserveState: true, replace: true });
};

const edit = (sales_form) => {
    if (!isFormVisible.value) {
        isFormVisible.value = true;
    }
    form.id = sales_form.id;
    form.customer_id = sales_form.customer_id;
    form.discount_id = sales_form.discount_id;
    form.date_sold = sales_form.date_sold;
    form.phone_no = sales_form.supplier?.phone_no || '';
    form.tin_no = sales_form.supplier?.tin_no || '';

    if (sales_form.date_sold) {
        const dateObj = new Date(sales_form.date_sold + 'Z');
        form.date_sold = dateObj.toISOString().split('T')[0];
    } else {
        form.date_sold = '';
    }

    form.items = sales_form.items ?
        [...sales_form.items] : [];

    selectedItems.value = form.items.map(item => item.stock_id || null);
    selectedCustomer.value = sales_form.customer_id;
    selectedDiscount.value = sales_form.discount_id;
    editing.value = true;
};

watch(selectedCustomer, (newCustomerId) => {
    if (newCustomerId) {
        const selectedCustomer = props.customers.find(customer => customer.id === newCustomerId);
        if (selectedCustomer) {
            form.phone_no = selectedCustomer.phone_no;
            form.tin_no = selectedCustomer.tin_no;
            form.email = selectedCustomer.email;
            form.address = `${selectedCustomer.street}, ${selectedCustomer.municipality}, ${selectedCustomer.city}`;
        }
    } else {
        form.phone_no = '';
        form.tin_no = '';
        form.email = '';
        form.address = '';
    }
});

watch([() => form.items, () => selectedDiscount.value], () => {
    form.total_price = total_price.value;
}, { deep: true });

const confirmDelete = (id) => {
    itemToDelete.value = id;
    showDeleteConfirmation.value = true;
};

const deleteItem = () => {
    form.delete(route('sales.destroy', itemToDelete.value), {
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
    form.customer_id = '';
    form.discount_id = '';
    form.date_sold = '';
    form.items = [];
    selectedItems.value = [];
    selectedCustomer.value = null;
    selectedDiscount.value = null;
    selectedCategory.value = null;
};

const addItem = () => {
    form.items.push({
        id: null,
        tsb_id: '',
        stock_id: '',
        item_qty: '',
        item_price: '',
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

const handleInventorySelection = (inventoryItem, index) => {
    form.items[index].stock_id = inventoryItem.id;
    selectedItems.value[index] = inventoryItem.id;
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

const selectCategory = (category) => {
    selectedCategory.value = category;
};

const submit = () => {
    dialogAction.value = editing.value ? 'update' : 'add';
    showConfirmDialog.value = true;
};

const confirmSubmit = () => {
    if (editing.value) {
        form.post(route('sales.update', form.id), {
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
        form.post(route('sales.store'), {
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
    <AppLayout title="Transaction Sales Form">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Transaction Sales Forms
            </h2>
        </template>
        <Modal :show="isFormVisible" @close="!isFormVisible" class="fixed inset-0 z-50">
            <div v-if="isFormVisible" class="grid grid-cols-1 gap-1 xl:grid-cols-2">
                <div
                    class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black dark:border-gray-500 dark:bg-gray-800">
                    <div>
                        <h1 class="text-2xl font-extrabold dark:text-gray-200">Sales Form</h1>
                    </div>
                    <button @click="toggleFormVisibility"
                        class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>

                <!-- Two-grid layout -->
                <div class="p-5 pt-16">
                    <!-- Left side - Item settings -->
                    <div class="h-full bg-white dark:bg-gray-800">
                        <!-- <h2 class="p-1 font-bold dark:text-gray-200">Customer Information</h2> -->
                        <!-- Customer info -->
                        <div
                            class="grid grid-cols-1 px-5 py-1 !text-[10px] 2xl:!text-sm mb-4 bg-gray-300 border-8 border-gray-600 border-double rounded-lg gap-x-2 md:grid-cols-2">
                            <div class="mb-0.5">
                                <label
                                    class="!text-[8px] lg:!text-[10px] font-medium 2xl:!text-sm dark:text-gray-200">Customer</label>
                                <SearchableDropdown class="bg-white border rounded-lg border-slate-600"
                                    v-model="selectedCustomer" :items="customers" placeholder="Search Customer..."
                                    @change="form.customer_id = $event.id" />
                            </div>
                            <div>
                                <CustomInput name="Date Sold" type="date" v-model="form.date_sold"
                                    :message="form.errors.date_sold" />
                            </div>
                            <div>
                                <CustomInput name="Customer Phone Number" type="text" v-model="form.phone_no"
                                    disabled />
                            </div>
                            <div>
                                <CustomInput name="Customer Email" type="text" v-model="form.email" disabled />
                            </div>
                            <div>
                                <CustomInput name="Tax Identification Number" type="text" v-model="form.tin_no"
                                    disabled />
                            </div>
                            <div>
                                <CustomInput name="Customer Address" type="text" v-model="form.address" disabled />
                            </div>
                        </div>

                        <!-- Category buttons -->
                        <div class="grid grid-cols-2 gap-2">
                            <div
                                class="flex flex-col px-1 py-3 bg-indigo-300 border-4 border-gray-600 border-double rounded-lg">
                                <label
                                    class="text-[8px] lg:text-[10px] font-medium 2xl:text-sm dark:text-gray-200">Press
                                    to add
                                    item<b class="text-red-500">*</b></label>
                                <button @click="addItem"
                                    class="flex items-center justify-center w-full px-4 py-2 text-white rounded-lg bg-emerald-700 hover:bg-emerald-900">
                                    <PhRowsPlusBottom :size="20" class="mr-2" />
                                    Add Item
                                </button>
                            </div>
                            <div
                                class="grid grid-cols-2 gap-2 px-1 py-3 bg-pink-200 border-4 border-gray-600 border-double rounded-lg 2xl:grid-cols-4 2xl:gap-0 2xl:gap-x-2">
                                <label
                                    class="text-[8px] lg:text-[10px] font-medium 2xl:text-sm dark:text-gray-200 col-span-2 2xl:col-span-4">Select
                                    a
                                    category<i class="text-xs font-thin text-gray-500">(optional)</i></label>
                                <button @click="selectedCategory = null" :class="[
                                    'px-3 py-2 rounded-lg border border-black text-sm font-medium',
                                    !selectedCategory
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-emerald-100 text-gray-800 dark:bg-gray-600 hover:text-white dark:text-gray-200 hover:bg-emerald-600'
                                ]">
                                    All Items
                                </button>
                                <button v-for="category in props.categories" :key="category.id"
                                    @click="selectCategory(category.name)" :class="[
                                        'px-3 py-2 rounded-lg border border-black text-sm font-medium',
                                        selectedCategory === category.name
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-emerald-100 text-gray-800 dark:bg-gray-600 hover:text-white dark:text-gray-200 hover:bg-emerald-500'
                                    ]">
                                    {{ category.name }}
                                </button>
                            </div>

                            <!-- Item buttons (filtered by category) -->
                            <div class="px-1 py-3 border-4 border-gray-600 border-double rounded-lg bg-amber-200">
                                <label
                                    class="text-[8px] lg:text-[10px] font-medium 2xl:text-sm dark:text-gray-200">Select
                                    an
                                    item<b class="text-red-500">*</b></label>
                                <div class=" grid grid-cols-2 gap-2 overflow-y-auto 2xl:grid-cols-3 max-h-[30vh]">
                                    <button v-for="item in filteredInventories" :key="item.id"
                                        @click="handleInventorySelection(item, form.items.length - 1)"
                                        class="px-3 py-2 text-sm font-medium text-left text-gray-800 truncate border border-black rounded-lg bg-emerald-100 dark:bg-gray-600 dark:text-gray-200 hover:text-white hover:bg-emerald-500"
                                        :title="item.name || item.item_code">
                                        {{ item.name || item.item_code }}
                                    </button>
                                </div>
                            </div>
                            <div
                                class="flex flex-col items-start px-1 py-3 border-4 border-gray-600 border-double rounded-lg bg-lime-100">
                                <!-- Quantity input -->
                                <label
                                    class="text-[8px] lg:text-[10px] font-medium 2xl:text-sm dark:text-gray-200">Input
                                    quantity here<b class="text-red-500">*</b></label>
                                <input type="number" v-model="lastItemQty"
                                    class="w-full px-3 py-2 border-2 rounded-lg border-emerald-700 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    min="1" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-5 pt-16">
                    <!-- Right side - Form details and items list -->
                    <div class="h-full p-5 bg-white rounded shadow dark:bg-gray-800">

                        <!-- Items list -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-bold dark:text-gray-200">Added Items</h3>
                            </div>
                            <div>
                                <div v-if="form.items.length === 0"
                                    class="py-4 text-center rounded dark:text-gray-400 dark:bg-gray-600 bg-gray-50">
                                    <p>No items added yet. Add items from the left panel.</p>
                                </div>
                                <div v-else class="flex flex-col border rounded-lg">
                                    <div class="overflow-y-auto max-h-[20vh]">
                                        <table class="w-full">
                                            <thead class="sticky z-10 -top-1">
                                                <tr class="text-left bg-gray-100 dark:bg-gray-600">
                                                    <th class="px-2 py-1 border dark:text-gray-200 whitespace-nowrap">
                                                        Item Code
                                                    </th>
                                                    <th class="px-2 py-1 border dark:text-gray-200 whitespace-nowrap">
                                                        Quantity
                                                    </th>
                                                    <th class="px-2 py-1 border dark:text-gray-200 whitespace-nowrap">
                                                        Price</th>
                                                    <th class="px-2 py-1 border dark:text-gray-200 whitespace-nowrap">
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="overflow-y-auto max-h-64">
                                                <tr v-for="(item, index) in form.items" :key="index"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-400">
                                                    <td class="px-2 py-1 border">
                                                        {{props.inventories.find(inv => inv.id ===
                                                            item.stock_id)?.item_code ||
                                                            'No item selected'}}
                                                    </td>
                                                    <td class="px-2 py-1 border">
                                                        {{ item.item_qty }}
                                                    </td>
                                                    <td class="px-2 py-1 border">
                                                        {{ parseFloat(item.item_price).toFixed(2) }}
                                                    </td>
                                                    <td class="px-2 py-1 border whitespace-nowrap">
                                                        <div class="inline-flex gap-2">
                                                            <button type="button" @click="removeItem(index)"
                                                                class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                                                                <PhTrash :size="16" />
                                                            </button>
                                                            <button type="button"
                                                                @click="form.items[form.items.length - 1] = item; removeItem(index)"
                                                                class="px-2 py-1 text-white bg-blue-500 rounded hover:bg-blue-600">
                                                                <PhPencil :size="16" />
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- <div class="mt-6">
                                        <button @click="addItem"
                                            class="flex items-center justify-center w-full px-4 py-2 text-white rounded-lg bg-emerald-700 hover:bg-emerald-900">
                                            <PhRowsPlusBottom :size="20" class="mr-2" />
                                            Add Item
                                        </button>
                                    </div> -->
                                </div>
                            </div>
                        </div>

                        <!-- Discount and totals -->
                        <div>
                            <div class="flex items-center mb-2">
                                <span class="mr-2 text-sm font-medium dark:text-gray-200">Discount:</span>
                                <SearchableDropdown class="border rounded-lg border-slate-600"
                                    v-model="selectedDiscount" :items="discounts" placeholder="Search Discount..."
                                    @change="form.discount_id = $event.id" />
                            </div>

                            <!-- Display Total Price -->
                            <div
                                class="flex flex-col w-full p-4 py-2 mt-2 rounded-lg dark:text-gray-200 dark:bg-gray-600 bg-gray-50">
                                <div class="flex justify-between w-full py-1">
                                    <span class="font-medium text-md">Subtotal:</span>
                                    <span class="text-md">{{ getSubtotal().toFixed(2) }}</span>
                                </div>

                                <div v-if="getDiscountInfo().applied" class="flex justify-between w-full py-1">
                                    <span class="font-medium text-md">
                                        {{ getDiscountInfo().name }} ({{ getDiscountInfo().description }}):
                                    </span>
                                    <span class="text-red-400 text-md">-{{ getDiscountInfo().amount.toFixed(2) }}</span>
                                </div>

                                <div
                                    class="flex justify-between w-full py-1 pt-2 mt-1 border-t-4 border-black border-dashed">
                                    <span class="text-lg font-bold">Total Price:</span>
                                    <span class="text-lg font-bold">{{ total_price.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form actions -->
                        <div class="flex items-center justify-center gap-2 mt-6">
                            <ButtonCode type="button" @click="submit" :icon="editing ? PhFloppyDisk : PhFilePlus"
                                color="bg-emerald-700 hover:bg-emerald-900" :text="editing ? 'Update' : 'Add'" />
                            <ButtonCode v-if="editing" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel"
                                @click="cancelForm" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation dialogs -->
            <ConfirmationModal :show="showConfirmDialog" @close="cancelConfirmDialog">
                <template #title>
                    <div>
                        <h2 v-if="dialogAction === 'add'" class="text-xl font-bold">Confirm Add Form</h2>
                        <h2 v-else-if="dialogAction === 'update'" class="text-xl font-bold">Confirm Update Form</h2>
                        <h2 v-else-if="dialogAction === 'cancel'" class="text-xl font-bold">Confirm Cancel</h2>
                    </div>
                </template>
                <template #content>
                    <div>
                        <p v-if="dialogAction === 'add'">Are you sure you want to add this sales form?</p>
                        <p v-else-if="dialogAction === 'update'">Are you sure you want to update this sales form?</p>
                        <p v-else-if="dialogAction === 'cancel'">Are you sure you want to cancel? All changes will be
                            lost.</p>
                    </div>
                </template>
                <template #footer>
                    <div>
                        <button v-if="dialogAction === 'add' || dialogAction === 'update'" @click="confirmSubmit"
                            class="px-4 py-2 text-white rounded bg-emerald-700 hover:bg-emerald-900">
                            Confirm
                        </button>
                        <button v-else-if="dialogAction === 'cancel'" @click="confirmCancel"
                            class="px-4 py-2 text-white rounded bg-emerald-700 hover:bg-emerald-900">
                            Confirm
                        </button>
                        <button @click="cancelConfirmDialog"
                            class="px-4 py-2 ml-2 text-gray-800 bg-gray-200 rounded hover:bg-gray-300 dark:text-gray-200 dark:bg-gray-600 dark:hover:bg-gray-500">
                            Cancel
                        </button>
                    </div>
                </template>
            </ConfirmationModal>

            <ConfirmationModal :show="showDeleteConfirmation" @close="cancelDelete">
                <template #title>
                    <div>
                        <h2 class="text-xl font-bold">Confirm Delete</h2>
                    </div>
                </template>
                <template #content>
                    <div>
                        <p>Are you sure you want to delete this sales form? This action cannot be undone.</p>
                    </div>
                </template>
                <template #footer>
                    <div>
                        <button @click="deleteItem" class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">
                            Delete
                        </button>
                        <button @click="cancelDelete"
                            class="px-4 py-2 ml-2 text-gray-800 bg-gray-200 rounded hover:bg-gray-300 dark:text-gray-200 dark:bg-gray-600 dark:hover:bg-gray-500">
                            Cancel
                        </button>
                    </div>
                </template>
            </ConfirmationModal>
        </Modal>
        <div class="p-5">
            <div class="p-6 mt-2 bg-white rounded shadow dark:bg-gray-800">
                <!-- Search Bar -->
                <div class="flex flex-col items-end justify-end gap-2 mb-4 md:items-center md:flex-row">
                    <ButtonCode @click="toggleFormVisibility" text="Add Sales" :icon="PhFilePlus"
                        color="bg-emerald-700 hover:bg-emerald-900" />
                    <div class="relative">
                        <PhListMagnifyingGlass
                            class="absolute text-gray-400 transform -translate-y-1/2 dark:text-gray-500 left-2 top-1/2"
                            :size="20" />
                        <input type="text" v-model="search" placeholder="Search..."
                            class="py-1 pl-8 pr-2 text-sm border dark:bg-gray-300 dark:text-gray-500 rounded-2xl" />
                    </div>
                </div>
                <!-- items Table -->
                <div class="overflow-x-auto border rounded-lg dark:border-gray-600">
                    <table class="min-w-full divide-y divide-gray-200 dark:border-gray-600 dark:divide-gray-600">
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
                                    <button @click="sort('customer_id')"
                                        class="flex items-center justify-center w-full">
                                        CUSTOMER
                                        <PhCaretUp v-if="sortField === 'customer_id' && sortDirection === 'asc'"
                                            class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'customer_id' && sortDirection === 'desc'"
                                            class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('date_sold')" class="flex items-center justify-center w-full">
                                        DATE SOLD
                                        <PhCaretUp v-if="sortField === 'date_sold' && sortDirection === 'asc'"
                                            class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'date_sold' && sortDirection === 'desc'"
                                            class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('total_price')"
                                        class="flex items-center justify-center w-full">
                                        TOTAL PRICE
                                        <PhCaretUp v-if="sortField === 'total_price' && sortDirection === 'asc'"
                                            class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'total_price' && sortDirection === 'desc'"
                                            class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('created_by')" class="flex items-center justify-center w-full">
                                        CREATED BY
                                        <PhCaretUp v-if="sortField === 'created_by' && sortDirection === 'asc'"
                                            class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'created_by' && sortDirection === 'desc'"
                                            class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">
                                    <button @click="sort('created_at')" class="flex items-center justify-center w-full">
                                        DATE CREATED
                                        <PhCaretUp v-if="sortField === 'created_at' && sortDirection === 'asc'"
                                            class="ml-1" :size="16" />
                                        <PhCaretDown v-if="sortField === 'created_at' && sortDirection === 'desc'"
                                            class="ml-1" :size="16" />
                                    </button>
                                </th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-xs text-gray-600 md:text-base dark:text-gray-50 dark:bg-gray-500 dark:even:bg-gray-600 dark:hover:bg-gray-800 hover:bg-blue-100 even:bg-gray-50"
                                v-for="form in forms.data" :key="form.id">
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.id }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.customer?.name }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(form.date_sold) }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.total_price }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.creator?.name }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(form.created_at) }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">
                                    <div class="inline-flex justify-center w-full h-full gap-2 ">
                                        <button @click="handlePrint(form.id)"
                                            class="p-3 text-white bg-green-700 rounded-full hover:bg-green-900">
                                            <PhPrinter :size="16" />
                                        </button>
                                        <button @click="edit(form)"
                                            class="p-3 text-white bg-blue-700 rounded-full hover:bg-blue-900">
                                            <PhPencil :size="16" />
                                        </button>
                                        <button @click="confirmDelete(form.id)"
                                            class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                                            <PhTrash :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <PaginationButton :data="forms" />
            </div>
        </div>
        <SimpleDialog v-model="showDeleteConfirmation" theme="red" :icon="PhWarning" title="Confirm Delete"
            description="Are you sure you want to delete this? This action cannot be undone." confirmText="Yes, Delete"
            @confirm="deleteItem" @cancel="cancelDelete" />
        <SimpleDialog v-model="showConfirmDialog"
            :theme="dialogAction === 'add' || dialogAction === 'update' ? 'blue' : 'yellow'"
            :icon="dialogAction === 'add' || dialogAction === 'update' ? PhDownloadSimple : PhWarning"
            :title="dialogAction === 'add' ? 'Confirm Add' : dialogAction === 'update' ? 'Confirm Update' : 'Confirm Cancel'"
            :description="dialogAction === 'add' ? 'Are you sure you want to add this?' :
                dialogAction === 'update' ? 'Are you sure you want to update this?' :
                    'Are you sure you want to cancel? Any unsaved changes will be lost.'"
            :confirmText="dialogAction === 'add' ? 'Yes, Add' : dialogAction === 'update' ? 'Yes, Update' : 'Yes, Cancel'"
            cancelText="No"
            @confirm="dialogAction === 'add' || dialogAction === 'update' ? confirmSubmit() : confirmCancel()"
            @cancel="cancelConfirmDialog" />

        <!-- Toast Notification -->
        <TopToast ref="topToast" />
        <Print ref="printRef" />
    </AppLayout>
</template>
