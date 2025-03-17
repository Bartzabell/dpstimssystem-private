<script setup>
    import { ref, watch } from 'vue';
    import { useForm, router, usePage } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { PhRowsPlusBottom, PhEyeSlash, PhPrinter, PhFilePlus, PhDownloadSimple, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass, PhWarning } from "@phosphor-icons/vue";

    const props = defineProps({
        forms: Object,
        customers: Array,
        inventories: Array,
        filters: Object
    });

    const search = ref(props.filters.search || '');
    const isFormVisible = ref(false);
    const editing = ref(false);
    const topToast = ref(null);

    const selectedItems = ref([]);
    const selectedCustomer = ref(null);

    function toggleFormVisibility() {
        isFormVisible.value = !isFormVisible.value;
    };

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
        customer_id: '',
        date_sold: '',
        items: [],
    });

    watch(search, (value) => {
        router.get(route('sales.index'), { search: value }, { preserveState: true, replace: true });
    }, { deep: true });

    const edit = (sales_form) => {
        if (!isFormVisible.value) {
            isFormVisible.value = true;
        }
        form.id = sales_form.id;
        form.customer_id = sales_form.customer_id;
        form.date_sold = sales_form.date_sold;

        if (sales_form.date_sold) {
            const dateObj = new Date(sales_form.date_sold + 'Z');
            form.date_sold = dateObj.toISOString().split('T')[0];
        } else {
            form.date_sold= '';
        }

        form.items = sales_form.items ?
            [...sales_form.items] : [];

        selectedItems.value = form.items.map(item => item.stock_id || null);
        selectedCustomer.value = sales_form.customer_id;

        editing.value = true;
    };

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
        form.date_sold = '';
        form.items = [];
        selectedItems.value = [];
        selectedCustomer.value = null;
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

    // Add this print function
    function printTest() {
    // Get content from your template
    const printContent = document.getElementById('printSection').innerHTML;
    
    // Create an invisible iframe
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    
    // Write the content to the iframe
    iframe.contentDocument.write(`
        <html>
        <head>
            <title>Print Test</title>
            <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
            <style>
            @page {
                size: A4;
                margin: 20mm;
            }
            @media print {
                body {
                width: 210mm;
                height: 297mm;
                }
            }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `);
    
    // Close the document
    iframe.contentDocument.close();
    
    // Wait for resources to load before printing
    iframe.onload = function() {
        // Trigger print
        iframe.contentWindow.print();
        
        // Remove the iframe after printing
        setTimeout(() => {
        document.body.removeChild(iframe);
        }, 1000);
    };
    }
</script>

<template>
    <AppLayout title="Transaction Sales Form">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Transaction Sales Forms</h2>
        </template>
        <div id="printSection" class="hidden">
            <div class="max-w-[210mm] h-full mx-auto">
                <h1 class="w-full mb-1 text-4xl font-bold text-center">
                    DELLOSA'S SOAP AND DETERGENTS MANUFACTURING
                </h1>
                <p class="w-full text-lg text-center"><b>DEALERS IN: </b>Products</p>
                <p class="w-full text-lg text-center">Zenaida subdivision, Limaco street, 3930 Brgy, Biñan, 4024 Laguna</p>
                <p class="w-full text-lg text-center">EMAIL: dellosaspm@gmail.com</p>
                <div class="grid grid-cols-4 mt-5 border border-black">
                    <div class="col-span-2 p-1 font-bold border border-black">
                        NAME OF CONSIGNEE/BUYER
                    </div>
                    <div class="p-1 font-bold border border-black">
                        Invoice No.:
                    </div>
                    <div class="p-1 border border-black">
                        451280-90
                    </div>
                    <div class="col-span-2 p-1 border border-black">
                        Stephanie Hawking
                    </div>
                    <div class="p-1 font-bold border border-black">
                        Date:
                    </div>
                    <div class="p-1 border border-black">
                        March 29, 2025
                    </div>
                    <div class="flex items-center justify-center w-full col-span-2 row-span-3 p-1 border border-black">
                        Signature(Maybe)
                    </div>
                    <div class="p-1 font-bold border border-black">
                        Terms:
                    </div>
                    <div class="p-1 border border-black">
                        Cash on Delivery
                    </div>
                    <div class="p-1 font-bold border border-black">
                        VEH No.:
                    </div>
                    <div class="p-1 border border-black">
                        GHE-785
                    </div>
                    <div class="p-1 font-bold border border-black">
                        Destination:
                    </div>
                    <div class="p-1 border border-black">
                        Tanza, Cavite
                    </div>
                    <div class="col-span-2 p-1 border border-black">
                        <b>TIN: </b>123-456-789-00
                    </div>
                    <div class="p-1 font-bold border border-black">
                        Business Type:
                    </div>
                    <div class="p-1 border border-black">
                        Convenience Store
                    </div>
                </div>
                <div class="grid grid-cols-10 mt-0.5 border border-black">
                    <div class="p-1 font-bold text-center border border-black">
                        
                    </div>
                    <div class="col-span-3 p-1 font-bold text-center border border-black">
                        ITEM/S
                    </div>
                    <div class="col-span-2 p-1 font-bold text-center border border-black">
                        QTY
                    </div>
                    <div class="col-span-2 p-1 font-bold text-center border border-black">
                        Unit Price
                    </div>
                    <div class="col-span-2 p-1 font-bold text-center border border-black">
                        Amount
                    </div>
                    <!-- Body -->
                    <div class="p-1 text-center border border-black">
                        1
                    </div>
                    <div class="col-span-3 p-1 text-center border border-black">
                        16oz_PB0091
                    </div>
                    <div class="col-span-2 p-1 text-center border border-black">
                        50
                    </div>
                    <div class="col-span-2 p-1 text-center border border-black">
                        200
                    </div>
                    <div class="col-span-2 p-1 text-center border border-black">
                        10,000
                    </div>
                </div>
                <div class="grid grid-cols-10 mt-0.5 border border-black">
                    <div class="col-span-4 p-1 font-bold border border-black">
                        Total Sales(VAT Inclusive)
                    </div>
                    <div class="col-span-6 p-1 border border-black">
                        10,000
                    </div>
                    <div class="col-span-4 p-1 font-bold border border-black">
                        Less VAT
                    </div>
                    <div class="col-span-6 p-1 border border-black">
                        
                    </div>
                    <div class="col-span-4 p-1 font-bold border border-black">
                        Amount: Net of VAT
                    </div>
                    <div class="col-span-6 p-1 border border-black">
                        500
                    </div>
                    <div class="col-span-4 p-1 font-bold border border-black">
                        Less: SC/PWD-Discount
                    </div>
                    <div class="col-span-6 p-1 border border-black">
                        
                    </div>
                    <div class="col-span-4 p-1 font-bold border border-black">
                        Amount Due
                    </div>
                    <div class="col-span-6 p-1 border border-black">
                        
                    </div>
                    <div class="col-span-4 p-1 font-bold border border-black">
                        Total Due:
                    </div>
                    <div class="col-span-6 p-1 border border-black">
                        10,500
                    </div>
                </div>
            </div>
            <div class="flex justify-end w-full mx-auto mt-auto">
                <!-- name of buyer/consignee -->
                <h1 class="px-5 pt-1 border-t border-black">Stephanie Hawking</h1>
            </div>
        </div>
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
                    <h1 class="px-6 py-2 text-2xl font-extrabold">Sales Form</h1>
                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                        <div class="spanlabel">
                            <label class="block mb-1 text-sm font-medium">Customer</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedCustomer"
                                :items="customers"
                                placeholder="Search Customer..."
                                @change="form.customer_id = $event.id"
                            />
                        </div>
                        <div>
                            <CustomInput name="Date Sold" type="date" v-model="form.date_sold" :message="form.errors.date_sold" />
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
                                                <th class="w-3/5 px-2 py-1 border whitespace-nowrap">Item Code</th>
                                                <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Quantity</th>
                                                <th class="w-1/5 px-2 py-1 border whitespace-nowrap">Price</th>
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
                                                <td class="px-2 py-1 border whitespace-nowrap"><CustomInput v-model="item.item_price" min="1" /></td>
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
                <div class="flex items-center justify-end gap-5 mb-4">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Add Sales"
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
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">CUSTOMER</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">DATE SOLD</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">TOTAL PRICE</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">CREATED BY</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">DATE CREATED</th>
                                <th class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50" v-for="form in forms.data" :key="form.id">
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.id }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.customer?.name }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(form.date_sold) }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.total_price }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ form.creator?.name }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(form.created_at) }}</td>
                                <td class="px-2 py-1 border whitespace-nowrap">
                                    <div class="inline-flex justify-center w-full h-full gap-2 ">
                                        <button @click="printTest()" class="p-3 text-white bg-green-700 rounded-full hover:bg-green-900"><PhPrinter :size="16" /></button>
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
