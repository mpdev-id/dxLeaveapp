
<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Leave Types</h1>
      <nuxt-link to="/admin/leave-types/create" class="inline-flex items-center bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg shadow-md">
        <Icon name="heroicons:plus-solid" class="w-5 h-5 mr-2" />
        Create Leave Type
      </nuxt-link>
    </div>

    <!-- Search and Filters -->
    <div class="mb-4">
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Icon name="heroicons:magnifying-glass" class="w-5 h-5 text-gray-400" />
        </div>
        <input 
          type="text"
          v-model="searchQuery"
          placeholder="Search by name..."
          class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white/70 backdrop-blur-xl"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white/70 backdrop-blur-xl shadow-lg rounded-lg my-6 overflow-x-auto">
      <table class="min-w-full table-auto">
        <thead>
          <tr class="bg-slate-100/80 text-gray-600 uppercase text-sm leading-normal">
            <th class="py-3 px-6 text-left cursor-pointer" @click="handleSort('name')">
              <span class="inline-flex items-center">
                Name
                <Icon v-if="sortBy === 'name'" :name="sortDir === 'asc' ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" class="w-4 h-4 ml-1" />
              </span>
            </th>
            <th class="py-3 px-6 text-left cursor-pointer" @click="handleSort('default_entitlement_days')">
              <span class="inline-flex items-center">
                Default Days
                <Icon v-if="sortBy === 'default_entitlement_days'" :name="sortDir === 'asc' ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" class="w-4 h-4 ml-1" />
              </span>
            </th>
            <th class="py-3 px-6 text-center">Actions</th>
          </tr>
        </thead>
        <tbody v-if="pending" class="text-gray-600 text-sm font-light">
          <tr>
            <td colspan="3" class="text-center py-6">Loading...</td>
          </tr>
        </tbody>
        <tbody v-else-if="error" class="text-gray-600 text-sm font-light">
          <tr>
            <td colspan="3" class="text-center py-6 text-red-500">Error fetching leave types: {{ error.message }}</td>
          </tr>
        </tbody>
        <tbody v-else class="text-gray-600 text-sm font-light">
          <tr v-for="leaveType in leaveTypes.data" :key="leaveType.id" class="border-b border-gray-200/50 hover:bg-gray-100/50">
            <td class="py-3 px-6 text-left whitespace-nowrap">{{ leaveType.name }}</td>
            <td class="py-3 px-6 text-left">{{ leaveType.default_entitlement_days }}</td>
            <td class="py-3 px-6 text-center">
              <nuxt-link :to="`/admin/leave-types/${leaveType.id}`" class="p-2 rounded-lg hover:bg-slate-200">
                <Icon name="heroicons:pencil-square" class="w-5 h-5 text-slate-600" />
              </nuxt-link>
            </td>
          </tr>
          <tr v-if="leaveTypes.data.length === 0">
              <td colspan="3" class="text-center py-6">No leave types found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="leaveTypes && leaveTypes.meta" class="flex justify-between items-center mt-4">
        <span class="text-sm text-gray-700">
            Showing {{ leaveTypes.meta.from }} to {{ leaveTypes.meta.to }} of {{ leaveTypes.meta.total }} results
        </span>
        <div class="flex space-x-2">
            <button @click="page--" :disabled="!leaveTypes.links.prev" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white/70 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
            <button @click="page++" :disabled="!leaveTypes.links.next" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white/70 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

definePageMeta({
  layout: 'admin',
});

const searchQuery = ref('');
const sortBy = ref('name');
const sortDir = ref('asc');
const page = ref(1);

const queryParams = computed(() => ({
  search: searchQuery.value,
  sort_by: sortBy.value,
  sort_dir: sortDir.value,
  page: page.value,
}));

const { data: leaveTypes, pending, error, refresh } = await useAsyncData(
  'leave-types',
  () => useApi('/admin/master/leave-types', { params: queryParams.value }),
  {
    watch: [queryParams]
  }
);

const handleSort = (column) => {
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDir.value = 'asc';
  }
};

</script>
