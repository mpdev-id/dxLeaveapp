
export default defineNuxtRouteMiddleware((to, from) => {
  const token = useCookie('auth_token');

  // If user is not authenticated
  if (!token.value) {
    // Allow access to login page
    if (to.path !== '/login') {
      return navigateTo('/login');
    }
  } else {
    // If user is authenticated and tries to access login page, redirect to dashboard
    if (to.path === '/login') {
      return navigateTo('/admin/dashboard');
    }
  }
});
