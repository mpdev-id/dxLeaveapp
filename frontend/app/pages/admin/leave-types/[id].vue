
<template>
  <div>
    <h1 v-if="leaveType" class="text-3xl font-bold mb-4">Edit Leave Type: {{ leaveType.data.name }}</h1>
    <p v-else-if="error" class="text-red-500">Error fetching leave type: {{ error.message }}</p>
    <p v-else>Loading leave type...</p>

    <form v-if="leaveType" @submit.prevent="updateLeaveType" class="w-full max-w-lg">
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="name">
            Name
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="name" type="text" v-model="leaveType.data.name" required>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <button class="bg-slate-500 hover:bg-slate-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
          Update
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

  const route = useRoute();
  const id = route.params.id;

  const { data: leaveType, error } = await useApi(`/admin/master/leave-types/${id}`);

  const updateLeaveType = async () => {
    try {
      await useApi(`/admin/master/leave-types/${id}`,
        {
          method: 'PUT',
          body: leaveType.value.data,
        }
      );
      await navigateTo('/admin/leave-types');
    } catch (error) {
      console.error('Error updating leave type:', error);
    }
  };
</script>
