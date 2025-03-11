<script setup>
defineProps({
    text: {
        type: String,
        required: true,
    },

    icon: {
        type: Object, // Accepts a Vue component as an icon
        default: null, // Optional
    },

    color: {
        type: String,
        default: "bg-blue-500 hover:bg-blue-700",
    },

    type: {
        type: String,
        default: "button",
    }
});
</script>

<template>
    <button
        :type="type"
        :class="[color, 'px-4 py-2 text-white rounded-3xl flex items-center gap-2']"
    >
        <!-- Render icon if provided -->
        <component v-if="icon" :is="icon" class="w-5 h-5" />
        {{ text }}
    </button>
</template>
