
<template>
  <div class="flex h-screen bg-gray-100">
    <aside class="w-64 bg-gray-800 text-white">
      <div class="p-4">
        <h1 class="text-2xl font-bold">Admin</h1>
      </div>
      <nav class="mt-10">
        <nuxt-link to="/admin/dashboard" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Dashboard</nuxt-link>
        <nuxt-link to="/admin/users" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Users</nuxt-link>
        <nuxt-link to="/admin/departments" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Departments</nuxt-link>
        <nuxt-link to="/admin/leave-types" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Leave Types</nuxt-link>
        <nuxt-link to="/admin/public-holidays" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Public Holidays</nuxt-link>
        <nuxt-link to="/admin/employee-entitlements" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Employee Entitlements</nuxt-link>
        <nuxt-link to="/admin/leave-requests" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Leave Requests</nuxt-link>
      </nav>
    </aside>
    <main class="flex-1 p-10">
      <slot />
    </main>
  </div>
</template>

<script setup>
</script>

<style>
.router-link-active {
  @apply bg-gray-700;
}
</style>
