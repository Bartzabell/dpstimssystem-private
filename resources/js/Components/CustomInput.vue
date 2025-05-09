<script setup>
const model = defineModel({
    type: null,
    required: false,
});

defineProps({
    name: {
        type: String,
        required: false,
    },

    type: {
        type: String,
        default: "text",
    },
    message: String,
    readonly: Boolean,
    disabled: Boolean
});

</script>

<template>
    <div class="mb-0.5">
        <label class="text-[8px] lg:text-[10px] font-medium 2xl:text-sm dark:text-gray-200">{{ name }}</label>
        <div :class="{ '!border-red-500 !border-2': message }"
            class="flex transition bg-white rounded-lg dark:bg-gray-400 focus-within:ring-1 dark:focus-within:ring-teal-300 focus-within:ring-teal-900 ring-teal-100">
            <input v-model="model" :readonly="readonly" :disabled="disabled" :class="[
                'w-full rounded-lg py-0 2xl:py-1.5 !text-[10px] 2xl:!text-sm focus-within:ring-1 dark:bg-gray-500 dark:text-gray-200 dark:focus-within:ring-teal-300 focus-within:ring-teal-900 ring-teal-100 focus:outline-none pl-2',
                { 'bg-gray-100 dark:bg-gray-800': readonly || disabled }
            ]" :type="type">
        </div>
        <small class="text-xs italic text-red-500 md:text-sm" v-if="message">{{ message }}</small>
    </div>
</template>