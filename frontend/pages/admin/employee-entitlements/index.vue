
<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-3xl font-bold">Employee Entitlements</h1>
      <nuxt-link to="/admin/employee-entitlements/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Employee Entitlement</nuxt-link>
    </div>
    <div class="bg-white shadow-md rounded my-6">
      <table class="min-w-full table-auto">
        <thead>
          <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <th class="py-3 px-6 text-left">User</th>
            <th class="py-3 px-6 text-left">Leave Type</th>
            <th class="py-3 px-6 text-left">Entitlement</th>
            <th class="py-3 px-6 text-left">Year</th>
            <th class="py-3 px-6 text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
          <tr v-for="entitlement in employeeEntitlements.data" :key="entitlement.id" class="border-b border-gray-200 hover:bg-gray-100">
            <td class="py-3 px-6 text-left whitespace-nowrap">{{ entitlement.user.name }}</td>
            <td class="py-3 px-6 text-left">{{ entitlement.leave_type.name }}</td>
            <td class="py-3 px-6 text-left">{{ entitlement.entitlement }}</td>
            <td class="py-3 px-6 text-left">{{ entitlement.year }}</td>
            <td class="py-3 px-6 text-center">
              <nuxt-link :to="`/admin/employee-entitlements/${entitlement.id}`" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Edit</nuxt-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="error" class="text-red-500">Error fetching employee entitlements: {{ error.message }}</p>
    <p v-else-if="!employeeEntitlements">Loading employee entitlements...</p>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
  });

  const { token } = useAuth();

  const { data: employeeEntitlements, error } = await useFetch('/api/admin/master/employee-entitlements', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });
</script>
