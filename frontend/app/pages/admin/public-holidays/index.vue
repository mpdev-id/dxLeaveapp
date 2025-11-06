
<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-3xl font-bold">Public Holidays</h1>
      <nuxt-link to="/admin/public-holidays/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Public Holiday</nuxt-link>
    </div>
    <div class="bg-white shadow-md rounded my-6">
      <table class="min-w-full table-auto">
        <thead>
          <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <th class="py-3 px-6 text-left">Name</th>
            <th class="py-3 px-6 text-left">Date</th>
            <th class="py-3 px-6 text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
          <tr v-for="publicHoliday in publicHolidays.data" :key="publicHoliday.id" class="border-b border-gray-200 hover:bg-gray-100">
            <td class="py-3 px-6 text-left whitespace-nowrap">{{ publicHoliday.name }}</td>
            <td class="py-3 px-6 text-left">{{ publicHoliday.date }}</td>
            <td class="py-3 px-6 text-center">
              <nuxt-link :to="`/admin/public-holidays/${publicHoliday.id}`" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Edit</nuxt-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="error" class="text-red-500">Error fetching public holidays: {{ error.message }}</p>
    <p v-else-if="!publicHolidays">Loading public holidays...</p>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
  });

  const { token } = useAuth();

  const { data: publicHolidays, error } = await useFetch('/api/admin/master/public-holidays', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });
</script>
