<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useCampingStore } from '../stores/camping';

const router = useRouter();
const campingStore = useCampingStore();

const step = ref(1);
const campingForm = ref({
  camping_name: '',
  description: '',
  company_name: '',
  tax_id: '',
  billing_address: '',
  location: {
    postcode: '',
    county: '',
    city: '',
    street: '',
    street_number: ''
  }
});

const spotsForm = ref({
  rows: 3,
  columns: 4,
  spots: []
});

const generateSpots = () => {
  spotsForm.value.spots = [];
  let counter = 1;
  for (let r = 1; r <= spotsForm.value.rows; r++) {
    for (let c = 1; c <= spotsForm.value.columns; c++) {
      spotsForm.value.spots.push({
        name: `Hely ${counter}`,
        row: r,
        column: c,
        type: 'standard',
        capacity: 2,
        price_per_night: 5000,
        is_available: true,
        description: '',
        tags: 'wifi,parking',
        services: 'electricity,water'
      });
      counter++;
    }
  }
  step.value = 2;
};

const submitCamping = async () => {
  try {
    const campingData = {
      ...campingForm.value,
      spots: spotsForm.value.spots
    };
    
    await campingStore.createCamping(campingData);
    alert('Camping created successfully!');
    router.push('/dashboard');
  } catch (error) {
    console.error('Error creating camping:', error);
    alert(error.response?.data?.message || 'Failed to create camping');
  }
};
</script>

<template>
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-4xl font-bold mb-8">Create New Camping</h1>

    <!-- Step 1: Camping Details -->
    <div v-if="step === 1" class="card">
      <h2 class="text-2xl font-bold mb-6">Step 1: Camping Information</h2>
      
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Camping Name *</label>
          <input v-model="campingForm.camping_name" type="text" class="input-field" required />
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Description</label>
          <textarea v-model="campingForm.description" class="input-field" rows="4"></textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-2">Company Name</label>
            <input v-model="campingForm.company_name" type="text" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Tax ID</label>
            <input v-model="campingForm.tax_id" type="text" class="input-field" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Billing Address</label>
          <input v-model="campingForm.billing_address" type="text" class="input-field" />
        </div>

        <h3 class="text-xl font-bold mt-6 mb-4">Location Details</h3>

        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium mb-2">Postcode *</label>
            <input v-model="campingForm.location.postcode" type="text" class="input-field" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">County *</label>
            <input v-model="campingForm.location.county" type="text" class="input-field" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">City *</label>
            <input v-model="campingForm.location.city" type="text" class="input-field" required />
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-2">Street *</label>
            <input v-model="campingForm.location.street" type="text" class="input-field" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Street Number *</label>
            <input v-model="campingForm.location.street_number" type="text" class="input-field" required />
          </div>
        </div>

        <h3 class="text-xl font-bold mt-6 mb-4">Grid Layout</h3>
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-2">Number of Rows *</label>
            <input v-model.number="spotsForm.rows" type="number" min="1" max="10" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Number of Columns *</label>
            <input v-model.number="spotsForm.columns" type="number" min="1" max="10" class="input-field" />
          </div>
        </div>

        <button @click="generateSpots" class="btn-primary w-full mt-6">
          Next: Configure Spots ({{ spotsForm.rows }} × {{ spotsForm.columns }} = {{ spotsForm.rows * spotsForm.columns }} spots)
        </button>
      </div>
    </div>

    <!-- Step 2: Spots Configuration -->
    <div v-if="step === 2" class="card">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Step 2: Configure Spots</h2>
        <button @click="step = 1" class="btn-secondary">← Back</button>
      </div>

      <div class="mb-6 overflow-x-auto">
        <p class="text-sm text-gray-600 mb-4">Preview Grid: {{ spotsForm.rows }} rows × {{ spotsForm.columns }} columns</p>
        <div class="space-y-2">
          <div v-for="r in spotsForm.rows" :key="r" class="flex gap-2 justify-center">
            <div 
              v-for="c in spotsForm.columns" 
              :key="`${r}-${c}`"
              class="w-20 h-20 bg-green-100 border-2 border-green-400 rounded flex items-center justify-center text-xs"
            >
              R{{ r }}-C{{ c }}
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6 max-h-96 overflow-y-auto">
        <div v-for="(spot, index) in spotsForm.spots" :key="index" class="border rounded p-4 bg-gray-50">
          <h4 class="font-bold mb-3">{{ spot.name }} (Row {{ spot.row }}, Col {{ spot.column }})</h4>
          
          <div class="grid md:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs mb-1">Type</label>
              <select v-model="spot.type" class="input-field text-sm">
                <option value="standard">Standard</option>
                <option value="premium">Premium</option>
                <option value="vip">VIP</option>
              </select>
            </div>
            <div>
              <label class="block text-xs mb-1">Price/Night (Ft)</label>
              <input v-model.number="spot.price_per_night" type="number" class="input-field text-sm" />
            </div>
            <div>
              <label class="block text-xs mb-1">Capacity</label>
              <input v-model.number="spot.capacity" type="number" min="1" class="input-field text-sm" />
            </div>
          </div>
        </div>
      </div>

      <button @click="submitCamping" class="btn-primary w-full mt-6">
        Create Camping & Spots
      </button>
    </div>
  </div>
</template>
