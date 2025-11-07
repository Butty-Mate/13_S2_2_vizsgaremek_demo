import { defineStore } from 'pinia';
import api from '../services/api';

export const useBookingStore = defineStore('booking', {
  state: () => ({
    bookings: [],
    currentBooking: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchBookings(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.getBookings(params);
        this.bookings = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch bookings';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createBooking(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.createBooking(data);
        this.bookings.push(response.data);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create booking';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateBookingStatus(id, status) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.updateBooking(id, { status });
        const index = this.bookings.findIndex(b => b.id === id);
        if (index !== -1) {
          this.bookings[index] = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update booking';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async cancelBooking(id) {
      this.loading = true;
      this.error = null;
      try {
        await api.updateBooking(id, { status: 'cancelled' });
        const index = this.bookings.findIndex(b => b.id === id);
        if (index !== -1) {
          this.bookings[index].status = 'cancelled';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to cancel booking';
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
