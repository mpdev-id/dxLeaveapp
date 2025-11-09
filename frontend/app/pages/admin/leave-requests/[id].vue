<template>
  <div>
    <h1 v-if="form.id" class="text-3xl font-bold mb-4">Edit Leave Request #{{ form.id }}</h1>
    <p v-else-if="error" class="text-red-500">Error fetching leave request: {{ error.message }}</p>
    <p v-else>Loading leave request...</p>

    <form v-if="form.id" @submit.prevent="updateLeaveRequest" class="w-full max-w-lg">
      <fieldset :disabled="form.current_status !== 'Draft'">
        <!-- User and Leave Type Selectors -->
        <div class="flex flex-wrap -mx-3 mb-6">
          <div class="w-full px-3">
            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="user">User</label>
            <select class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="user" v-model="form.user_id" required>
              <option v-for="user in users.data" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>
        </div>
        <div class="flex flex-wrap -mx-3 mb-6">
          <div class="w-full px-3">
            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="leave_type">Leave Type</label>
            <select class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="leave_type" v-model="form.leave_type_id" required>
              <option v-for="leaveType in leaveTypes.data" :key="leaveType.id" :value="leaveType.id">{{ leaveType.name }}</option>
            </select>
          </div>
        </div>
        
        <!-- Date Pickers -->
        <div class="flex flex-wrap -mx-3 mb-6">
          <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="start_date">Start Date</label>
            <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="start_date" type="date" v-model="form.start_date" required>
          </div>
          <div class="w-full md:w-1/2 px-3">
            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="end_date">End Date</label>
            <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="end_date" type="date" v-model="form.end_date" required>
          </div>
        </div>

        <!-- Leave Period Radio Buttons -->
        <div class="flex flex-wrap -mx-3 mb-6">
          <div class="w-full px-3">
            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">Leave Period</label>
            <div class="mt-2 flex">
              <label class="inline-flex items-center mr-6">
                <input type="radio" class="form-radio" value="full_day" v-model="form.leave_period">
                <span class="ml-2">Full Day</span>
              </label>
              <label class="inline-flex items-center mr-6">
                <input type="radio" class="form-radio" value="half_day_morning" v-model="form.leave_period" :disabled="isMultiDay">
                <span class="ml-2">Half Day (Morning)</span>
              </label>
              <label class="inline-flex items-center">
                <input type="radio" class="form-radio" value="half_day_afternoon" v-model="form.leave_period" :disabled="isMultiDay">
                <span class="ml-2">Half Day (Afternoon)</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Reason Textarea -->
        <div class="flex flex-wrap -mx-3 mb-6">
          <div class="w-full px-3">
            <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="reason">Reason</label>
            <textarea class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="reason" v-model="form.reason" rows="3" required></textarea>
          </div>
        </div>
      </fieldset>

      <!-- Action Buttons -->
      <div class="flex items-center justify-between mt-6">
        <button v-if="form.current_status === 'Draft'" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
          Update
        </button>
        <div v-else class="text-sm text-gray-600">This request cannot be edited.</div>
        <nuxt-link to="/admin/leave-requests" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
          Back to List
        </nuxt-link>
      </div>
    </form>

    <!-- Approval History (existing code) -->
  </div>
</template>

<script setup>
import { ref, computed, watch, watchEffect } from 'vue';
import { useApi } from '~/composables/useApi';
import { useAuth } from '~/composables/useAuth';

definePageMeta({
  layout: 'admin',
});

const route = useRoute();
const id = route.params.id;

const form = ref({
  id: null,
  user_id: '',
  leave_type_id: '',
  start_date: '',
  end_date: '',
  leave_period: 'full_day',
  reason: '',
  current_status: '',
});

const { data: leaveRequest, error } = await useApi(`/admin/master/leave-requests/${id}`);
const { data: users } = await useApi('/admin/master/users');
const { data: leaveTypes } = await useApi('/admin/master/leave-types');

watchEffect(() => {
  if (leaveRequest.value) {
    const data = leaveRequest.value.data;
    form.value.id = data.id;
    form.value.user_id = data.user.id;
    form.value.leave_type_id = data.leave_type.id;
    form.value.start_date = new Date(data.start_date).toISOString().split('T')[0];
    form.value.end_date = new Date(data.end_date).toISOString().split('T')[0];
    form.value.leave_period = data.leave_period || 'full_day';
    form.value.reason = data.reason;
    form.value.current_status = data.current_status;
  }
});

const isMultiDay = computed(() => {
  return form.value.start_date && form.value.end_date && form.value.start_date !== form.value.end_date;
});

watch(isMultiDay, (newVal) => {
  if (newVal) {
    form.value.leave_period = 'full_day';
  }
});

const updateLeaveRequest = async () => {
  try {
    await useApi(`/admin/master/leave-requests/${id}`, {
      method: 'PUT',
      body: form.value,
    });
    await navigateTo('/admin/leave-requests');
  } catch (error) {
    console.error('Error updating leave request:', error);
  }
};
</script>
