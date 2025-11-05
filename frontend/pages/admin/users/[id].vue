
<template>
  <div>
    <h1 v-if="user" class="text-3xl font-bold mb-4">Edit User: {{ user.data.name }}</h1>
    <p v-else-if="error" class="text-red-500">Error fetching user: {{ error.message }}</p>
    <p v-else>Loading user...</p>

    <form v-if="user" @submit.prevent="updateUser" class="w-full max-w-lg">
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="name">
            Name
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="name" type="text" v-model="user.data.name" required>
        </div>
      </div>
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="email">
            Email
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="email" type="email" v-model="user.data.email" required>
        </div>
      </div>
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
            Roles
          </label>
          <div v-for="role in roles.data" :key="role.id">
            <label class="inline-flex items-center">
              <input type="checkbox" :value="role.name" v-model="selectedRoles" class="form-checkbox h-5 w-5 text-gray-600">
              <span class="ml-2 text-gray-700">{{ role.name }}</span>
            </label>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
          Update
        </button>
        <nuxt-link to="/admin/users" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
          Cancel
        </nuxt-link>
      </div>
    </form>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
  });

  const { token } = useAuth();
  const route = useRoute();
  const id = route.params.id;

  const { data: user, error } = await useFetch(`/api/admin/master/users/${id}`, {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });

  const { data: roles } = await useFetch('/api/admin/master/roles', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });

  const selectedRoles = ref(user.value.data.roles.map(role => role.name));

  const updateUser = async () => {
    try {
      await useFetch(`/api/admin/master/users/${id}`,
        {
          method: 'PUT',
          body: { ...user.value.data, roles: selectedRoles.value },
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        }
      );
      await navigateTo('/admin/users');
    } catch (error) {
      console.error('Error updating user:', error);
    }
  };
</script>
