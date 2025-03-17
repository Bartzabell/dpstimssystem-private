<template>
    <div v-if="links.length > 3">
      <div class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in links" :key="key">
          <div v-if="link.url === null"
               class="px-4 py-2 mb-1 mr-1 text-sm text-gray-400 border rounded"
               :class="{ 'ml-auto': link.label === 'Next' }">
            {{ link.label }}
          </div>
          <Link v-else
                class="px-4 py-2 mb-1 mr-1 text-sm border rounded hover:bg-gray-100 focus:outline-none"
                :class="{ 'bg-blue-500 text-white hover:bg-blue-600': link.active, 'ml-auto': link.label === 'Next' }"
                :href="link.url">
            {{ link.label }}
          </Link>
        </template>
      </div>
    </div>
</template>

<script>
    import { Link } from '@inertiajs/vue3';

    export default {
        components: {
        Link,
        },
        props: {
        links: Array,
        },
    };
</script>
