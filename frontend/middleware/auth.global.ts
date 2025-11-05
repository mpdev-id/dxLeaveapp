
export default defineNuxtRouteMiddleware((to, from) => {
  if (to.path.startsWith('/admin')) {
    const token = useCookie('auth_token');
    if (!token.value) {
      return navigateTo('/login');
    }
  }
});
