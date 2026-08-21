# Society Manager Pro

Mobile-first society management app built with **Laravel 12**, **Livewire**, **Supabase (PostgreSQL)**, and **PWA** support. Optional **NativePHP Mobile** builds for Android/iOS.

---

## Features

- Mobile OTP + password authentication (Sanctum API)
- Role-based access: Super Admin, Society Admin, Treasurer, Member
- Member management with family and vehicle details
- Finance (income/expense), dashboard analytics, reports (PDF/Excel)
- House-wise ledger, complaints, announcements, visitors, parking, documents
- Gujarati + English UI, dark mode, touch-friendly mobile layout
- **PWA** — install on Android/iPhone home screen (no app store required)
- Firebase push notifications (optional)
- Clean architecture: Repository pattern, service layer, API resources

---

## Requirements (local development)

- PHP **8.2+** with **pdo_pgsql** extension enabled
- Composer, Node.js 20.19+
- [Supabase](https://supabase.com) account (free tier works)

### Enable PostgreSQL in PHP (Windows)

1. Open `php.ini`
2. Uncomment: `extension=pdo_pgsql` and `extension=pgsql`
3. Restart terminal and run: `php -m | findstr pgsql`

---

## Supabase setup

1. Go to [supabase.com](https://supabase.com) → **New project**
2. Choose region, set a **database password** (save it)
3. Wait for the project to finish provisioning
4. Open **Project Settings → Database**
5. Copy connection details from **Connection string → URI** or **Host**

### Local `.env` (direct connection)

```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_db_password
DB_SSLMODE=require
```

### Vercel / serverless (use pooler — recommended)

In Supabase: **Connect → Connection pooling → Transaction mode**

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-south-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.xxxxxxxxxxxx
DB_PASSWORD=your_supabase_db_password
DB_SSLMODE=require
```

---

## Quick start (local)

```bash
git clone https://github.com/SahilPatelM/SocietyManagerPro.git
cd SocietyManagerPro

composer install
cp .env.example .env
php artisan key:generate
```

Add your Supabase credentials to `.env`, then:

```bash
php artisan migrate --seed --force
npm install
npm run build
php artisan serve
```

Open: http://127.0.0.1:8000/login

### Demo logins

| Role | Mobile | Password |
|------|--------|----------|
| Society Admin | 9876543210 | password |
| Treasurer | 9876543211 | password |
| Member | 9800000001 | password |

Local OTP is always `123456`.

---

## Deploy on Vercel (full guide)

Vercel hosts this Laravel app using the **vercel-php** runtime. Database is **Supabase PostgreSQL** (external).

### Important limitations on Vercel

| Works | Does not work well |
|-------|-------------------|
| Web UI + Livewire | Background queue workers |
| PWA install (HTTPS included) | Local file uploads (documents) |
| REST API + Supabase | NativePHP mobile builds |
| Supabase PostgreSQL | Persistent `storage/` files |

---

### Step 1 — Push code to GitHub

```bash
git add .
git commit -m "Switch to Supabase PostgreSQL"
git push origin main
```

Do **not** commit `.env`.

---

### Step 2 — Run migrations (from your PC)

Vercel cannot run `php artisan migrate` for you. Run once locally against Supabase:

```powershell
cd "D:\Projects\Native PHP\society-manager-pro"
php artisan migrate --seed --force
```

Use the same Supabase credentials as production in your local `.env`.

---

### Step 3 — Vercel environment variables

**Project → Settings → Environment Variables**

#### Required

| Variable | Example | Notes |
|----------|---------|-------|
| `APP_NAME` | `Society Manager Pro` | |
| `APP_ENV` | `production` | |
| `APP_KEY` | `base64:...` | `php artisan key:generate --show` |
| `APP_DEBUG` | `false` | |
| `APP_URL` | `https://your-app.vercel.app` | Must use **https://** (not http) |
| `DB_CONNECTION` | `pgsql` | |
| `DB_HOST` | `aws-0-....pooler.supabase.com` | **Pooler host** for Vercel |
| `DB_PORT` | `6543` | Transaction pooler port |
| `DB_DATABASE` | `postgres` | |
| `DB_USERNAME` | `postgres.xxxxxxxxxxxx` | Pooler username |
| `DB_PASSWORD` | `********` | Supabase DB password |
| `DB_SSLMODE` | `require` | Required for Supabase |

#### Recommended for Vercel

| Variable | Value |
|----------|-------|
| `SESSION_DRIVER` | `cookie` |
| `CACHE_STORE` | `array` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |
| `VERCEL_FORCE_NO_BUILD_CACHE` | `1` |

Apply to **Production**, **Preview**, and **Development**.

---

### Step 4 — Vercel project settings

| Setting | Value |
|---------|--------|
| Framework Preset | **Other** |
| Output Directory | `public` |
| Build Command | `npm run build` |
| Install Command | `npm ci` |

Deploy → **Redeploy without cache** after env changes.

---

### Step 5 — Pull Vercel env locally (optional)

```powershell
cd "D:\Projects\Native PHP\society-manager-pro"
vercel link
vercel env pull .env.vercel
```

Copy DB values from `.env.vercel` into `.env`, then run migrations.

---

### Troubleshooting Vercel + Supabase

| Problem | Fix |
|---------|-----|
| Empty page / HTTP 500 | Set `APP_KEY`, Supabase pooler vars (`DB_PORT=6543`), `SESSION_DRIVER=cookie`, `LOG_CHANNEL=stderr`. Check **Functions → Logs** in Vercel. Run migrations locally. |
| `could not connect to server` | Use Supabase **Transaction pooler** host + port **6543** on Vercel |
| `DB_HOST` repeated / invalid | Re-enter host — **one value, no spaces** |
| `pdo_pgsql` missing | Supabase needs PostgreSQL; vercel-php includes pgsql |
| Session errors | Set `SESSION_DRIVER=cookie` |
| Build failed | Redeploy with `VERCEL_FORCE_NO_BUILD_CACHE=1` |
| Tables missing | Run `php artisan migrate --seed --force` locally |

---

## PWA (install on phone)

After deploy on Vercel (HTTPS):

- **Android Chrome:** Install banner or Settings → Install App
- **iPhone Safari:** Settings → Install App → Share → Add to Home Screen

---

## NativePHP Android / iOS (not on Vercel)

```bash
php artisan native:install android
npm run build
php artisan native:run android
```

See [NativePHP Mobile docs](https://nativephp.com/docs/mobile).

---

## API

Base URL: `/api/v1`

| Method | Endpoint | Body |
|--------|----------|------|
| POST | `/auth/otp/send` | `{ "mobile": "9876543210" }` |
| POST | `/auth/login` | `{ "mobile", "password" }` |
| GET | `/dashboard` | Bearer token |
| GET | `/members` | Bearer token |

---

## Environment reference

```env
APP_NAME="Society Manager Pro"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=
DB_SSLMODE=require

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

NATIVEPHP_APP_ID=com.societymanager.pro
FIREBASE_SERVER_KEY=
FIREBASE_PROJECT_ID=
```

---

## License

MIT
