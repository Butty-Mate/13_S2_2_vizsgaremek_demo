<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: ''
});

const error = ref('');
const loading = ref(false);

const handleLogin = async () => {
  error.value = '';
  loading.value = true;

  try {
    await authStore.login(form.value);
    router.push({ name: 'Home' });
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
    <div class="max-w-md w-full">
      <div class="card">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-8">
          Login to CampSite
        </h2>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Error Message -->
          <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ error }}
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="input-field"
              placeholder="your@email.com"
            />
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="input-field"
              placeholder="••••••••"
            />
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Logging in...' : 'Login' }}
          </button>
        </form>

        <p class="mt-6 text-center text-gray-600">
          Don't have an account?
          <router-link to="/register" class="text-green-600 hover:text-green-700 font-semibold">
            Sign up
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Additional styles if needed */
</style>
