# XY Cubic Shopee

XY Cubic Shopee helps Shopee sellers process thermal labels, order PDFs, and picking lists locally: validate uploads, merge PDFs, normalize layouts, and produce print-ready outputs—without Shopee API integration in Phase 1.

## Tech stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 12, PHP 8.3 |
| Database | MySQL 8 |
| Cache / queues | Redis (Predis) |
| Admin | Filament 5 |
| Auth (merchant UI) | Laravel Breeze |
| Permissions | Spatie Laravel Permission |
| Frontend | Blade, Tailwind CSS 3, Alpine.js, Vite |
| PDF / documents | FPDI, FPDF, Maatwebsite Excel, Spatie Browsershot |

## Requirements

- PHP 8.3+ with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`
- Composer 2
- Node.js 20+ and npm
- MySQL 8
- Redis

## Installation

```bash
git clone <repository-url> print-flow-pro
cd print-flow-pro

composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env` (database, Redis, `APP_URL`, `APP_TIMEZONE`). Then:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install
npm run build
```

### Database seeding

Seeders run in order via `DatabaseSeeder`:

| Seeder | Purpose |
| --- | --- |
| `PermissionSeeder` | Creates permissions from `config/permissions.php` |
| `RoleSeeder` | Creates roles and assigns permissions (`super_admin` receives all) |
| `BillingPlanSeeder` | Seeds Starter, Pro, and Enterprise plans |
| `AdminUserSeeder` | Creates the default super admin user |

```bash
# Full seed (permissions, roles, admin user)
php artisan db:seed

# Individual seeders
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminUserSeeder

# Reset database and reseed
php artisan migrate:fresh --seed
```

**Default super admin** (local only — change in production):

| Field | Value |
| --- | --- |
| Email | `admin@example.com` |
| Password | `password` |

Filament (`/admin`) requires the `access_admin_panel` permission. Granted to `super_admin` (all permissions) and `regional_partner` (subset). Merchants use Breeze at `/login` and `/uploads`.

## Local development

Use the Composer dev script to run the app server, queue worker, log tail, and Vite together:

```bash
composer dev
```

Or run services separately:

```bash
php artisan serve
php artisan queue:work
npm run dev
```

| URL | Purpose |
| --- | --- |
| `/` | Welcome |
| `/login`, `/register` | Breeze merchant auth |
| `/dashboard` | Authenticated merchant area |
| `/` | SaaS landing page |
| `/admin` | Filament admin panel (`access_admin_panel` permission) |
| `/uploads` | Merchant upload history (auth + verified) |

Run tests:

```bash
composer test
```

Format PHP:

```bash
./vendor/bin/pint
```

## Architecture

Application code follows a service-oriented layout:

```
app/
├── Actions/      # Single-purpose business operations
├── DTOs/         # Structured data transfer objects
├── Enums/        # Domain enumerations (e.g. roles)
├── Filament/     # Admin panel resources, pages, widgets
├── Http/         # Controllers, middleware, form requests
├── Jobs/         # Queued work
├── Models/       # Eloquent models
├── Services/     # Domain services (PDF, picking, labels)
└── Support/      # Shared helpers and utilities
```

Authorization is defined in `config/permissions.php` (groups + role assignments) with matching cases in `App\Enums\Permission` for type-safe checks.

### Domain models

| Model | Purpose |
| --- | --- |
| `Merchant` | Shopee seller account |
| `BillingPlan` | Subscription tier |
| `UploadJob` | PDF / file processing job |
| `PdfUpload` | Individual uploaded PDF |
| `PickingList` | Picking list import/output |
| `DeliveryLabel` | Generated delivery label |
| `AuditLog` | Admin, upload, and auth activity |

Activity is recorded via `App\Services\AuditLogService` (auth events, upload job lifecycle, Filament CRUD).

### Upload workflow

Merchants register at `/register` (creates a `merchants` profile automatically). Upload types:

| Type | Files |
| --- | --- |
| `order_pdf` | PDF |
| `thermal_label` | PDF |
| `picking_list` | CSV, XLS, XLSX |
| `delivery_label` | PDF |

```bash
# Merchant routes (after login + email verification)
/uploads          # history
/uploads/create   # drag & drop uploader
```

Configure limits in `.env`: `PRINTFLOW_UPLOAD_MAX_KB`, `PRINTFLOW_UPLOAD_MAX_FILES`.

Design principles (see `PROJECT_ARCHITECTURE.md` and `CURSOR_RULES.md`):

- Thin controllers; business logic in Actions and Services
- Form requests for validation
- Queue-ready jobs for heavy PDF work
- Region isolation and temp-file security (planned in Phase 1 TODO)

Filesystem disks:

- `local` — private app storage
- `temp` — short-lived upload/processing files (auto-cleanup planned)
- `public` — user-facing assets via `storage:link`

## Branch workflow

1. Branch from `main`: `feature/<short-description>` or `fix/<short-description>`
2. Keep commits focused; run `composer test` and `npm run build` before pushing
3. Open a pull request against `main` with a clear summary and test plan
4. Merge after review; deploy from tagged releases or `main` per your environment policy

## Phase 1 scope

**Included:** local file processing, thermal label normalization, PDF merging, picking list aggregation, delivery label generation, Filament admin, security cleanup, mobile-friendly upload workflows.

**Excluded:** Shopee API, OAuth, webhooks, realtime sync, advanced queue concurrency, 429 retry logic.

Track implementation progress in `TODO.md`.

## License

MIT — see [LICENSE](LICENSE).
