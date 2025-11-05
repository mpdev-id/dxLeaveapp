
<template>
  <div>
    <h1 class="text-3xl font-bold mb-4">Create User</h1>
    <form @submit.prevent="createUser" class="w-full max-w-lg">
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="name">
            Name
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="name" type="text" v-model="form.name" required>
        </div>
      </div>
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="email">
            Email
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="email" type="email" v-model="form.email" required>
        </div>
      </div>
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="password">
            Password
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="password" type="password" v-model="form.password" required>
        </div>
      </div>
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
            Roles
          </label>
          <div v-for="role in roles" :key="role.id">
            <label class="inline-flex items-center">
              <input type="checkbox" :value="role.name" v-model="form.roles" class="form-checkbox h-5 w-5 text-gray-600">
              <span class="ml-2 text-gray-700">{{ role.name }}</span>
            </label>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
          Create
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

  const form = ref({
    name: '',
    email: '',
    password: '',
    roles: [],
  });

  const { data: roles } = await useFetch('/api/admin/master/roles', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });

  const createUser = async () => {
    try {
      await useFetch('/api/admin/master/users', {
        method: 'POST',
        body: form.value,
        headers: {
          Authorization: `Bearer ${token.value}`,
        },
      });
      await navigateTo('/admin/users');
    } catch (error) {
      console.error('Error creating user:', error);
    }
  };
</script>
