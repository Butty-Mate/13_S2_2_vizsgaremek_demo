<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import AuthDemo from '@/components/AuthDemo.vue'

const router = useRouter()
const authStore = useAuthStore()

// Search form
const searchForm = ref({
  location: '',
  arrivalDate: '',
  departureDate: '',
  guests: 2
})

// Autocomplete
const suggestions = ref([])
const showSuggestions = ref(false)
const isLoadingSuggestions = ref(false)

// Watch for location input changes - Magyar Helységnévtár API
let searchTimeout = null
watch(() => searchForm.value.location, (newValue) => {
  if (searchTimeout) clearTimeout(searchTimeout)
  
  if (newValue.length < 1) {
    suggestions.value = []
    showSuggestions.value = false
    return
  }
  
  isLoadingSuggestions.value = true
  searchTimeout = setTimeout(async () => {
    try {
      // Magyar Helységnévtár API használata
      const response = await axios.get(`https://hur.webmania.cc/zips/${encodeURIComponent(newValue)}.json`)
      
      if (response.data && response.data.zips) {
        // Csak egyedi városneveket gyűjtünk
        const uniqueCities = new Map()
        response.data.zips.forEach(zip => {
          const cityName = zip.name
          if (!uniqueCities.has(cityName)) {
            uniqueCities.set(cityName, {
              name: cityName,
              zip: zip.zip,
              label: `${cityName} (${zip.zip})`
            })
          }
        })
        
        suggestions.value = Array.from(uniqueCities.values()).slice(0, 10)
        showSuggestions.value = suggestions.value.length > 0
      } else {
        suggestions.value = []
        showSuggestions.value = false
      }
    } catch (error) {
      console.error('Error fetching city suggestions:', error)
      suggestions.value = []
      showSuggestions.value = false
    } finally {
      isLoadingSuggestions.value = false
    }
  }, 300)
})

const selectSuggestion = (suggestion) => {
  searchForm.value.location = suggestion.name
  showSuggestions.value = false
}

const handleSearch = () => {
  router.push({ 
    name: 'Campings',
    query: {
      location: searchForm.value.location,
      arrival: searchForm.value.arrivalDate,
      departure: searchForm.value.departureDate,
      guests: searchForm.value.guests
    }
  })
}

// FAQ
const faqs = ref([
  { 
    question: 'Hogyan foglalhatok szálláshelyet?', 
    answer: 'Böngésszen a kempingek között, válasszon ki egy tetszőleges helyet, és kattintson a "Foglalás" gombra. Válassza ki az érkezés és távozás dátumát, majd erősítse meg a foglalást.',
    open: false
  },
  { 
    question: 'Mikor kell fizetnem a foglalásért?', 
    answer: 'A fizetés a helyszínen történik érkezéskor. A foglalás ingyenes, de kérjük, időben lemondani, ha mégsem tud elmenni.',
    open: false
  },
  { 
    question: 'Hogyan mondhatom le a foglalásom?', 
    answer: 'Lépjen be a fiókjába, menjen a "Foglalásaim" menüpontra, és kattintson a lemondás gombra a foglalás mellett.',
    open: false
  },
  { 
    question: 'Milyen típusú szálláshelyek érhetők el?', 
    answer: 'Kempingeink standard, prémium és VIP helyeket kínálnak különböző árakkal és kapacitással.',
    open: false
  },
  { 
    question: 'Tudok tulajdonosként kempinget hozzáadni?', 
    answer: 'Igen! Regisztráljon tulajdonosi fiókkal, és a vezérlőpultról hozzáadhat új kempingeket és kezelheti a foglalásokat.',
    open: false
  }
])

const toggleFaq = (index) => {
  faqs.value[index].open = !faqs.value[index].open
}
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section with Search -->
    <section 
      class="relative bg-cover bg-center text-white py-32"
      style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://picsum.photos/id/1036/1920/1080');"
    >
      <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-12">
          <h1 class="text-5xl md:text-6xl font-bold mb-6 drop-shadow-lg">Találd meg az ideális kempinget</h1>
          <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto drop-shadow-md">
            Fedezd fel a legszebb helyeket, foglalj egyszerűen és élvezd a természetet
          </p>
        </div>

        <!-- Search Form -->
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-2xl p-6 md:p-8">
          <form @submit.prevent="handleSearch" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Location with Autocomplete -->
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  📍 Helyszín
                </label>
                <input 
                  v-model="searchForm.location"
                  @focus="showSuggestions = suggestions.length > 0"
                  @blur="setTimeout(() => showSuggestions = false, 200)"
                  type="text" 
                  placeholder="pl. Balaton, Tisza-tó..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                  autocomplete="off"
                />
                <!-- Autocomplete Dropdown -->
                <div 
                  v-if="showSuggestions && suggestions.length > 0"
                  class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                >
                  <div 
                    v-for="(suggestion, index) in suggestions" 
                    :key="index"
                    @click="selectSuggestion(suggestion)"
                    class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b last:border-b-0 text-gray-900"
                  >
                    <div class="font-semibold text-green-700">{{ suggestion.name }}</div>
                    <div class="text-sm text-gray-600">Irányítószám: {{ suggestion.zip }}</div>
                  </div>
                </div>
                <!-- Loading indicator -->
                <div v-if="isLoadingSuggestions" class="absolute right-3 top-11 text-gray-400">
                  <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </div>

              <!-- Arrival Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  📅 Érkezés
                </label>
                <input 
                  v-model="searchForm.arrivalDate"
                  type="date" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                  required
                />
              </div>

              <!-- Departure Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  📅 Távozás
                </label>
                <input 
                  v-model="searchForm.departureDate"
                  type="date" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                  required
                />
              </div>

              <!-- Guests -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  👥 Személyek száma
                </label>
                <select 
                  v-model.number="searchForm.guests"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900"
                >
                  <option :value="1">1 személy</option>
                  <option :value="2">2 személy</option>
                  <option :value="3">3 személy</option>
                  <option :value="4">4 személy</option>
                  <option :value="5">5 személy</option>
                  <option :value="6">6+ személy</option>
                </select>
              </div>
            </div>

            <div class="flex justify-center pt-2">
              <button 
                type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-12 rounded-lg text-lg transition-colors shadow-lg"
              >
                🔍 Keresés
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Auth Demo Section (Development Only) -->
    <section class="py-16 bg-gradient-to-r from-indigo-100 to-purple-100">
      <div class="container mx-auto px-4">
        <AuthDemo />
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 text-gray-800">Miért válassz minket?</h2>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="bg-white p-8 rounded-xl shadow-lg text-center hover:shadow-xl transition-shadow">
            <div class="text-green-600 text-5xl mb-4">
              <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-semibold mb-3 text-gray-800">Könnyű keresés</h3>
            <p class="text-gray-600">
              Találd meg a tökéletes kempinget az intuitív keresőnkkel és szűrőrendszerünkkel.
            </p>
          </div>
          
          <div class="bg-white p-8 rounded-xl shadow-lg text-center hover:shadow-xl transition-shadow">
            <div class="text-blue-600 text-5xl mb-4">
              <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-semibold mb-3 text-gray-800">Azonnali foglalás</h3>
            <p class="text-gray-600">
              Foglalj azonnal az egyszerű és gyors foglalási rendszerünkkel.
            </p>
          </div>
          
          <div class="bg-white p-8 rounded-xl shadow-lg text-center hover:shadow-xl transition-shadow">
            <div class="text-purple-600 text-5xl mb-4">
              <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-semibold mb-3 text-gray-800">Ellenőrzött helyszínek</h3>
            <p class="text-gray-600">
              Minden kemping ellenőrzött és értékelt a kemping közösségünk által.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="text-4xl font-bold text-center mb-12 text-gray-800">Gyakran Ismételt Kérdések</h2>
        <div class="space-y-4">
          <div 
            v-for="(faq, index) in faqs" 
            :key="index"
            class="border border-gray-200 rounded-lg overflow-hidden"
          >
            <button 
              @click="toggleFaq(index)"
              class="w-full px-6 py-4 text-left bg-gray-50 hover:bg-gray-100 transition-colors flex justify-between items-center"
            >
              <span class="font-semibold text-gray-800">{{ faq.question }}</span>
              <svg 
                class="w-6 h-6 transform transition-transform text-green-600" 
                :class="{ 'rotate-180': faq.open }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>
            <div 
              v-show="faq.open"
              class="px-6 py-4 bg-white text-gray-600"
            >
              {{ faq.answer }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-gradient-to-r from-blue-500 to-green-600 text-white py-16">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-6">Készen állsz a kalandra?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
          Csatlakozz elégedett kempingezeink közösségéhez és fedezd fel a legszebb helyeket még ma!
        </p>
        <button @click="router.push({ name: authStore.isAuthenticated ? 'Campings' : 'Register' })" class="bg-white text-green-600 hover:bg-gray-100 font-bold py-3 px-8 rounded-lg text-lg transition-colors shadow-lg">
          {{ authStore.isAuthenticated ? 'Kempingek böngészése' : 'Regisztrálj most' }}
        </button>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300">
      <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- Logo and Description -->
          <div class="col-span-1">
            <div class="flex items-center mb-4 space-x-2">
              <span class="text-4xl">🏕️</span>
              <span class="text-2xl font-bold text-green-500">CampSite</span>
            </div>
            <p class="text-sm text-gray-400">
              A legjobb kempingek egy helyen. Foglalj egyszerűen, kempingezz boldogan!
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h3 class="text-white font-semibold text-lg mb-4">Gyors linkek</h3>
            <ul class="space-y-2">
              <li>
                <button @click="router.push({ name: 'Home' })" class="hover:text-white transition-colors">
                  Kezdőlap
                </button>
              </li>
              <li>
                <button @click="router.push({ name: 'Campings' })" class="hover:text-white transition-colors">
                  Kempingek
                </button>
              </li>
              <li v-if="authStore.isAuthenticated">
                <button @click="router.push({ name: 'MyBookings' })" class="hover:text-white transition-colors">
                  Foglalásaim
                </button>
              </li>
              <li v-if="authStore.isAuthenticated && authStore.user?.role === 'owner'">
                <button @click="router.push({ name: 'OwnerDashboard' })" class="hover:text-white transition-colors">
                  Tulajdonosi vezérlőpult
                </button>
              </li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h3 class="text-white font-semibold text-lg mb-4">Támogatás</h3>
            <ul class="space-y-2">
              <li>
                <a href="#faq" class="hover:text-white transition-colors" @click.prevent="$el.closest('html').querySelector('.space-y-4').scrollIntoView({ behavior: 'smooth' })">
                  GYIK
                </a>
              </li>
              <li>
                <a href="mailto:info@campsite.hu" class="hover:text-white transition-colors">
                  Kapcsolat
                </a>
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors">
                  Felhasználási feltételek
                </a>
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors">
                  Adatvédelmi szabályzat
                </a>
              </li>
            </ul>
          </div>

          <!-- Contact -->
          <div>
            <h3 class="text-white font-semibold text-lg mb-4">Elérhetőség</h3>
            <ul class="space-y-2 text-sm">
              <li class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <a href="mailto:info@campsite.hu" class="hover:text-white transition-colors">
                  info@campsite.hu
                </a>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <span>+36 1 234 5678</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Budapest, Magyarország</span>
              </li>
            </ul>

            <!-- Social Media -->
            <div class="flex space-x-4 mt-6">
              <a href="#" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
          <p>&copy; 2025 CampSite. Minden jog fenntartva.</p>
          <p class="mt-2">Made with ❤️ for campers</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Additional styles if needed */
</style>
