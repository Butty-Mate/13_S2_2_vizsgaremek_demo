# 🚀 Gyors Kezdés - Backend API

## 👋 Üdvözöllek!

Ez az útmutató segít megérteni, hogyan működik a backend API kommunikáció a projektben.

---

## 📖 Mi van a projektben?

```
Backend (Laravel)  ←→  Frontend (Vue.js)

   API Végpontok        HTTP Kérések
   Token Auth          Axios Service
   JSON Response       State Management
```

---

## 🎯 3 Lépés a Megértéshez

### 1️⃣ **Kezdd az alapokkal** (10 perc)

Olvasd el: **[BACKEND_API_SUMMARY.md](BACKEND_API_SUMMARY.md)**

- Egyszerű magyarázatok
- Gyors példák
- Kulcsfogalmak

### 2️⃣ **Nézd meg vizuálisan** (15 perc)

Nézd át: **[BACKEND_API_VISUAL.md](BACKEND_API_VISUAL.md)**

- Diagramok
- Folyamatábrák
- Lépésről lépésre

### 3️⃣ **Próbáld ki gyakorlatban** (30 perc)

Kövesd: **[BACKEND_API_EXAMPLES.md](BACKEND_API_EXAMPLES.md)**

- Konkrét kódpéldák
- Frontend + Backend együtt
- Copy-paste ready

---

## ⚡ Gyors Demo

### **Szerverek indítása:**

```bash
# Backend (Terminal 1)
cd backend
php artisan serve
# → http://127.0.0.1:8000

# Frontend (Terminal 2)
cd frontend
npm run dev
# → http://localhost:5174
```

### **Tesztelés a böngészőben:**

1. Nyisd meg: `http://localhost:5174`
2. Görgess le a **"Auth Demo"** szekcióhoz
3. Próbáld ki a **Quick Login**-t:
   - Email: `guest@test.com`
   - Password: `password`
4. Kattints **"Login"** gombra
5. 🎉 Működik! Látod a választ JSON-ban

---

## 🔍 Mit látsz?

### **Frontend (Böngésző Console):**

```javascript
// Kérés
POST http://127.0.0.1:8000/api/login
Body: { email: "guest@test.com", password: "password" }

// Válasz
200 OK
{
  user: { id: 1, name: "Test Guest", ... },
  token: "1|abc123..."
}
```

### **Backend (Laravel Log):**

```
POST /api/login
├─ Validáció ✅
├─ User found ✅
├─ Password OK ✅
├─ Token created ✅
└─ Response sent ✅
```

---

## 📊 Backend Fájlok - Hol van mi?

```
backend/
│
├── routes/api.php
│   └─ URL route-ok (POST /login)
│
├── app/Http/Controllers/Api/
│   └── AuthController.php
│       └─ login() függvény
│
├── app/Models/
│   └── User.php
│       └─ Token generálás
│
└── config/
    ├── cors.php (Cross-origin)
    └── sanctum.php (Token auth)
```

---

## 🎓 Amit meg fogsz tanulni

### **Alapok:**

- ✅ Mi az a REST API?
- ✅ Hogyan kommunikál a frontend és backend?
- ✅ Mi az a JSON?
- ✅ HTTP status kódok (200, 401, 422, stb.)

### **Laravel specifikus:**

- ✅ Route-ok definiálása
- ✅ Controller-ek létrehozása
- ✅ Request validálás
- ✅ JSON response-ok
- ✅ Laravel Sanctum token auth

### **Sanctum (Token Auth):**

- ✅ Token generálás
- ✅ Token tárolás
- ✅ Token validálás
- ✅ Védett route-ok

### **Best Practices:**

- ✅ Hibakezelés
- ✅ Validáció
- ✅ CORS konfiguráció
- ✅ API biztonság

---

## 🗺️ Tanulási Térkép

```
1. Alapok megértése
   ├─ REST API koncepció
   ├─ HTTP kérések/válaszok
   └─ JSON formátum

2. Laravel alapok
   ├─ Route-ok
   ├─ Controller-ek
   └─ Model-ek

3. API Kommunikáció
   ├─ Request handling
   ├─ Validation
   └─ Response formatting

4. Autentikáció
   ├─ Laravel Sanctum
   ├─ Token generálás
   └─ Middleware

5. Gyakorlat
   ├─ Saját endpoint-ok
   ├─ CRUD műveletek
   └─ Kapcsolatok kezelése
```

---

## 💡 Tippek

### **Debugging:**

```php
// Laravel route-ok listázása
php artisan route:list

// Specifikus API route-ok
php artisan route:list --path=api

// Log nézése
tail -f storage/logs/laravel.log
```

### **Tesztelés:**

```bash
# API endpoint tesztelés cURL-lel
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"guest@test.com","password":"password"}'
```

### **Gyors hibakeresés:**

1. **Nem működik az API?**

   - Ellenőrizd: Laravel szerver fut?
   - Nézd meg: `http://127.0.0.1:8000/api/test`

2. **CORS hiba?**

   - Nézd: `config/cors.php`
   - Engedélyezve van az origin?

3. **401 Unauthorized?**
   - Token érvényes?
   - Authorization header helyes?

---

## 📚 Dokumentáció Struktúra

```
BACKEND_API_INDEX.md          ← Kezdd itt!
├─ Útmutató és tartalomjegyzék
│
├─ BACKEND_API_SUMMARY.md     ← Alapok
│  └─ Gyors összefoglaló magyarul
│
├─ BACKEND_API_VISUAL.md      ← Vizuális
│  └─ Diagramok és folyamatábrák
│
├─ BACKEND_API_COMMUNICATION.md ← Technikai
│  └─ Teljes dokumentáció
│
└─ BACKEND_API_EXAMPLES.md    ← Gyakorlat
   └─ Kódpéldák és implementáció
```

---

## ✅ Checklist - Első Lépések

Mielőtt elkezdenéd:

- [ ] Backend szerver fut (http://127.0.0.1:8000)
- [ ] Frontend szerver fut (http://localhost:5174)
- [ ] Adatbázis létezik (`database.sqlite`)
- [ ] Seederek lefutottak (teszt userek)
- [ ] Browser Developer Tools nyitva (F12)
- [ ] Dokumentációk letöltve/olvasásra készen

Tanulás közben:

- [ ] Elolvastam a SUMMARY-t
- [ ] Megnéztem a VISUAL diagramokat
- [ ] Kipróbáltam az EXAMPLES példákat
- [ ] Elkészítettem első saját endpoint-omat
- [ ] Debuggoltam egy hibát

---

## 🎯 Következő Lépések

### **Most kezdtem:**

1. Olvasd el a **SUMMARY**-t
2. Nézd meg az **Auth Demo**-t a böngészőben
3. Kövesd végig egy request-et a devtools-ban

### **Értem az alapokat:**

1. Nézd át a **VISUAL** diagramokat
2. Próbáld ki az **EXAMPLES** példákat
3. Módosítsd az `AuthController`-t

### **Készen állok továbblépni:**

1. Olvasd el a teljes **COMMUNICATION** dokut
2. Készíts saját endpoint-ot
3. Implementálj új feature-t

---

## 🎓 Hasznos Linkek

### **Laravel Dokumentáció:**

- [Laravel Official Docs](https://laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Validation](https://laravel.com/docs/validation)

### **Vue.js Dokumentáció:**

- [Vue 3 Docs](https://vuejs.org/)
- [Pinia](https://pinia.vuejs.org/)
- [Axios](https://axios-http.com/)

### **HTTP & API:**

- [HTTP Status Codes](https://httpstatuses.com/)
- [REST API Tutorial](https://restfulapi.net/)
- [JSON Introduction](https://www.json.org/)

---

## 💬 Gyakori Kérdések

**Q: Muszáj mindent elolvasnom?**  
A: Nem! Kezd a SUMMARY-val, aztán ahogy kell, olvasd a többit.

**Q: Mennyi idő, amíg megértem?**  
A: Alapszinten 1 óra, mélyen 3-4 óra.

**Q: Tudok közben kérdezni?**  
A: Igen! Nézd meg a kódot, próbáld ki, debuggold.

**Q: Mi a legfontosabb?**  
A: Érts meg egy teljes Request → Response ciklust.

**Q: Hol kezdjem?**  
A: BACKEND_API_SUMMARY.md → Olvasd el az első 3 fejezetet.

---

## 🚀 Kezdjük el!

**Első lépés:**  
[📖 BACKEND_API_SUMMARY.md - Gyors Összefoglaló](BACKEND_API_SUMMARY.md)

**Ha szereted a képeket:**  
[🎨 BACKEND_API_VISUAL.md - Vizuális Magyarázat](BACKEND_API_VISUAL.md)

**Ha kódot akarsz:**  
[💻 BACKEND_API_EXAMPLES.md - Gyakorlati Példák](BACKEND_API_EXAMPLES.md)

---

**Jó tanulást! 🎓**

Ha készen állsz, nyisd meg az első dokumentumot! →
