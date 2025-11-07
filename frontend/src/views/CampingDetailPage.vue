<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const camping = ref(null);
const spots = ref([]);
const loading = ref(true);
const selectedSpot = ref(null);
const showBookingModal = ref(false);

// Booking form
const bookingForm = ref({
  arrival_date: '',
  departure_date: ''
});

onMounted(async () => {
  await loadCampingDetails();
});

const loadCampingDetails = async () => {
  try {
    loading.value = true;
    const [campingRes, spotsRes] = await Promise.all([
      api.getCamping(route.params.id),
      api.getCampingSpots({ camping_id: route.params.id })
    ]);
    camping.value = campingRes.data;
    spots.value = spotsRes.data.data || spotsRes.data;
  } catch (error) {
    console.error('Error loading camping:', error);
    alert('Failed to load camping details');
  } finally {
    loading.value = false;
  }
};

// Grid létrehozása
const gridRows = computed(() => {
  if (!spots.value.length) return [];
  const maxRow = Math.max(...spots.value.map(s => s.row));
  const rows = [];
  
  for (let r = 1; r <= maxRow; r++) {
    const rowSpots = spots.value.filter(s => s.row === r).sort((a, b) => a.column - b.column);
    rows.push(rowSpots);
  }
  return rows;
});

const selectSpot = (spot) => {
  if (!spot.is_available) {
    alert('This spot is not available');
    return;
  }
  if (!authStore.isAuthenticated) {
    alert('Please login to book a spot');
    router.push('/login');
    return;
  }
  selectedSpot.value = spot;
  showBookingModal.value = true;
};

const createBooking = async () => {
  if (!bookingForm.value.arrival_date || !bookingForm.value.departure_date) {
    alert('Please select arrival and departure dates');
    return;
  }
  
  try {
    await api.createBooking({
      camping_id: camping.value.id,
      camping_spot_id: selectedSpot.value.id,
      arrival_date: bookingForm.value.arrival_date,
      departure_date: bookingForm.value.departure_date,
      status: 'pending'
    });
    
    alert('Booking created successfully!');
    showBookingModal.value = false;
    selectedSpot.value = null;
    bookingForm.value = { arrival_date: '', departure_date: '' };
    await loadCampingDetails();
  } catch (error) {
    console.error('Booking error:', error);
    alert(error.response?.data?.message || 'Failed to create booking');
  }
};

const getSpotClass = (spot) => {
  const baseClass = 'p-4 border-2 rounded-lg text-center cursor-pointer transition-all ';
  if (!spot.is_available) return baseClass + 'bg-red-100 border-red-400 cursor-not-allowed';
  if (spot.type === 'vip') return baseClass + 'bg-purple-100 border-purple-400 hover:scale-105';
  if (spot.type === 'premium') return baseClass + 'bg-blue-100 border-blue-400 hover:scale-105';
  return baseClass + 'bg-green-100 border-green-400 hover:scale-105';
};
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <button @click="router.back()" class="btn-secondary mb-6">← Back</button>

    <div v-if="loading" class="text-center py-12">
      <p class="text-xl">Loading...</p>
    </div>

    <div v-else-if="camping">
      <!-- Camping Info -->
      <div class="card overflow-hidden p-0 mb-8">
        <img 
          v-if="camping.image_url" 
          :src="camping.image_url" 
          :alt="camping.camping_name"
          class="w-full h-64 object-cover"
        />
        <div class="p-6">
          <h1 class="text-4xl font-bold mb-4">{{ camping.camping_name }}</h1>
          <p class="text-xl text-gray-600 mb-2">
            📍 {{ camping.location?.city }}, {{ camping.location?.county }}
          </p>
          <p class="text-gray-700 mb-4">{{ camping.description }}</p>
          <div class="grid md:grid-cols-3 gap-4 mt-4">
            <div class="bg-gray-100 p-4 rounded">
              <p class="text-sm text-gray-600">Company</p>
              <p class="font-semibold">{{ camping.company_name || 'N/A' }}</p>
            </div>
            <div class="bg-gray-100 p-4 rounded">
              <p class="text-sm text-gray-600">Total Spots</p>
              <p class="font-semibold">{{ spots.length }}</p>
            </div>
            <div class="bg-gray-100 p-4 rounded">
              <p class="text-sm text-gray-600">Available</p>
              <p class="font-semibold text-green-600">{{ spots.filter(s => s.is_available).length }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Spots Grid -->
      <div class="card">
        <h2 class="text-2xl font-bold mb-6">Select Your Spot</h2>
        
        <!-- Legend -->
        <div class="flex gap-4 mb-6 flex-wrap">
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 bg-green-100 border-2 border-green-400 rounded"></div>
            <span class="text-sm">Standard</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 bg-blue-100 border-2 border-blue-400 rounded"></div>
            <span class="text-sm">Premium</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 bg-purple-100 border-2 border-purple-400 rounded"></div>
            <span class="text-sm">VIP</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 bg-red-100 border-2 border-red-400 rounded"></div>
            <span class="text-sm">Occupied</span>
          </div>
        </div>

        <!-- Grid View -->
        <div class="overflow-x-auto">
          <div v-for="(row, idx) in gridRows" :key="idx" class="flex gap-3 mb-3 justify-center">
            <div
              v-for="spot in row"
              :key="spot.id"
              :class="getSpotClass(spot)"
              @click="selectSpot(spot)"
              class="min-w-[120px]"
            >
              <p class="font-bold">{{ spot.name }}</p>
              <p class="text-sm">{{ spot.type }}</p>
              <p class="text-sm font-semibold">{{ spot.price_per_night }} Ft/night</p>
              <p class="text-xs">{{ spot.capacity }} guests</p>
              <p class="text-xs" v-if="!spot.is_available">❌ Occupied</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Modal -->
    <div v-if="showBookingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-2xl font-bold mb-4">Book {{ selectedSpot?.name }}</h3>
        
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">Type: <span class="font-semibold">{{ selectedSpot?.type }}</span></p>
          <p class="text-sm text-gray-600 mb-2">Price: <span class="font-semibold">{{ selectedSpot?.price_per_night }} Ft/night</span></p>
          <p class="text-sm text-gray-600 mb-2">Capacity: <span class="font-semibold">{{ selectedSpot?.capacity }} guests</span></p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Arrival Date</label>
          <input v-model="bookingForm.arrival_date" type="date" class="input-field" :min="new Date().toISOString().split('T')[0]" />
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium mb-2">Departure Date</label>
          <input v-model="bookingForm.departure_date" type="date" class="input-field" :min="bookingForm.arrival_date" />
        </div>

        <div class="flex gap-4">
          <button @click="createBooking" class="btn-primary flex-1">Confirm Booking</button>
          <button @click="showBookingModal = false; selectedSpot = null" class="btn-secondary flex-1">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>
