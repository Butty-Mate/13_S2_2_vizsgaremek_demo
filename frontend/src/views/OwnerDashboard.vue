<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCampingStore } from '../stores/camping';
import { useBookingStore } from '../stores/booking';
import api from '../services/api';

const router = useRouter();
const campingStore = useCampingStore();
const bookingStore = useBookingStore();

const myCampings = ref([]);
const allBookings = ref([]);
const loading = ref(true);
const selectedCampingId = ref(null);

onMounted(async () => {
  await loadDashboardData();
});

const loadDashboardData = async () => {
  try {
    loading.value = true;
    // Load owner's campings
    const campingsRes = await campingStore.fetchCampings();
    myCampings.value = campingStore.campings;
    
    // Load all bookings
    const bookingsRes = await api.getBookings();
    allBookings.value = bookingsRes.data.data || bookingsRes.data;
  } catch (error) {
    console.error('Error loading dashboard:', error);
  } finally {
    loading.value = false;
  }
};

const filteredBookings = () => {
  if (!selectedCampingId.value) return allBookings.value;
  return allBookings.value.filter(b => b.camping_id === selectedCampingId.value);
};

const updateBookingStatus = async (bookingId, status) => {
  try {
    await api.updateBooking(bookingId, { status });
    alert('Booking status updated!');
    await loadDashboardData();
  } catch (error) {
    alert('Failed to update booking status');
  }
};

const deleteCamping = async (id) => {
  if (!confirm('Are you sure you want to delete this camping? All spots and bookings will be removed.')) return;
  
  try {
    await campingStore.deleteCamping(id);
    alert('Camping deleted successfully!');
    await loadDashboardData();
  } catch (error) {
    alert('Failed to delete camping');
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
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-4xl font-bold">Owner Dashboard</h1>
      <button @click="router.push('/create-camping')" class="btn-primary">
        + Add New Camping
      </button>
    </div>

    <div v-if="loading" class="text-center py-12">
      <p class="text-xl">Loading...</p>
    </div>

    <div v-else>
      <!-- Statistics -->
      <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="card">
          <p class="text-gray-600 text-sm mb-1">Total Campings</p>
          <p class="text-3xl font-bold">{{ myCampings.length }}</p>
        </div>
        <div class="card">
          <p class="text-gray-600 text-sm mb-1">Total Bookings</p>
          <p class="text-3xl font-bold">{{ allBookings.length }}</p>
        </div>
        <div class="card">
          <p class="text-gray-600 text-sm mb-1">Pending Bookings</p>
          <p class="text-3xl font-bold text-yellow-600">
            {{ allBookings.filter(b => b.status === 'pending').length }}
          </p>
        </div>
      </div>

      <!-- My Campings -->
      <div class="card mb-8">
        <h2 class="text-2xl font-bold mb-6">My Campings</h2>
        
        <div v-if="myCampings.length" class="space-y-4">
          <div v-for="camping in myCampings" :key="camping.id" class="border rounded p-4 bg-gray-50">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold">{{ camping.camping_name }}</h3>
                <p class="text-gray-600">{{ camping.location?.city }}, {{ camping.location?.county }}</p>
                <p class="text-sm text-gray-500 mt-2">
                  {{ camping.spots?.length || 0 }} spots | 
                  {{ camping.spots?.filter(s => s.is_available).length || 0 }} available
                </p>
              </div>
              <div class="flex gap-2">
                <button 
                  @click="router.push({ name: 'CampingDetail', params: { id: camping.id }})"
                  class="btn-primary text-sm"
                >
                  View
                </button>
                <button 
                  @click="deleteCamping(camping.id)"
                  class="btn-secondary text-sm bg-red-100 hover:bg-red-200 text-red-700"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No campings yet. Create your first camping!
        </div>
      </div>

      <!-- Bookings Management -->
      <div class="card">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Bookings Management</h2>
          <select v-model="selectedCampingId" class="input-field max-w-xs">
            <option :value="null">All Campings</option>
            <option v-for="camping in myCampings" :key="camping.id" :value="camping.id">
              {{ camping.camping_name }}
            </option>
          </select>
        </div>

        <div v-if="filteredBookings().length" class="space-y-4">
          <div v-for="booking in filteredBookings()" :key="booking.id" class="border rounded p-4 bg-gray-50">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-bold">{{ booking.camping?.camping_name }}</h4>
                <p class="text-sm text-gray-600">Spot: {{ booking.camping_spot?.name }}</p>
                <p class="text-sm text-gray-600">Guest: {{ booking.user?.name }} ({{ booking.user?.email }})</p>
                <p class="text-sm text-gray-600">
                  📅 {{ booking.arrival_date }} → {{ booking.departure_date }}
                </p>
              </div>
              <div class="text-right">
                <span :class="['px-3 py-1 rounded-full text-xs font-semibold mb-2 inline-block', getStatusColor(booking.status)]">
                  {{ booking.status }}
                </span>
                <div class="flex gap-2 mt-2">
                  <button 
                    v-if="booking.status === 'pending'"
                    @click="updateBookingStatus(booking.id, 'confirmed')"
                    class="btn-primary text-xs px-3 py-1"
                  >
                    Confirm
                  </button>
                  <button 
                    v-if="booking.status === 'confirmed'"
                    @click="updateBookingStatus(booking.id, 'checked_in')"
                    class="btn-primary text-xs px-3 py-1"
                  >
                    Check In
                  </button>
                  <button 
                    v-if="booking.status === 'checked_in'"
                    @click="updateBookingStatus(booking.id, 'checked_out')"
                    class="btn-primary text-xs px-3 py-1"
                  >
                    Check Out
                  </button>
                  <button 
                    v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                    @click="updateBookingStatus(booking.id, 'cancelled')"
                    class="btn-secondary text-xs px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No bookings found.
        </div>
      </div>
    </div>
  </div>
</template>
