<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
  data: Object
})
</script>

<template>
  <div class="flex flex-col items-start justify-start gap-2 mt-4 mr-4 md:items-center md:flex-row md:justify-between">
    <div>
      <p class="text-sm text-gray-700 dark:text-gray-100">
        Showing
        <span class="font-medium">{{ data.from }}</span>
        to
        <span class="font-medium">{{ data.to }}</span>
        of
        <span class="font-medium">{{ data.total }}</span>
        results
      </p>
    </div>
    <div>
      <button
        class="px-2 py-1 text-xs border border-teal-700 dark:border-teal-300 md:px-4 md:py-2 md:text-base dark:hover:bg-teal-900 dark:bg-teal-700 hover:bg-teal-200 bg-teal-50"
        :class="{
          'bg-teal-100 dark:bg-teal-800 border-teal-500 dark:text-teal-200 text-teal-600': item.active,
          'bg-white border-gray-300 text-gray-500 dark:text-gray-200': !item.active,
        }"
        v-for="item in data.links"
        :key="item.label"
        @click="router.get(item.url, {}, { preserveState: true })"
        :disabled="item.active || !item.url"
      >
        <span v-html="item.label"></span>
      </button>
    </div>
  </div>
</template>