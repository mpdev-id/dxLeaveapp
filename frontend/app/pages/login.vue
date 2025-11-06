
<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
      <h1 class="text-3xl font-bold text-center text-gray-900">Super Admin Login</h1>
      <form @submit.prevent="login" class="space-y-6">
        <div>
          <label for="identifier" class="text-sm font-medium text-gray-700">Email or Employee Code</label>
          <input type="text" id="identifier" v-model="form.identifier" required class="block w-full px-3 py-2 mt-1 text-gray-900 bg-gray-200 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
          <label for="password" class="text-sm font-medium text-gray-700">Password</label>
          <input type="password" id="password" v-model="form.password" required class="block w-full px-3 py-2 mt-1 text-gray-900 bg-gray-200 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
          <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Login</button>
        </div>
      </form>
      <p v-if="error" class="mt-4 text-sm text-red-600">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
  });

  const form = ref({
    identifier: '',
    password: '',
  });

  const error = ref(null);
  const { public: { apiBase } } = useRuntimeConfig();
  const { token } = useAuth();

  const login = async () => {
    error.value = null;
    try {
      const response = await useFetch(`${apiBase}/login`, {
        method: 'POST',
        body: form.value,
      });

      if (response.error.value) {
        console.error('Login error:', response.error.value);
        throw new Error(response.error.value.data?.message || 'An error occurred during login.');
      }

      const responseData = response.data.value;
      if (responseData && responseData.data.access_token) {
        token.value = responseData.data.access_token;
        console.log('Logged in successfully, token:', token.value);
        await navigateTo('/admin/dashboard');
      } else {
        throw new Error('Login failed: No access token received.');
      }
    } catch (e) {
      console.error('Caught exception:', e);
      error.value = e.message;
    }
  };
</script>
