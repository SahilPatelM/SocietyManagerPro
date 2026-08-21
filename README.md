# Society Manager Pro

Android-ready society management app built with **Laravel 12**, **NativePHP Mobile v3**, **Livewire**, and **MySQL**.

## Features

- Mobile OTP + password authentication (Sanctum API)
- Role-based access: Super Admin, Society Admin, Treasurer, Member
- Member management with family & vehicle details
- Finance (income/expense), dashboard analytics, reports (PDF/Excel)
- House-wise ledger, complaints, announcements, visitors, parking, documents
- Gujarati + English UI, dark mode, large touch-friendly controls
- Firebase push notifications (HTTP API)
- Clean architecture: Repository pattern, service layer, API resources

## Requirements

- PHP **8.3+** (required for NativePHP Mobile; 8.2 works for Laravel web/API with `--ignore-platform-reqs`)
- Composer, Node.js 20+
- MySQL 8+
- Android Studio (for `native:run android`) or [Jump app](https://bifrost.nativephp.com/jump) for instant device testing

## Quick Start

```bash
cd society-manager-pro
composer install
cp .env.example .env   # configure DB + NATIVEPHP_APP_ID
php artisan key:generate

# .env
NATIVEPHP_APP_ID=com.societymanager.pro
NATIVEPHP_APP_VERSION=DEBUG
DB_CONNECTION=mysql
DB_DATABASE=society_manager_pro

php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Open http://localhost:8000/login

### Demo logins

| Role | Mobile | Password |
|------|--------|----------|
| Society Admin | 9876543210 | password |
| Treasurer | 9876543211 | password |
| Member | 9800000001 | password |

Local OTP is always `123456`.

## NativePHP Android

```bash
php artisan native:install
npm run build
php artisan native:run android
# Or instant test:
php artisan native:jump
```

## API

Base URL: `/api/v1`

- `POST /auth/otp/send` `{ "mobile": "9876543210" }`
- `POST /auth/login` `{ "mobile", "password" }`
- `GET /dashboard` (Bearer token)
- `GET /members`, `POST /members`, etc.

## Firebase

```env
FIREBASE_SERVER_KEY=your_fcm_server_key
FIREBASE_PROJECT_ID=your_project_id
```

## Project structure

```
app/
  Enums/           # Income, expense, complaint enums
  Http/Controllers/Api/
  Http/Resources/
  Http/Requests/
  Livewire/        # Mobile-first UI screens
  Models/
  Repositories/    # Contracts + Eloquent
  Services/        # Auth, Dashboard, Finance, Reports, FCM
database/migrations/
lang/en, lang/gu
resources/views/layouts/mobile.blade.php
routes/api.php, routes/web.php
```

## License

MIT
