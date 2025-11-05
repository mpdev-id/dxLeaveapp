
<template>
  <div>
    <h1 v-if="publicHoliday" class="text-3xl font-bold mb-4">Edit Public Holiday: {{ publicHoliday.data.name }}</h1>
    <p v-else-if="error" class="text-red-500">Error fetching public holiday: {{ error.message }}</p>
    <p v-else>Loading public holiday...</p>

    <form v-if="publicHoliday" @submit.prevent="updatePublicHoliday" class="w-full max-w-lg">
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="name">
            Name
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="name" type="text" v-model="publicHoliday.data.name" required>
        </div>
      </div>
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="date">
            Date
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="date" type="date" v-model="publicHoliday.data.date" required>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
          Update
        </button>
        <nuxt-link to="/admin/public-holidays" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
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

  const { data: publicHoliday, error } = await useFetch(`/api/admin/master/public-holidays/${id}`, {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });

  const updatePublicHoliday = async () => {
    try {
      await useFetch(`/api/admin/master/public-holidays/${id}`,
        {
          method: 'PUT',
          body: publicHoliday.value.data,
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        }
      );
      await navigateTo('/admin/public-holidays');
    } catch (error) {
      console.error('Error updating public holiday:', error);
    }
  };
</script>
