# Tonishen's Kitchen – Full Stack Setup Guide

## Architecture
- **Frontend**: React 19 + Vite + Tailwind CSS v4
- **Backend**: Laravel 11 API + JWT Authentication
- **Database**: MySQL (MariaDB via XAMPP)

---

## 1. Prerequisites

Install these before proceeding:

| Tool       | Version | Download |
|------------|---------|----------|
| **Node.js** | 18+ ✅ (already installed) | https://nodejs.org |
| **PHP**     | 8.2+   | https://www.php.net/downloads or via XAMPP |
| **Composer** | 2.x   | https://getcomposer.org |
| **MySQL**   | 5.7+   | Via XAMPP (already installed) |

---

## 2. Backend Setup (Laravel)

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
copy .env.example .env

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Create the database in MySQL
# (Open phpMyAdmin and create database: tonishens_kitchen)

# Run migrations
php artisan migrate

# (Optional) Migrate old data from existing database
# First rename your old tables or point to the old DB
php artisan db:seed --class=MigrateOldDataSeeder

# Create storage symlink
php artisan storage:link

# Start the API server
php artisan serve --port=8000
```

---

## 3. Frontend Setup (React)

```bash
cd frontend

# Install dependencies (already done)
npm install

# Start dev server
npm run dev
```

Frontend runs at `http://localhost:3000` and proxies API calls to `http://localhost:8000`.

---

## 4. Default Credentials

After running the seeder, your old user accounts will be migrated.

To create a fresh admin manually:
```bash
php artisan tinker
```
```php
App\Models\User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@tonishens.com',
    'password' => 'admin123',
    'role' => 'admin',
]);
```

---

## 5. API Endpoints Summary

### Public
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register |
| POST | `/api/auth/login` | Login |
| GET | `/api/dishes` | List dishes |
| GET | `/api/dishes/{id}` | Dish detail |
| GET | `/api/categories` | List categories |
| GET | `/api/about` | About page |

### Customer (JWT required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/PUT | `/api/profile` | Profile CRUD |
| POST | `/api/profile/avatar` | Upload avatar |
| PUT | `/api/profile/password` | Change password |
| GET/POST | `/api/cart` | Cart operations |
| DELETE | `/api/cart/{id}` | Remove item |
| GET/POST | `/api/orders` | Orders |
| POST | `/api/orders/{id}/cancel` | Cancel order |
| POST | `/api/orders/{id}/feedback` | Submit feedback |

### Admin (JWT + admin role required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/dashboard` | Dashboard stats |
| CRUD | `/api/admin/dishes` | Manage dishes |
| CRUD | `/api/admin/categories` | Manage categories |
| GET/PUT | `/api/admin/orders` | Manage orders |
| POST | `/api/admin/orders/bulk-update` | Bulk status update |
| CRUD | `/api/admin/ingredients` | Inventory |
| GET | `/api/admin/reports/sales` | Sales report |
| GET | `/api/admin/reports/top-selling` | Top selling |
| GET | `/api/admin/reports/*/export` | CSV exports |
| GET/PUT | `/api/admin/about` | About CMS |
| CRUD | `/api/admin/accounts` | Admin accounts |
| GET | `/api/admin/notifications` | Notifications |

---

## 6. Security Improvements Over Old Version

1. ✅ **SQL Injection**: All queries use Eloquent ORM with parameter binding
2. ✅ **Authentication**: JWT tokens with 15min expiry + refresh flow
3. ✅ **Authorization**: Role-based middleware (admin vs customer)
4. ✅ **Password Hashing**: bcrypt via Laravel's `Hash` facade
5. ✅ **Input Validation**: Form Request classes with Zod on frontend
6. ✅ **CORS**: Configured for frontend origin only
7. ✅ **File Uploads**: Validated type/size, stored outside web root
8. ✅ **No Debug Leaks**: No passwords or SQL errors exposed in responses
9. ✅ **BLOB → File**: Images stored as files instead of database BLOBs

---

## 7. Project Structure

```
tonishens_kitchen/
├── frontend/              # React SPA
│   ├── src/
│   │   ├── api/           # Axios API layer
│   │   ├── components/    # Reusable UI + Layout
│   │   ├── context/       # Auth + Cart providers
│   │   ├── guards/        # Route protection
│   │   ├── pages/         # Page components
│   │   │   ├── admin/     # 8 admin pages
│   │   │   ├── customer/  # 5 customer pages
│   │   │   └── public/    # 5 public pages
│   │   └── utils/         # Helpers
│   └── vite.config.js
├── backend/               # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Admin/  # 8 admin controllers
│   │   │   │   └── *.php   # 7 customer controllers
│   │   │   ├── Middleware/  # JWT + Admin guards
│   │   │   └── Requests/   # Form validation
│   │   └── Models/         # 15 Eloquent models
│   ├── database/
│   │   ├── migrations/     # 15 migration files
│   │   └── seeders/        # Data migration seeder
│   ├── routes/api.php      # All API routes
│   └── config/             # App, auth, JWT, CORS, DB
└── README.md
```
