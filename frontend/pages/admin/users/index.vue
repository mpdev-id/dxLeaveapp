
<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-3xl font-bold">Users</h1>
      <nuxt-link to="/admin/users/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create User</nuxt-link>
    </div>
    <div class="bg-white shadow-md rounded my-6">
      <table class="min-w-full table-auto">
        <thead>
          <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <th class="py-3 px-6 text-left">Name</th>
            <th class="py-3 px-6 text-left">Email</th>
            <th class="py-3 px-6 text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
          <tr v-for="user in users.data" :key="user.id" class="border-b border-gray-200 hover:bg-gray-100">
            <td class="py-3 px-6 text-left whitespace-nowrap">{{ user.name }}</td>
            <td class="py-3 px-6 text-left">{{ user.email }}</td>
            <td class="py-3 px-6 text-center">
              <nuxt-link :to="`/admin/users/${user.id}`" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Edit</nuxt-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="error" class="text-red-500">Error fetching users: {{ error.message }}</p>
    <p v-else-if="!users">Loading users...</p>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
  });

  const { token } = useAuth();

  const { data: users, error } = await useFetch('/api/admin/master/users', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });
</script>
