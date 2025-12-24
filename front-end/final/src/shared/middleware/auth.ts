import { useAuthStore } from '~/stores/auth';

export default defineVueRouteMiddleware((to) => {
  const authStore = useAuthStore();
  const { authenticated } = storeToRefs(authStore); // Make `authenticated` reactive

  const token = useCookie('token'); // Read token from cookies

  if (token.value) {
    authenticated.value = true; // Set auth state
  } else {
    authenticated.value = false;
  }

  // If authenticated and trying to visit login page, redirect to home
  if (authenticated.value && to?.name === 'auth-login') {
    return navigateTo('/');
  }

  // If not authenticated and trying to access a protected route
  if (!authenticated.value && to?.name !== 'auth-login') {
    return navigateTo('/auth/login');
  }
});
