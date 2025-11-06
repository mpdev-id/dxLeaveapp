
<template>
  <div>
    <h1 class="text-3xl font-bold mb-4">Create Leave Request</h1>
    <form @submit.prevent="createLeaveRequest" class="w-full max-w-lg">
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="user">
            User
          </label>
          <select class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="user" v-model="form.user_id" required>
            <option v-for="user in users.data" :key="user.id" :value="user.id">{{ user.name }}</option>
          </select>
        </div>
      </div>
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="leave_type">
            Leave Type
          </label>
          <select class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="leave_type" v-model="form.leave_type_id" required>
            <option v-for="leaveType in leaveTypes.data" :key="leaveType.id" :value="leaveType.id">{{ leaveType.name }}</option>
          </select>
        </div>
      </div>
      <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="start_date">
            Start Date
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="start_date" type="date" v-model="form.start_date" required>
        </div>
        <div class="w-full md:w-1/2 px-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="end_date">
            End Date
          </label>
          <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="end_date" type="date" v-model="form.end_date" required>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <button class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
          Create
        </button>
        <nuxt-link to="/admin/leave-requests" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
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
    user_id: '',
    leave_type_id: '',
    start_date: '',
    end_date: '',
  });

  const { data: users } = await useApi('/admin/master/users');

  const { data: leaveTypes } = await useApi('/admin/master/leave-types');

  const createLeaveRequest = async () => {
    try {
      await useApi('/admin/master/leave-requests', {
        method: 'POST',
        body: form.value,
      });
      await navigateTo('/admin/leave-requests');
    } catch (error) {
      console.error('Error creating leave request:', error);
    }
  };
</script>
