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

A single Laravel codebase serves three isolated surfaces via `Route::domain()` and middleware.

**Infrastructure** (marketing + admin hosts, routing toggle) remains in `.env` via `config/domains.php`.

**Merchant regions** (hosts, locales, branding, feature toggles, upload limits) are stored in the database and loaded through `DomainConfigurationService`. Run `DomainSettingSeeder` after migrate (included in `db:seed`).

| Surface | Production host | Purpose |
| --- | --- | --- |
| Marketing | `xycubic.com` | Public site (`/`, `/tw`, …) |
| Merchant (TW) | `tw.xycubic.com` | Breeze auth, uploads, dashboard |
| Admin | `manage-xy.xycubic.com` | Filament panel **only** under `/boss` |

Future regions (`ph.xycubic.com`, `vn.xycubic.com`) are seeded in `domain_settings`. Inactive regions return **403** with `Region Not Activated Yet`. Enable a region by setting `is_active = true` on its row (or re-seeding from updated `config/domains.php` fallback definitions).

### Merchant domain configuration (database-driven)

Merchant surface settings live in three tables:

| Table | Purpose |
| --- | --- |
| `domain_settings` | Region key, host, country code, active flag, session cookie, branding, JSON settings (upload limits, etc.) |
| `domain_locales` | Supported locales per region (default locale flag) |
| `domain_features` | Feature toggles per region (`uploads`, printing modules, etc.) |

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

**`.env` (infrastructure only):**

```env
DOMAIN_ROUTING_ENABLED=true
MARKETING_DOMAIN=xycubic.com
ADMIN_DOMAIN=manage-xy.xycubic.com
ADMIN_PATH_PREFIX=boss
```

PHPUnit sets `DOMAIN_ROUTING_ENABLED=false` so feature tests keep using a single `localhost` host without breaking named routes. Domain configuration tests use the full merchant URL (e.g. `http://tw.xycubic.com/login`) so host resolution matches production behavior.

| File | Registered on |
| --- | --- |
| `routes/marketing.php` | Marketing domain |
| `routes/merchant.php` | Active merchant domains (full app on primary region, e.g. TW) |
| `routes/admin.php` | Admin domain fallbacks (404 for unmapped paths) |

`App\Providers\RouteServiceProvider` registers domain groups. Filament is constrained to the admin domain and `ADMIN_PATH_PREFIX` (default `boss`) in `AdminPanelProvider`.

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

`ResolveRegion` binds `App\Support\Domains\DomainContext` and `App\DTOs\Domain\MerchantDomainConfig` into the container for services, branding, and future payment isolation.

### Local development (port-based)

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
APP_URL=http://localhost:8001
MARKETING_DOMAIN=localhost:8000
ADMIN_DOMAIN=localhost:8002
ADMIN_PATH_PREFIX=boss
DOMAIN_ROUTING_ENABLED=true
```

| Local URL | Surface |
| --- | --- |
| http://localhost:8000/ | Marketing home |
| http://localhost:8000/tw | Marketing TW landing |
| http://localhost:8001/login | Merchant auth |
| http://localhost:8001/dashboard | Merchant dashboard |
| http://localhost:8002/boss | Filament admin (`admin` only) |
| http://localhost:8002/ | **404** (obfuscated) |
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

### Merchant architecture audit & roadmap

Full analysis of the merchant domain (port 8001): current architecture, gaps, DB settings strategy, localization plan, folder structure, UI/components strategy, and phased implementation plan:

**[MERCHANT_DOMAIN_ARCHITECTURE.md](MERCHANT_DOMAIN_ARCHITECTURE.md)**

**[MERCHANT_RECONCILIATION_REPORT.md](MERCHANT_RECONCILIATION_REPORT.md)** — Audit and cleanup pass (2026-05-30): architecture alignment, namespace migration, legacy view removal, CSS/JS deduplication, localization fixes.

Summary of upcoming merchant-only phases (no admin/marketing changes):

| Phase | Focus |
| --- | --- |
| 0 | Foundation cleanup — legacy views, lang keys, redirect fixes | **Mostly done** — see [reconciliation report](MERCHANT_RECONCILIATION_REPORT.md) |
| 1 | `<x-merchant::*>` components, controller/service namespaces | **Partial** — controllers migrated; component namespace partial |
| 2 | Locale switcher (EN / zh-TW) | **Done** |
| 3 | Printing module master-detail shells | **Done** |
| 4 | Preview engine + live preview + CSV + print + DB preview config | **Done** |
| 5 | Upload show preview, dark mode, navbar UX | **Done** |
| 6 | Sidebar collapse, footer actions, locale loader, form standardization | **Done** |
| 7 | Dashboard stats, CI domain-routing tests |

### Local merchant URLs

| URL | Page |
| --- | --- |
| http://localhost:8001/login | Merchant login |
| http://localhost:8001/dashboard | Merchant dashboard |
| http://localhost:8001/uploads | Upload history |
| http://localhost:8001/uploads/create | New upload |
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

### Preview engine (150×100 mm)

Reusable Blade components (registered as `x-merchant.preview.*`):

| Component | Purpose |
| --- | --- |
| `PreviewWrapper` | Outer shell, loading overlay, toolbar slot |
| `PreviewContainer` | Fixed 150×100 mm canvas (3:2 ratio), responsive scaling, safe zone overlay |
| `PreviewSafeZone` | Dashed 5 mm inset print boundary guide (toggleable) |
| `PreviewAspectWarning` | Amber warning banner + force-adjustment toggle when asset ratio deviates >10% from 3:2 |
| `PreviewToolbar` | Heading, description, safe-zone toggle, print action |

**Paths:**

| Layer | Location |
| --- | --- |
| Blade classes | `app/View/Components/Merchant/Preview/` |
| Blade views | `resources/views/merchant/components/preview/` |
| CSS | `resources/css/merchant/preview/` (imported via `merchant.css`) |
| JS | `resources/js/merchant/preview/` (`constants.js`, `scale.js`, `safe-zone.js`, `aspect-ratio.js`, `engine.js`) |

The canvas uses a logical 566×378 px surface (150×100 mm at 96 dpi) and scales down via `ResizeObserver` to fit the stage while preserving 3:2 ratio.

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
| `width_mm` | 150 | Canvas width |
| `height_mm` | 100 | Canvas height |
| `aspect_ratio` | 1.5 | Target ratio for validation |
| `safe_zone_inset_mm` | 5 | Safe print zone inset |
| `default_zoom` | 1.0 | Max scale factor |
| `scaling_behavior` | `fit` | Responsive scaling mode |

`PreviewConfigurationService` reads values via `MerchantConfig`. `PreviewContainer`, `AspectRatioValidationService`, and client-side scaling (`scale.js`) consume these settings — no hardcoded dimensions in production paths.

### Upload detail preview

Upload show pages (`/uploads/{id}`) embed the shared preview engine. `UploadPreviewService` maps upload job types to existing preview DTOs; AJAX refresh via `POST /uploads/{upload}/preview`. See [UX completion report](MERCHANT_UX_COMPLETION_REPORT.md).

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
- Set `DOMAIN_ROUTING_ENABLED=true` and infrastructure domains in `.env`.
- Run `php artisan db:seed --class=DomainSettingSeeder` (or full `db:seed`) so merchant hosts, locales, and features exist in `domain_settings`.
- Use separate session cookie names per surface so cookies do not leak across subdomains (stored on `domain_settings.session_cookie` for merchants).
- Enable PH/VN by setting `is_active = true` on the corresponding `domain_settings` row when ready; until then, those hosts respond with 403.

## Local development

Use the Composer dev script to run the app server, queue worker, log tail, and Vite together:

```bash
composer dev
```

For multi-domain local testing, use the three `php artisan serve` commands above instead of a single server.

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
├── Http/         # Controllers, middleware, form requests
├── Jobs/         # Queued work
├── Models/       # Eloquent models (incl. DomainSetting, DomainLocale, DomainFeature)
├── Repositories/ # DomainSettingRepository
├── Services/     # Domain services (PDF, picking, labels, DomainConfigurationService)
└── Support/      # Shared helpers (MerchantConfig, DomainResolver)
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

Configure upload limits per region in `domain_settings.settings` (seeded by `DomainSettingSeeder`) or via `MerchantConfig::get('upload.max_file_size_kb')`.

Design principles (see `PROJECT_ARCHITECTURE.md` and `CURSOR_RULES.md`):

- Thin controllers; business logic in Actions and Services
- Form requests for validation
- Queue-ready jobs for heavy PDF work
- Multi-domain routing and per-region session isolation (`config/domains.php`, domain middleware)
- Temp-file security (planned in Phase 1 TODO)

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

Track implementation progress in `TODO.md` and the merchant architecture roadmap in `MERCHANT_DOMAIN_ARCHITECTURE.md`.

## License

MIT — see [LICENSE](LICENSE).
