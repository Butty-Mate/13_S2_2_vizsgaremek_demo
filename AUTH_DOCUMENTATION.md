# 🔐 Auth System - Login & Register Működési Dokumentáció

## 📋 Rendszer áttekintés

A teljes autentikációs rendszer működik Laravel Sanctum + Axios + Vue 3 + Pinia stack-kel.

## 🚀 Szerverek indítása

### Backend (Laravel)

```bash
cd backend
php artisan serve
```

**Elérhető:** `http://127.0.0.1:8000`

### Frontend (Vue.js)

```bash
cd frontend
npm run dev
```

**Elérhető:** `http://localhost:5173` vagy `http://localhost:5174`

## 👥 Teszt felhasználók

A következő teszt fiókokat hoztuk létre a seederrel:

| Email             | Jelszó     | Szerepkör          |
| ----------------- | ---------- | ------------------ |
| `admin@admin.com` | `admidmin` | Owner (tulajdonos) |
| `owner@test.com`  | `password` | Owner (tulajdonos) |
| `guest@test.com`  | `password` | Guest (vendég)     |

## 🔌 API Végpontok

### Publikus (nem igényel autentikációt)

#### 🔑 Regisztráció

```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "guest",  // vagy "owner"
  "phone_number": "+36 30 123 4567",  // opcionális
  "birth_date": "1990-01-01"  // opcionális
}
```

**Válasz (201):**

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "guest",
    "phone_number": "+36 30 123 4567",
    "birth_date": "1990-01-01"
  },
  "token": "1|abc123..."
}
```

#### 🔓 Bejelentkezés

```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Válasz (200):**

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "guest"
  },
  "token": "2|xyz789..."
}
```

### Védett (Authorization Bearer token szükséges)

#### 👤 Aktuális felhasználó adatai

```http
GET /api/me
Authorization: Bearer {token}
```

**Válasz (200):**

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "guest",
  "phone_number": "+36 30 123 4567",
  "birth_date": "1990-01-01",
  "created_at": "2025-11-25T10:00:00.000000Z"
}
```

#### 🚪 Kijelentkezés

```http
POST /api/logout
Authorization: Bearer {token}
```

**Válasz (200):**

```json
{
  "message": "Logged out successfully"
}
```

## 🏗️ Frontend Architektúra

### 1. **API Service** (`src/services/api.js`)

Axios kliens interceptor-okkal:

- Automatikusan hozzáadja a Bearer token-t minden kéréshez
- 401 hiba esetén átirányít a login oldalra
- localStorage-ból olvassa a tokent

```javascript
import api from "@/services/api";

// Használat
const response = await api.login({ email, password });
const user = await api.getMe();
```

### 2. **Pinia Store** (`src/stores/auth.js`)

Központi autentikációs state management:

```javascript
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

// State
authStore.user; // Aktuális felhasználó
authStore.token; // Auth token
authStore.isAuthenticated; // Boolean

// Getters
authStore.isOwner; // true ha owner role
authStore.isGuest; // true ha guest role
authStore.userName; // Felhasználó neve

// Actions
await authStore.login({ email, password });
await authStore.register(formData);
await authStore.logout();
await authStore.fetchUser();
```

### 3. **Router Guards** (`src/router/index.js`)

Automatikus navigációs védelem:

- **`meta: { guest: true }`** - Csak nem bejelentkezett felhasználók (login, register)
- **`meta: { requiresAuth: true }`** - Csak bejelentkezett felhasználók
- **`meta: { requiresOwner: true }`** - Csak owner szerepkörű felhasználók

```javascript
{
  path: '/my-bookings',
  name: 'MyBookings',
  component: () => import('./views/MyBookingsPage.vue'),
  meta: { requiresAuth: true }
}
```

### 4. **Vue Komponensek**

#### Login oldal (`src/views/LoginPage.vue`)

```vue
<template>
  <form @submit.prevent="handleLogin">
    <input v-model="form.email" type="email" />
    <input v-model="form.password" type="password" />
    <button type="submit">Login</button>
  </form>
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";
const authStore = useAuthStore();

const handleLogin = async () => {
  await authStore.login(form.value);
  router.push({ name: "Home" });
};
</script>
```

#### Register oldal (`src/views/RegisterPage.vue`)

```vue
<template>
  <form @submit.prevent="handleRegister">
    <input v-model="form.name" type="text" />
    <input v-model="form.email" type="email" />
    <input v-model="form.password" type="password" />
    <input v-model="form.password_confirmation" type="password" />
    <select v-model="form.role">
      <option value="guest">Guest</option>
      <option value="owner">Owner</option>
    </select>
    <button type="submit">Register</button>
  </form>
</template>
```

#### Navigation Bar (`src/components/NavBar.vue`)

```vue
<template>
  <nav>
    <template v-if="authStore.isAuthenticated">
      <span>{{ authStore.userName }}</span>
      <button @click="handleLogout">Logout</button>
    </template>
    <template v-else>
      <router-link to="/login">Login</router-link>
      <router-link to="/register">Sign Up</router-link>
    </template>
  </nav>
</template>
```

## 🧪 Tesztelés (AuthDemo komponens)

A főoldalon (`HomePage.vue`) megtalálható az `AuthDemo` komponens, ami lehetővé teszi:

1. ✅ **Gyors login teszt** - Előre kitöltött teszt adatokkal
2. ✅ **Gyors register teszt** - Új felhasználó létrehozása
3. ✅ **Aktuális state megtekintése** - User, token, role
4. ✅ **API válaszok megjelenítése** - JSON formátumban
5. ✅ **Token érvényesítés teszt** - `/api/me` endpoint hívás
6. ✅ **Logout teszt**

**Demo elérhető:** `http://localhost:5174/` után a hero szekció alatt

## 🔒 Biztonság

### Backend (Laravel)

- ✅ Laravel Sanctum token autentikáció
- ✅ Password hashing (bcrypt)
- ✅ CSRF védelem
- ✅ Input validáció
- ✅ Email uniqueness ellenőrzés
- ✅ Minimum 8 karakteres jelszó

### Frontend

- ✅ Token tárolás localStorage-ban
- ✅ Automatikus token refresh (interceptor)
- ✅ 401 esetén automatikus kijelentkezés
- ✅ Route guards védelem
- ✅ XSS védelem (Vue 3 alapértelmezett)

## 📦 LocalStorage Struktúra

```javascript
// Bejelentkezés után
localStorage.setItem("auth_token", "Bearer_token_itt");
localStorage.setItem(
  "user",
  JSON.stringify({
    id: 1,
    name: "John Doe",
    email: "john@example.com",
    role: "guest",
  })
);

// Kijelentkezéskor
localStorage.removeItem("auth_token");
localStorage.removeItem("user");
```

## 🌊 Adatfolyam (Flow)

### Regisztráció

```
1. User kitölti a form-ot (RegisterPage.vue)
   ↓
2. handleRegister() függvény meghívása
   ↓
3. authStore.register(formData)
   ↓
4. api.register(data) - POST /api/register
   ↓
5. Backend validál és user-t létrehoz
   ↓
6. Token generálás (Sanctum)
   ↓
7. Response: { user, token }
   ↓
8. authStore.setAuth() - localStorage mentés
   ↓
9. Router átirányít Home-ra
```

### Bejelentkezés

```
1. User beírja email + jelszó (LoginPage.vue)
   ↓
2. handleLogin() függvény
   ↓
3. authStore.login({ email, password })
   ↓
4. api.login(data) - POST /api/login
   ↓
5. Backend ellenőrzi credentials
   ↓
6. Token generálás
   ↓
7. Response: { user, token }
   ↓
8. localStorage mentés
   ↓
9. Átirányítás
```

### Védett Route elérése

```
1. User navigál /my-bookings-ra
   ↓
2. Router guard ellenőrzi: meta.requiresAuth
   ↓
3. authStore.isAuthenticated check
   ↓
4. Ha false → Redirect /login
   ↓
5. Ha true → Route betöltődik
   ↓
6. Komponens API hívást küld
   ↓
7. Axios interceptor hozzáadja Bearer token-t
   ↓
8. Backend validálja token-t (auth:sanctum middleware)
   ↓
9. Ha valid → Response data
   ↓
10. Ha invalid (401) → Interceptor logout + redirect login
```

## 🛠️ Hibakezelés

### Backend hibák

```javascript
try {
  await authStore.login(form.value);
} catch (error) {
  // error.response.status - HTTP kód (401, 422, stb)
  // error.response.data.message - Üzenet
  // error.response.data.errors - Validációs hibák

  if (error.response?.status === 422) {
    // Validációs hiba
    const errors = error.response.data.errors;
    console.log(errors.email[0]); // "Email már létezik"
  }
}
```

### Gyakori hibák

| HTTP Kód | Jelentés         | Megoldás                                 |
| -------- | ---------------- | ---------------------------------------- |
| 401      | Unauthorized     | Helytelen email/jelszó vagy lejárt token |
| 422      | Validation Error | Form mezők nem megfelelőek               |
| 500      | Server Error     | Backend hiba (logok ellenőrzése)         |

## 📝 Következő lépések

1. ✅ **Backend & Frontend fut**
2. ✅ **API kommunikáció működik**
3. ✅ **Auth system kész**
4. ⏭️ **Kempingek listázása és CRUD műveletek**
5. ⏭️ **Foglalások kezelése**
6. ⏭️ **Képfeltöltés implementálása**

## 🎯 Használati példa

```bash
# 1. Nyisd meg a böngészőt
http://localhost:5174/

# 2. Görgess le az Auth Demo részhez

# 3. Tesztelj login-t:
Email: guest@test.com
Password: password
→ Kattints "Login"

# 4. Ellenőrizd a NavBar-ban:
"Test Guest" név jelenik meg + "Logout" gomb

# 5. Tesztelj API hívást:
→ Kattints "Test /api/me"
→ JSON válasz megjelenik

# 6. Tesztelj logout-ot:
→ Kattints "Logout"
→ State törlődik, átirányít

# 7. Tesztelj register-t:
→ Adj meg új email címet
→ Válassz role-t (guest/owner)
→ Kattints "Register"
→ Új user létrejön + automatikus login
```

## 🎉 Eredmény

**A teljes Auth rendszer működik!** 🚀

- ✅ Register működik
- ✅ Login működik
- ✅ Logout működik
- ✅ Token management működik
- ✅ Protected routes működnek
- ✅ Role-based access működik
- ✅ Automatikus átirányítások működnek
- ✅ Error handling működik

---

**Fejlesztő:** Butty Máté  
**Dátum:** 2025-11-25  
**Stack:** Laravel 12 + Vue 3 + Pinia + Axios + Sanctum
