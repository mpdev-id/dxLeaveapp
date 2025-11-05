
<template>
  <div>
    <h1 v-if="leaveRequest" class="text-3xl font-bold mb-4">Edit Leave Request</h1>
    <p v-else-if="error" class="text-red-500">Error fetching leave request: {{ error.message }}</p>
    <p v-else>Loading leave request...</p>

    <form v-if="leaveRequest" @submit.prevent="updateLeaveRequest" class="w-full max-w-lg">
      <!-- ... form fields ... -->
    </form>

    <div v-if="leaveRequest && leaveRequest.data.approvals" class="mt-10">
      <h2 class="text-2xl font-bold mb-4">Approval History</h2>
      <div class="bg-white shadow-md rounded my-6">
        <table class="min-w-full table-auto">
          <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
              <th class="py-3 px-6 text-left">Approver</th>
              <th class="py-3 px-6 text-left">Status</th>
              <th class="py-3 px-6 text-left">Date</th>
            </tr>
          </thead>
          <tbody class="text-gray-600 text-sm font-light">
            <tr v-for="approval in leaveRequest.data.approvals" :key="approval.id" class="border-b border-gray-200 hover:bg-gray-100">
              <td class="py-3 px-6 text-left whitespace-nowrap">{{ approval.user.name }}</td>
              <td class="py-3 px-6 text-left">{{ approval.status }}</td>
              <td class="py-3 px-6 text-left">{{ new Date(approval.created_at).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
  });

  const { token } = useAuth();
  const route = useRoute();
  const id = route.params.id;

  const { data: leaveRequest, error } = await useFetch(`/api/admin/master/leave-requests/${id}`, {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });

  const { data: users } = await useFetch('/api/admin/master/users', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });

  const { data: leaveTypes } = await useFetch('/api/admin/master/leave-types', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  });

  const updateLeaveRequest = async () => {
    try {
      await useFetch(`/api/admin/master/leave-requests/${id}`,
        {
          method: 'PUT',
          body: leaveRequest.value.data,
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        }
      );
      await navigateTo('/admin/leave-requests');
    } catch (error) {
      console.error('Error updating leave request:', error);
    }
  };
</script>
