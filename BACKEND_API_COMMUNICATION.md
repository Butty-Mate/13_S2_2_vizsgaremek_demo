# 🔧 Backend API Kommunikáció - Laravel Sanctum

## 📋 Áttekintés

A backend API kommunikáció **Laravel Sanctum** token-based autentikációval működik. Ez egy egyszerű, de biztonságos megoldás SPA (Single Page Application) alkalmazásokhoz.

---

## 🏗️ Architektúra

```
┌─────────────────────────────────────────────────────────────┐
│                         FRONTEND                             │
│  (Vue.js - http://localhost:5174)                           │
│                                                              │
│  Axios HTTP Request                                          │
│  ├─ Authorization: Bearer {token}                           │
│  ├─ Content-Type: application/json                          │
│  └─ Accept: application/json                                │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           │ HTTP Request
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (Laravel)                         │
│              http://127.0.0.1:8000/api                       │
│                                                              │
│  1️⃣  CORS Middleware                                        │
│      ├─ Ellenőrzi origin-t                                  │
│      └─ Engedélyezi cross-origin kéréseket                  │
│                                                              │
│  2️⃣  Route Matching                                         │
│      ├─ routes/api.php                                      │
│      └─ URL pattern alapján controller választás            │
│                                                              │
│  3️⃣  Sanctum Middleware (védett route-ok)                  │
│      ├─ Bearer token ellenőrzés                             │
│      ├─ Token validálás (personal_access_tokens tábla)      │
│      └─ User betöltés                                        │
│                                                              │
│  4️⃣  Controller Action                                      │
│      ├─ Request validáció                                   │
│      ├─ Business logika                                     │
│      └─ Response generálás                                  │
│                                                              │
│  5️⃣  JSON Response                                          │
│      └─ HTTP Status Code + JSON data                        │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           │ HTTP Response
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (Vue.js)                         │
│  Axios Response Handling                                     │
│  ├─ Success: data feldolgozás                               │
│  └─ Error: hibakezelés                                      │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛣️ Route Struktúra

### 📁 `routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// ============================================
// 🌐 PUBLIKUS ROUTE-OK (nincs autentikáció)
// ============================================

// Teszt endpoint
Route::get('/test', function() {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

// Autentikáció
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Kempingek böngészése (publikus)
Route::get('/campings', [CampingController::class, 'index']);
Route::get('/campings/{id}', [CampingController::class, 'show']);

// ============================================
// 🔒 VÉDETT ROUTE-OK (auth:sanctum middleware)
// ============================================

Route::middleware('auth:sanctum')->group(function () {

    // User műveletek
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Kempingek kezelése (owner)
    Route::post('/campings', [CampingController::class, 'store']);
    Route::put('/campings/{id}', [CampingController::class, 'update']);
    Route::delete('/campings/{id}', [CampingController::class, 'destroy']);

    // Foglalások
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
});
```

---

## 🎯 Controller Példa - AuthController

### 📁 `app/Http/Controllers/Api/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 📝 REGISZTRÁCIÓ
     * POST /api/register
     */
    public function register(Request $request)
    {
        // 1️⃣ REQUEST VALIDÁCIÓ
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:guest,owner',
            'birth_date' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20'
        ]);

        // 2️⃣ USER LÉTREHOZÁS
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Jelszó titkosítás
            'role' => $validated['role'],
            'birth_date' => $validated['birth_date'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null
        ]);

        // 3️⃣ TOKEN GENERÁLÁS (Laravel Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4️⃣ JSON RESPONSE
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201); // HTTP 201 Created
    }

    /**
     * 🔑 BEJELENTKEZÉS
     * POST /api/login
     */
    public function login(Request $request)
    {
        // 1️⃣ VALIDÁCIÓ
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2️⃣ USER KERESÉS
        $user = User::where('email', $request->email)->first();

        // 3️⃣ JELSZÓ ELLENŐRZÉS
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        // 4️⃣ TOKEN GENERÁLÁS
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5️⃣ RESPONSE
        return response()->json([
            'user' => $user,
            'token' => $token
        ]); // HTTP 200 OK
    }

    /**
     * 🚪 KIJELENTKEZÉS
     * POST /api/logout
     * Védett: auth:sanctum middleware
     */
    public function logout(Request $request)
    {
        // 1️⃣ AKTUÁLIS TOKEN TÖRLÉSE
        $request->user()->currentAccessToken()->delete();

        // 2️⃣ RESPONSE
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * 👤 AKTUÁLIS USER LEKÉRÉSE
     * GET /api/me
     * Védett: auth:sanctum middleware
     */
    public function me(Request $request)
    {
        // $request->user() automatikusan elérhető a middleware-től
        return response()->json($request->user());
    }
}
```

---

## 🔐 Laravel Sanctum - Hogyan működik?

### 1️⃣ **Token Generálás**

```php
// User model-ben
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens; // ← Ez teszi lehetővé a token kezelést
}

// Token létrehozása
$token = $user->createToken('auth_token')->plainTextToken;
// Eredmény: "1|abc123def456ghi789..." formátumú string
```

### 2️⃣ **Token Tárolás (Adatbázis)**

A tokenek a `personal_access_tokens` táblában tárolódnak:

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT PRIMARY KEY,
    tokenable_type VARCHAR(255),  -- "App\Models\User"
    tokenable_id BIGINT,           -- User ID
    name VARCHAR(255),             -- "auth_token"
    token VARCHAR(64),             -- Hash-elt token
    abilities TEXT,                -- Jogosultságok
    expires_at TIMESTAMP,          -- Lejárat
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3️⃣ **Token Validáció**

```php
// Middleware: auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Védett route-ok
});

// A middleware:
// 1. Kiveszi a Bearer token-t a Header-ből
// 2. Hash-eli és megkeresi az adatbázisban
// 3. Ha talál match-et, betölti a User-t
// 4. $request->user() elérhető lesz
```

### 4️⃣ **Token Küldés (Frontend → Backend)**

```javascript
// Axios automatikusan hozzáadja (interceptor)
axios.get("/api/me", {
  headers: {
    Authorization: "Bearer 1|abc123def456...",
    Accept: "application/json",
  },
});
```

---

## 🌐 CORS Konfiguráció

### 📁 `config/cors.php`

```php
<?php

return [
    // Mely path-okra vonatkozik
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Engedélyezett HTTP metódusok
    'allowed_methods' => ['*'], // GET, POST, PUT, DELETE, stb.

    // Engedélyezett origin-ok
    'allowed_origins' => ['*'], // Minden origin (fejlesztéshez)
    // Production-ben: ['http://localhost:5174', 'https://yourdomain.com']

    // Engedélyezett header-ök
    'allowed_headers' => ['*'],

    // Credentials küldés
    'supports_credentials' => false, // Token-based auth-nál false
];
```

**Mit csinál a CORS middleware?**

- ✅ Engedélyezi, hogy a Vue.js (localhost:5174) hívjon Laravel API-t (localhost:8000)
- ✅ Hozzáadja az `Access-Control-Allow-Origin` header-t a válaszhoz
- ✅ Kezeli a preflight OPTIONS kéréseket

---

## 📊 Adatáramlás Példa

### Regisztráció Folyamat

```
FRONTEND (Vue.js)
  │
  │ 1. User kitölti form-ot
  │    { name, email, password, role }
  │
  ▼
axios.post('/api/register', formData)
  │
  │ 2. HTTP POST Request
  │    URL: http://127.0.0.1:8000/api/register
  │    Headers: {
  │      Content-Type: application/json,
  │      Accept: application/json
  │    }
  │    Body: {
  │      "name": "John Doe",
  │      "email": "john@test.com",
  │      "password": "password123",
  │      "password_confirmation": "password123",
  │      "role": "guest"
  │    }
  │
  ▼
BACKEND (Laravel)
  │
  ├─ 3. CORS Middleware
  │    └─ ✅ Origin engedélyezve
  │
  ├─ 4. Route Matching
  │    └─ routes/api.php: POST /register → AuthController@register
  │
  ├─ 5. Controller Action
  │    │
  │    ├─ a) Request Validáció
  │    │    ✅ name: required, string
  │    │    ✅ email: required, email, unique
  │    │    ✅ password: min:8, confirmed
  │    │    ✅ role: in:guest,owner
  │    │
  │    ├─ b) User Létrehozás
  │    │    User::create([...])
  │    │    └─ Password Hash: bcrypt
  │    │    └─ users táblába mentés
  │    │
  │    ├─ c) Token Generálás
  │    │    $token = $user->createToken('auth_token')
  │    │    └─ personal_access_tokens táblába mentés
  │    │
  │    └─ d) JSON Response
  │         return response()->json([
  │           'user' => $user,
  │           'token' => $token
  │         ], 201)
  │
  ▼
  6. HTTP Response
     Status: 201 Created
     Headers: {
       Content-Type: application/json,
       Access-Control-Allow-Origin: *
     }
     Body: {
       "user": {
         "id": 1,
         "name": "John Doe",
         "email": "john@test.com",
         "role": "guest",
         "created_at": "2025-11-25T10:00:00.000000Z"
       },
       "token": "1|abc123def456..."
     }
  │
  ▼
FRONTEND (Vue.js)
  │
  ├─ 7. Axios Response Handling
  │    const response = await axios.post(...)
  │    const { user, token } = response.data
  │
  ├─ 8. Token Tárolás
  │    localStorage.setItem('auth_token', token)
  │    localStorage.setItem('user', JSON.stringify(user))
  │
  └─ 9. State Frissítés (Pinia)
       authStore.user = user
       authStore.token = token
       authStore.isAuthenticated = true
```

---

## 🔒 Védett Route Hívás Példa

### GET /api/me

```
FRONTEND
  │
  │ 1. User már be van jelentkezve
  │    localStorage: { auth_token: "1|abc123..." }
  │
  ▼
axios.get('/api/me')
  │
  │ 2. Axios Interceptor
  │    ├─ Kiveszi token-t localStorage-ból
  │    └─ Hozzáadja Authorization header-t
  │
  │ 3. HTTP GET Request
  │    URL: http://127.0.0.1:8000/api/me
  │    Headers: {
  │      Authorization: "Bearer 1|abc123...",
  │      Accept: "application/json"
  │    }
  │
  ▼
BACKEND
  │
  ├─ 4. CORS Middleware
  │    └─ ✅ OK
  │
  ├─ 5. Route Matching
  │    └─ GET /me → AuthController@me
  │
  ├─ 6. auth:sanctum Middleware
  │    │
  │    ├─ a) Bearer token kivétel header-ből
  │    │    Authorization: "Bearer 1|abc123..."
  │    │    └─ Token: "1|abc123..."
  │    │
  │    ├─ b) Token hash és keresés
  │    │    SELECT * FROM personal_access_tokens
  │    │    WHERE token = HASH('abc123...')
  │    │
  │    ├─ c) User betöltés
  │    │    $user = User::find($token->tokenable_id)
  │    │
  │    └─ d) Request-hez csatolás
  │         $request->setUserResolver(function() use ($user) {
  │             return $user;
  │         })
  │
  ├─ 7. Controller Action
  │    public function me(Request $request) {
  │        return response()->json($request->user());
  │    }
  │    // $request->user() már elérhető a middleware-től
  │
  ▼
  8. HTTP Response
     Status: 200 OK
     Body: {
       "id": 1,
       "name": "John Doe",
       "email": "john@test.com",
       "role": "guest"
     }
  │
  ▼
FRONTEND
  │
  └─ 9. Response feldolgozás
       const userData = response.data
       // Frissítés, megjelenítés, stb.
```

---

## ❌ Hibakezelés

### Validációs Hiba (422)

```php
// Backend
$request->validate([
    'email' => 'required|email|unique:users'
]);

// Ha hiba van:
return response()->json([
    'message' => 'The given data was invalid.',
    'errors' => [
        'email' => ['The email has already been taken.']
    ]
], 422);
```

### Unauthorized (401)

```php
// Ha nincs token vagy érvénytelen
return response()->json([
    'message' => 'Unauthenticated.'
], 401);

// Frontend Axios interceptor elkapja:
// → localStorage.clear()
// → redirect('/login')
```

### Not Found (404)

```php
$camping = Camping::findOrFail($id);
// Ha nem található:
return response()->json([
    'message' => 'Camping not found.'
], 404);
```

---

## 🧪 API Tesztelés

### Postman / Insomnia / cURL példák

#### 1. Regisztráció

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "guest"
  }'
```

#### 2. Login

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "guest@test.com",
    "password": "password"
  }'
```

#### 3. User lekérés (védett)

```bash
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Accept: application/json"
```

---

## 📚 Összefoglalás

### ✅ Backend API kommunikáció elemei:

1. **Routes** (`routes/api.php`) - URL pattern → Controller mapping
2. **Controllers** (`app/Http/Controllers/Api/`) - Business logika
3. **Middleware** (`auth:sanctum`) - Token validáció
4. **Models** (`app/Models/`) - Adatbázis műveletek
5. **CORS** (`config/cors.php`) - Cross-origin engedélyezés
6. **Sanctum** (`config/sanctum.php`) - Token kezelés
7. **Validation** - Request adatok ellenőrzése
8. **JSON Responses** - Strukturált válaszok

### 🔄 Kommunikációs folyamat:

```
Request → CORS → Route → Middleware → Controller → Model → Database
                                                             ↓
Response ← JSON ← Controller ← Model ← Database Query Result
```

### 🎯 Kulcs komponensek:

- **Laravel Sanctum**: Token-based autentikáció
- **API Routes**: RESTful endpoint-ok
- **JSON Responses**: Egységes adatformátum
- **CORS**: Frontend-backend kommunikáció engedélyezése
- **Validation**: Adatbiztonság és integritás

---

**A backend teljesen készen áll az API kommunikációra! 🚀**
