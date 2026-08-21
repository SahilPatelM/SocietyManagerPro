# Society Manager Pro

Mobile-first society management app built with **Laravel 12**, **Livewire**, **MySQL**, and **PWA** support. Optional **NativePHP Mobile** builds for Android/iOS.

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

- PHP **8.2+** (8.3+ recommended for NativePHP Mobile)
- Composer, Node.js 20+
- MySQL 8+

---

## Quick start (local)

```bash
git clone https://github.com/SahilPatelM/SocietyManagerPro.git
cd SocietyManagerPro

composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=society_manager_pro
DB_USERNAME=root
DB_PASSWORD=your_password
```

```bash
php artisan migrate --seed
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

Vercel can host this Laravel app using the community **vercel-php** runtime. This project already includes `vercel.json` and `api/index.php`.

### Important limitations on Vercel

| Works | Does not work well |
|-------|-------------------|
| Web UI + Livewire | Background queue workers |
| PWA install (HTTPS included) | Local file uploads (documents) |
| REST API | NativePHP mobile builds |
| MySQL (external database) | Persistent `storage/` files |

Use **Railway**, **Render**, or a **VPS** if you need queues, file storage, or long-running workers.

---

### Step 1 — Push code to GitHub

```bash
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/SahilPatelM/SocietyManagerPro.git
git push -u origin main
```

Do **not** commit `.env`. Secrets go in the Vercel dashboard only.

---

### Step 2 — Create a MySQL database (required)

Vercel does not provide MySQL. Use an external host, for example:

- [Railway](https://railway.app) — MySQL plugin
- [Aiven](https://aiven.io) — MySQL
- [PlanetScale](https://planetscale.com) — MySQL-compatible
- Any shared hosting / VPS MySQL

Note the connection details:

```
Host:     xxxxx.railway.app
Port:     3306
Database: railway
Username: root
Password: ********
```

Run migrations once from your PC (before or after Vercel deploy):

```bash
# Temporarily point .env to the remote DB, then:
php artisan migrate --seed --force
```

Or let Vercel run migrations on deploy (already configured in `composer.json` → `vercel` script).

---

### Step 3 — Create a Vercel project

1. Go to [vercel.com](https://vercel.com) and sign in
2. Click **Add New → Project**
3. **Import** your GitHub repo `SocietyManagerPro`
4. Configure:

| Setting | Value |
|---------|--------|
| Framework Preset | **Other** |
| Root Directory | `./` (project root) |
| Build Command | `npm ci && npm run build` (auto from `vercel.json`) |
| Output Directory | leave empty |
| Install Command | auto from `vercel.json` |

5. Do **not** deploy yet — add environment variables first

---

### Step 4 — Environment variables (Vercel dashboard)

Go to **Project → Settings → Environment Variables** and add:

#### Required

| Variable | Example | Notes |
|----------|---------|-------|
| `APP_NAME` | `Society Manager Pro` | |
| `APP_ENV` | `production` | |
| `APP_KEY` | `base64:...` | Run `php artisan key:generate --show` locally |
| `APP_DEBUG` | `false` | Never `true` in production |
| `APP_URL` | `https://your-app.vercel.app` | Your Vercel URL (update after first deploy) |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST` | `xxxxx.railway.app` | From your DB provider |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | `railway` | |
| `DB_USERNAME` | `root` | |
| `DB_PASSWORD` | `********` | Mark as **Sensitive** |

#### Recommended for Vercel

| Variable | Value | Why |
|----------|-------|-----|
| `SESSION_DRIVER` | `cookie` | Works without DB writes every request |
| `CACHE_STORE` | `array` | Serverless has no persistent file cache |
| `QUEUE_CONNECTION` | `sync` | No queue worker on Vercel |
| `LOG_CHANNEL` | `stderr` | Logs appear in Vercel dashboard |
| `VERCEL_FORCE_NO_BUILD_CACHE` | `1` | Helps when Composer deploy fails |

#### Optional

| Variable | Purpose |
|----------|---------|
| `FIREBASE_SERVER_KEY` | Push notifications |
| `FIREBASE_PROJECT_ID` | Push notifications |
| `MAIL_MAILER` | Email (use Resend, Mailgun, etc.) |
| `MAIL_HOST` | SMTP host |
| `MAIL_USERNAME` | SMTP user |
| `MAIL_PASSWORD` | SMTP password |

Apply variables to **Production**, **Preview**, and **Development**.

---

### Step 5 — Deploy

Click **Deploy** in Vercel, or from your PC:

```bash
npm i -g vercel
vercel login
vercel
vercel --prod
```

First deploy takes 3–8 minutes (Composer + npm + PHP runtime).

---

### Step 6 — After first deploy

1. Copy your live URL, e.g. `https://society-manager-pro.vercel.app`
2. Update **`APP_URL`** in Vercel env vars to that URL
3. **Redeploy** (Deployments → ⋮ → Redeploy)

Test:

- Login page loads
- Login works
- Dashboard loads
- PWA manifest: `https://your-app.vercel.app/manifest.webmanifest`

---

### Step 7 — Custom domain (optional)

1. Vercel → **Project → Settings → Domains**
2. Add your domain, e.g. `app.yoursociety.com`
3. Add DNS records Vercel shows (usually `CNAME`)
4. Update `APP_URL` to `https://app.yoursociety.com`
5. Redeploy

PWA install works best with a custom domain and HTTPS (Vercel provides HTTPS automatically).

---

### Troubleshooting Vercel

| Problem | Fix |
|---------|-----|
| **composer: command not found** | Do not run Composer in `installCommand`. Use `"installCommand": "npm ci"` only — `vercel-php` runs `composer install` automatically. |
| **No Output Directory named "public"** | Push `vercel.json`, `api/`, and `.vercelignore` to GitHub. In Vercel → Settings → General: **Framework Preset = Other**, **Output Directory = public**. Redeploy. |
| 500 error | Check Vercel → **Functions → Logs**; verify `APP_KEY` and DB vars |
| CSS/JS missing | Ensure `npm run build` succeeded; check **Build Logs** |
| Database connection failed | Allow remote connections on MySQL host; verify host/port/password |
| Migration failed | Run `php artisan migrate --force` locally against remote DB |
| Deploy cache error | Set `VERCEL_FORCE_NO_BUILD_CACHE=1` and redeploy |
| Session/login issues | Set `SESSION_DRIVER=cookie` and redeploy |
| File upload fails | Expected on Vercel — use S3 (see below) |

---

### File uploads on Vercel (documents)

Local disk storage is **not persistent** on Vercel. For document uploads, configure S3-compatible storage:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

Or use [Cloudflare R2](https://www.cloudflare.com/products/r2/) (S3-compatible).

---

## PWA (install on phone)

After deploy on Vercel (HTTPS):

- **Android Chrome:** tap **Install** banner or Settings → Install App
- **iPhone Safari:** Settings → Install App → follow steps (Share → Add to Home Screen)

Icons and manifest are in `public/manifest.webmanifest` and `public/icons/`.

---

## NativePHP Android / iOS (not on Vercel)

Native apps are built locally, not on Vercel:

```bash
# Add public/icon.png (1024x1024)
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

## Project structure

```
app/
  Enums/
  Http/Controllers/Api/
  Http/Resources/
  Livewire/           # Mobile-first UI
  Models/
  Repositories/
  Services/
api/index.php         # Vercel entry point
vercel.json           # Vercel config
public/
  manifest.webmanifest
  sw.js
  icons/
database/migrations/
lang/en, lang/gu
resources/views/layouts/mobile.blade.php
routes/web.php, routes/api.php
```

---

## Environment reference (local `.env`)

```env
APP_NAME="Society Manager Pro"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=society_manager_pro
DB_USERNAME=root
DB_PASSWORD=

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
