import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    isAuthenticated: !!localStorage.getItem('auth_token')
  }),

  getters: {
    isOwner: (state) => state.user?.role === 'owner',
    isGuest: (state) => state.user?.role === 'guest',
    userName: (state) => state.user?.name || 'Guest'
  },

  actions: {
    async register(data) {
      try {
        const response = await api.register(data);
        this.setAuth(response.data);
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async login(data) {
      try {
        const response = await api.login(data);
        this.setAuth(response.data);
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async logout() {
      try {
        await api.logout();
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.clearAuth();
      }
    },

    async fetchUser() {
      try {
        const response = await api.getMe();
        this.user = response.data;
        localStorage.setItem('user', JSON.stringify(response.data));
      } catch (error) {
        this.clearAuth();
        throw error;
      }
    },

    setAuth(data) {
      this.user = data.user;
      this.token = data.token;
      this.isAuthenticated = true;
      localStorage.setItem('user', JSON.stringify(data.user));
      localStorage.setItem('auth_token', data.token);
    },

    clearAuth() {
      this.user = null;
      this.token = null;
      this.isAuthenticated = false;
      localStorage.removeItem('user');
      localStorage.removeItem('auth_token');
    }
  }
});
