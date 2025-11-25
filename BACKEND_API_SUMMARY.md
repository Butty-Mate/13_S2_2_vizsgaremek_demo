# 🚀 Backend API Kommunikáció - Gyors Összefoglaló

## 🎯 Hogyan van megcsinálva?

### 1️⃣ **Alapok**

```
Frontend (Vue.js)  ←→  Backend (Laravel)
  localhost:5174        localhost:8000/api

       HTTP Request
    ────────────────→
       JSON Response
    ←────────────────
```

---

## 🔧 Backend Komponensek

### **1. Routes (Útvonalak)** 📁 `routes/api.php`

```php
// Publikus route (mindenki hozzáfér)
Route::post('/login', [AuthController::class, 'login']);

// Védett route (csak bejelentkezett usernek)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});
```

**Mit csinál?**

- URL-t hozzárendeli Controller függvényhez
- Middleware-t alkalmaz (auth:sanctum = token ellenőrzés)

---

### **2. Controllers** 📁 `app/Http/Controllers/Api/`

```php
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validáció
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. User keresés
        $user = User::where('email', $request->email)->first();

        // 3. Jelszó ellenőrzés
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid'], 401);
        }

        // 4. Token generálás
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. JSON válasz
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}
```

**Mit csinál?**

- Request adatok fogadása
- Validálás
- Business logika (adatbázis műveletek)
- JSON response visszaküldése

---

### **3. Middleware (auth:sanctum)** 🔒

```php
// Automatikusan megtörténik:
Route::middleware('auth:sanctum')->group(function () {
    // ...
});

// Mit csinál a háttérben:
1. Kiveszi a Bearer token-t a header-ből
2. Megkeresi az adatbázisban (personal_access_tokens tábla)
3. Ha valid, betölti a User-t
4. $request->user() elérhető lesz
```

---

### **4. Models** 📁 `app/Models/`

```php
class User extends Authenticatable
{
    use HasApiTokens;  // ← Token funkciók

    protected $fillable = ['name', 'email', 'password', 'role'];

    // Token létrehozás
    $token = $user->createToken('auth_token')->plainTextToken;
}
```

**Mit csinál?**

- Adatbázis tábla reprezentáció
- Kapcsolatok (relationships)
- Token kezelés (Sanctum)

---

### **5. CORS konfig** 📁 `config/cors.php`

```php
return [
    'paths' => ['api/*'],
    'allowed_origins' => ['*'],  // Minden origin engedélyezve
    'allowed_methods' => ['*'],   // GET, POST, PUT, DELETE, stb.
    'allowed_headers' => ['*'],
];
```

**Mit csinál?**

- Engedélyezi, hogy a frontend (localhost:5174) hívja a backend-et (localhost:8000)
- Cross-Origin Resource Sharing

---

## 🔄 Kommunikációs Folyamat

### **Login Példa:**

```
1️⃣  USER: Kitölti email + password
              ↓
2️⃣  FRONTEND: axios.post('/api/login', { email, password })
              ↓
3️⃣  HTTP REQUEST:
    POST http://127.0.0.1:8000/api/login
    Headers: { Content-Type: application/json }
    Body: { "email": "guest@test.com", "password": "password" }
              ↓
4️⃣  BACKEND:
    ├─ CORS middleware ✅
    ├─ Route matching: POST /login → AuthController@login
    ├─ Validáció: email & password ✅
    ├─ User keresés adatbázisban
    ├─ Jelszó check (Hash::check)
    ├─ Token generálás (Sanctum)
    └─ JSON response
              ↓
5️⃣  HTTP RESPONSE:
    Status: 200 OK
    Body: {
      "user": { "id": 1, "name": "Test", ... },
      "token": "1|abc123..."
    }
              ↓
6️⃣  FRONTEND:
    ├─ Token mentés: localStorage.setItem('auth_token', token)
    ├─ User mentés: localStorage.setItem('user', JSON.stringify(user))
    └─ Átirányítás: router.push('/home')
```

---

## 🔐 Token működés

### **Token generálás (Backend):**

```php
// User model-ben
use Laravel\Sanctum\HasApiTokens;

// Token létrehozás
$token = $user->createToken('auth_token')->plainTextToken;
// Eredmény: "1|abc123def456..."

// Adatbázis tárolás (personal_access_tokens tábla):
// - tokenable_id: 1 (User ID)
// - token: hash('abc123...')
```

### **Token használat (Frontend):**

```javascript
// Axios automatikusan hozzáadja minden kéréshez:
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

### **Token validálás (Backend):**

```php
// auth:sanctum middleware automatikusan:
1. Kiveszi a Bearer token-t
2. Hash-eli és megkeresi az adatbázisban
3. Ha talál egyezést, betölti a User-t
4. $request->user() elérhető lesz a controller-ben
```

---

## 📊 API Endpoint Típusok

### **1. Publikus (nincs token):**

```php
// routes/api.php
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/campings', [CampingController::class, 'index']);
```

**Példa hívás:**

```javascript
await axios.post("http://127.0.0.1:8000/api/login", { email, password });
```

### **2. Védett (token szükséges):**

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/bookings', [BookingController::class, 'store']);
});
```

**Példa hívás:**

```javascript
await axios.get("http://127.0.0.1:8000/api/me", {
  headers: {
    Authorization: `Bearer ${token}`,
  },
});
```

---

## ✅ HTTP Status Kódok

| Kód     | Jelentés      | Mikor használjuk               |
| ------- | ------------- | ------------------------------ |
| **200** | OK            | Sikeres GET, PUT, DELETE       |
| **201** | Created       | Sikeres POST (új resource)     |
| **400** | Bad Request   | Rossz kérés formátum           |
| **401** | Unauthorized  | Nincs vagy rossz token         |
| **403** | Forbidden     | Token OK, de nincs jogosultság |
| **404** | Not Found     | Resource nem található         |
| **422** | Unprocessable | Validációs hiba                |
| **500** | Server Error  | Backend hiba                   |

---

## 🎯 Backend Response Formátumok

### **Sikeres response:**

```json
{
  "message": "Success",
  "data": {
    "id": 1,
    "name": "Test"
  }
}
```

### **Hiba response (validáció):**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### **Hiba response (általános):**

```json
{
  "message": "An error occurred",
  "error": "Detailed error message"
}
```

---

## 🔧 Validáció példák

```php
// Controller-ben
$validated = $request->validate([
    // Kötelező, string, max 255 karakter
    'name' => 'required|string|max:255',

    // Kötelező, email formátum, unique a users táblában
    'email' => 'required|email|unique:users',

    // Kötelező, minimum 8 karakter, confirmed (password_confirmation mező)
    'password' => 'required|min:8|confirmed',

    // Kötelező, egyik értéke: guest vagy owner
    'role' => 'required|in:guest,owner',

    // Opcionális, dátum formátum
    'birth_date' => 'nullable|date',

    // Kötelező, integer, minimum 1
    'guest_count' => 'required|integer|min:1',

    // Kötelező, létező camping_spots tábla id-ja
    'camping_spot_id' => 'required|exists:camping_spots,id',
]);
```

---

## 🗂️ Adatbázis Kapcsolatok (Relationships)

```php
// Camping Model
class Camping extends Model
{
    // Egy kemping tartozik egy tulajdonoshoz
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Egy kempingnek sok helye van
    public function spots()
    {
        return $this->hasMany(CampingSpot::class);
    }
}

// Controller-ben eager loading:
$camping = Camping::with(['owner', 'spots', 'location'])->find($id);

// JSON response tartalmazza a kapcsolatokat:
{
  "id": 1,
  "camping_name": "Balaton Camping",
  "owner": {
    "id": 2,
    "name": "Owner Name"
  },
  "spots": [
    { "id": 1, "name": "Standard 1" },
    { "id": 2, "name": "Premium 1" }
  ]
}
```

---

## 📝 Gyors Checklist

Backend API működéséhez szükséges:

- ✅ **Route definiálva** (`routes/api.php`)
- ✅ **Controller létrehozva** (`app/Http/Controllers/Api/`)
- ✅ **Model létezik** (`app/Models/`)
- ✅ **Validáció implementálva**
- ✅ **CORS engedélyezve** (`config/cors.php`)
- ✅ **Middleware alkalmazva** (ha védett route)
- ✅ **JSON response visszaküldve**
- ✅ **HTTP status kód helyes**

---

## 🎓 Összefoglalás

### **Backend API = 5 egyszerű lépés:**

1. **Route**: URL → Controller mapping
2. **Middleware**: Token ellenőrzés (ha védett)
3. **Controller**: Request feldolgozás + validálás
4. **Model**: Adatbázis műveletek
5. **Response**: JSON visszaküldés

### **Kulcs technológiák:**

- **Laravel Sanctum**: Token-based auth
- **Eloquent ORM**: Adatbázis műveletek
- **Validation**: Adatok ellenőrzése
- **CORS**: Frontend-backend kommunikáció
- **JSON API**: RESTful endpoint-ok

---

**Ennyi! Most már érted a backend API kommunikációt! 🚀**
