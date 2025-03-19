<script setup>
import { ref } from 'vue';
import { PhCheckCircle, PhWarningCircle, PhInfo, PhXCircle, PhTrash } from "@phosphor-icons/vue";

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

defineExpose({ showToast })
</script>

<template>
  <transition 
            enter-active-class="transition duration-300 ease-out transform"
            enter-from-class="translate-y-[-20px] opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-700 ease-in transform"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-[-20px] opacity-0"
    >
        <div v-if="toast.show" class="fixed z-50 flex items-center p-4 rounded-lg shadow-lg top-4 right-4"
            :class="{
                'bg-green-100 border-l-4 border-green-500': toast.type === 'success',
                'bg-orange-100 border-l-4 border-orange-500': toast.type === 'delete',
                'bg-red-100 border-l-4 border-red-500': toast.type === 'error',
                'bg-blue-100 border-l-4 border-blue-500': toast.type === 'info'
            }"
            >
            <div class="flex flex-col gap-2">
                <div class="flex flex-row items-start">
                    <img src="/img/dpstlogo.png" alt="Logo" class="w-10 h-8">
                    <div class="flex flex-col gap-0 ml-2 text-gray-500">
                        <h1 class="text-xs font-semibold">Dellosa's Soap and Detergents Manufacturing</h1>
                        <span class="text-[8px] font-thin">Inventory Management System</span>
                    </div>
                </div>
                <div class="flex flex-row items-start justify-start">
                        <div class="mr-3"
                        :class="{
                            'text-green-500': toast.type === 'success',
                            'text-orange-500': toast.type === 'delete',
                            'text-red-500': toast.type === 'error',
                            'text-blue-500': toast.type === 'info'
                        }"
                    >
                        <!-- Success Icon -->
                        <PhCheckCircle v-if="toast.type === 'success'" :size="24" />
                        <PhTrash v-if="toast.type === 'delete'" :size="24" />
                        <PhWarningCircle v-if="toast.type === 'error'" :size="24" />
                        <PhInfo v-if="toast.type === 'info'" :size="24" />
                    </div>
                    <div class="font-bold">{{ toast.message }}</div>
                </div>
            </div>
            <button @click="toast.show = false" class="ml-4 text-gray-500 hover:text-gray-700">
                <PhXCircle :size="28" />
            </button>
        </div>
    </transition>
</template>