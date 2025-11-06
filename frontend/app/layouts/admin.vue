
<template>
  <div class="relative min-h-screen md:flex animated-gradient-bg">
    <!-- Mobile Menu Button -->
    <div class="bg-white/80 backdrop-blur-sm text-gray-800 flex justify-between md:hidden sticky top-0 z-10">
      <a href="#" class="block p-4 text-gray-600 font-bold">Admin</a>
      <button @click="isSidebarOpen = !isSidebarOpen" class="p-4 focus:outline-none focus:bg-gray-100">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>

    <!-- Sidebar -->
    <aside 
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="bg-white/80 backdrop-blur-sm text-gray-800 w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform md:relative md:translate-x-0 transition duration-200 ease-in-out z-20 border-r border-gray-200/50 
      rounded-lg">
      
      <a href="#" class="text-gray-800 text-2xl font-extrabold px-4">Admin Panel</a>
      
      <nav class="space-y-2">
        <nuxt-link to="/admin/dashboard" class="nav-link"><Icon name="heroicons:home" class="mr-3" />Dashboard</nuxt-link>
        <nuxt-link to="/admin/users" class="nav-link"><Icon name="heroicons:users" class="mr-3" />Users</nuxt-link>
        <nuxt-link to="/admin/departments" class="nav-link"><Icon name="heroicons:building-office-2" class="mr-3" />Departments</nuxt-link>
        <nuxt-link to="/admin/leave-types" class="nav-link"><Icon name="heroicons:document-text" class="mr-3" />Leave Types</nuxt-link>
        <nuxt-link to="/admin/public-holidays" class="nav-link"><Icon name="heroicons:calendar-days" class="mr-3" />Public Holidays</nuxt-link>
        <nuxt-link to="/admin/employee-entitlements" class="nav-link"><Icon name="heroicons:clipboard-document-list" class="mr-3" />Entitlements</nuxt-link>
        <nuxt-link to="/admin/leave-requests" class="nav-link"><Icon name="heroicons:paper-airplane" class="mr-3" />Leave Requests</nuxt-link>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6 sm:p-10 bg-transparent">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const isSidebarOpen = ref(false)
</script>

<style>
.animated-gradient-bg {
  background: linear-gradient(45deg, #a8c0ff, #c2e9fb, #fbc2eb, #a8c0ff);
  background-size: 400% 400%;
  animation: gradientBG 15s ease infinite;
}

@keyframes gradientBG {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.nav-link {
  @apply block py-2.5 px-4 rounded transition duration-200 text-gray-600 hover:bg-white/50 hover:text-gray-900;
}
.router-link-active {
  @apply bg-white/60 text-slate-800 font-semibold;
}
.router-link-active:hover {
  @apply bg-white/90;
}
</style>
