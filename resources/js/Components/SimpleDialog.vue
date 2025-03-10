<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  icon: {
        type: Object, // Accepts a Vue component as an icon
        default: null, // Optional
    },
  theme: {
    type: String,
    default: 'green',
    validator: (value) => [
      'slate', 'gray', 'zinc', 'neutral', 'stone',
      'red', 'orange', 'amber', 'yellow', 'lime', 
      'green', 'emerald', 'teal', 'cyan', 
      'blue', 'indigo', 'violet', 'purple', 
      'fuchsia', 'pink', 'rose'
    ].includes(value)
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  }
})

const emit = defineEmits(['cancel', 'confirm'])

const closeDialog = () => {
  emit('cancel')
}

const confirmDialog = () => {
  emit('confirm')
}

const themeBg = {
  slate: 'bg-slate-50',
  gray: 'bg-gray-50',
  zinc: 'bg-zinc-50',
  neutral: 'bg-neutral-50',
  stone: 'bg-stone-50',
  red: 'bg-red-50',
  orange: 'bg-orange-50',
  amber: 'bg-amber-50',
  yellow: 'bg-yellow-50',
  lime: 'bg-lime-50',
  green: 'bg-green-50',
  emerald: 'bg-emerald-50',
  teal: 'bg-teal-50',
  cyan: 'bg-cyan-50',
  blue: 'bg-blue-50',
  indigo: 'bg-indigo-50',
  violet: 'bg-violet-50',
  purple: 'bg-purple-50',
  fuchsia: 'bg-fuchsia-50',
  pink: 'bg-pink-50',
  rose: 'bg-rose-50'
}

const themeText = {
  slate: 'text-slate-600',
  gray: 'text-gray-600',
  zinc: 'text-zinc-600',
  neutral: 'text-neutral-600',
  stone: 'text-stone-600',
  red: 'text-red-600',
  orange: 'text-orange-600',
  amber: 'text-amber-600',
  yellow: 'text-yellow-600',
  lime: 'text-lime-600',
  green: 'text-green-600',
  emerald: 'text-emerald-600',
  teal: 'text-teal-600',
  cyan: 'text-cyan-600',
  blue: 'text-blue-600',
  indigo: 'text-indigo-600',
  violet: 'text-violet-600',
  purple: 'text-purple-600',
  fuchsia: 'text-fuchsia-600',
  pink: 'text-pink-600',
  rose: 'text-rose-600'
};

const themeClasses = {
  slate: 'bg-slate-500 hover:bg-slate-600',
  gray: 'bg-gray-500 hover:bg-gray-600',
  zinc: 'bg-zinc-500 hover:bg-zinc-600',
  neutral: 'bg-neutral-500 hover:bg-neutral-600',
  stone: 'bg-stone-500 hover:bg-stone-600',
  red: 'bg-red-500 hover:bg-red-600',
  orange: 'bg-orange-500 hover:bg-orange-600',
  amber: 'bg-amber-500 hover:bg-amber-600',
  yellow: 'bg-yellow-500 hover:bg-yellow-600',
  lime: 'bg-lime-500 hover:bg-lime-600',
  green: 'bg-green-500 hover:bg-green-600',
  emerald: 'bg-emerald-500 hover:bg-emerald-600',
  teal: 'bg-teal-500 hover:bg-teal-600',
  cyan: 'bg-cyan-500 hover:bg-cyan-600',
  blue: 'bg-blue-500 hover:bg-blue-600',
  indigo: 'bg-indigo-500 hover:bg-indigo-600',
  violet: 'bg-violet-500 hover:bg-violet-600',
  purple: 'bg-purple-500 hover:bg-purple-600',
  fuchsia: 'bg-fuchsia-500 hover:bg-fuchsia-600',
  pink: 'bg-pink-500 hover:bg-pink-600',
  rose: 'bg-rose-500 hover:bg-rose-600'
}
</script>

<template>
  <transition 
    enter-active-class="transition-opacity duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div 
      v-if="modelValue" 
      class="fixed inset-0 !z-[600] flex items-center justify-center bg-black bg-opacity-50"
    >
      <transition 
        appear
        enter-active-class="transition duration-300 ease-out delay-150 transform"
        enter-from-class="scale-90 opacity-0"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="transition duration-200 ease-in transform"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-90 opacity-0"
      >
        <div :class="['p-6 rounded-lg !z-[1100] shadow-lg w-96', themeBg[theme]]">
            <div class="flex items-center justify-center w-full">
                <component v-if="icon" :is="icon" :class="['w-20 h-20', themeText[theme]]" />
            </div>
          <h3 class="mb-2 text-lg font-medium text-center">{{ title }}</h3>
          <p v-if="description" class="px-4 mb-6 text-center text-gray-600">{{ description }}</p>
          <div class="flex justify-end gap-2">
            <button
              @click="closeDialog"
              class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300"
            >
              {{ cancelText }}
            </button>
            <button
              @click="confirmDialog"
              :class="['px-4 py-2 text-white rounded', themeClasses[theme]]"
            >
              {{ confirmText }}
            </button>
          </div>
        </div>
      </transition>
    </div>
  </transition>
</template>