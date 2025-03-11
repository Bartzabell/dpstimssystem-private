<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  label: {
    type: String,
    required: false
  },
  name: {
    type: String,
    required: false
  },
  modelValue: {
    type: [String, Number],
    required: false
  },
  options: {
    type: Array,
    required: true
    // Each option should have a value and label property
    // [{value: 'production', label: 'Production'}, ...]
  },
  readonly: Boolean,
  disabled: Boolean
})

const emit = defineEmits(['update:modelValue'])

const updateValue = (event) => {
  emit('update:modelValue', event.target.value)
}
</script>

<template>
  <div class="mb-2">
    <label class="text-sm font-medium">{{ label }}</label>
    <div class="flex w-full transition bg-white rounded-lg focus-within:ring-1 focus-within:ring-teal-900 ring-teal-100">
      <select 
        :name="name" 
        :value="modelValue" 
        @input="updateValue"
        :readonly="readonly" 
        :disabled="disabled"
        :class="[
            'w-full rounded-lg py-1.5 focus-within:ring-1 focus-within:ring-teal-900 ring-teal-100 focus:outline-none pl-2',
            { 'bg-gray-100': readonly || disabled }
        ]"
      >
        <option 
          v-for="option in options" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
    </div>
  </div>
</template>