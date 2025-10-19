# 🍽️ Restaurant App Backend (Laravel)

This repository contains the **Laravel-based backend API** for the Restaurant Management System.
It provides endpoints for products, orders, users, roles, and reports — along with authentication and authorization using **Laravel Sanctum**.

---

## 🚀 Tech Stack

* Laravel 11
* MySQL / PostgreSQL
* Sanctum Authentication
* RESTful API structure
* Role-Based Access Control (RBAC)
* Validation, Pagination & Eloquent relationships

---

## ⚙️ Setup Instructions

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/faisalnur7/restaurant-app-backend.git
cd restaurant-app-backend
```

---

### 2️⃣ Install Dependencies

```bash
composer install
cp .env.example .env
```

---

### 3️⃣ Configure the `.env` File

Update your `.env` file with the following configuration:

```env
APP_NAME=ResPOS
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ================================
# 🗄️ DATABASE CONFIGURATION
# ================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# ================================
# 🧩 SESSION & SANCTUM SETTINGS
# ================================
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=localhost
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=false

# Sanctum / React setup
SANCTUM_STATEFUL_DOMAINS=localhost:5174
FRONTEND_URL=http://localhost:5174

VITE_API_URL=http://localhost:8000

# ================================
# ⚙️ CACHE / QUEUE / FILESYSTEM
# ================================
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

# ================================
# 📨 MAIL SETTINGS
# ================================
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# ================================
# ☁️ OPTIONAL (Redis / AWS)
# ================================
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# ================================
# 🔧 VITE (for local asset builds)
# ================================
VITE_APP_NAME="${APP_NAME}"
```

---

### 4️⃣ Generate App Key

```bash
php artisan key:generate
```

---

### 5️⃣ Cache Important Configurations

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔗 Related Repository

Frontend (React):
👉 **[restaurant-app-frontend](https://github.com/faisalnur7/restaurant-app-frontend)**

---

## 👨‍💻 Author

**Faisal Nur**
🔗 GitHub: [https://github.com/faisalnur7](https://github.com/faisalnur7)
💼 Portfolio: [https://faisalnurroney.com](https://faisalnurroney.com)

---
