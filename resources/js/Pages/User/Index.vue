<script setup>
    import { ref, watch } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { PhWarning, PhFilePlus, PhFloppyDisk, PhTrash, PhPencil, PhListMagnifyingGlass, PhDownloadSimple, PhX } from "@phosphor-icons/vue";

    const props = defineProps({
        users: Object,
        roles: Array,
        filters: Object
    });

    const topToast = ref(null);
    const itemToDelete = ref(null);
    const selectedRole = ref(null);
    const showDeleteConfirmation = ref(false);
    const showConfirmDialog = ref(false);
    const dialogAction = ref('');

    const search = ref(props.filters.search || '');

    watch(search, (value) => {
        router.get(route('user.index'), { search: value }, { preserveState: true, replace: true });
    }, { deep: true });

    const isFormVisible = ref(false);

    const form = useForm({
        id: null,
        name: '',
        username: '',
        email: '',
        role_id: '',
        password: '',
    });

    const formatDate = (date) => {
        if (!date) return "";
        return new Date(date).toLocaleDateString("en-GB", {
            day: "numeric",
            month: "long",
            year: "numeric"
        });
    };

    // FOR DELETE
    const confirmDelete = (id) => {
        itemToDelete.value = id;
        showDeleteConfirmation.value = true;
    };

    const deleteItem = () => {
        form.delete(route('user.destroy', itemToDelete.value), {
            onSuccess: () => {
                if (isFormVisible.value) {
                    isFormVisible.value = false;
                }
                topToast.value.showToast('User deleted successfully', 'success');
                showDeleteConfirmation.value = false;
            },
            onError: () => {
                topToast.value.showToast('Failed to delete user', 'error');
                showDeleteConfirmation.value = false;
            }
        });
    };

    const cancelDelete = () => {
        showDeleteConfirmation.value = false;
        topToast.value.showToast('Delete operation cancelled', 'info');
    };

    const edit = (user) => {
        if (!isFormVisible.value) {
            isFormVisible.value = true;
        }
        form.id = user.id;
        form.name = user.name;
        form.username = user.username;
        form.email = user.email;
        form.role_id = user.role_id;
        form.password = user.password;

        selectedRole.value = user.role_id;

        editing.value = true;
    };

    const resetForm = () => {
        form.id = null;
        form.name = '';
        form.email = '';
        form.username = '';
        form.role_id = '';
        form.password = '';
        selectedRole.value = null;
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
            form.put(route('user.update', form.id), {
                onSuccess: () => {
                    if (isFormVisible.value) {
                        isFormVisible.value = false;
                    }
                    resetForm();
                    editing.value = false;
                    showConfirmDialog.value = false; // Close the modal
                    topToast.value.showToast('User updated successfully', 'success');
                },
                onError: () => {
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Failed to update user', 'error');
                }
            });
        } else {
            form.post(route('user.store'), {
                onSuccess: () => {
                    if (isFormVisible.value) {
                        isFormVisible.value = false;
                    }
                    resetForm();
                    showConfirmDialog.value = false;
                    topToast.value.showToast('User added successfully', 'success');
                },
                onError: () => {
                    showConfirmDialog.value = false;
                    topToast.value.showToast('Failed to add user', 'error');
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
    <AppLayout title="Users">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Manage Users
            </h2>
        </template>
        <Modal :show="isFormVisible" @close="isFormVisible = false" class="fixed inset-0 z-50">
            <div v-if="isFormVisible">
                <div class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black dark:border-gray-500 dark:bg-gray-800">
                    <div>
                        <h1 class="text-2xl font-extrabold dark:text-gray-200">User Form</h1>
                    </div>
                    <button @click="toggleFormVisibility" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>
                <form @submit.prevent="submit" class="pb-4 m-3 bg-white rounded shadow dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-5 p-5 mt-10 md:grid-cols-2">
                        <CustomInput name="Name:" v-model="form.name" />
                        <CustomInput name="Username:" v-model="form.username"/>
                        <CustomInput name="Email:" v-model="form.email" type="email" />
                        <div>
                            <label class="text-sm font-medium dark:text-gray-200">Role:</label>
                            <SearchableDropdown
                                class="border rounded-lg border-slate-600"
                                v-model="selectedRole"
                                :items="roles"
                                placeholder="Search Role..."
                                @change="form.role_id = $event.id"
                            />
                        </div>
                        <CustomInput name="Password:" v-model="form.password" type="password" />
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
            <div class="p-6 mt-2 bg-white rounded shadow dark:bg-gray-700">
                <div class="flex flex-col items-end justify-end gap-2 mb-4 md:items-center md:flex-row">
                    <ButtonCode
                        @click="toggleFormVisibility"
                        text="Add User"
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
                <div v-if="users && users.data && users.data.length > 0">
                    <div class="overflow-x-auto border rounded-lg dark:border-gray-600">
                        <table class="min-w-full divide-y divide-gray-200 dark:border-gray-600 dark:divide-gray-600">
                            <thead>
                                <tr class="text-xs text-center text-white bg-gray-100 md:text-base">
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Name</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Username</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Role</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Email</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Date Created</td>
                                    <td class="px-2 py-1 border bg-emerald-800 whitespace-nowrap">Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-xs text-gray-600 dark:text-gray-50 md:text-base dark:hover:bg-gray-800 hover:bg-blue-100 dark:bg-gray-500 dark:even:bg-gray-600 even:bg-gray-50" v-for="user in users.data" :key="user.id">
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ user.name }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ user.username }}</td>
                                    <td class="px-2 py-1 capitalize border whitespace-nowrap">{{ user.role?.name }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ user.email }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">{{ formatDate(user.created_at) }}</td>
                                    <td class="px-2 py-1 border whitespace-nowrap">
                                        <div class="inline-flex justify-center w-full h-full gap-2 ">
                                            <button @click="edit(user)" class="p-2 text-white bg-blue-500 rounded-full"><PhPencil :size="16" /></button>
                                            <button @click="confirmDelete(user.id)" class="p-2 text-white bg-red-500 rounded-full"><PhTrash :size="16" /></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <PaginationButton :data="users" />
                </div>
                <div v-else class="p-4 text-center dark:text-gray-100">
                    <p>No users found. Create your user data above.</p>
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
