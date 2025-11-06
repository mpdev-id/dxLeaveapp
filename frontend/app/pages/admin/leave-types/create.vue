
<template>
  <div>
    <h1 class="text-3xl font-bold mb-4">Create Leave Type</h1>
    <form @submit.prevent="createLeaveType" class="w-full max-w-lg">
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="name">
            Name
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="name" type="text" v-model="form.name" required>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <button class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
          Create
        </button>
        <nuxt-link to="/admin/leave-types" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
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
  });

  const createLeaveType = async () => {
    try {
      await useApi('/admin/master/leave-types', {
        method: 'POST',
        body: form.value,
      });
      await navigateTo('/admin/leave-types');
    } catch (error) {
      console.error('Error creating leave type:', error);
    }
  };
</script>
