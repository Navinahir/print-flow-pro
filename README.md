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
cp .env.example .env   # local only — .env is gitignored and must never be pushed
php artisan key:generate
```

Configure `.env` on each machine separately (database, Redis, `APP_KEY`, `APP_URL`). **Do not commit or deploy `.env` via git** — the VPS keeps its own production `.env` (create from `.env.example` once on the server). Domain hostnames are in the database, not in `.env`.

Then:

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
| `RoleSeeder` | Creates roles and assigns permissions |
| `DomainSettingSeeder` | Seeds merchant domain hosts, locales, branding, features, and upload limits |
| `BillingPlanSeeder` | Seeds Starter, Pro, and Enterprise plans |
| `AdminUserSeeder` | Creates default admin + merchant local users |

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

**Seeded users (V2.4)** — local only; change in production:

| Email | Role | Merchant (`:8001`) | Admin (`:8002/boss`) |
| --- | --- | --- | --- |
| `staff@example.com` | `admin` | No | Yes |
| `merchant@example.com` | `merchant` | Yes | No |

Password for all: `password`

### Roles & surface access (V2.4)

Two roles only, stored on `users.role` and synced to Spatie for future permission expansion:

| Role | Merchant portal | Admin dashboard |
| --- | --- | --- |
| `admin` | **Blocked** | Allowed |
| `merchant` | Allowed | **Blocked** |

**Enforcement (middleware, not controllers):**

| Middleware | Alias | Where applied |
| --- | --- | --- |
| `EnsureMerchantAccess` | `access.merchant` | Merchant domain stack + authenticated Breeze routes |
| `EnsureAdminAccess` | `access.admin` | Filament `authMiddleware` (after `Authenticate`) |

Unauthorized access: logout, session invalidation, safe redirect to the surface login (or 403 for JSON).

**Breeze merchant login** (`LoginRequest`): rejects users who cannot access the merchant surface (e.g. `admin` staff).

**Filament** (`User::canAccessPanel`): requires an admin-surface role plus `access_admin_panel` (granted to `admin` via `RoleSeeder`).

New merchants register at `/register` → `merchant` role + `merchants` record. Spatie permission groups in `config/permissions.php` are ready for finer Filament policies later; the milestone does not use a large permission matrix.

### Authentication flow

```
Marketing (:8000)     → public pages only (no app login)

Merchant (:8001)      → Breeze /login, /register
                      → LoginRequest checks merchant-surface role
                      → access.merchant on /dashboard, /uploads, /profile
                      → Separate session cookie (ConfigureDomainSession)

Admin (:8002/boss)    → Filament login
                      → canAccessPanel + EnsureAdminAccess
                      → ObfuscateAdminAccess hides non-/boss paths
                      → Separate session cookie from merchant host
```

`admin` and `merchant` are strictly separated by surface.

## Multi-domain architecture

**One Laravel codebase, one `public/` folder** — not separate project paths per domain. Nginx (or multiple `artisan serve` ports locally) points every vhost at the same app; Laravel picks the surface from the HTTP `Host` header.

| Layer | How isolation works |
| --- | --- |
| Marketing / admin | `Route::domain()` on `MARKETING_DOMAIN` and `ADMIN_DOMAIN` |
| Merchant (all regions) | `ResolveRegion` + `EnsureExpectedSurface` middleware (host → TW/PH/VN from DB) |
| Unknown host | `RejectUnmappedDomain` → 404 when `DOMAIN_ROUTING_ENABLED=true` |

**Routing toggles** (`DOMAIN_ROUTING_ENABLED`, `DOMAIN_PORT_ROUTING`) live in `.env`.

**All hostnames** (marketing, admin, merchant regions) are stored in `domain_settings` and loaded through `DomainConfigurationService`. Run `DomainSettingSeeder` after migrate (included in `db:seed`). Hosts are **not** read from `.env` — only `APP_ENV` selects defaults when seeding (`local` → `localhost:800x`, `production` → `xycubic.com`, etc.).

**Local vs live:** locally, `DOMAIN_PORT_ROUTING=true` and seeded hosts use `localhost:8000` / `8001` / `8002`. In production, the same table uses `xycubic.com`, `tw.xycubic.com`, and `manage-xy.xycubic.com` on ports 80/443 (no port in URLs).

| Surface | Production host | Root URL behaviour |
| --- | --- | --- |
| Marketing | `xycubic.com` | `/` → `/tw` or `/en` |
| Merchant (TW) | `tw.xycubic.com` | `/` → `/login` (guest) or `/dashboard` (signed in) |
| Admin | `manage-xy.xycubic.com` | `/` → **403** (guest); use `/boss` to sign in |

Future regions (`ph.xycubic.com`, `vn.xycubic.com`) are seeded in `domain_settings`. Inactive regions return **403** with `Region Not Activated Yet`. Enable a region by setting `is_active = true` on its row (or re-seeding from updated `config/domains.php` fallback definitions).

### Domain configuration (database-driven)

All surfaces share `domain_settings`. Merchant rows use `surface = merchant` and related locale/feature tables.

| Table | Purpose |
| --- | --- |
| `domain_settings` | Host, surface (`marketing` / `admin` / `merchant`), region key, active flag, session cookie, branding, JSON settings |
| `domain_locales` | Supported locales per **merchant** region |
| `domain_features` | Feature toggles per **merchant** region (`uploads`, printing modules, etc.) |

**Infrastructure rows** (seeded, editable without server `.env` access):

| `region_key` | `surface` | Production `host` | Notes |
| --- | --- | --- | --- |
| `marketing` | `marketing` | `xycubic.com` | Public landing |
| `admin` | `admin` | `manage-xy.xycubic.com` | `settings.path_prefix` → `boss` (Filament) |
| `tw` | `merchant` | `tw.xycubic.com` | Taiwan merchant portal |
| `ph` | `merchant` | `ph.xycubic.com` | Inactive until `is_active = true` |
| `vn` | `merchant` | `vn.xycubic.com` | Inactive until `is_active = true` |

**Local seeded hosts** (`APP_ENV=local`): `localhost:8000`, `localhost:8002`, `localhost:8001`, `localhost:8003`, `localhost:8004`.

**Architecture:**

```
DomainSettingRepository  →  DomainConfigurationService  →  MerchantConfig (views / requests)
         ↑                              ↑
   Eloquent models              ResolveRegion middleware (sets current region)
```

| Class | Role |
| --- | --- |
| `App\Models\DomainSetting` | Eloquent model for merchant region rows |
| `App\Models\DomainLocale` | Locales linked to a domain setting |
| `App\Models\DomainFeature` | Feature flags linked to a domain setting |
| `App\Contracts\Domain\DomainSettingRepositoryInterface` | Repository contract |
| `App\Repositories\Domain\DomainSettingRepository` | Loads settings with locales + features |
| `App\Services\Domain\DomainConfigurationService` | Cached config, routing map, feature checks |
| `App\DTOs\Domain\MerchantDomainConfig` | Immutable config DTO bound per request |
| `App\Support\MerchantConfig` | Static accessor for Blade/controllers (`MerchantConfig::get()`, `MerchantConfig::feature()`) |

**Usage in merchant code:**

```php
use App\Support\MerchantConfig;

MerchantConfig::get('brand.name');
MerchantConfig::get('upload.max_file_size_kb');
MerchantConfig::feature('uploads');
```

`ResolveRegion` resolves the host, sets `MerchantDomainConfig` as the current region, and applies the default locale. Config is cached under `domain_configuration.merchant_regions` and invalidated when domain models change.

**Seeding / changing merchant domains:**

```bash
php artisan db:seed --class=DomainSettingSeeder
```

Initial values come from `config/domains.php` → `fallback_merchants` (used when the database is empty or as the seeder source). For production, edit rows in the database or extend the seeder — do not add per-region `.env` keys.

**Production `.env` (minimal — hosts come from the database):**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tw.xycubic.com

DOMAIN_ROUTING_ENABLED=true
DOMAIN_PORT_ROUTING=false
```

**`.env` deleted on the server?** Restore it via GitHub Actions secrets (`APP_KEY`, `DB_*`, `VPS_IP`, `SSH_PRIVATE_KEY`) — the deploy workflow runs `scripts/sync-production-env.sh` to rewrite `.env` on the VPS without SSH.

After deploy (or auto-deploy on push to `main` via `.github/workflows/deploy.yml`):

```bash
php artisan migrate --force
php artisan db:seed --class=DomainSettingSeeder
php artisan config:clear
php artisan domain:validate --fix-hosts
```

`domain:validate` checks routing flags, loopback hosts in production, and all `domain_settings.host` rows. `--fix-hosts` re-runs `DomainSettingSeeder` from `config/domains.php` fallbacks (production hostnames when `APP_ENV=production`).

**Change a live hostname without SSH:** update the `host` column on the matching `domain_settings` row (e.g. `region_key = tw`). Cache clears automatically on save.

PHPUnit sets `DOMAIN_ROUTING_ENABLED=false` so feature tests keep using a single `localhost` host without breaking named routes. Domain configuration tests use the full merchant URL (e.g. `http://tw.xycubic.com/login`) so host resolution matches production behavior.

| File | Registered on |
| --- | --- |
| `routes/marketing.php` | `MARKETING_DOMAIN` only |
| `routes/merchant.php` | All merchant hosts (middleware enforces surface + active region) |
| `routes/admin.php` | `ADMIN_DOMAIN` fallbacks (404 for unmapped paths) |

`App\Providers\RouteServiceProvider` registers domain groups. Filament is constrained to the admin domain and `ADMIN_PATH_PREFIX` (default `boss`) in `AdminPanelProvider`.

**Troubleshooting:** If `tw.xycubic.com` or `manage-xy.xycubic.com` show the marketing page at `/tw`, production is using a local `.env` or stale config cache. Set `APP_ENV=production`, `DOMAIN_PORT_ROUTING=false`, run `php artisan config:clear`, then `php artisan domain:validate --fix-hosts`. Ensure Nginx points all vhosts at the same Laravel `public/` directory (routing is by `Host`, not separate code paths).

### Middleware

| Alias | Class | Role |
| --- | --- | --- |
| `domain.resolve` | `ResolveRegion` | Detect host, set `DomainContext`, locale, `config('domains.current')` |
| `domain.session` | `ConfigureDomainSession` | Per-surface session cookie name and host-scoped cookie domain |
| `domain.reject-unmapped` | `RejectUnmappedDomain` | 404 for unknown hosts when routing is enabled |
| `region.active` | `EnsureRegionIsActive` | 403 for inactive merchant regions |
| `admin.obfuscate` | `ObfuscateAdminAccess` | On admin host, allow only `/boss` (+ Filament subpaths); 404 elsewhere |
| `access.merchant` | `EnsureMerchantAccess` | Merchant-surface role gate (logout if unauthorized) |
| `access.admin` | `EnsureAdminAccess` | Admin-surface role gate (Filament auth stack) |

`SetMarketingLocale` (web group) sets `app()->getLocale()` on the marketing surface from the locale cookie, path (`/tw`, `/en`), defaulting to `zh-TW`.

`ResolveRegion` binds `App\Support\Domains\DomainContext` and `App\DTOs\Domain\MerchantDomainConfig` into the container for services, branding, and future payment isolation.

### Marketing site

Public landing only — no Breeze login. Merchant signup CTA links to the TW merchant host `/register`.

| Local URL | Purpose |
| --- | --- |
| `http://localhost:8000/` | Redirects to `/tw` or `/en` (locale cookie / first visit → `/tw`) |
| `http://localhost:8000/tw` | Traditional Chinese landing (default) |
| `http://localhost:8000/en` | English landing |

**Stack:** `resources/views/marketing/` (layout + components), `resources/css/marketing.css`, `resources/js/marketing.js`, `lang/{en,zh-TW}/marketing.php`, `config/marketing.php`.

**UX:** Responsive nav (drawer below 992px width), dark/light theme (`localStorage`), locale switcher (`localStorage` + cookie).

### Environment variables

| Variable | Purpose |
| --- | --- |
| `DOMAIN_ROUTING_ENABLED` | `true` — host-based surfaces; `false` — single-host dev/tests |
| `DOMAIN_PORT_ROUTING` | `true` locally (artisan serve ports); **must be `false` in production** |
| `APP_ENV` | `local` vs `production` — controls seeded hosts in `DomainSettingSeeder` (via `config/domains.php`) |

Merchant hosts and `is_active` are **not** in `.env` — use `domain_settings` only.

PHPUnit sets `DOMAIN_ROUTING_ENABLED=false` so most feature tests use a single host without breaking named routes. Domain tests enable routing explicitly.

### Local development (port-based)

**Frontend assets:** Do not run `npm run build` manually during development. Start Vite alongside PHP so CSS/JS reload automatically:

```bash
# Merchant portal only (localhost:8001) — recommended for UI work
composer dev:merchant
# or: npm run dev:merchant

# All three domains (8000 marketing, 8001 merchant, 8002 admin) + one Vite dev server
composer dev:domains
# or: npm run dev:all

# Full stack (single artisan serve + queue + logs + Vite)
composer dev
```

If you cannot run the Vite dev server, use automatic production rebuilds instead:

```bash
composer dev:watch
# or: npm run watch
```

Only run `npm run build` once before deploying to production (the VPS deploy script does this automatically).

```bash
# Terminal 1 — marketing (xycubic.com)
php artisan serve --host=localhost --port=8000

# Terminal 2 — merchant TW (tw.xycubic.com)
php artisan serve --host=localhost --port=8001

# Terminal 3 — admin (manage-xy.xycubic.com)
php artisan serve --host=localhost --port=8002
```

`.env` for local ports (see `.env.example`). Merchant hosts are seeded into `domain_settings` (TW → `localhost:8001`, PH → `localhost:8003`, VN → `localhost:8004` when `APP_ENV=local`):

```env
APP_ENV=local
APP_URL=http://localhost:8001
MARKETING_DOMAIN=localhost:8000
ADMIN_DOMAIN=localhost:8002
ADMIN_PATH_PREFIX=boss
DOMAIN_ROUTING_ENABLED=true
DOMAIN_PORT_ROUTING=true
```

`DOMAIN_PORT_ROUTING=true` skips `Route::domain()` for marketing/admin so `artisan serve` on ports 8000–8002 works. **Must be `false` in production.**

| Production URL | Expected root behaviour |
| --- | --- |
| `https://xycubic.com/` | Redirect to `/tw` or `/en` |
| `https://tw.xycubic.com/` | Redirect to `/login` (guest) or `/dashboard` (authenticated) |
| `https://manage-xy.xycubic.com/` | **403** for guests at `/`; sign in at `/boss` |

| Local URL | Surface |
| --- | --- |
| http://localhost:8000/ | Redirects to `/tw` or `/en` |
| http://localhost:8000/tw | Marketing landing (zh-TW, default) |
| http://localhost:8000/en | Marketing landing (en) |
| http://localhost:8001/login | Merchant auth |
| http://localhost:8001/dashboard | Merchant dashboard |
| http://localhost:8002/boss | Filament admin (`admin` only) |
| http://localhost:8002/ | **403** (guest) or Filament when signed in at `/boss` |
| http://localhost:8002/admin | **404** (obfuscated) |

Route smoke checks:

```bash
php artisan route:list --domain=localhost:8001
php artisan route:list --path=boss
```

### Role testing (local)

After `php artisan migrate --seed` (or `db:seed`):

1. Start three servers (ports 8000 / 8001 / 8002) as above.
2. **Merchant isolation:** log in at `http://localhost:8001/login` as `merchant@example.com` → dashboard works; visiting `http://localhost:8002/boss` should not grant admin access.
3. **Admin isolation:** log in at `http://localhost:8002/boss` as `staff@example.com` → Filament works; `http://localhost:8001/login` should reject or logout on merchant routes.
4. **Cross-surface check:** `staff@example.com` must fail on merchant login/routes, while `merchant@example.com` must fail on admin dashboard.

Automated coverage: `tests/Feature/SurfaceAccessTest.php`.

## Merchant UI (TW portal — port 8001)

The merchant domain uses a **dedicated Blade UI foundation** separate from the marketing site and Filament admin panel. Legacy Breeze views under `resources/views/layouts/` and `resources/views/auth/` remain in the repo but are no longer used by merchant routes.

### View structure

```
resources/views/merchant/
├── layouts/          app.blade.php (authenticated), guest.blade.php (auth pages)
├── partials/         header, sidebar, sidebar-footer, mobile-nav, footer
├── components/       breadcrumb, page-header, empty-state, loading-state, page-loader
│   └── form/         label, error, field (required asterisk + validation)
├── dashboard/        merchant home
├── pages/            uploads, profile
├── auth/             login, register, password reset, verify email
└── printing/         master-detail printing modules
    ├── order-details/
    ├── logistics-labels/
    ├── picking-list/
    └── delivery-labels/
```

### Assets

| File | Purpose |
| --- | --- |
| `resources/css/merchant.css` | Tailwind + merchant component classes + SweetAlert2 styles |
| `resources/css/merchant/sidebar.css` | Collapsible sidebar width, icon-only mode, pinned footer |
| `resources/css/merchant/form.css` | Form label required indicator + validation error styling |
| `resources/css/merchant/page-loader.css` | Full-page locale-switch overlay + spinner |
| `resources/js/merchant.js` | Entry point (Alpine shell, upload form, flash toasts, SweetAlert2) |
| `resources/js/merchant/storage.js` | `localStorage` helpers (sidebar collapse, locale-switch flag) |
| `resources/js/merchant/shell.js` | Alpine `merchantShell` (sidebar open/collapse, locale loader state) |
| `resources/js/merchant/ajax.js` | Axios client with CSRF + error toasts (`window.MerchantAjax`) |
| `resources/js/merchant/toast.js` | Toast notifications (`window.MerchantToast`) |
| `resources/js/merchant/sweetalert.js` | Confirm dialogs (`window.MerchantAlert`) |

### Sticky footer (authenticated layout)

`merchant.layouts.app` uses a **Flexbox sticky footer** (no `position: fixed`):

| Class | Role |
| --- | --- |
| `.merchant-layout` | Root shell — `min-h-screen flex flex-col` |
| `.merchant-layout__body` | Content column (sidebar offset) — `flex-1 flex flex-col` |
| `.merchant-layout__main` | Page content — `flex-1` expands to fill available height |
| `.merchant-layout__footer` | Footer — `shrink-0`; sits at viewport bottom when content is short |

Long pages scroll normally and the footer follows content below the fold. Applies globally to dashboard, uploads, profile, and all printing modules (`printing-module` extends `app`).

### Sidebar collapse & footer

The authenticated layout sidebar supports **collapse/expand** via a header toggle (desktop). When collapsed, only icons are shown; labels and section titles are hidden; content offset shrinks from `lg:pl-64` to `lg:pl-20`. State persists in `localStorage` (`merchant_sidebar_collapsed`) across navigation, refresh, and login.

**Profile** and **Log out** live in a pinned **sidebar footer** (`merchant.partials.sidebar-footer`) — always visible at the bottom of the sidebar. Tooltips (`title`) appear on footer links when collapsed. The navbar user menu shows the signed-in name only (actions moved to the sidebar).

### Language switch loader

Changing language via `x-merchant.locale-switcher` shows a full-page blur overlay (`x-merchant.page-loader`) until the redirected page finishes loading. A `merchant_locale_switching` flag in `localStorage` keeps the overlay visible across the redirect; it clears on `window.load` after Alpine initializes.

### Form components

Merchant forms use reusable Blade components (registered in `AppServiceProvider`):

| Component | Purpose |
| --- | --- |
| `x-merchant.form.label` | Localized label with optional red `*` required indicator |
| `x-merchant.form.error` | Validation message below the field (supports named error bags) |
| `x-merchant.form.field` | Label + slot + error wrapper |

HTML `required` attributes are **not** used on merchant inputs; validation remains on Laravel form requests. Placeholders are localized in `lang/*/merchant.php` and `lang/*/auth.php`.

Build merchant assets:

```bash
npm run build   # or npm run dev
```

Vite registers `resources/css/merchant.css` and `resources/js/merchant.js` alongside the default app bundle.

### Localization

All merchant user-facing strings use Laravel `__()` with keys in:

- `lang/en/merchant.php`, `lang/en/auth.php`
- `lang/zh-TW/merchant.php`, `lang/zh-TW/auth.php`

On the TW merchant domain (`tw.xycubic.com` / `localhost:8001`), `ResolveRegion` sets the default locale from `domain_locales`. Merchants can switch between seeded locales (TW: `zh-TW` + `en`) via the navbar **Language** dropdown; preference persists in session (`LocaleService` + `SetMerchantLocale` middleware).

### Theme (light / dark)

Merchants can toggle **Light**, **Dark**, or **System** theme from the navbar. Preference persists in a cookie + `localStorage` (`ThemeService`, `resources/js/merchant/theme.js`). Tailwind `darkMode: 'class'` applies across merchant layouts, cards, preview panes, and nav controls. Print output is unaffected (dedicated `@media print` rules in `resources/css/merchant/preview/print.css`).

### Merchant portal status

The TW merchant portal (port 8001) includes Milestone 1 printing workspaces plus **Milestone 2 upload processing**: async jobs for thermal labels, order PDF (spreadsheet → A4), and picking list (spreadsheet → A4), with upload history, type-specific detail pages, per-file status, combined/separate output modes, partial batch success (`completed_with_errors`), regenerate (whole job or single print output), spreadsheet preview modal, and a full-page blur overlay during regeneration. Delivery label upload processing remains planned. Track remaining items in `TODO.md`, `MILESTONE_1_AUDIT.md`, and `MILESTONE_2_AUDIT.md`.

### Local merchant URLs

| URL | Page |
| --- | --- |
| http://localhost:8001/login | Merchant login |
| http://localhost:8001/dashboard | Merchant dashboard |
| http://localhost:8001/uploads | Upload history |
| http://localhost:8001/uploads/create | New upload |
| http://localhost:8001/uploads/{id} | Upload detail (status, source files, print outputs, preview) |
| http://localhost:8001/profile | Account settings |
| http://localhost:8001/printing/order-details | Order details workspace |
| http://localhost:8001/printing/logistics-labels | Logistics labels workspace |
| http://localhost:8001/printing/picking-list | Picking list workspace |
| http://localhost:8001/printing/delivery-labels | Delivery labels workspace |

### Printing modules (master-detail shells)

Four printing workspaces share a master-detail layout (`merchant.layouts.printing-module`): list pane (left) + preview engine (right).

| Module | URL | Feature flag |
| --- | --- | --- |
| Order details | `/printing/order-details` | `printing_order_details` |
| Logistics labels | `/printing/logistics-labels` | `printing_logistics_labels` |
| Picking list | `/printing/picking-list` | `printing_picking_list` |
| Delivery labels | `/printing/delivery-labels` | `printing_delivery_labels` |

**Architecture:**

```
routes/merchant/printing.php
    → App\Http\Controllers\Merchant\Printing\*Controller
    → App\Services\Merchant\Printing\*Service
    → merchant.printing.{module}.index (Blade)
```

### Preview engine (100×150 mm thermal canvas)

Reusable Blade components (registered as `x-merchant.preview.*`):

| Component | Purpose |
| --- | --- |
| `PreviewWrapper` | Outer shell, loading overlay, toolbar slot |
| `PreviewContainer` | Fixed 100×150 mm portrait canvas (2:3 ratio), responsive scaling, safe zone overlay |
| `PreviewSafeZone` | Dashed 5 mm inset print boundary guide (toggleable) |
| `PreviewAspectWarning` | Amber warning banner + force-adjustment toggle when asset ratio deviates >10% from 2:3 |
| `PreviewToolbar` | Heading, description, safe-zone toggle, print action |

**Paths:**

| Layer | Location |
| --- | --- |
| Blade classes | `app/View/Components/Merchant/Preview/` |
| Blade views | `resources/views/merchant/components/preview/` |
| CSS | `resources/css/merchant/preview/` (imported via `merchant.css`) |
| JS | `resources/js/merchant/preview/` (`constants.js`, `scale.js`, `safe-zone.js`, `aspect-ratio.js`, `engine.js`) |

The thermal preview canvas uses a logical 100×150 mm portrait surface and scales down via `ResizeObserver` to fit the stage. A4 upload outputs (thermal sheets, order PDF, picking list) use separate PDF preview iframes on the upload detail page.

**Safe print zone:** `PreviewSafeZone` renders a dashed amber guide inset 5 mm from each edge (percentage-based insets scale with the canvas). Toggle visibility via **Hide safe zone** / **Show safe zone** in the preview toolbar (`safeZoneVisible` on `printingWorkspace` / `merchantPreview` Alpine state). Shared automatically across all printing modules through `PreviewContainer`.

**Aspect ratio validation:** `AspectRatioValidationService` validates uploaded assets and list-item dimensions against the 150×100 mm (3:2) target. Deviation above **10%** triggers an amber warning banner (`PreviewAspectWarning`), a pulsing canvas border, and a SweetAlert2 dialog. Merchants can enable **Force adjustment** to proceed despite the warning. Validation runs via AJAX (`POST /printing/aspect-ratio/validate`) and mirrors client-side logic in `aspect-ratio.js`. Placeholder list item `placeholder-2` (800×600) demonstrates the warning flow.

**Live HTML preview content** is implemented for all four printing modules. Sample list items ship with embedded preview payloads; selecting an item renders module-specific content inside the 150×100 mm canvas. AJAX refresh is available via `POST /printing/preview` (`printing.preview.show`).

| Module | Preview service | Content |
| --- | --- | --- |
| Order details | `OrderDetailsPreviewService` | Order metadata, line items, summary totals |
| Logistics labels | `LogisticsLabelsPreviewService` | Carrier, tracking, barcode placeholder, recipient |
| Picking list | `PickingListPreviewService` | SKU table, locations, quantities |
| Delivery labels | `DeliveryLabelPreviewService` | Recipient, auto-shrink address, remarks, tracking |

**Preview architecture:**

```
PrintingPreviewController
    → PrintingPreviewResolver
    → {Module}PreviewService → Preview DTO (PrintingPreviewPayload)
    → preview-content.blade.php (module-specific partials)
```

Shared JS: `resources/js/merchant/preview/preview-fetch.js` (AJAX refresh on item selection). Shared CSS: `resources/css/merchant/printing/previews/`. Localization: `merchant.printing.preview.*` and `merchant.delivery_labels.*` (en + zh-TW).

Feature toggles per region in `domain_features`. TW region seeds all printing modules enabled via `DomainSettingSeeder`. Alpine `printingWorkspace` integrates with `registerMerchantPreview()` for scale refresh and `refreshSelectedPreview()` on item selection.

### Database-driven preview configuration

Preview dimensions and behavior are stored in `domain_settings.settings.preview` (seeded via `config/domains.php` → `DomainSettingSeeder`):

| Key | Default | Purpose |
| --- | --- | --- |
| `width_mm` | 100 | Canvas width (thermal preview) |
| `height_mm` | 150 | Canvas height (thermal preview) |
| `aspect_ratio` | 0.667 | Target ratio for validation (2:3 portrait) |
| `safe_zone_inset_mm` | 5 | Safe print zone inset |
| `default_zoom` | 1.0 | Max scale factor |
| `scaling_behavior` | `fit` | Responsive scaling mode |

`PreviewConfigurationService` reads values via `MerchantConfig`. `PreviewContainer`, `AspectRatioValidationService`, and client-side scaling (`scale.js`) consume these settings — no hardcoded dimensions in production paths.

### Upload detail preview

Upload show pages (`/uploads/{id}`) use a type-specific layout: uploaded source files (with per-file success/error status), processing summary, and print-ready output cards (preview, download, print, regenerate). `UploadPreviewService` resolves print-job PDF previews for thermal, order PDF, and picking list jobs; delivery labels use the legacy preview DTO. AJAX refresh via `POST /uploads/{upload}/preview` while jobs are processing. Regeneration shows a full-page blurred overlay (`upload-preview.js` + `uploads.css`).

### Print workflow

Browser print targets only the preview canvas (`data-print-area`). Shared `printPreview()` JS (`resources/js/merchant/preview/print.js`) + `@media print` CSS hide chrome and safe-zone guides. Print button is enabled on printing workspaces and upload detail pages (`x-merchant.preview.print-button`).

### Delivery labels — courier address auto-shrink & CSV import

The delivery labels module uses a dedicated layout and asset bundle so other printing modules stay unchanged.

| Layer | Location |
| --- | --- |
| Typography service | `app/Services/Merchant/Printing/DeliveryLabels/CourierAddressTypographyService.php` |
| CSV reader / mapper / import | `CourierCsvReaderService`, `DeliveryLabelCsvRowMapper`, `DeliveryLabelCsvImportService` |
| CSV header detector | `app/Services/Merchant/Printing/DeliveryLabels/CourierCsvHeaderDetector.php` |
| Preview builder | `app/Services/Merchant/Printing/DeliveryLabels/DeliveryLabelPreviewService.php` |
| Upload controller | `DeliveryLabelCsvUploadController` → `POST /printing/delivery-labels/csv` |
| DTOs | `app/DTOs/Merchant/Printing/DeliveryLabels/` |
| Views | `resources/views/merchant/printing/delivery-labels/` |
| JS | `resources/js/merchant/printing/delivery-labels/` (`font-shrink.js`, `layout.js`, `workspace.js`, `csv-upload.js`) |
| CSS | `resources/css/merchant/printing/delivery-labels/` |
| Vite entry | `merchant-delivery-labels.js` / `merchant-delivery-labels.css` |

**Behavior:**

- Flexbox vertical label layout inside the 150×100 mm canvas (recipient → address → spacer → remarks).
- Courier address typography starts at **18 px** and shrinks proportionally for addresses longer than **35 characters**, with a **14 px** floor.
- Multi-line addresses wrap on word boundaries or honour explicit newlines.
- Remarks section is pushed to the bottom via flex `margin-top: auto` and never overlaps the address block.
- Client-side `fitAddressFontSize()` re-measures on canvas resize for responsive preview rendering.

**CSV workflow:**

1. Merchant uploads a CSV via AJAX (`csv-upload.js`) with SweetAlert2 confirmation and toast feedback.
2. `CourierCsvReaderService` parses headers and rows; `CourierCsvHeaderDetector` maps recipient, address, remarks, tracking, and carrier columns.
3. `DeliveryLabelCsvImportService` creates an `UploadJob`, persists `DeliveryLabel` rows, and returns list items with embedded preview payloads.
4. Imported labels appear in the left pane; selecting one renders the auto-shrink preview on the right canvas.

Sample list items demonstrate standard, long, and multi-line addresses when no DB records exist. Strings live under `merchant.delivery_labels.*` (en + zh-TW).

### Production deployment

- Point DNS A/AAAA records for marketing, `tw`, and `manage-xy` hosts to the app.
- **Nginx:** one `root` → Laravel `public/` for all vhosts (routing is by `Host`). All three production hosts share the same document root.
- Use a **production-specific `.env`** — not the local port-based file. Required: `APP_ENV=production`, `DOMAIN_ROUTING_ENABLED=true`, `DOMAIN_PORT_ROUTING=false`.
- Seed or verify `domain_settings` (marketing, admin, merchant hosts) — see § Domain configuration.
- Run `php artisan config:clear` after any `.env` change (avoid stale `config:cache` from a dev machine).
- Run `php artisan domain:validate --fix-hosts` to verify hosts and re-seed `domain_settings` if needed.
- Use separate session cookie names per surface so cookies do not leak across subdomains (stored on `domain_settings.session_cookie` for merchants).
- Enable PH/VN by setting `is_active = true` on the corresponding `domain_settings` row when ready; until then, those hosts respond with 403.

**Host matching:** `DomainResolver` normalizes HTTPS hosts (e.g. `tw.xycubic.com:443` → `tw.xycubic.com`) so SSL termination does not break surface detection.

## Local development

Use the Composer dev scripts so Vite rebuilds assets automatically — **no manual `npm run build` needed** while developing:

| Command | Use when |
| --- | --- |
| `composer dev:merchant` | Working on merchant UI (port 8001) |
| `composer dev:domains` | Testing marketing + merchant + admin together |
| `composer dev` | Single-server mode with queue + logs |
| `composer dev:watch` | Fallback: auto `vite build` on file changes (no HMR) |

For multi-domain local testing without the scripts above, run three `php artisan serve` instances **plus** `npm run dev` in a fourth terminal.

Or run services separately:

```bash
php artisan serve --port=8001
php artisan queue:work
npm run dev
```

| URL (single-server / tests) | Purpose |
| --- | --- |
| `/` | Marketing home (`DOMAIN_ROUTING_ENABLED=false` in PHPUnit) |
| `/login`, `/register` | Merchant auth (dedicated `merchant/` views + assets) |
| `/dashboard` | Merchant dashboard (`merchant.dashboard.index`) |
| `/boss` | Filament admin (admin-surface roles + `access_admin_panel` permission) |
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
├── Contracts/    # Repository interfaces
├── DTOs/         # Structured data transfer objects (incl. MerchantDomainConfig)
├── Enums/        # Domain enumerations (e.g. roles)
├── Filament/     # Admin panel resources, pages, widgets
├── Http/         # Controllers, middleware (`SetMarketingLocale`, …), form requests
├── Jobs/         # Queued work
├── Models/       # Eloquent models (incl. DomainSetting, DomainLocale, DomainFeature)
├── Repositories/ # DomainSettingRepository
├── Services/     # Domain services (PDF, picking, labels, DomainConfigurationService)
├── Support/      # Shared helpers (MerchantConfig, DomainResolver)
└── View/         # Blade components; `MarketingComposer` (merchant register URL for landing)
```

Authorization (V2.4 milestone):

1. **Surface roles** — `users.role` (`admin`, `merchant`) + `EnsureAdminAccess` / `EnsureMerchantAccess`.
2. **Spatie (lightweight)** — two roles mirrored in `config/permissions.php`; `admin` gets admin-panel subset, `merchant` none for now. Expand groups/permissions when Filament resources need finer gates.

**Future scalability:** add permissions to `config/permissions.php` and `App\Enums\Permission` without changing surface middleware; optional team/region scopes can layer on `users.role` later.

### Domain models

| Model | Purpose |
| --- | --- |
| `DomainSetting` | Merchant region host, branding, settings JSON |
| `DomainLocale` | Locales per merchant region |
| `DomainFeature` | Feature toggles per merchant region |
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

| Type | Input files | Output |
| --- | --- | --- |
| `order_pdf` | CSV, XLS, XLSX (Shopee picking export) | A4 order PDF (2 orders per page) |
| `thermal_label` | PDF | Normalized A4 sheets (100×150 mm labels) |
| `picking_list` | CSV, XLS, XLSX | A4 picking sheet PDF |
| `delivery_label` | PDF | *(processing planned)* |

```bash
# Merchant routes (after login + email verification)
/uploads                              # history (ID/type link to detail)
/uploads/create                       # drag & drop uploader with type guide + samples
/uploads/{upload}                     # detail
/uploads/{upload}/regenerate          # reprocess whole job
/uploads/{upload}/print-jobs/{id}/regenerate  # reprocess one output
/uploads/{upload}/spreadsheets/{index}/preview|download
```

Configure upload limits per region in `domain_settings.settings` (seeded by `DomainSettingSeeder`) or via `MerchantConfig::get('upload.max_file_size_kb')`.

Design principles:

- Thin controllers; business logic in Actions and Services
- Form requests for validation
- Queue-ready jobs for heavy PDF work — see `MILESTONE_2_AUDIT.md` (M2)
- Multi-domain routing and per-region session isolation (`config/domains.php`, domain middleware)

Filesystem disks:

- `local` — private app storage
- `temp` — short-lived upload/processing files (M2: download shred + 10-min cron purge — see `MILESTONE_2_AUDIT.md`)
- `public` — user-facing assets via `storage:link`

## Branch workflow

1. Branch from `main`: `feature/<short-description>` or `fix/<short-description>`
2. Keep commits focused; run `composer test` and `npm run build` before pushing
3. Open a pull request against `main` with a clear summary and test plan
4. Merge after review; deploy from tagged releases or `main` per your environment policy

## Phase 1 scope

**Included:** local file processing, thermal label normalization, PDF merging, picking list aggregation, delivery label generation, Filament admin, security cleanup, mobile-friendly upload workflows.

**Excluded:** Shopee API, OAuth, webhooks, realtime sync, advanced queue concurrency, 429 retry logic.

Track implementation progress in `TODO.md`. Milestone audits: `MILESTONE_1_AUDIT.md` (complete), `MILESTONE_2_AUDIT.md` (architecture plan).

## Milestone 2 — PDF Engine

**Status:** PDF engine foundation, logistics labels, order PDF, and picking list processors implemented (through 2026-06).

| Deliverable | Status |
| --- | --- |
| `config/pdf.php` + `PdfServiceProvider` | Done |
| Core services (`PdfEngineService`, `PdfNormalizationService`, `PdfCanvasService`, `PdfValidationService`, `PdfBoundaryDetectionService`) | Done |
| FPDI integration (`setasign/fpdf` + `FpdiDocumentAdapter`) | Done — validation + normalization |
| Processing pipeline (6 stages) + Actions | Done |
| DTOs, interfaces, exceptions, localization | Done |
| **Logistics labels processor** | Done — thermal only; A4 rejected; multi-label A4 sheets |
| **Order PDF processor** | Done — spreadsheet → A4 PDF via `OrderPdfProcessor` |
| **Picking list processor** | Done — spreadsheet → A4 picking sheet via `PickingListProcessor` |
| `ProcessUploadJob` queue dispatch from `UploadService` | Done |
| Upload detail UI, per-file status, partial batch errors, regenerate | Done |
| **Delivery label processor** | Not started |
| Download shred + temp purge cron | Planned |

**Documentation:**

- Architecture plan: [`MILESTONE_2_AUDIT.md`](MILESTONE_2_AUDIT.md)  
- Foundation report: [`MILESTONE_2_IMPLEMENTATION_REPORT.md`](MILESTONE_2_IMPLEMENTATION_REPORT.md)  
- **Logistics labels report:** [`MILESTONE_2_LOGISTICS_IMPLEMENTATION_REPORT.md`](MILESTONE_2_LOGISTICS_IMPLEMENTATION_REPORT.md)  

**Developer usage:**

```php
$result = app(\App\Actions\Merchant\Pdf\RunPdfProcessingPipeline::class)->execute($uploadJob);
```

**Dependencies:** `setasign/fpdi`, `setasign/fpdf ^1.8.6` (loaded via `app/Support/pdf_fpdf_bootstrap.php`)

**M2 infrastructure (enable when queue processing ships):**

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
# BROWSERSHOT_NODE_BINARY=
# BROWSERSHOT_NPM_BINARY=
# BROWSERSHOT_CHROME_PATH=
```

**Developer entry point after M1:**

```bash
composer dev:merchant    # Vite + merchant server (port 8001)
php artisan queue:work     # local testing once jobs exist
```

## License

MIT — see [LICENSE](LICENSE).
