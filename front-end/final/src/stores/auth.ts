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
      const auth = await apiPost('/login', { username, password });

      if (auth.success) {

        const token = auth.data.token;
        console.log(token);
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
