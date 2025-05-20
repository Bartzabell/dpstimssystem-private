<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    modelValue: [String, Number],
    items: {
        type: Array,
        required: true
    },
    placeholder: {
        type: String,
        default: 'Search items...'
    },
    hasColorSwatches: {
        type: Boolean,
        default: false
    },
    valueField: {
        type: String,
        default: 'id'
    },
    labelField: {
        type: String,
        default: 'name'
    },
    descriptionField: {
        type: String,
        default: 'item_code'
    }
});

const emit = defineEmits(['update:modelValue', 'change', 'selectedName']);

const searchQuery = ref('');
const isOpen = ref(false);
const filteredItems = ref([]);
const selectedLabel = ref('');
const selectedDescription = ref('');
const inputRef = ref(null);

// Find the currently selected item
const selectedItem = computed(() => {
    if (props.modelValue) {
        return props.items.find(item => item[props.valueField] == props.modelValue);
    }
    return null;
});

const updateFilteredItems = () => {
    filteredItems.value = props.items
        .filter(item => {
            const query = searchQuery.value.toLowerCase();
            const labelMatch = item[props.labelField] ?
                item[props.labelField].toString().toLowerCase().includes(query) : false;
            const valueMatch = item[props.valueField].toString().toLowerCase().includes(query);
            const descMatch = item[props.descriptionField] ?
                item[props.descriptionField].toString().toLowerCase().includes(query) : false;
            return valueMatch || labelMatch || descMatch;
        })
        .slice(0, 100);
};

// Calculate display label for an item
const getItemDisplayLabel = (item) => {
    return item[props.labelField] || item[props.valueField].toString();
};

// Calculate display description for an item
const getItemDisplayDescription = (item) => {
    return item[props.descriptionField] || '';
};

// Detect if the items include hex color values
const hasHexColors = computed(() => {
    return props.hasColorSwatches || (props.items.length > 0 && 'hex' in props.items[0]);
});

// Call updateFilteredItems immediately on mount
onMounted(() => {
    updateFilteredItems();
});

const debouncedSearch = debounce(updateFilteredItems, 300);

watch(searchQuery, () => {
    debouncedSearch();
});

// Watch for changes in items to update filtered list
watch(() => props.items, () => {
    updateFilteredItems();
}, { immediate: true });

// Watch for changes in modelValue to update selected label
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        const selected = props.items.find(item => item[props.valueField] == newValue);
        if (selected) {
            selectedLabel.value = getItemDisplayLabel(selected);
            selectedDescription.value = getItemDisplayDescription(selected);
        } else {
            selectedLabel.value = newValue.toString();
            selectedDescription.value = '';
        }
    } else {
        selectedLabel.value = '';
        selectedDescription.value = '';
    }
}, { immediate: true });

const selectItem = (item) => {
    emit('update:modelValue', item[props.valueField]);
    emit('change', item);
    emit('selectedName', item[props.labelField]);
    searchQuery.value = '';
    isOpen.value = false;
};

const handleClick = () => {
    isOpen.value = true;

    if (selectedItem.value) {
        searchQuery.value = getItemDisplayLabel(selectedItem.value);
    } else if (props.modelValue) {
        searchQuery.value = props.modelValue.toString();
    }

    if (inputRef.value) {
        setTimeout(() => {
            inputRef.value.focus();
        }, 0);
    }
};

const dropdownRef = ref(null);
onMounted(() => {
    document.addEventListener('click', (e) => {
        if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
            isOpen.value = false;
        }
    });
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <div class="relative w-full border border-gray-500 rounded-lg cursor-pointer select-none dark:bg-gray-500"
            @mousedown.prevent="handleClick">
            <div class="w-full 2xl:px-1 px-1 py-1 2xl:py-1.5">
                <template v-if="isOpen">
                    <input ref="inputRef" v-model="searchQuery" type="text"
                        class="w-full !text-[10px] 2xl:!text-sm py-0 dark:bg-gray-400 dark:text-gray-200 outline-none cursor-text"
                        :placeholder="placeholder">
                </template>
                <template v-else>
                    <div class="flex flex-col dark:bg-gray-500">
                        <div class="flex items-center">
                            <span v-if="selectedItem && hasHexColors && selectedItem.hex"
                                class="inline-block w-4 h-4 mr-2 rounded-full"
                                :style="{ backgroundColor: selectedItem.hex }">
                            </span>
                            <span class="block w-full truncate dark:text-gray-300">{{ selectedLabel || placeholder }}</span>
                        </div>
                        <!-- <div v-if="selectedDescription" class="text-xs text-gray-500 truncate dark:text-gray-400">
                            {{ selectedDescription }}
                        </div> -->
                    </div>
                </template>
            </div>
        </div>

        <div v-if="isOpen"
            class="absolute z-50 w-full mt-2 overflow-auto bg-white border rounded-lg shadow-lg dark:bg-gray-300 max-h-60">
            <div v-if="filteredItems.length === 0" class="p-1 text-gray-500">No item found</div>
            <div v-for="item in filteredItems" :key="item[valueField]" @mousedown.prevent="selectItem(item)"
                class="flex flex-col px-4 py-1 cursor-pointer dark:hover:gray-500 hover:bg-gray-100"
                :class="{ 'bg-blue-100': item[valueField] == modelValue }">
                <div class="flex items-center">
                    <span v-if="hasHexColors && item.hex" class="inline-block w-4 h-4 mr-2 border border-black rounded-full"
                        :style="{ backgroundColor: item.hex }">
                    </span>
                    <span>{{ getItemDisplayLabel(item) }}</span>
                </div>
                <div v-if="getItemDisplayDescription(item)" class="text-xs text-gray-500">
                    {{ getItemDisplayDescription(item) }}
                </div>
            </div>
            <div v-if="filteredItems.length === 100" class="p-1 text-sm text-gray-500 bg-gray-50">
                Showing first 100 results. Please refine your search if needed.
            </div>
        </div>
    </div>
</template>
