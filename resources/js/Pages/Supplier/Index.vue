<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { PhWarning, PhFilePlus, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass, PhDownloadSimple, PhX } from "@phosphor-icons/vue";

const props = defineProps({
    suppliers: Object,
    filters: Object
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('supplier.index'), { search: value }, { preserveState: true, replace: true });
}, { deep: true });

const isFormVisible = ref(false);
const topToast = ref(null);
const itemToDelete = ref(null);
const showDeleteConfirmation = ref(false);
const showConfirmDialog = ref(false);
const dialogAction = ref('');

const form = useForm({
    id: null,
    name: '',
    phone_no: '',
    email: '',
    tin_no: '',
    street: '',
    municipality: '',
    city: '',
});

const edit = (supplier) => {
    if (!isFormVisible.value) {
        isFormVisible.value = true;
    }
    form.id = supplier.id;
    form.name = supplier.name;
    form.email = supplier.email;
    form.street = supplier.street;
    form.municipality = supplier.municipality;
    form.city = supplier.city;
    form.tin_no = supplier.tin_no;
    form.phone_no = supplier.phone_no;
    editing.value = true;
};

const confirmDelete = (id) => {
    itemToDelete.value = id;
    showDeleteConfirmation.value = true;
};

const deleteItem = () => {
    form.delete(route('supplier.destroy', itemToDelete.value), {
        onSuccess: () => {
            if (isFormVisible.value) {
                isFormVisible.value = false;
            }
            topToast.value.showToast('Supplier deleted successfully', 'success');
            showDeleteConfirmation.value = false;
        },
        onError: () => {
            topToast.value.showToast('Failed to delete supplier', 'error');
            showDeleteConfirmation.value = false;
        }
    });
};

const cancelDelete = () => {
    showDeleteConfirmation.value = false;
    topToast.value.showToast('Delete operation cancelled', 'info');
};

const resetForm = () => {
    form.id = null;
    form.name = '';
    form.email = '';
    form.street = '';
    form.municipality = '';
    form.city = '';
    form.tin_no = '';
    form.phone_no = '';
};

function toggleFormVisibility() {
    if (isFormVisible.value) {
        // Form is currently visible, so we're closing it
        resetForm();
        editing.value = false;
    }
    isFormVisible.value = !isFormVisible.value;
}

const editing = ref(false);

const submit = () => {
    dialogAction.value = editing.value ? 'update' : 'add';
    showConfirmDialog.value = true;
};

const confirmSubmit = () => {
    if (editing.value) {
        form.put(route('supplier.update', form.id), {
            onSuccess: () => {
                if (isFormVisible.value) {
                    isFormVisible.value = false;
                }
                resetForm();
                editing.value = false;
                showConfirmDialog.value = false;
                topToast.value.showToast('Supplier updated successfully', 'success');
            },
            onError: () => {
                showConfirmDialog.value = false;
                topToast.value.showToast('Failed to update supplier', 'error');
            }
        });
    } else {
        form.post(route('supplier.store'), {
            onSuccess: () => {
                if (isFormVisible.value) {
                    isFormVisible.value = false;
                }
                resetForm();
                showConfirmDialog.value = false;
                topToast.value.showToast('Supplier added successfully', 'success');
            },
            onError: () => {
                showConfirmDialog.value = false;
                topToast.value.showToast('Failed to add supplier', 'error');
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

const cancelConfirmDialog = () => {
    showConfirmDialog.value = false;
};
</script>

<template>
    <AppLayout title="Supplier">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Supplier
            </h2>
        </template>
        <Modal :show="isFormVisible" @close="isFormVisible = false" class="fixed inset-0 z-50">
            <div v-if="isFormVisible">
                <div class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black">
                    <div>
                        <h1 class="text-2xl font-extrabold">Supplier Form</h1>
                    </div>
                    <button @click="toggleFormVisibility" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>
                <form @submit.prevent="submit" class="pb-4 m-3 bg-white rounded shadow">
                    <div class="grid grid-cols-1 gap-5 p-5 mt-10 md:grid-cols-2">
                        <CustomInput name="Supplier Name:" v-model="form.name" />
                        <CustomInput name="Phone Number:" v-model="form.phone_no"/>
                        <CustomInput name="Email:" v-model="form.email" type="email" />
                        <CustomInput name="TIN:" v-model="form.tin_no" />
                        <CustomInput name="Street:" v-model="form.street" />
                        <CustomInput name="Municipality:" v-model="form.municipality" />
                        <CustomInput name="City/Province:" v-model="form.city" />
                        <div class="flex items-center justify-end w-full">
                            <div class="flex items-center justify-center gap-2 mt-2">
                                <ButtonCode type="submit" :icon="editing ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing ? 'Update' : 'Add'" />
                                <ButtonCode v-if="editing" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel" @click="cancelForm" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </Modal>
        <div class="p-5">
            <div class="p-6 mt-2 bg-white rounded shadow">
                <div class="flex flex-col items-end justify-end gap-2 mb-4 md:items-center md:flex-row">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Add Supplier"
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
                <div v-if="suppliers && suppliers.data && suppliers.data.length > 0">
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-xs text-center text-white bg-gray-100 md:text-base">
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Supplier Name</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Phone Number</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Email</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">TIN</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Address</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50" v-for="supplier in suppliers.data" :key="supplier.id">
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ supplier.name }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ supplier.phone_no}}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ supplier.email }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ supplier.tin_no }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ supplier.street }} {{ supplier.municipality }} {{ supplier.city }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                        <div class="inline-flex justify-center w-full h-full gap-2 ">
                                            <button @click="edit(supplier)" class="p-2 text-white bg-blue-500 rounded-full"><PhPencil :size="16" /></button>
                                            <button @click="confirmDelete(supplier.id)" class="p-2 text-white bg-red-500 rounded-full"><PhTrash :size="16" /></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <PaginationButton :data="suppliers" />
                </div>
                <div v-else class="p-4 text-center">
                    <p>No supplier found. Create your first supplier above.</p>
                </div>
            </div>
        </div>
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
        <TopToast ref="topToast" />
    </AppLayout>
</template>
