<script setup>
import { useAuthStore } from './stores/auth';
import NavBar from './components/NavBar.vue';

const authStore = useAuthStore();
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <NavBar />
    <main class="flex-1">
      <router-view />
    </main>
    <footer class="bg-gray-800 text-white py-6 mt-auto">
      <div class="container mx-auto px-4 text-center">
        <p>&copy; 2025 CampSite - Camping Booking System</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Additional styles if needed */
</style>
