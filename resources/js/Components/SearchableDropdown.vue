<script setup>
    import { ref, watch, onMounted, computed } from 'vue';
    import debounce from 'lodash/debounce';
    import AppLayout from '@/Layouts/AppLayout.vue';

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
            default: false  // Control whether to show color swatches
        }
    });

    const emit = defineEmits(['update:modelValue', 'change', 'selectedName']);

    const searchQuery = ref('');
    const isOpen = ref(false);
    const filteredItems = ref([]);
    const selectedLabel = ref('');
    const inputRef = ref(null);

    // Find the currently selected item
    const selectedItem = computed(() => {
        if (props.modelValue) {
            return props.items.find(item => item.id == props.modelValue); // Use loose equality for type coercion
        }
        return null;
    });

    const updateFilteredItems = () => {
        filteredItems.value = props.items
            .filter(item => {
                const query = searchQuery.value.toLowerCase();
                const nameMatch = item.name ? item.name.toLowerCase().includes(query) : false;
                const idMatch = item.id.toString().toLowerCase().includes(query);
                const itemCodeMatch = item.item_code ? item.item_code.toLowerCase().includes(query) : false;
                return idMatch || nameMatch || itemCodeMatch;
            })
            .slice(0, 100);
    };

    // Calculate display label for an item
    const getItemDisplayLabel = (item) => {
        // Return the first available value in this priority: name, item_code, id
        return item.name || (item.item_code ? item.item_code : item.id.toString());
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
            // Use loose equality to match string IDs with number values
            const selected = props.items.find(item => item.id == newValue);
            if (selected) {
                selectedLabel.value = getItemDisplayLabel(selected);
            } else {
                // If we can't find the item in the list, just show the ID
                selectedLabel.value = newValue.toString();
            }
        } else {
            selectedLabel.value = '';
        }
    }, { immediate: true });

    const selectItem = (item) => {
        emit('update:modelValue', item.id);
        emit('change', item);
        emit('selectedName', item.name); // Emitting item.name separately
        searchQuery.value = '';
        isOpen.value = false;
    };

    const handleClick = () => {
        isOpen.value = true;

        // Pre-fill search with current selection when dropdown opens
        if (selectedItem.value) {
            searchQuery.value = getItemDisplayLabel(selectedItem.value);
        } else if (props.modelValue) {
            // If we have a modelValue but no matching item, use the ID as search
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
        <div class="relative w-full rounded-lg cursor-pointer select-none dark:bg-gray-500" @mousedown.prevent="handleClick" >
            <div class="w-full p-1.5">
                <template v-if="isOpen">
                    <input ref="inputRef" v-model="searchQuery" type="text" class="w-full py-0.5 dark:bg-gray-400 dark:text-gray-200 outline-none cursor-text" :placeholder="placeholder" >
                </template>
                <template v-else>
                    <div class="flex items-center dark:bg-gray-500">
                        <span v-if="selectedItem && hasHexColors && selectedItem.hex"
                              class="inline-block w-4 h-4 mr-2 rounded-full"
                              :style="{ backgroundColor: selectedItem.hex }">
                        </span>
                        <span class="block w-full truncate dark:text-gray-300">{{ selectedLabel || placeholder }}</span>
                    </div>
                </template>
            </div>
        </div>

        <div v-if="isOpen" class="absolute z-50 w-full mt-2 overflow-auto bg-white border rounded-lg shadow-lg dark:bg-gray-300 max-h-60">
            <div v-if="filteredItems.length === 0" class="p-1 text-gray-500">No item found</div>
            <div
                v-for="item in filteredItems"
                :key="item.id"
                @mousedown.prevent="selectItem(item)"
                class="flex items-center px-4 py-1 cursor-pointer dark:hover:gray-500 hover:bg-gray-100"
                :class="{ 'bg-blue-100': item.id == modelValue }"
            >
                <span v-if="hasHexColors && item.hex"
                      class="inline-block w-4 h-4 mr-2 border border-black rounded-full"
                      :style="{ backgroundColor: item.hex }">
                </span>
                <span>{{ getItemDisplayLabel(item) }}</span>
            </div>
            <div v-if="filteredItems.length === 100" class="p-1 text-sm text-gray-500 bg-gray-50">
                Showing first 100 results. Please refine your search if needed.
            </div>
        </div>
    </div >
</template >
