import { defineStore } from 'pinia';
import api from '../services/api';

export const useCampingStore = defineStore('camping', {
  state: () => ({
    campings: [],
    currentCamping: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchCampings(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.getCampings(params);
        this.campings = response.data.data || response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch campings';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchCamping(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.getCamping(id);
        this.currentCamping = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch camping';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createCamping(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.createCamping(data);
        this.campings.push(response.data);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create camping';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateCamping(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.updateCamping(id, data);
        const index = this.campings.findIndex(c => c.id === id);
        if (index !== -1) {
          this.campings[index] = response.data;
        }
        if (this.currentCamping?.id === id) {
          this.currentCamping = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update camping';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteCamping(id) {
      this.loading = true;
      this.error = null;
      try {
        await api.deleteCamping(id);
        this.campings = this.campings.filter(c => c.id !== id);
        if (this.currentCamping?.id === id) {
          this.currentCamping = null;
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete camping';
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
