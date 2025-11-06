<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>

    <!-- Loading and Error States -->
    <div v-if="pending" class="text-center text-gray-500">
      <p>Loading dashboard data...</p>
    </div>
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline"> Failed to load dashboard data. Please try again later.</span>
    </div>

    <!-- Dashboard Content -->
    <div v-if="data" class="space-y-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white/70 p-6 rounded-lg shadow-lg backdrop-blur-xl">
          <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
          <p class="mt-2 text-3xl font-bold text-indigo-600">{{ data.stats.total_users }}</p>
        </div>
        <div class="bg-white/70 p-6 rounded-lg shadow-lg backdrop-blur-xl">
          <h3 class="text-sm font-medium text-gray-500">Total Departments</h3>
          <p class="mt-2 text-3xl font-bold text-indigo-600">{{ data.stats.total_departments }}</p>
        </div>
        <div class="bg-white/70 p-6 rounded-lg shadow-lg backdrop-blur-xl">
          <h3 class="text-sm font-medium text-gray-500">Pending Requests</h3>
          <p class="mt-2 text-3xl font-bold text-amber-600">{{ data.stats.pending_requests }}</p>
        </div>
        <div class="bg-white/70 p-6 rounded-lg shadow-lg backdrop-blur-xl">
          <h3 class="text-sm font-medium text-gray-500">Approved This Month</h3>
          <p class="mt-2 text-3xl font-bold text-green-600">{{ data.stats.approved_this_month }}</p>
        </div>
      </div>

      <!-- Lists -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activity -->
        <div class="bg-white/70 p-6 rounded-lg shadow-lg backdrop-blur-xl">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Leave Requests</h3>
          <ul v-if="data.recentActivity.length" class="space-y-4">
            <li v-for="request in data.recentActivity" :key="request.id" class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div :class="statusColor(request.status)" class="h-2.5 w-2.5 rounded-full"></div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ request.user.name }}</p>
                <p class="text-sm text-gray-500 truncate">{{ request.leave_type.name }}</p>
              </div>
              <div class="text-sm text-gray-500">{{ new Date(request.start_date).toLocaleDateString() }}</div>
            </li>
          </ul>
          <p v-else class="text-gray-500">No recent activity.</p>
        </div>

        <!-- Upcoming Leaves -->
        <div class="bg-white/70 p-6 rounded-lg shadow-lg backdrop-blur-xl">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Leaves (Next 7 Days)</h3>
          <ul v-if="data.upcomingLeaves.length" class="space-y-4">
            <li v-for="leave in data.upcomingLeaves" :key="leave.id" class="flex items-center space-x-3">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ leave.user.name }}</p>
                <p class="text-sm text-gray-500 truncate">Starting: {{ new Date(leave.start_date).toLocaleDateString() }}</p>
              </div>
            </li>
          </ul>
          <p v-else class="text-gray-500">No upcoming leaves in the next 7 days.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
  });

  const { data, pending, error } = await useAsyncData('dashboard-data', async () => {
    const [stats, recentActivity, upcomingLeaves] = await Promise.all([
      useApi('/admin/dashboard/stats').then(res => res.data.value.data),
      useApi('/admin/dashboard/recent-activity').then(res => res.data.value.data),
      useApi('/admin/dashboard/upcoming-leaves').then(res => res.data.value.data),
    ])
    return { stats, recentActivity, upcomingLeaves }
  });

  const statusColor = (status) => {
    switch (status) {
      case 'approved': return 'bg-green-500';
      case 'rejected': return 'bg-red-500';
      case 'pending': return 'bg-amber-500';
      default: return 'bg-gray-500';
    }
  };

</script>