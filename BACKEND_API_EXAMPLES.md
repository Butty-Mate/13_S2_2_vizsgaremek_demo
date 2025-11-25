# 🎓 Backend API Kommunikáció - Gyakorlati Példák

## 📚 Tartalom

1. [Egyszerű GET kérés](#1-egyszerű-get-kérés)
2. [POST kérés validációval](#2-post-kérés-validációval)
3. [Védett endpoint token-nel](#3-védett-endpoint-token-nel)
4. [Hibakezelés](#4-hibakezelés)
5. [Kapcsolatok (relationships)](#5-kapcsolatok-relationships)

---

## 1️⃣ Egyszerű GET Kérés

### Frontend (Vue.js)

```javascript
// src/services/api.js
export default {
  getCampings(params) {
    return apiClient.get('/campings', { params })
  }
}

// src/views/CampingsPage.vue
<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const campings = ref([])
const loading = ref(false)

const fetchCampings = async () => {
  loading.value = true
  try {
    const response = await api.getCampings({
      search: 'Balaton',
      page: 1
    })
    campings.value = response.data.data
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCampings()
})
</script>
```

### Backend (Laravel)

```php
// routes/api.php
Route::get('/campings', [CampingController::class, 'index']);

// app/Http/Controllers/Api/CampingController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Camping;
use Illuminate\Http\Request;

class CampingController extends Controller
{
    public function index(Request $request)
    {
        $query = Camping::with(['location', 'owner', 'spots']);

        // Keresés név alapján
        if ($request->has('search')) {
            $query->where('camping_name', 'like', '%' . $request->search . '%');
        }

        // Szűrés város alapján
        if ($request->has('city')) {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('city', 'like', '%' . $request->city . '%');
            });
        }

        // Pagináció (15 per oldal)
        $campings = $query->paginate(15);

        return response()->json($campings);
    }
}
```

### Request példa

```http
GET http://127.0.0.1:8000/api/campings?search=Balaton&page=1
Accept: application/json
```

### Response példa

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "camping_name": "Balaton Camping",
      "description": "Gyönyörű helyen a Balaton partján",
      "owner": {
        "id": 2,
        "name": "Test Owner",
        "email": "owner@test.com"
      },
      "location": {
        "id": 1,
        "city": "Siófok",
        "county": "Somogy",
        "street": "Parti út",
        "street_number": "123"
      },
      "spots": [
        {
          "id": 1,
          "name": "Standard 1",
          "type": "standard",
          "capacity": 4,
          "price_per_night": 5000,
          "is_available": true
        }
      ]
    }
  ],
  "total": 45,
  "per_page": 15,
  "last_page": 3
}
```

---

## 2️⃣ POST Kérés Validációval

### Frontend (Vue.js)

```javascript
// src/views/CreateCampingPage.vue
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const form = ref({
  camping_name: '',
  description: '',
  company_name: '',
  location: {
    postcode: '',
    county: '',
    city: '',
    street: '',
    street_number: ''
  }
})

const errors = ref({})
const loading = ref(false)

const handleSubmit = async () => {
  errors.value = {}
  loading.value = true

  try {
    const response = await api.createCamping(form.value)
    console.log('Success:', response.data)
    router.push({ name: 'OwnerDashboard' })
  } catch (error) {
    if (error.response?.status === 422) {
      // Validációs hibák
      errors.value = error.response.data.errors
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <div>
      <label>Kemping neve</label>
      <input v-model="form.camping_name" />
      <span v-if="errors.camping_name" class="error">
        {{ errors.camping_name[0] }}
      </span>
    </div>

    <div>
      <label>Város</label>
      <input v-model="form.location.city" />
      <span v-if="errors['location.city']" class="error">
        {{ errors['location.city'][0] }}
      </span>
    </div>

    <button type="submit" :disabled="loading">
      {{ loading ? 'Mentés...' : 'Kemping létrehozása' }}
    </button>
  </form>
</template>
```

### Backend (Laravel)

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/campings', [CampingController::class, 'store']);
});

// app/Http/Controllers/Api/CampingController.php
public function store(Request $request)
{
    // 1️⃣ VALIDÁCIÓ
    $validated = $request->validate([
        'camping_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'company_name' => 'nullable|string|max:255',
        'location' => 'required|array',
        'location.postcode' => 'required|string|max:10',
        'location.county' => 'required|string|max:100',
        'location.city' => 'required|string|max:100',
        'location.street' => 'required|string|max:255',
        'location.street_number' => 'required|string|max:20',
    ]);

    // 2️⃣ ELLENŐRZÉS: Csak owner hozhat létre kempinget
    if ($request->user()->role !== 'owner') {
        return response()->json([
            'message' => 'Only owners can create campings'
        ], 403); // Forbidden
    }

    // 3️⃣ LOCATION LÉTREHOZÁSA
    $location = Location::create([
        'postcode' => $validated['location']['postcode'],
        'county' => $validated['location']['county'],
        'city' => $validated['location']['city'],
        'street' => $validated['location']['street'],
        'street_number' => $validated['location']['street_number'],
    ]);

    // 4️⃣ CAMPING LÉTREHOZÁSA
    $camping = Camping::create([
        'camping_name' => $validated['camping_name'],
        'description' => $validated['description'] ?? null,
        'company_name' => $validated['company_name'] ?? null,
        'owner_id' => $request->user()->id,  // Bejelentkezett user
        'location_id' => $location->id
    ]);

    // 5️⃣ KAPCSOLATOK BETÖLTÉSE (eager loading)
    $camping->load(['location', 'owner']);

    // 6️⃣ RESPONSE
    return response()->json([
        'message' => 'Camping created successfully',
        'camping' => $camping
    ], 201); // HTTP 201 Created
}
```

### Request példa

```http
POST http://127.0.0.1:8000/api/campings
Authorization: Bearer 1|abc123def456...
Content-Type: application/json
Accept: application/json

{
  "camping_name": "Balaton Beach Camping",
  "description": "Családbarát kemping a Balaton partján",
  "company_name": "Balaton Camping Kft.",
  "location": {
    "postcode": "8600",
    "county": "Somogy",
    "city": "Siófok",
    "street": "Parti út",
    "street_number": "123"
  }
}
```

### Response példa (sikeres)

```json
{
  "message": "Camping created successfully",
  "camping": {
    "id": 5,
    "camping_name": "Balaton Beach Camping",
    "description": "Családbarát kemping a Balaton partján",
    "company_name": "Balaton Camping Kft.",
    "owner_id": 2,
    "location_id": 3,
    "created_at": "2025-11-25T12:00:00.000000Z",
    "owner": {
      "id": 2,
      "name": "Test Owner",
      "email": "owner@test.com"
    },
    "location": {
      "id": 3,
      "city": "Siófok",
      "county": "Somogy",
      "street": "Parti út"
    }
  }
}
```

### Response példa (validációs hiba)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "camping_name": ["The camping name field is required."],
    "location.city": ["The location.city field is required."]
  }
}
```

---

## 3️⃣ Védett Endpoint Token-nel

### Frontend (Vue.js)

```javascript
// src/stores/booking.js
import { defineStore } from "pinia";
import api from "@/services/api";

export const useBookingStore = defineStore("booking", {
  state: () => ({
    myBookings: [],
  }),

  actions: {
    async fetchMyBookings() {
      try {
        // Token automatikusan hozzáadódik az interceptor által
        const response = await api.getBookings();
        this.myBookings = response.data;
      } catch (error) {
        console.error("Error fetching bookings:", error);
        throw error;
      }
    },

    async createBooking(bookingData) {
      try {
        const response = await api.createBooking(bookingData);
        this.myBookings.push(response.data);
        return response.data;
      } catch (error) {
        throw error;
      }
    },
  },
});
```

### Backend (Laravel)

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
});

// app/Http/Controllers/Api/BookingController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CampingSpot;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * GET /api/bookings
     * Csak a bejelentkezett user foglalásai
     */
    public function index(Request $request)
    {
        // $request->user() elérhető az auth:sanctum middleware miatt
        $bookings = Booking::with(['campingSpot.camping', 'user'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bookings);
    }

    /**
     * POST /api/bookings
     * Új foglalás létrehozása
     */
    public function store(Request $request)
    {
        // 1️⃣ VALIDÁCIÓ
        $validated = $request->validate([
            'camping_spot_id' => 'required|exists:camping_spots,id',
            'arrival_date' => 'required|date|after_or_equal:today',
            'departure_date' => 'required|date|after:arrival_date',
            'guest_count' => 'required|integer|min:1'
        ]);

        // 2️⃣ CAMPING SPOT ELLENŐRZÉS
        $spot = CampingSpot::findOrFail($validated['camping_spot_id']);

        if (!$spot->is_available) {
            return response()->json([
                'message' => 'This camping spot is not available'
            ], 422);
        }

        // 3️⃣ KAPACITÁS ELLENŐRZÉS
        if ($validated['guest_count'] > $spot->capacity) {
            return response()->json([
                'message' => "Maximum capacity is {$spot->capacity} guests"
            ], 422);
        }

        // 4️⃣ ÁTFEDÉS ELLENŐRZÉS (nincs más foglalás ugyanabban az időszakban)
        $hasOverlap = Booking::where('camping_spot_id', $spot->id)
            ->where(function($query) use ($validated) {
                $query->whereBetween('arrival_date', [
                    $validated['arrival_date'],
                    $validated['departure_date']
                ])
                ->orWhereBetween('departure_date', [
                    $validated['arrival_date'],
                    $validated['departure_date']
                ])
                ->orWhere(function($q) use ($validated) {
                    $q->where('arrival_date', '<=', $validated['arrival_date'])
                      ->where('departure_date', '>=', $validated['departure_date']);
                });
            })
            ->exists();

        if ($hasOverlap) {
            return response()->json([
                'message' => 'This spot is already booked for the selected dates'
            ], 422);
        }

        // 5️⃣ ÁR SZÁMÍTÁS
        $nights = \Carbon\Carbon::parse($validated['arrival_date'])
            ->diffInDays($validated['departure_date']);
        $totalPrice = $nights * $spot->price_per_night;

        // 6️⃣ FOGLALÁS LÉTREHOZÁSA
        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'camping_spot_id' => $validated['camping_spot_id'],
            'arrival_date' => $validated['arrival_date'],
            'departure_date' => $validated['departure_date'],
            'guest_count' => $validated['guest_count'],
            'total_price' => $totalPrice,
            'status' => 'confirmed'
        ]);

        // 7️⃣ KAPCSOLATOK BETÖLTÉSE
        $booking->load(['campingSpot.camping.location', 'user']);

        // 8️⃣ RESPONSE
        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking
        ], 201);
    }
}
```

### Request példa

```http
POST http://127.0.0.1:8000/api/bookings
Authorization: Bearer 1|abc123def456...
Content-Type: application/json
Accept: application/json

{
  "camping_spot_id": 1,
  "arrival_date": "2025-12-01",
  "departure_date": "2025-12-05",
  "guest_count": 4
}
```

### Response példa

```json
{
  "message": "Booking created successfully",
  "booking": {
    "id": 10,
    "user_id": 1,
    "camping_spot_id": 1,
    "arrival_date": "2025-12-01",
    "departure_date": "2025-12-05",
    "guest_count": 4,
    "total_price": 20000,
    "status": "confirmed",
    "created_at": "2025-11-25T12:30:00.000000Z",
    "user": {
      "id": 1,
      "name": "Test Guest",
      "email": "guest@test.com"
    },
    "campingSpot": {
      "id": 1,
      "name": "Standard 1",
      "type": "standard",
      "price_per_night": 5000,
      "camping": {
        "id": 1,
        "camping_name": "Balaton Camping",
        "location": {
          "city": "Siófok"
        }
      }
    }
  }
}
```

---

## 4️⃣ Hibakezelés

### Backend

```php
// app/Http/Controllers/Api/CampingController.php
public function show($id)
{
    try {
        // findOrFail: 404 ha nem található
        $camping = Camping::with(['location', 'owner', 'spots'])
            ->findOrFail($id);

        return response()->json($camping);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Camping not found'
        ], 404);

    } catch (\Exception $e) {
        // Általános hiba (logging is jó lenne)
        \Log::error('Error fetching camping: ' . $e->getMessage());

        return response()->json([
            'message' => 'An error occurred',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}
```

### Frontend

```javascript
// src/services/api.js
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    // 401 Unauthorized
    if (error.response?.status === 401) {
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user");
      window.location.href = "/login";
    }

    // 403 Forbidden
    if (error.response?.status === 403) {
      console.error("Access denied");
      // Optionally redirect
    }

    // 404 Not Found
    if (error.response?.status === 404) {
      console.error("Resource not found");
    }

    // 422 Validation Error
    if (error.response?.status === 422) {
      console.error("Validation errors:", error.response.data.errors);
    }

    // 500 Server Error
    if (error.response?.status === 500) {
      console.error("Server error");
    }

    return Promise.reject(error);
  }
);
```

---

## 5️⃣ Kapcsolatok (Relationships)

### Backend Model-ek

```php
// app/Models/Camping.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camping extends Model
{
    protected $fillable = [
        'camping_name', 'description', 'company_name',
        'owner_id', 'location_id'
    ];

    // Kapcsolatok
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function spots()
    {
        return $this->hasMany(CampingSpot::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Eager Loading

```php
// Rossz ❌ (N+1 query problem)
$campings = Camping::all();
foreach ($campings as $camping) {
    echo $camping->location->city;  // Minden ciklusban külön query!
}

// Jó ✅ (eager loading)
$campings = Camping::with(['location', 'owner', 'spots'])->get();
foreach ($campings as $camping) {
    echo $camping->location->city;  // Nincs extra query
}
```

### Nested Relationships

```php
// Mélyen egymásba ágyazott kapcsolatok
$bookings = Booking::with([
    'user',
    'campingSpot.camping.location',
    'campingSpot.camping.owner'
])->get();

// Response:
{
  "id": 1,
  "user": { ... },
  "campingSpot": {
    "id": 1,
    "name": "Standard 1",
    "camping": {
      "id": 1,
      "camping_name": "Balaton Camping",
      "location": {
        "city": "Siófok"
      },
      "owner": {
        "name": "Test Owner"
      }
    }
  }
}
```

---

## 🎯 Összefoglalás

### Backend API Best Practices:

1. ✅ **Validáció mindig** - soha ne bízz a client-ben
2. ✅ **Eager loading** - kerüld az N+1 query problémát
3. ✅ **HTTP status kódok** - használd helyesen (200, 201, 400, 401, 404, 422, 500)
4. ✅ **Consistent responses** - egységes JSON struktúra
5. ✅ **Error handling** - minden kivétel kezelve
6. ✅ **Authorization** - mindig ellenőrizd a jogosultságokat
7. ✅ **Pagination** - nagy listák lapozással
8. ✅ **Logging** - hibák naplózása

### Frontend API Best Practices:

1. ✅ **Centralizált API service** - egy helyen az összes endpoint
2. ✅ **Interceptors** - token automatikus hozzáadása
3. ✅ **Error handling** - minden error kezelve
4. ✅ **Loading states** - user feedback
5. ✅ **Validation** - client-side is validálj
6. ✅ **Store management** - Pinia/Vuex használata

---

**Most már tudsz gyakorlati példákat használni! 🎓**
