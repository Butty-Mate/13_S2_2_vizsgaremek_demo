# 🏕️ Camping Booking System - Vizsgaremek Demo

Teljes értékű kempingfoglalási rendszer modern Laravel backend és Vue.js frontend technológiákkal.

## 📋 Tartalomjegyzék

- [Projekt áttekintés](#projekt-áttekintés)
- [Technológiai stack](#technológiai-stack)
- [Funkciók](#funkciók)
- [Projekt struktúra](#projekt-struktúra)
- [Telepítés és indítás](#telepítés-és-indítás)
- [API dokumentáció](#api-dokumentáció)
- [Felhasználói szerepkörök](#felhasználói-szerepkörök)

## 🎯 Projekt áttekintés

Ez egy komplex webes alkalmazás, amely lehetővé teszi kempingek és kempinghelyek kezelését, valamint foglalások létrehozását. A rendszer három fő felhasználói szerepkört támogat: vendég, felhasználó és tulajdonos.

### Főbb jellemzők

- 🔐 Biztonságos autentikáció (Laravel Sanctum)
- 🏕️ Kempingek és kempinghelyek teljes körű kezelése
- 📅 Foglalási rendszer dátum validálással
- 💬 Véleményezési rendszer
- 🎨 Modern, reszponzív felhasználói felület
- 🔍 Kereső és szűrő funkciók
- 📍 Helyszín alapú keresés

## 🛠️ Technológiai stack

### Backend

- **Framework:** Laravel 12.x
- **PHP verzió:** 8.2+
- **Adatbázis:** SQLite (dev), MySQL/PostgreSQL (production ready)
- **Autentikáció:** Laravel Sanctum
- **API:** RESTful API

### Frontend

- **Framework:** Vue.js 3.5+
- **State Management:** Pinia
- **Routing:** Vue Router 4
- **HTTP Client:** Axios
- **CSS Framework:** Tailwind CSS 3.4+
- **Build Tool:** Vite 7+

## ✨ Funkciók

### Publikus funkciók

- ✅ Kempingek böngészése és keresése
- ✅ Részletes kemping információk megtekintése
- ✅ Kempinghelyek és szolgáltatások áttekintése
- ✅ Regisztráció és bejelentkezés

### Felhasználói funkciók

- ✅ Foglalások létrehozása és kezelése
- ✅ Saját foglalások megtekintése
- ✅ Vélemények írása kempingekről
- ✅ Profil kezelés

### Tulajdonosi funkciók

- ✅ Kempingek létrehozása, szerkesztése, törlése
- ✅ Kempinghelyek kezelése
- ✅ Foglalások áttekintése
- ✅ Képek feltöltése

## 📁 Projekt struktúra

```
13_S2_2_vizsgaremek_demo/
├── backend/                 # Laravel backend
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/     # API Controllers
│   │   └── Models/          # Eloquent modellek
│   ├── database/
│   │   ├── migrations/      # Adatbázis migrációk
│   │   └── seeders/         # Seederek
│   ├── routes/
│   │   └── api.php          # API útvonalak
│   └── ...
│
└── frontend/                # Vue.js frontend
    ├── src/
    │   ├── components/      # Vue komponensek
    │   ├── views/           # Oldal nézetek
    │   ├── router/          # Vue Router konfig
    │   ├── stores/          # Pinia store-ok
    │   └── services/        # API szolgáltatások
    └── ...
```

## 🚀 Telepítés és indítás

### Előfeltételek

- PHP 8.2 vagy újabb
- Composer
- Node.js (18.x vagy újabb) és npm
- Git

### Backend telepítés

1. **Navigálj a backend mappába:**

```bash
cd backend
```

2. **Telepítsd a függőségeket:**

```bash
composer install
```

3. **Másold át és konfiguráld a .env fájlt:**

```bash
cp .env.example .env
```

4. **Generálj alkalmazás kulcsot:**

```bash
php artisan key:generate
```

5. **Futtasd a migrációkat és a seedereket:**

```bash
php artisan migrate --seed
```

6. **Indítsd el a backend szervert:**

```bash
php artisan serve
```

A backend elérhető lesz: `http://localhost:8000`

### Frontend telepítés

1. **Navigálj a frontend mappába:**

```bash
cd frontend
```

2. **Telepítsd a függőségeket:**

```bash
npm install
```

3. **Indítsd el a dev szervert:**

```bash
npm run dev
```

A frontend elérhető lesz: `http://localhost:5173`

### Gyors indítás (opcionális)

A backend composer.json tartalmaz egyszerűsített parancsokat:

```bash
# Backend telepítés és konfiguráció
composer run setup

# Fejlesztői környezet indítása (server + queue + logs + vite)
composer run dev
```

## 🔌 API dokumentáció

### Publikus végpontok

#### Autentikáció

- `POST /api/register` - Regisztráció
- `POST /api/login` - Bejelentkezés

#### Kempingek

- `GET /api/campings` - Összes kemping listázása
- `GET /api/campings/{id}` - Kemping részletei
- `GET /api/campings/suggestions` - Ajánlott kempingek

#### Kempinghelyek

- `GET /api/camping-spots` - Kempinghelyek listázása
- `GET /api/camping-spots/{id}` - Kempinghely részletei

### Védett végpontok (auth:sanctum)

#### Profil

- `POST /api/logout` - Kijelentkezés
- `GET /api/me` - Aktuális felhasználó adatai

#### Kempingek (tulajdonos)

- `POST /api/campings` - Új kemping létrehozása
- `PUT /api/campings/{id}` - Kemping frissítése
- `DELETE /api/campings/{id}` - Kemping törlése

#### Kempinghelyek (tulajdonos)

- `POST /api/camping-spots` - Új kempinghely létrehozása
- `PUT /api/camping-spots/{id}` - Kempinghely frissítése
- `DELETE /api/camping-spots/{id}` - Kempinghely törlése

#### Foglalások

- `GET /api/bookings` - Saját foglalások
- `POST /api/bookings` - Új foglalás létrehozása
- `GET /api/bookings/{id}` - Foglalás részletei
- `PUT /api/bookings/{id}` - Foglalás módosítása
- `DELETE /api/bookings/{id}` - Foglalás törlése

### API válaszok

#### Sikeres válasz

```json
{
  "success": true,
  "data": {
    /* adatok */
  },
  "message": "Művelet sikeres"
}
```

#### Hiba válasz

```json
{
  "success": false,
  "message": "Hibaüzenet",
  "errors": {
    /* validációs hibák */
  }
}
```

## 👥 Felhasználói szerepkörök

### Vendég (Guest)

- Kempingek böngészése
- Részletes információk megtekintése
- Regisztráció és bejelentkezés

### Felhasználó (User)

- Összes vendég funkció
- Foglalások létrehozása és kezelése
- Vélemények írása

### Tulajdonos (Owner)

- Összes felhasználói funkció
- Saját kempingek kezelése
- Kempinghelyek létrehozása és szerkesztése
- Foglalások áttekintése

## 📊 Adatbázis modellek

- **User** - Felhasználók
- **Location** - Helyszínek (települések)
- **Camping** - Kempingek
- **CampingSpot** - Kempinghelyek
- **Booking** - Foglalások
- **Comment** - Vélemények

## 🧪 Tesztelés

### Backend tesztek futtatása

```bash
cd backend
php artisan test
```

### Frontend tesztek futtatása

```bash
cd frontend
npm run test
```

## 📦 Build production-höz

### Backend

```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend

```bash
cd frontend
npm run build
```

A build kimenet a `frontend/dist` mappában lesz.

## 🔧 Környezeti változók

### Backend (.env)

```env
APP_NAME="Camping Booking System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# vagy MySQL/PostgreSQL production-ben

SANCTUM_STATEFUL_DOMAINS=localhost:5173
SESSION_DOMAIN=localhost
```

### Frontend

```env
VITE_API_URL=http://localhost:8000/api
```

## 📚 Részletes Dokumentáció

### Backend API Kommunikáció

A projekt részletes dokumentációval rendelkezik a backend API működéséről:

- **[BACKEND_API_INDEX.md](BACKEND_API_INDEX.md)** - Dokumentációs útmutató és tartalomjegyzék
- **[BACKEND_API_SUMMARY.md](BACKEND_API_SUMMARY.md)** - Gyors összefoglaló magyarul (⚡ Kezdd itt!)
- **[BACKEND_API_VISUAL.md](BACKEND_API_VISUAL.md)** - Vizuális diagramok és folyamatábrák
- **[BACKEND_API_COMMUNICATION.md](BACKEND_API_COMMUNICATION.md)** - Teljes technikai dokumentáció
- **[BACKEND_API_EXAMPLES.md](BACKEND_API_EXAMPLES.md)** - Gyakorlati kódpéldák

### Autentikáció

- **[AUTH_DOCUMENTATION.md](AUTH_DOCUMENTATION.md)** - Auth rendszer részletes dokumentációja
- **[AUTH_SUMMARY.md](AUTH_SUMMARY.md)** - Auth összefoglaló és checklist

## 🤝 Közreműködés

Ez egy vizsgaremek projekt. Javaslatokat és fejlesztési ötleteket szívesen fogadok!

## 📄 Licenc

Ez a projekt oktatási célokat szolgál.

## 👨‍💻 Fejlesztő

Készítette: Butty Máté  
Projekt: Vizsgaremek Demo  
Dátum: 2025

---

**Megjegyzések:**

- A projekt fejlesztés alatt áll
- SQLite adatbázist használ fejlesztési környezetben
- Production környezethez ajánlott MySQL vagy PostgreSQL használata
- A képfeltöltés funkcióhoz konfiguráld a storage mappát
