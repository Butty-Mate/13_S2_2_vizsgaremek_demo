<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'Home' });
};
</script>

<template>
  <nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2">
          <span class="text-3xl">🏕️</span>
          <span class="text-2xl font-bold text-green-600">CampSite</span>
        </router-link>

        <!-- Navigation Links -->
        <div class="flex items-center space-x-6">
          <router-link to="/" class="text-gray-700 hover:text-green-600 font-medium transition-colors">
            Home
          </router-link>
          <router-link to="/campings" class="text-gray-700 hover:text-green-600 font-medium transition-colors">
            Browse Campings
          </router-link>

          <!-- Authenticated User -->
          <template v-if="authStore.isAuthenticated">
            <!-- Guest Links -->
            <router-link v-if="authStore.isGuest" to="/my-bookings" 
                         class="text-gray-700 hover:text-green-600 font-medium transition-colors">
              My Bookings
            </router-link>

            <!-- Owner Links -->
            <router-link v-if="authStore.isOwner" to="/owner/dashboard" 
                         class="text-gray-700 hover:text-green-600 font-medium transition-colors">
              Dashboard
            </router-link>
            <router-link v-if="authStore.isOwner" to="/owner/campings/create" 
                         class="text-gray-700 hover:text-green-600 font-medium transition-colors">
              Add Camping
            </router-link>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
              <span class="text-gray-700">{{ authStore.userName }}</span>
              <button @click="handleLogout" class="btn-outline py-1 px-4 text-sm">
                Logout
              </button>
            </div>
          </template>

          <!-- Guest User -->
          <template v-else>
            <router-link to="/login" class="text-gray-700 hover:text-green-600 font-medium transition-colors">
              Login
            </router-link>
            <router-link to="/register" class="btn-primary py-2 px-4">
              Sign Up
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* Additional styles if needed */
</style>
