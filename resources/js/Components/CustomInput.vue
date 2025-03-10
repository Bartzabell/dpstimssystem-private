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
    <div class="mb-2">
        <label class="text-sm font-medium">{{ name }}</label>
        <div :class="{'!border-red-500 !border-2' : message}" class="flex transition bg-white rounded-lg focus-within:ring-1 focus-within:ring-teal-900 ring-teal-100">
            <input
                v-model="model"
                :readonly="readonly"
                :disabled="disabled"
                :class="[
                    'w-full rounded-lg py-1.5 focus-within:ring-1 focus-within:ring-teal-900 ring-teal-100 focus:outline-none pl-2',
                    { 'bg-gray-100': readonly || disabled }
                ]"
                :type="type">
        </div>
        <small class="text-xs italic text-red-500 md:text-sm" v-if="message">{{ message }}</small>
    </div>
</template>