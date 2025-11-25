<template>
  <div class="max-w-4xl mx-auto p-6">
    <div class="card">
      <h2 class="text-2xl font-bold mb-6 text-center">🔐 Auth System Demo</h2>
      
      <!-- Current Status -->
      <div class="mb-6 p-4 bg-blue-50 rounded-lg">
        <h3 class="font-semibold mb-2">Current Status:</h3>
        <div class="space-y-1 text-sm">
          <p><strong>Authenticated:</strong> {{ authStore.isAuthenticated ? '✅ Yes' : '❌ No' }}</p>
          <p v-if="authStore.user"><strong>User:</strong> {{ authStore.user.name }} ({{ authStore.user.email }})</p>
          <p v-if="authStore.user"><strong>Role:</strong> {{ authStore.user.role }}</p>
          <p v-if="authStore.token"><strong>Token:</strong> {{ authStore.token.substring(0, 20) }}...</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Login Demo -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-semibold mb-3">Quick Login</h3>
          <form @submit.prevent="quickLogin" class="space-y-3">
            <input
              v-model="loginForm.email"
              type="email"
              placeholder="Email"
              class="input-field"
              required
            />
            <input
              v-model="loginForm.password"
              type="password"
              placeholder="Password"
              class="input-field"
              required
            />
            <button type="submit" class="btn-primary w-full" :disabled="loading">
              {{ loading ? 'Logging in...' : 'Login' }}
            </button>
          </form>
          <div class="mt-3 text-xs text-gray-500">
            <p>Test accounts (if seeded):</p>
            <p>• admin@test.com / password</p>
            <p>• owner@test.com / password</p>
            <p>• guest@test.com / password</p>
          </div>
        </div>

        <!-- Register Demo -->
        <div class="p-4 border rounded-lg">
          <h3 class="font-semibold mb-3">Quick Register</h3>
          <form @submit.prevent="quickRegister" class="space-y-3">
            <input
              v-model="registerForm.name"
              type="text"
              placeholder="Full Name"
              class="input-field"
              required
            />
            <input
              v-model="registerForm.email"
              type="email"
              placeholder="Email"
              class="input-field"
              required
            />
            <input
              v-model="registerForm.password"
              type="password"
              placeholder="Password (min 8 chars)"
              class="input-field"
              required
              minlength="8"
            />
            <select v-model="registerForm.role" class="input-field">
              <option value="guest">Guest (Book campings)</option>
              <option value="owner">Owner (Manage campings)</option>
            </select>
            <button type="submit" class="btn-secondary w-full" :disabled="loading">
              {{ loading ? 'Registering...' : 'Register' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Messages -->
      <div v-if="successMessage" class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        ✅ {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        ❌ {{ errorMessage }}
      </div>

      <!-- Actions -->
      <div v-if="authStore.isAuthenticated" class="mt-6 flex gap-3">
        <button @click="testGetMe" class="btn-outline flex-1">
          Test /api/me
        </button>
        <button @click="handleLogout" class="btn-outline flex-1 text-red-600 border-red-300 hover:border-red-600">
          Logout
        </button>
      </div>

      <!-- API Response -->
      <div v-if="apiResponse" class="mt-6">
        <h3 class="font-semibold mb-2">Last API Response:</h3>
        <pre class="bg-gray-800 text-green-400 p-4 rounded-lg overflow-x-auto text-xs">{{ apiResponse }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';

const authStore = useAuthStore();

const loginForm = ref({
  email: 'guest@test.com',
  password: 'password'
});

const registerForm = ref({
  name: 'Test User',
  email: 'newuser@test.com',
  password: 'password123',
  password_confirmation: 'password123',
  role: 'guest'
});

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const apiResponse = ref(null);

const clearMessages = () => {
  successMessage.value = '';
  errorMessage.value = '';
};

const quickLogin = async () => {
  clearMessages();
  loading.value = true;
  
  try {
    const response = await authStore.login(loginForm.value);
    successMessage.value = `Successfully logged in as ${response.user.name}!`;
    apiResponse.value = JSON.stringify(response, null, 2);
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login failed';
    apiResponse.value = JSON.stringify(error.response?.data, null, 2);
  } finally {
    loading.value = false;
  }
};

const quickRegister = async () => {
  clearMessages();
  loading.value = true;
  
  // Auto-fill password confirmation
  registerForm.value.password_confirmation = registerForm.value.password;
  
  try {
    const response = await authStore.register(registerForm.value);
    successMessage.value = `Account created for ${response.user.name}!`;
    apiResponse.value = JSON.stringify(response, null, 2);
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Registration failed';
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat();
      errorMessage.value = errors.join(', ');
    }
    apiResponse.value = JSON.stringify(error.response?.data, null, 2);
  } finally {
    loading.value = false;
  }
};

const testGetMe = async () => {
  clearMessages();
  loading.value = true;
  
  try {
    const response = await api.getMe();
    successMessage.value = 'Successfully fetched user data!';
    apiResponse.value = JSON.stringify(response.data, null, 2);
  } catch (error) {
    errorMessage.value = 'Failed to fetch user data';
    apiResponse.value = JSON.stringify(error.response?.data, null, 2);
  } finally {
    loading.value = false;
  }
};

const handleLogout = async () => {
  clearMessages();
  try {
    await authStore.logout();
    successMessage.value = 'Successfully logged out!';
    apiResponse.value = null;
  } catch (error) {
    errorMessage.value = 'Logout failed';
  }
};
</script>
