<script setup>
    import { ref, watch } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { PhEyeSlash, PhFilePlus, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass } from "@phosphor-icons/vue";

    const props = defineProps({
        customers: Object,
        filters: Object
    });

    const search = ref(props.filters.search || '');

    watch(search, (value) => {
        router.get(route('customer.index'), { search: value }, { preserveState: true, replace: true });
    }, { deep: true });

    const isFormVisible = ref(false);

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

    const edit = (customer) => {
        if (!isFormVisible.value) {
            isFormVisible.value = true;
        }
        form.id = customer.id;
        form.name = customer.name;
        form.email = customer.email;
        form.street = customer.street;
        form.municipality = customer.municipality;
        form.city = customer.city;
        form.tin_no = customer.tin_no;
        form.phone_no = customer.phone_no;
        editing.value = true;
    };

    const deleteItem = (id) => {
        if (confirm('Are you sure?')) {
            form.delete(route('customer.destroy', id));
        }
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
    isFormVisible.value = !isFormVisible.value;
    };

    const editing = ref(false);

    const submit = () => {
        if (editing.value) {
            form.put(route('customer.update', form.id), {
                onSuccess: () => {
                    resetForm();
                    editing.value = false;
                    isFormVisible.value = false; // Close the modal
                }
            });
        } else {
            form.post(route('customer.store'), {
                onSuccess: () => {
                    resetForm();
                    isFormVisible.value = false; // Close the modal
                }
            });
        }
    };
</script>

<template>
    <AppLayout title="Customer">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Customer
            </h2>
        </template>
        <Modal :show="isFormVisible" @close="isFormVisible = false" class="fixed inset-0 z-50">
            <div v-if="isFormVisible">
                <div class="absolute flex justify-end w-full right-1 top-1">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Close"
                        color="bg-red-500 hover:bg-red-700"
                    />
                </div>
                <form @submit.prevent="submit" class="pb-4 m-3 bg-white rounded shadow">
                    <h1 class="px-6 py-2 text-2xl font-extrabold">Customer Form</h1>
                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                        <CustomInput name="Customer Name:" v-model="form.name" />
                        <CustomInput name="Phone Number:" v-model="form.phone_no"/>
                        <CustomInput name="Email:" v-model="form.email" type="email" />
                        <CustomInput name="TIN:" v-model="form.tin_no" />
                        <CustomInput name="Street:" v-model="form.street" />
                        <CustomInput name="Municipality:" v-model="form.municipality" />
                        <CustomInput name="City:" v-model="form.city" />
                        <div class="flex items-center justify-end w-full">
                            <div class="w-max">
                                <ButtonCode type="submit" :icon="editing ? PhFloppyDisk : PhFilePlus" color="bg-blue-500 hover:bg-blue-700" :text="editing ? 'Update Customer' : 'Add Customer'" />
                            </div>
                        </div>
                    </div>
                </form>
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
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Customer Name</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Phone Number</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Email</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">TIN</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Address</td>
                            <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Actions</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-xs text-gray-600 md:text-base hover:bg-blue-100 even:bg-gray-50" v-for="customer in customers.data" :key="customer.id">
                            <td class="px-2 py-1 border whitespace-nowrap">{{ customer.name }}</td>
                            <td class="px-2 py-1 border whitespace-nowrap">{{ customer.phone_no}}</td>
                            <td class="px-2 py-1 border whitespace-nowrap">{{ customer.email }}</td>
                            <td class="px-2 py-1 border whitespace-nowrap">{{ customer.tin_no }}</td>
                            <td class="px-2 py-1 border whitespace-nowrap">{{ customer.street }} {{ customer.municipality }} {{ customer.city }}</td>
                            <td class="px-2 py-1 border whitespace-nowrap">
                                <div class="inline-flex justify-center w-full h-full gap-2 ">
                                    <button @click="edit(customer)" class="p-2 text-white bg-blue-500 rounded-full"><PhPencil :size="16" /></button>
                                    <button @click="deleteItem(customer.id)" class="p-2 text-white bg-red-500 rounded-full"><PhTrash :size="16" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
