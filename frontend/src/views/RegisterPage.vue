<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'guest',
  birth_date: '',
  phone_number: ''
});

const error = ref('');
const loading = ref(false);

const handleRegister = async () => {
  error.value = '';
  
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }

  loading.value = true;

  try {
    await authStore.register(form.value);
    router.push({ name: 'Home' });
  } catch (err) {
    error.value = err.response?.data?.message || 'Registration failed. Please try again.';
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat();
      error.value = errors.join(', ');
    }
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
          Create Account
        </h2>

        <form @submit.prevent="handleRegister" class="space-y-4">
          <!-- Error Message -->
          <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ error }}
          </div>

          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              Full Name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="input-field"
              placeholder="John Doe"
            />
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

          <!-- Role -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              I am a...
            </label>
            <div class="flex space-x-4">
              <label class="flex items-center">
                <input
                  v-model="form.role"
                  type="radio"
                  value="guest"
                  class="mr-2"
                />
                <span>Guest (Book camping spots)</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.role"
                  type="radio"
                  value="owner"
                  class="mr-2"
                />
                <span>Owner (Manage campings)</span>
              </label>
            </div>
          </div>

          <!-- Phone -->
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
              Phone Number (Optional)
            </label>
            <input
              id="phone"
              v-model="form.phone_number"
              type="tel"
              class="input-field"
              placeholder="+36 30 123 4567"
            />
          </div>

          <!-- Birth Date -->
          <div>
            <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
              Birth Date (Optional)
            </label>
            <input
              id="birth_date"
              v-model="form.birth_date"
              type="date"
              class="input-field"
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
              minlength="8"
              class="input-field"
              placeholder="••••••••"
            />
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
              Confirm Password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
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
            {{ loading ? 'Creating Account...' : 'Sign Up' }}
          </button>
        </form>

        <p class="mt-6 text-center text-gray-600">
          Already have an account?
          <router-link to="/login" class="text-green-600 hover:text-green-700 font-semibold">
            Login
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Additional styles if needed */
</style>
