<script setup>
import { ref, onMounted } from 'vue';
import { useBookingStore } from '../stores/booking';
import { useRouter } from 'vue-router';

const bookingStore = useBookingStore();
const router = useRouter();
const loading = ref(true);

onMounted(async () => {
  await loadBookings();
});

const loadBookings = async () => {
  try {
    loading.value = true;
    await bookingStore.fetchBookings();
  } catch (error) {
    console.error('Error loading bookings:', error);
  } finally {
    loading.value = false;
  }
};

const cancelBooking = async (id) => {
  if (!confirm('Are you sure you want to cancel this booking?')) return;
  
  try {
    await bookingStore.cancelBooking(id);
    alert('Booking cancelled successfully');
    await loadBookings();
  } catch (error) {
    alert('Failed to cancel booking');
  }
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    checked_in: 'bg-blue-100 text-blue-800',
    checked_out: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">My Bookings</h1>

    <div v-if="loading" class="text-center py-12">
      <p class="text-xl">Loading bookings...</p>
    </div>

    <div v-else-if="bookingStore.bookings.length" class="space-y-4">
      <div v-for="booking in bookingStore.bookings" :key="booking.id" class="card">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <h3 class="text-xl font-bold mb-2">{{ booking.camping?.camping_name }}</h3>
            <p class="text-gray-600 mb-1">
              📍 {{ booking.camping?.location?.city }}, {{ booking.camping?.location?.county }}
            </p>
            <p class="text-gray-700 mb-2">
              <strong>Spot:</strong> {{ booking.camping_spot?.name }} ({{ booking.camping_spot?.type }})
            </p>
            <p class="text-gray-700 mb-2">
              📅 <strong>Arrival:</strong> {{ booking.arrival_date }} | 
              <strong>Departure:</strong> {{ booking.departure_date }}
            </p>
            <p class="text-gray-700">
              💰 <strong>Price:</strong> {{ booking.camping_spot?.price_per_night }} Ft/night
            </p>
          </div>
          <div class="text-right">
            <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getStatusColor(booking.status)]">
              {{ booking.status.toUpperCase() }}
            </span>
            <div class="mt-4 space-y-2">
              <button 
                v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                @click="cancelBooking(booking.id)"
                class="btn-secondary text-sm w-full"
              >
                Cancel
              </button>
              <button 
                @click="router.push({ name: 'CampingDetail', params: { id: booking.camping_id }})"
                class="btn-primary text-sm w-full"
              >
                View Camping
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-xl text-gray-600 mb-4">You don't have any bookings yet.</p>
      <button @click="router.push('/campings')" class="btn-primary">
        Browse Campings
      </button>
    </div>
  </div>
</template>
