
<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-3xl font-bold">Leave Requests</h1>
      <nuxt-link to="/admin/leave-requests/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Leave Request</nuxt-link>
    </div>
    <div class="bg-white shadow-md rounded my-6">
      <table class="min-w-full table-auto">
        <thead>
          <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <th class="py-3 px-6 text-left">User</th>
            <th class="py-3 px-6 text-left">Leave Type</th>
            <th class="py-3 px-6 text-left">Start Date</th>
            <th class="py-3 px-6 text-left">End Date</th>
            <th class="py-3 px-6 text-left">Status</th>
            <th class="py-3 px-6 text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
          <tr v-for="leaveRequest in leaveRequests.data" :key="leaveRequest.id" class="border-b border-gray-200 hover:bg-gray-100">
            <td class="py-3 px-6 text-left whitespace-nowrap">{{ leaveRequest.user.name }}</td>
            <td class="py-3 px-6 text-left">{{ leaveRequest.leave_type.name }}</td>
            <td class="py-3 px-6 text-left">{{ leaveRequest.start_date }}</td>
            <td class="py-3 px-6 text-left">{{ leaveRequest.end_date }}</td>
            <td class="py-3 px-6 text-left">{{ leaveRequest.status }}</td>
            <td class="py-3 px-6 text-center">
              <nuxt-link :to="`/admin/leave-requests/${leaveRequest.id}`" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Edit</nuxt-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="error" class="text-red-500">Error fetching leave requests: {{ error.message }}</p>
    <p v-else-if="!leaveRequests">Loading leave requests...</p>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
  });

  const { token } = useAuth();

  const { data: leaveRequests, error } = await useFetch('/api/admin/master/leave-requests', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });
</script>
