<script setup>
    import { ref, watch } from 'vue';
    import { router } from '@inertiajs/vue3';
    import debounce from 'lodash/debounce';
    import AppLayout from '@/Layouts/AppLayout.vue';

    // Props
    const props = defineProps({
        categories: Object,
        colors: Object,
        materials: Object,
        uoms: Object,
        discounts: Object,
        filters: Object
    });

    // Tabs
    const tabs = [
        { label: 'Categories', value: 'category' },
        { label: 'Colors', value: 'color' },
        { label: 'Materials', value: 'material' },
        { label: 'UOMs', value: 'uom' },
        { label: 'Discounts', value: 'discount' }
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
        color: { name: '' },
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
        } else {
            router.post(route('category.store'), forms.value.category);
        }
        resetCategoryForm();
    }

    function deleteCategory(category) {
        if (confirm('Are you sure you want to delete this category?')) {
            router.delete(route('category.destroy', category.id));
        }
    }

    // Color methods
    function editColor(color) {
        editing.value.color = color;
        forms.value.color = { name: color.name };
    }

    function resetColorForm() {
        editing.value.color = null;
        forms.value.color = { name: '' };
    }

    function submitColorForm() {
        if (editing.value.color) {
            router.put(route('color.update', editing.value.color.id), forms.value.color);
        } else {
            router.post(route('color.store'), forms.value.color);
        }
        resetColorForm();
    }

    function deleteColor(color) {
        if (confirm('Are you sure you want to delete this color?')) {
            router.delete(route('color.destroy', color.id));
        }
    }

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
        } else {
            router.post(route('material.store'), forms.value.material);
        }
        resetMaterialForm();
    }

    function deleteMaterial(material) {
        if (confirm('Are you sure you want to delete this material?')) {
            router.delete(route('material.destroy', material.id));
        }
    }

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
        } else {
            router.post(route('uom.store'), forms.value.uom);
        }
        resetUomForm();
    }

    function deleteUom(uom) {
        if (confirm('Are you sure you want to delete this UOM?')) {
            router.delete(route('uom.destroy', uom.id));
        }
    }

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
        } else {
            router.post(route('discount.store'), forms.value.discount);
        }
        resetDiscountForm();
    }

    function deleteDiscount(discount) {
        if (confirm('Are you sure you want to delete this discount?')) {
            router.delete(route('discount.destroy', discount.id));
        }
    }
</script>

<template>
    <AppLayout title="Transaction Sales Form">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Transaction Sales Forms</h2>
        </template>
        <div class="container px-4 mx-auto">
            <div class="p-6 bg-white rounded-lg shadow">
                <h1 class="mb-6 text-2xl font-bold">Settings</h1>

                <!-- Search Bar -->
                <div class="mb-6">
                    <input
                        type="text"
                        placeholder="Search..."
                        v-model="search"
                        class="w-full px-3 py-2 border rounded-md md:w-72"
                        @input="debouncedSearch"
                    />
                </div>

                <!-- Tabs -->
                <div class="mb-4 border-b">
                    <div class="flex flex-wrap -mb-px">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="activeTab = tab.value"
                            class="px-4 py-2 font-medium"
                            :class="[
                                activeTab === tab.value
                                ? 'border-b-2 border-blue-500 text-blue-600'
                                : 'text-gray-500 hover:text-gray-700'
                            ]"
                            >
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
                                <button
                                    type="submit"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    {{ editing.category ? 'Update' : 'Save' }}
                                </button>
                                <button
                                    v-if="editing.category"
                                    type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                    @click="resetCategoryForm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div>
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
                                            <button
                                            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                                            @click="editCategory(category)"
                                            >
                                            Edit
                                            </button>
                                            <button
                                            class="px-3 py-1 text-sm text-white bg-red-600 rounded-md hover:bg-red-700"
                                            @click="deleteCategory(category)"
                                            >
                                            Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <PaginationButton :data="categories.links" />
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
                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    {{ editing.color ? 'Update' : 'Save' }}
                                </button>
                                <button
                                    v-if="editing.color"
                                    type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                    @click="resetColorForm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div>
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
                            <tr v-for="color in colors.data" :key="color.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ color.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ color.name }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button
                                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                                        @click="editColor(color)"
                                        >
                                        Edit
                                        </button>
                                        <button
                                        class="px-3 py-1 text-sm text-white bg-red-600 rounded-md hover:bg-red-700"
                                        @click="deleteColor(color)"
                                        >
                                        Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <PaginationButton :data="colors.links" />
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
                            <button
                                type="submit"
                                class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                            >
                                {{ editing.material ? 'Update' : 'Save' }}
                            </button>
                            <button
                                v-if="editing.material"
                                type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                @click="resetMaterialForm"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                    </form>
                </div>

                <!-- Table -->
                <div>
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
                                        <button
                                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                                        @click="editMaterial(material)"
                                        >
                                        Edit
                                        </button>
                                        <button
                                        class="px-3 py-1 text-sm text-white bg-red-600 rounded-md hover:bg-red-700"
                                        @click="deleteMaterial(material)"
                                        >
                                        Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <PaginationButton :data="materials.links" />
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
                            <button
                                type="submit"
                                class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                            >
                                {{ editing.uom ? 'Update' : 'Save' }}
                            </button>
                            <button
                                v-if="editing.uom"
                                type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                @click="resetUomForm"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                    </form>
                </div>

                <!-- Table -->
                <div>
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
                                            <button
                                            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                                            @click="editUom(uom)"
                                            >
                                            Edit
                                            </button>
                                            <button
                                            class="px-3 py-1 text-sm text-white bg-red-600 rounded-md hover:bg-red-700"
                                            @click="deleteUom(uom)"
                                            >
                                            Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <PaginationButton :data="uoms.links" />
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
                                <button
                                    type="submit"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    {{ editing.discount ? 'Update' : 'Save' }}
                                </button>
                                <button
                                    v-if="editing.discount"
                                    type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                    @click="resetDiscountForm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div>
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
                                        <button
                                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                                        @click="editDiscount(discount)"
                                        >
                                        Edit
                                        </button>
                                        <button
                                        class="px-3 py-1 text-sm text-white bg-red-600 rounded-md hover:bg-red-700"
                                        @click="deleteDiscount(discount)"
                                        >
                                        Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <PaginationButton :data="discounts.links" />
                </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
