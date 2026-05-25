// stores/auth.ts
import { defineStore } from 'pinia';
import { apiPost } from '../services/api/request'; // Mock user list

interface LoginPayload {
  username: string;
  password: string;
}

// interface User {
//   id: number;
//   username: string;
//   password: string;
//   token?: string;
// }

export const useAuthStore = defineStore('auth', {
  state: () => ({
    authenticated: false,
    loading: false,
  }),

  actions: {
    async authenticateUser({ username, password }: LoginPayload) {
      this.loading = true;

      // Simulate API authentication using mock data
      // const user = users.find(
      //   (u: User) => u.username === username && u.password === password
      // );
      
      let auth: { success: boolean; data?: any } = { success: false };
      let retryCount = 0;
      const maxRetries = 2;
      
      while (retryCount <= maxRetries) {
        try {
          auth = await apiPost('/login', { username, password });
          break;
        } catch (error: any) {
          if (retryCount === maxRetries) {
            auth = { success: false };
            break;
          }
          retryCount++;
          await new Promise(resolve => setTimeout(resolve, 1000));
        }
      }

      if (auth.success) {

        const token = auth.data.token;
        localStorage.setItem('token', token); // Store token in localStorage
        this.authenticated = true;
        this.loading = false;
        return { authenticated: true };
      } else {
        localStorage.removeItem('token');
        this.authenticated = false;
        this.loading = false;
        return { authenticated: false };
      }
    },

    logUserOut() {
      localStorage.removeItem('token');
      this.authenticated = false;
    },

    async logoutUser() {
      try {
        await apiPost('/logout');
        localStorage.clear();
        sessionStorage.clear();
        this.authenticated = false;
      } catch (error) {
        // Force logout even if API fails
        localStorage.clear();
        sessionStorage.clear();
        this.authenticated = false;
      }
    },

    // generateToken(user: User): string {
    //   // Simulated token generation
    //   return `Bearer-${user.id}-${user.username}`;
    // },

    checkAuthOnStartup() {
      const token = localStorage.getItem('token');
      this.authenticated = !!token;
    }
  }
});
