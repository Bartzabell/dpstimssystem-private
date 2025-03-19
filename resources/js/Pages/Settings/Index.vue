 <script setup>
    import { ref, watch } from 'vue';
    import { router } from '@inertiajs/vue3';
    import debounce from 'lodash/debounce';
    import AppLayout from '@/Layouts/AppLayout.vue';
import { PhListMagnifyingGlass, PhPencil, PhTrash, PhFloppyDisk, PhFilePlus, PhWarning, PhStack, PhPaintBucket, PhCube, PhRuler, PhPercent } from '@phosphor-icons/vue';

    // Props
    const props = defineProps({
        categories: Object,
        colors: Object,
        materials: Object,
        uoms: Object,
        discounts: Object,
        filters: Object
    });

    const topToast = ref(null);

    // Tabs
    const tabs = [
        { label: 'Categories', value: 'category', icon: PhStack },
        { label: 'Colors', value: 'color', icon: PhPaintBucket },
        { label: 'Materials', value: 'material', icon: PhCube },
        { label: 'UOMs', value: 'uom', icon: PhRuler },
        { label: 'Discounts', value: 'discount', icon: PhPercent }
    ];

    const activeTab = ref('category');

    // Search
    const search = ref(props.filters?.search || '');

    const debouncedSearch = debounce(() => {
        router.get(route('settings.index'), { search: search.value }, {
            preserveState: true,
            replace: true
        });
    }, 300);

    // Forms data
    const forms = ref({
        category: { name: '' },
        color: { name: '', hex: '' },
        material: { name: '' },
        uom: { name: '' },
        discount: { name: '', type: 'percentage', amount: '' }
    });

    // Editing state
    const editing = ref({
        category: null,
        color: null,
        material: null,
        uom: null,
        discount: null
    });

    // Category methods
    function editCategory(category) {
        editing.value.category = category;
        forms.value.category = { name: category.name };
    }

    function resetCategoryForm() {
        editing.value.category = null;
        forms.value.category = { name: '' };
    }

    function submitCategoryForm() {
        if (editing.value.category) {
            router.put(route('category.update', editing.value.category.id), forms.value.category);
            topToast.value.showToast('Category updated successfully', 'success');
        } else {
            router.post(route('category.store'), forms.value.category);
            topToast.value.showToast('Category added successfully', 'success');
        }
        resetCategoryForm();
    }

    const categoryToDelete = ref(null);
    const showDeleteCategory = ref(false);

    const confirmCategoryDelete = (id) => {
        categoryToDelete.value = id;
        showDeleteCategory.value = true;
    };

    const deleteCategory = () => {
        router.delete(route('category.destroy', categoryToDelete.value), {
            onSuccess: () => {
                topToast.value.showToast('Category deleted successfully', 'delete');
                showDeleteCategory.value = false;
            },
            onError: () => {
                topToast.value.showToast('Failed to delete category', 'error');
                showDeleteCategory.value = false;
            }
        });
    };

    const cancelCategoryDelete = () => {
        showDeleteCategory.value = false;
        topToast.value.showToast('Delete operation cancelled', 'info');
    };

    // Color methods
    function editColor(color) {
        editing.value.color = color;
        forms.value.color = { name: color.name, hex: color.hex };
    }

    function resetColorForm() {
        editing.value.color = null;
        forms.value.color = { name: '', hex: '' };
    }

    function submitColorForm() {
        if (editing.value.color) {
            router.put(route('color.update', editing.value.color.id), forms.value.color);
            topToast.value.showToast('Color updated successfully', 'success');
        } else {
            router.post(route('color.store'), forms.value.color);
            topToast.value.showToast('Color added successfully', 'success');
        }
        resetColorForm();
    }

    const colorToDelete = ref(null);
    const showDeleteColor = ref(false);

    const confirmColorDelete = (id) => {
        colorToDelete.value = id;
        showDeleteColor.value = true;
    };

    const deleteColor = () => {
        router.delete(route('color.destroy', colorToDelete.value), {
            onSuccess: () => {
                topToast.value.showToast('Color deleted successfully', 'delete');
                showDeleteColor.value = false;
            },
            onError: () => {
                topToast.value.showToast('Failed to delete color', 'error');
                showDeleteColor.value = false;
            }
        });
    };

    const cancelColorDelete = () => {
        showDeleteColor.value = false;
        topToast.value.showToast('Delete operation cancelled', 'info');
    };

    // Material methods
    function editMaterial(material) {
        editing.value.material = material;
        forms.value.material = { name: material.name };
    }

    function resetMaterialForm() {
        editing.value.material = null;
        forms.value.material = { name: '' };
    }

    function submitMaterialForm() {
        if (editing.value.material) {
            router.put(route('material.update', editing.value.material.id), forms.value.material);
            topToast.value.showToast('Material updated successfully', 'success');
        } else {
            router.post(route('material.store'), forms.value.material);
            topToast.value.showToast('Material added successfully', 'success');
        }
        resetMaterialForm();
    }

    const materialToDelete = ref(null);
    const showDeleteMaterial = ref(false);

    const confirmMaterialDelete = (id) => {
        materialToDelete.value = id;
        showDeleteMaterial.value = true;
    };

    const deleteMaterial = () => {
        router.delete(route('material.destroy', materialToDelete.value), {
            onSuccess: () => {
                topToast.value.showToast('Material deleted successfully', 'delete');
                showDeleteMaterial.value = false;
            },
            onError: () => {
                topToast.value.showToast('Failed to delete material', 'error');
                showDeleteMaterial.value = false;
            }
        });
    };

    const cancelMaterialDelete = () => {
        showDeleteMaterial.value = false;
        topToast.value.showToast('Delete operation cancelled', 'info');
    };

    // UOM methods
    function editUom(uom) {
        editing.value.uom = uom;
        forms.value.uom = { name: uom.name };
    }

    function resetUomForm() {
        editing.value.uom = null;
        forms.value.uom = { name: '' };
    }

    function submitUomForm() {
        if (editing.value.uom) {
            router.put(route('uom.update', editing.value.uom.id), forms.value.uom);
            topToast.value.showToast('UOM updated successfully', 'success');
        } else {
            router.post(route('uom.store'), forms.value.uom);
            topToast.value.showToast('UOM added successfully', 'success');
        }
        resetUomForm();
    }

    const uomToDelete = ref(null);
    const showDeleteUom = ref(false);

    const confirmUomDelete = (id) => {
        uomToDelete.value = id;
        showDeleteUom.value = true;
    };

    const deleteUom = () => {
        router.delete(route('uom.destroy', uomToDelete.value), {
            onSuccess: () => {
                topToast.value.showToast('UOM deleted successfully', 'delete');
                showDeleteUom.value = false;
            },
            onError: () => {
                topToast.value.showToast('Failed to delete uom', 'error');
                showDeleteUom.value = false;
            }
        });
    };

    const cancelUomDelete = () => {
        showDeleteUom.value = false;
        topToast.value.showToast('Delete operation cancelled', 'info');
    };

    // Discount methods
    function editDiscount(discount) {
        editing.value.discount = discount;
        forms.value.discount = {
            name: discount.name,
            type: discount.type,
            amount: discount.amount
        };
    }

    function resetDiscountForm() {
        editing.value.discount = null;
        forms.value.discount = { name: '', type: 'percentage', amount: '' };
    }

    function submitDiscountForm() {
        if (editing.value.discount) {
            router.put(route('discount.update', editing.value.discount.id), forms.value.discount);
            topToast.value.showToast('Discount updated successfully', 'success');
        } else {
            router.post(route('discount.store'), forms.value.discount);
            topToast.value.showToast('Discount added successfully', 'success');
        }
        resetDiscountForm();
    }

    const discountToDelete = ref(null);
    const showDeleteDiscount = ref(false);

    const confirmDiscountDelete = (id) => {
        discountToDelete.value = id;
        showDeleteDiscount.value = true;
    };

    const deleteDiscount = () => {
        router.delete(route('discount.destroy', discountToDelete.value), {
            onSuccess: () => {
                topToast.value.showToast('Discount deleted successfully', 'delete');
                showDeleteDiscount.value = false;
            },
            onError: () => {
                topToast.value.showToast('Failed to delete discount', 'error');
                showDeleteDiscount.value = false;
            }
        });
    };

    const cancelDiscountDelete = () => {
        showDeleteDiscount.value = false;
        topToast.value.showToast('Delete operation cancelled', 'info');
    };
</script>

<template>
    <AppLayout title="Settings">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Settings</h2>
        </template>
        <div class="flex items-center justify-center w-full px-4 md:mt-10">
            <div class="w-full md:w-[90vw] p-6 bg-white rounded-lg shadow">
                <!-- Search Bar -->
                <div class="md:flex md:items-center md:w-full md:justify-end">
                    <div class="relative">
                        <PhListMagnifyingGlass class="absolute text-gray-400 transform -translate-y-1/2 left-2 top-1/2" :size="20" />
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search..."
                            class="w-full py-2 pl-8 pr-2 text-sm border md:w-72 rounded-2xl"
                            @input="debouncedSearch"
                        />
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mb-4 border-b">
                    <div class="flex flex-wrap -mb-px">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="activeTab = tab.value"
                            class="flex items-center px-4 py-2 font-medium"
                            :class="[
                                activeTab === tab.value
                                ? 'border-b-2 border-blue-500 text-blue-600'
                                : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            <component :is="tab.icon" size="20" v-if="tab.icon" class="mr-1" />
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <!-- Tab Contents -->

                <!-- Category Tab -->
                <div v-if="activeTab === 'category'" class="grid gap-6 md:grid-cols-2">
                <!-- Form -->
                <div>
                    <form @submit.prevent="submitCategoryForm">
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold">{{ editing.category ? 'Edit' : 'Add' }} Category</h2>
                            <div>
                                <label for="categoryName" class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    id="categoryName"
                                    v-model="forms.category.name"
                                    placeholder="Enter category name"
                                    class="block w-full p-2 mt-1 border border-gray-300 rounded-md shadow-sm"
                                    required
                                />
                            </div>
                            <div class="flex gap-2">
                                <ButtonCode type="submit" :icon="editing.category ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing.category ? 'Update' : 'Add'" />
                                <ButtonCode v-if="editing.category" @click="resetCategoryForm" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel" />
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div v-if="categories && categories.data && categories.data.length > 0">
                    <div class="border rounded-md">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="category in categories.data" :key="category.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ category.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ category.name }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <button @click="editCategory(category)" class="p-3 text-white bg-blue-700 rounded-full hover:bg-blue-900">
                                                <PhPencil :size="16" />
                                            </button>
                                            <button @click="confirmCategoryDelete(category.id)" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                                                <PhTrash :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <PaginationButton :data="categories" />
                </div>
                </div>

                <!-- Color Tab -->
                <div v-if="activeTab === 'color'" class="grid gap-6 md:grid-cols-2">
                <!-- Form -->
                <div>
                    <form @submit.prevent="submitColorForm">
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold">{{ editing.color ? 'Edit' : 'Add' }} Color</h2>
                            <div>
                                <label for="colorName" class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    id="colorName"
                                    v-model="forms.color.name"
                                    placeholder="Enter color name"
                                    class="block w-full p-2 mt-1 border border-gray-300 rounded-md shadow-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label for="colorHex" class="block text-sm font-medium text-gray-700">Hex Code</label>
                                <input
                                    id="colorHex"
                                    v-model="forms.color.hex"
                                    placeholder="Enter color hex"
                                    class="block w-full p-2 mt-1 border border-gray-300 rounded-md shadow-sm"
                                    required
                                />
                            </div>
                            <div class="flex gap-2">
                                <ButtonCode type="submit" :icon="editing.color ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing.color ? 'Update' : 'Add'" />
                                <ButtonCode v-if="editing.color" @click="resetColorForm" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel" />
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div v-if="colors && colors.data && colors.data.length > 0">
                    <div class="border rounded-md">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Hex</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="color in colors.data" :key="color.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ color.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ color.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ color.hex }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button @click="editColor(color)" class="p-3 text-white bg-blue-700 rounded-full hover:bg-blue-900">
                                            <PhPencil :size="16" />
                                        </button>
                                        <button @click="confirmColorDelete(color.id)" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                                            <PhTrash :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <PaginationButton :data="colors" />
                </div>
                </div>

                <!-- Material Tab -->
                <div v-if="activeTab === 'material'" class="grid gap-6 md:grid-cols-2">
                <!-- Form -->
                <div>
                    <form @submit.prevent="submitMaterialForm">
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold">{{ editing.material ? 'Edit' : 'Add' }} Material</h2>
                        <div>
                            <label for="materialName" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                id="materialName"
                                v-model="forms.material.name"
                                placeholder="Enter material name"
                                class="block w-full p-2 mt-1 border border-gray-300 rounded-md shadow-sm"
                                required
                            />
                        </div>
                        <div class="flex gap-2">
                            <ButtonCode type="submit" :icon="editing.material ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing.material ? 'Update' : 'Add'" />
                            <ButtonCode v-if="editing.material" @click="resetMaterialForm" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel" />
                        </div>
                    </div>
                    </form>
                </div>

                <!-- Table -->
                <div v-if="materials && materials.data && materials.data.length > 0">
                    <div class="border rounded-md">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="material in materials.data" :key="material.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ material.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ material.name }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button @click="editMaterial(material)" class="p-3 text-white bg-blue-700 rounded-full hover:bg-blue-900">
                                            <PhPencil :size="16" />
                                        </button>
                                        <button @click="confirmMaterialDelete(material.id)" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                                            <PhTrash :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <PaginationButton :data="materials" />
                </div>
                </div>

                <!-- UOM Tab -->
                <div v-if="activeTab === 'uom'" class="grid gap-6 md:grid-cols-2">
                <!-- Form -->
                <div>
                    <form @submit.prevent="submitUomForm">
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold">{{ editing.uom ? 'Edit' : 'Add' }} UOM</h2>
                        <div>
                            <label for="uomName" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                id="uomName"
                                v-model="forms.uom.name"
                                placeholder="Enter UOM name"
                                class="block w-full p-2 mt-1 border border-gray-300 rounded-md shadow-sm"
                                required
                            />
                        </div>
                        <div class="flex gap-2">
                            <ButtonCode type="submit" :icon="editing.uom ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing.uom ? 'Update' : 'Add'" />
                            <ButtonCode v-if="editing.uom" @click="resetUomForm" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel" />
                        </div>
                    </div>
                    </form>
                </div>

                <!-- Table -->
                <div v-if="uoms && uoms.data && uoms.data.length > 0">
                    <div class="border rounded-md">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="uom in uoms.data" :key="uom.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ uom.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ uom.name }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <button @click="editUom(uom)" class="p-3 text-white bg-blue-700 rounded-full hover:bg-blue-900">
                                                <PhPencil :size="16" />
                                            </button>
                                            <button @click="confirmUomDelete(uom.id)" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                                                <PhTrash :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <PaginationButton :data="uoms" />
                </div>
                </div>

                <!-- Discount Tab -->
                <div v-if="activeTab === 'discount'" class="grid gap-6 md:grid-cols-2">
                <!-- Form -->
                <div>
                    <form @submit.prevent="submitDiscountForm">
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold">{{ editing.discount ? 'Edit' : 'Add' }} Discount</h2>
                            <div>
                                <label for="discountName" class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    id="discountName"
                                    v-model="forms.discount.name"
                                    placeholder="Enter discount name"
                                    class="block w-full p-2 mt-1 border border-gray-300 rounded-md shadow-sm"
                                    required
                                />
                            </div>
                            <div class="mt-4">
                                <label for="discountType" class="block text-sm font-medium text-gray-700">Type</label>
                                <select
                                    id="discountType"
                                    v-model="forms.discount.type"
                                    class="block w-full p-2 mt-1 border border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <label for="discountAmount" class="block text-sm font-medium text-gray-700">Amount</label>
                                <input
                                    id="discountAmount"
                                    v-model="forms.discount.amount"
                                    type="number"
                                    step="0.01"
                                    placeholder="Enter amount"
                                    class="block w-full p-2 mt-1 border border-gray-300 rounded-md shadow-sm"
                                    required
                                />
                            </div>
                            <div class="flex gap-2">
                                <ButtonCode type="submit" :icon="editing.discount ? PhFloppyDisk : PhFilePlus" color="bg-emerald-700 hover:bg-emerald-900" :text="editing.discount ? 'Update' : 'Add'" />
                                <ButtonCode v-if="editing.discount" @click="resetDiscountForm" type="button" color="bg-gray-500 hover:bg-gray-700" text="Cancel" />
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div v-if="discounts && discounts.data && discounts.data.length > 0">
                    <div class="border rounded-md">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="discount in discounts.data" :key="discount.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ discount.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ discount.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ discount.type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ discount.amount }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button @click="editDiscount(discount)" class="p-3 text-white bg-blue-700 rounded-full hover:bg-blue-900">
                                            <PhPencil :size="16" />
                                        </button>
                                        <button @click="confirmDiscountDelete(discount.id)" class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                                            <PhTrash :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <PaginationButton :data="discounts" />
                </div>
                </div>
            </div>
        </div>
        <SimpleDialog
            v-model="showDeleteCategory"
            theme="red"
            :icon="PhWarning"
            title="Deleting Category"
            description="Are you sure you want to delete this category? This action cannot be undone."
            confirmText="Yes, Delete"
            @confirm="deleteCategory"
            @cancel="cancelCategoryDelete"
        />
        <SimpleDialog
            v-model="showDeleteColor"
            theme="red"
            :icon="PhWarning"
            title="Deleting Color"
            description="Are you sure you want to delete this color? This action cannot be undone."
            confirmText="Yes, Delete"
            @confirm="deleteColor"
            @cancel="cancelColorDelete"
        />
        <SimpleDialog
            v-model="showDeleteMaterial"
            theme="red"
            :icon="PhWarning"
            title="Deleting Material"
            description="Are you sure you want to delete this material? This action cannot be undone."
            confirmText="Yes, Delete"
            @confirm="deleteMaterial"
            @cancel="cancelMaterialDelete"
        />
        <SimpleDialog
            v-model="showDeleteUom"
            theme="red"
            :icon="PhWarning"
            title="Deleting UOM"
            description="Are you sure you want to delete this uom? This action cannot be undone."
            confirmText="Yes, Delete"
            @confirm="deleteUom"
            @cancel="cancelUomDelete"
        />
        <SimpleDialog
            v-model="showDeleteDiscount"
            theme="red"
            :icon="PhWarning"
            title="Deleting Discount"
            description="Are you sure you want to delete this discount? This action cannot be undone."
            confirmText="Yes, Delete"
            @confirm="deleteDiscount"
            @cancel="cancelDiscountDelete"
        />
        <TopToast ref="topToast" />
    </AppLayout>
</template>
