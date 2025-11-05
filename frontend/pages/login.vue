
<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
      <h1 class="text-3xl font-bold text-center text-gray-900">Super Admin Login</h1>
      <form @submit.prevent="login" class="space-y-6">
        <div>
          <label for="email" class="text-sm font-medium text-gray-700">Email</label>
          <input type="email" id="email" v-model="form.email" required class="block w-full px-3 py-2 mt-1 text-gray-900 bg-gray-200 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
  const form = ref({
    email: '',
    password: '',
  });

  const error = ref(null);

  const login = async () => {
    try {
      const { data, error: loginError } = await useFetch('/api/login', {
        method: 'POST',
        body: form.value,
      });

      if (loginError.value) {
        throw new Error(loginError.value.data.message || 'An error occurred');
      }

      // In a real app, you would store the token securely
      // For example, using a cookie or a state management library
      const token = data.value.token;
      console.log('Logged in successfully, token:', token);

      // Store the token in a cookie for example
      const authToken = useCookie('auth_token');
      authToken.value = token;

      await navigateTo('/admin/dashboard');
    } catch (e) {
      error.value = e.message;
    }
  };
</script>
