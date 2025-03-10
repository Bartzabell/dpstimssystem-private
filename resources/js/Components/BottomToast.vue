<script setup>
import { ref } from 'vue';
import { PhCheckCircle, PhWarningCircle, PhInfo } from "@phosphor-icons/vue";

const props = defineProps({
  duration: {
    type: Number,
    default: 3000
  }
})

const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

const showToast = (message, type = 'success') => {
  toast.value = {
    show: true,
    message,
    type
  }

  setTimeout(() => {
    toast.value.show = false
  }, props.duration)
}

// Expose showToast method to parent components
defineExpose({ showToast })
</script>

<template>
  <transition 
    enter-active-class="transition duration-300 ease-out transform"
    enter-from-class="translate-y-[20px] opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition duration-700 ease-in transform"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-[20px] opacity-0"
  >
    <div
      v-if="toast.show"
      class="fixed z-50 max-w-md p-4 font-semibold rounded-lg shadow-lg bottom-6 right-6"
      :class="{
        'bg-green-400 text-white': toast.type === 'success',
        'bg-red-400 text-white': toast.type === 'error',
        'bg-blue-400 text-white': toast.type === 'info'
      }"
    >
      <div class="flex items-center gap-1">
        <PhCheckCircle v-if="toast.type === 'success'" :size="24" />
        <PhWarningCircle v-if="toast.type === 'error'" :size="24" />
        <PhInfo v-if="toast.type === 'info'" :size="24" />
        <span>{{ toast.message }}</span>
      </div>
    </div>
  </transition>
</template>