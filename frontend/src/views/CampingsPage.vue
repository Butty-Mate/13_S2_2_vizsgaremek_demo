<script setup>
import { ref, onMounted } from 'vue';
import { useCampingStore } from '../stores/camping';
import { useRouter } from 'vue-router';

const campingStore = useCampingStore();
const router = useRouter();

const searchQuery = ref('');
const cityFilter = ref('');

onMounted(async () => {
  await campingStore.fetchCampings();
});

const searchCampings = async () => {
  const params = {};
  if (searchQuery.value) params.search = searchQuery.value;
  if (cityFilter.value) params.city = cityFilter.value;
  await campingStore.fetchCampings(params);
};
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Browse Campings</h1>

    <!-- Search and Filter -->
    <div class="card mb-8">
      <div class="grid md:grid-cols-3 gap-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by name..."
          class="input-field"
          @input="searchCampings"
        />
        <input
          v-model="cityFilter"
          type="text"
          placeholder="Filter by city..."
          class="input-field"
          @input="searchCampings"
        />
        <button @click="searchCampings" class="btn-primary">
          Search
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="campingStore.loading" class="text-center py-12">
      <p class="text-xl text-gray-600">Loading campings...</p>
    </div>

    <!-- Campings Grid -->
    <div v-else-if="campingStore.campings.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="camping in campingStore.campings"
        :key="camping.id"
        class="card cursor-pointer hover:scale-105 transition-transform overflow-hidden p-0"
        @click="router.push({ name: 'CampingDetail', params: { id: camping.id } })"
      >
        <img 
          v-if="camping.image_url" 
          :src="camping.image_url" 
          :alt="camping.camping_name"
          class="w-full h-48 object-cover"
        />
        <div class="p-6">
          <h3 class="text-xl font-bold mb-2">{{ camping.camping_name }}</h3>
          <p class="text-gray-600 mb-4">
            📍 {{ camping.location?.city }}, {{ camping.location?.county }}
          </p>
          <p class="text-gray-700 mb-4 line-clamp-2">{{ camping.description }}</p>
          <div class="flex justify-between items-center">
            <span class="text-green-600 font-semibold">
              {{ camping.spots?.length || 0 }} spots available
            </span>
            <button class="btn-primary py-1 px-4 text-sm">
              View Details
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <p class="text-xl text-gray-600">No campings found. Try adjusting your search.</p>
    </div>
  </div>
</template>

<style scoped>
/* Additional styles if needed */
</style>
