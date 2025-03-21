<template>
    <AppLayout title="Activity Logs">
      <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Activity Logs
        </h2>
      </template>

      <div>
        <div class="mx-auto sm:px-6 lg:px-8">
          <div class="p-6 overflow-auto bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 rounded-lg dark:divide-gray-400">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300 whitespace-nowrap">ID</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300 whitespace-nowrap">Log Name</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300 whitespace-nowrap">Description</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300 whitespace-nowrap">Subject</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300 whitespace-nowrap">User</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300 whitespace-nowrap">Properties</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300 whitespace-nowrap">Created At</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-400 dark:bg-gray-600">
                <tr v-for="log in logs.data" :key="log.id">
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{{ log.id }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{{ log.log_name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{{ log.description }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">
                    {{ log.subject_type ? `${log.subject_type} (ID: ${log.subject_id})` : 'N/A' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">
                    {{ getUserName(log) }}
                  </td>
                  <td class="max-w-xs px-6 py-4 overflow-hidden text-sm text-gray-500 dark:text-gray-300">
                    <pre class="text-xs">{{ JSON.stringify(log.properties, null, 2) }}</pre>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{{ new Date(log.created_at).toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>

            <!-- Simple pagination navigation -->
            <div class="flex justify-between mt-6" v-if="logs.links && logs.links.length > 3">
              <Link v-if="logs.prev_page_url"
                    :href="logs.prev_page_url"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md dark:text-gray-200 dark:bg-gray-600">
                Previous
              </Link>
              <span v-else class="px-4 py-2 text-gray-400">Previous</span>

              <div class="flex space-x-2">
                <Link v-for="(link, i) in logs.links.slice(1, -1)"
                      :key="i"
                      :href="link.url"
                      class="px-4 py-2 rounded-md"
                      :class="link.active ? 'bg-blue-500 dark:bg-blue-800 text-white' : 'bg-gray-100 dark:text-gray-200 dark:bg-gray-600 text-gray-700'">
                  {{ link.label }}
                </Link>
              </div>

              <Link v-if="logs.next_page_url"
                    :href="logs.next_page_url"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md dark:text-gray-200 dark:bg-gray-600">
                Next
              </Link>
              <span v-else class="px-4 py-2 text-gray-400">Next</span>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>

  <script>
  import { defineComponent } from 'vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import { Link } from '@inertiajs/vue3';

  export default defineComponent({
    components: {
      AppLayout,
      Link
    },
    props: {
      logs: Object,
    },
    methods: {
      getUserName(log) {
        // Check if causer (user) exists and has a name
        if (log.causer && log.causer.name) {
          return log.causer.name;
        }

        // Fallback: show causer_id if available
        if (log.causer_id) {
          return `User ID: ${log.causer_id}`;
        }

        // If no causer information is available
        return 'System';
      }
    }
  });
  </script>
