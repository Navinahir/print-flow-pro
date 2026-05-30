# Milestone 1 TODO — Project Specification V2.4

Audit date: 2026-05-30  
Reference: Shopee Batch Print Integration SaaS V2.4 (M1: Portals & UI/UX)

Status legend: `[DONE]` fully implemented · `[PARTIAL]` partially implemented · `[PENDING]` not implemented

---

## A. Laravel 12 Migration

| Status | Requirement |
|--------|-------------|
| [DONE] | Project runs on Laravel 12 (`composer.json`: `laravel/framework: ^12.0`; locked `v12.60.2`; `php artisan --version` → Laravel Framework 12.60.2) |
| [DONE] | PHP 8.3 constraint (`composer.json`: `"php": "^8.3"`) |
| [DONE] | Filament 5 admin panel compatible with Laravel 12 |
| [DONE] | PHPUnit 11 test suite passes (70 passed, 2 skipped in default test env) |

**Files:** `composer.json`, `composer.lock`, `bootstrap/app.php`, `bootstrap/providers.php`

**Remaining upgrade work:** None identified for Laravel 12 migration itself. Operational items (Redis queues, Supervisor, Scheduler) are infrastructure follow-ups outside the framework version requirement.

---

## B. VPS Deployment Pipeline

| Status | Requirement |
|--------|-------------|
| [PENDING] | Automated deployment pipeline (no deploy scripts, hooks, or CI/CD in repository) |
| [PENDING] | Git Deploy Keys usage documented or configured in-repo |
| [PENDING] | Post-deploy automation (`composer install`, `migrate`, `npm run build`, cache, queue restart) |
| [PARTIAL] | Production deployment guidance exists as README prose only |

**Files:** `README.md` (§ Production deployment — DNS and `.env` bullets only)

**Why pending:** M1 spec requires the development team to set up deployment on the client VPS using Git Deploy Keys. The codebase contains no `.github/workflows/`, `deploy.sh`, Envoy, Forge, or similar artifacts. Deploy-key configuration lives on the server and cannot be verified from this repository.

---

## C. Domain-Based Routing

| Status | Requirement |
|--------|-------------|
| [DONE] | `Route::domain()` registration via `RouteServiceProvider` |
| [DONE] | Merchant portal isolated on `tw.xycubic.com` (database-driven via `domain_settings`) |
| [DONE] | Merchant domain settings moved from `.env` to database (`domain_settings`, `domain_locales`, `domain_features`) |
| [DONE] | `DomainConfigurationService` + `MerchantConfig` accessor for branding, locales, features, upload limits |
| [DONE] | Admin portal isolated on `manage-xy.xycubic.com` (configurable via `ADMIN_DOMAIN`) |
| [DONE] | Marketing site on `xycubic.com` with `/tw` route |
| [DONE] | Inactive regions return 403 `Region Not Activated Yet` |
| [DONE] | Port-based local dev mirror (`localhost:8000/8001/8002`) |
| [PARTIAL] | Obfuscated admin entry path — spec: `/bosslogin`; implementation: `/boss` (`ADMIN_PATH_PREFIX`) |
| [PARTIAL] | Admin domain root behavior — spec: 404/403 on root; implementation redirects root → `/boss` |

**Files:**

- `app/Providers/RouteServiceProvider.php` — domain groups for marketing, merchant, admin
- `config/domains.php` — infrastructure defaults; merchant fallback definitions for seeder
- `app/Services/Domain/DomainConfigurationService.php` — cached merchant config from database
- `app/Repositories/Domain/DomainSettingRepository.php` — loads domain settings with relations
- `app/Support/MerchantConfig.php` — static accessor for merchant views and form requests
- `database/migrations/2026_05_30_00000*_create_domain_*_table.php` — domain config tables
- `database/seeders/DomainSettingSeeder.php` — seeds TW/PH/VN from fallback config
- `app/Support/Domains/DomainResolver.php` — host resolution and route domain mapping
- `app/Support/Domains/DomainContext.php` — surface constants
- `routes/marketing.php`, `routes/merchant.php`, `routes/admin.php`
- `app/Providers/Filament/AdminPanelProvider.php` — Filament domain + path binding
- `app/Http/Controllers/RootController.php` — surface-aware root handling
- `tests/Feature/DomainRoutingTest.php` — obfuscation and inactive-region tests (skipped when `DOMAIN_ROUTING_ENABLED=false`)
- `tests/Feature/DomainConfigurationTest.php` — database-driven merchant config and `MerchantConfig` helper

---

## C2. Merchant Domain Database Configuration

| Status | Requirement |
|--------|-------------|
| [DONE] | `domain_settings` table — host, region, branding, JSON settings, active flag |
| [DONE] | `domain_locales` table — multi-language support per merchant region |
| [DONE] | `domain_features` table — feature toggles per merchant region |
| [DONE] | Eloquent models (`DomainSetting`, `DomainLocale`, `DomainFeature`) |
| [DONE] | Repository + service layer (`DomainSettingRepository`, `DomainConfigurationService`) |
| [DONE] | `MerchantDomainConfig` DTO + `MerchantConfig` static accessor |
| [DONE] | `DomainSettingSeeder` seeds TW/PH/VN from `config/domains.php` fallback |
| [DONE] | Merchant domain refactored — views, upload limits, routing use DB config |
| [DONE] | Infrastructure-only `.env` — marketing/admin hosts; merchant vars removed |
| [DONE] | Cache invalidation on domain model save/delete |
| [DONE] | Feature tests (`DomainConfigurationTest`) |

**Files:**

- Migrations: `database/migrations/2026_05_30_000001_create_domain_settings_table.php`, `000002`, `000003`
- Models: `app/Models/DomainSetting.php`, `DomainLocale.php`, `DomainFeature.php`
- Contract: `app/Contracts/Domain/DomainSettingRepositoryInterface.php`
- Repository: `app/Repositories/Domain/DomainSettingRepository.php`
- Service: `app/Services/Domain/DomainConfigurationService.php`
- DTO: `app/DTOs/Domain/MerchantDomainConfig.php`
- Support: `app/Support/MerchantConfig.php`, `app/Support/merchant_config.php` (optional helpers)
- Seeder: `database/seeders/DomainSettingSeeder.php`
- Refactored: `DomainResolver`, `ResolveRegion`, `RouteServiceProvider`, `StoreUploadRequest`, merchant Blade views

**Out of scope (this task):** Admin domain configuration UI, printing module feature implementation, Filament resources for domain settings.

---

## D. Security Obfuscation

| Status | Requirement |
|--------|-------------|
| [DONE] | Unknown hosts → 404 (`RejectUnmappedDomain`) |
| [DONE] | Wrong surface on domain → 404 (`EnsureExpectedSurface`) |
| [DONE] | Legacy `/admin` on admin domain → 404 (`ObfuscateAdminAccess` + tests) |
| [DONE] | Inactive merchant regions → 403 (`EnsureRegionIsActive`) |
| [DONE] | Cross-surface role isolation (`EnsureAdminAccess`, `EnsureMerchantAccess`, `SurfaceAccessTest`) |
| [DONE] | Per-domain session cookies (`ConfigureDomainSession`) |
| [PARTIAL] | Admin domain root `/` redirects to `/boss` instead of returning 404/403 |
| [PENDING] | `country_code` Global Scopes on all queries (spec §2 RBAC — not yet implemented) |

**Files:**

- `app/Http/Middleware/ObfuscateAdminAccess.php`
- `app/Http/Middleware/RejectUnmappedDomain.php`
- `app/Http/Middleware/EnsureExpectedSurface.php`
- `app/Http/Middleware/EnsureRegionIsActive.php`
- `app/Http/Middleware/EnsureAdminAccess.php`
- `app/Http/Middleware/EnsureMerchantAccess.php`
- `routes/admin.php` — fallback 404
- `tests/Feature/SurfaceAccessTest.php`, `tests/Feature/DomainRoutingTest.php`

---

## E. Frontend Portal Layouts

| Status | Requirement |
|--------|-------------|
| [DONE] | Dedicated merchant UI foundation (`resources/views/merchant/`) — layouts, partials, components, auth, dashboard, pages |
| [DONE] | Merchant master layout with header, sidebar, mobile nav, footer |
| [DONE] | Flexbox sticky footer on authenticated layout (short pages pin footer to viewport bottom) |
| [DONE] | Reusable breadcrumb, page-header, empty-state, loading-state components |
| [DONE] | Laravel localization (`lang/en`, `lang/zh-TW`) — no hardcoded merchant UI strings |
| [DONE] | Separate merchant assets (`resources/css/merchant.css`, `resources/js/merchant.js`) |
| [DONE] | SweetAlert2 + toast notifications + AJAX-ready axios client |
| [DONE] | TW merchant portal shell — auth, dashboard, upload workflow on port 8001 |
| [DONE] | Management portal — Filament 5 panel (unchanged; admin domain only) |
| [DONE] | Responsive Tailwind layouts (mobile nav, grid breakpoints, overflow-x tables) |
| [DONE] | Collapsible sidebar with `localStorage` persistence (icon-only collapsed mode) |
| [DONE] | Sidebar footer — Profile + Logout pinned to bottom; tooltips when collapsed |
| [DONE] | Full-page locale-switch loader (blur overlay until page load completes) |
| [DONE] | Standardized form labels, placeholders, required indicators, validation errors |
| [DONE] | Reusable form components (`x-merchant.form.label`, `.error`, `.field`) |
| [PARTIAL] | Card-based views — dashboard cards done; upload history remains table-first |
| [PARTIAL] | RWD master-detail printing workspaces — shells + live preview done |
| [DONE] | Module-specific left-pane lists + right-pane live HTML preview layouts |
| [PARTIAL] | Mobile single-column card collapse for printing modules — list/preview stack on mobile |

**Files (merchant UI foundation):**

- Layouts: `resources/views/merchant/layouts/app.blade.php`, `guest.blade.php`
- Partials: `resources/views/merchant/partials/{header,sidebar,sidebar-footer,mobile-nav,footer}.blade.php`
- Components: `resources/views/merchant/components/{breadcrumb,page-header,empty-state,loading-state,page-loader,upload-status-badge}.blade.php`
- Form components: `app/View/Components/Merchant/Form/`, `resources/views/merchant/components/form/`
- Pages: `resources/views/merchant/dashboard/index.blade.php`, `resources/views/merchant/pages/**`
- Auth: `resources/views/merchant/auth/*.blade.php`
- Assets: `resources/css/merchant.css`, `resources/css/merchant/{sidebar,form,page-loader}.css`, `resources/js/merchant.js`, `resources/js/merchant/{ajax,toast,sweetalert,shell,storage,http}.js`
- i18n: `lang/en/merchant.php`, `lang/en/auth.php`, `lang/zh-TW/merchant.php`, `lang/zh-TW/auth.php`
- Printing placeholder: `resources/views/merchant/printing/.gitkeep`

**Why partial:** Portal scaffolding and upload foundation exist; M1-specified printing-module master-detail UIs are not built yet.

---

## F. Static Staging Page (`xycubic.com/tw`)

| Status | Requirement |
|--------|-------------|
| [DONE] | Route `/tw` registered on marketing domain |
| [DONE] | Standard Tailwind CSS marketing template |
| [PARTIAL] | zh-TW content — route exists; page renders English copy from `home.blade.php` |
| [PENDING] | Client-provided Traditional Chinese copy and subscription/action links |
| [PENDING] | Locale switch for `/tw` (`config/domains.php` maps `tw` → `zh-TW` but marketing routes do not apply it) |

**Files:** `routes/marketing.php`, `resources/views/home.blade.php`, `resources/views/layouts/marketing.blade.php`, `config/domains.php` (`locale_prefixes`)

---

## G. Live Preview Container (150×100 mm)

| Status | Requirement |
|--------|-------------|
| [DONE] | Fixed-aspect-ratio 150×100 mm preview canvas (`x-merchant.preview.container`) |
| [DONE] | Responsive scaling via `resources/js/merchant/preview/scale.js` (3:2 ratio preserved) |
| [DONE] | Reusable preview components (`PreviewWrapper`, `PreviewToolbar`, `PreviewContainer`) |
| [DONE] | Integrated into all printing module preview panes |
| [DONE] | Localization (`merchant.preview.*` in en + zh-TW) |
| [DONE] | Feature test (`PreviewEngineTest`, `PrintingPreviewContentTest`) |
| [DONE] | Live HTML preview content injection (order details, logistics, picking list, delivery labels) |
| [DONE] | AJAX preview refresh (`POST /printing/preview`) |
| [DONE] | Upload show page preview integration |
| [DONE] | Browser print workflow (`print.js`, `print.css`, print button component) |
| [DONE] | Preview settings in `domain_settings.settings` JSON |

**Files:** `app/View/Components/Merchant/Preview/`, `app/Services/Merchant/Preview/`, `app/DTOs/Merchant/Preview/`, `resources/views/merchant/printing/components/previews/`, `resources/css/merchant/preview/`, `resources/js/merchant/preview/`

---

## H. Safe Print Zone (5 mm dashed guides)

| Status | Requirement |
|--------|-------------|
| [DONE] | 5 mm dashed safe-zone overlay inside preview canvas (`PreviewSafeZone`) |
| [DONE] | Visual rendering of print boundary guides (amber dashed border, responsive insets) |
| [DONE] | Toggle visibility via preview toolbar (`toggleSafeZone()` / `safeZoneVisible`) |
| [DONE] | Localization (`merchant.preview.safe_zone.*` in en + zh-TW) |
| [DONE] | Shared across all printing modules via `PreviewContainer` |

**Files:** `app/View/Components/Merchant/Preview/PreviewSafeZone.php`, `resources/views/merchant/components/preview/safe-zone.blade.php`, `resources/css/merchant/preview/safe-zone.css`, `resources/js/merchant/preview/safe-zone.js`

---

## I. Aspect Ratio Validation (>10% tolerance, amber warning)

| Status | Requirement |
|--------|-------------|
| [DONE] | Real-time aspect ratio deviation check vs 150×100 mm |
| [DONE] | >10% tolerance detection (`AspectRatioValidationService`) |
| [DONE] | Amber flashing border / warning glow on violation |
| [DONE] | Warning banner + SweetAlert2 dialog on violation |
| [DONE] | "Force Adjustment" override toggle |
| [DONE] | AJAX validation endpoint (`POST printing/aspect-ratio/validate`) |
| [DONE] | Localization (`merchant.preview.aspect_ratio.*` in en + zh-TW) |

**Files:** `app/Services/Merchant/Preview/AspectRatioValidationService.php`, `app/Http/Controllers/Merchant/Printing/AspectRatioValidationController.php`, `app/View/Components/Merchant/Preview/PreviewAspectWarning.php`, `resources/js/merchant/preview/aspect-ratio.js`, `resources/css/merchant/preview/aspect-warning.css`, `tests/Unit/Services/Merchant/Preview/AspectRatioValidationServiceTest.php`, `tests/Feature/AspectRatioValidationTest.php`

---

## J. Courier Address Auto-Shrink (18 px → 14 px)

| Status | Requirement |
|--------|-------------|
| [DONE] | Flexbox vertical delivery-label layout |
| [DONE] | Typography starts at 18 px, shrinks to 14 px floor for addresses >35 characters |
| [DONE] | Dynamic push-down of Remarks/Notes section (no overlap) |
| [DONE] | CSV header detection for courier addresses (recipient, address, remarks, tracking, carrier) |
| [DONE] | CSV upload + import workflow (`POST /printing/delivery-labels/csv`) |
| [DONE] | AJAX upload UX (toast, SweetAlert2, loading/empty states) |
| [DONE] | Parsed CSV data injected into delivery label preview |
| [DONE] | Responsive preview re-measure on canvas resize |
| [DONE] | Localization (`merchant.delivery_labels.*` in en + zh-TW) |

**Files:** `app/Services/Merchant/Printing/DeliveryLabels/`, `app/DTOs/Merchant/Printing/DeliveryLabels/`, `app/Http/Controllers/Merchant/Printing/DeliveryLabelCsvUploadController.php`, `resources/views/merchant/printing/delivery-labels/`, `resources/js/merchant/printing/delivery-labels/`, `resources/css/merchant/printing/delivery-labels/`, `tests/Unit/Services/Merchant/Printing/DeliveryLabels/CourierAddressTypographyServiceTest.php`, `tests/Feature/DeliveryLabelsAutoShrinkTest.php`, `tests/Feature/DeliveryLabelCsvImportTest.php`

---

## K. Deferred Features Verification (M1 scope boundary)

| Status | Requirement |
|--------|-------------|
| [DONE] | Payment gateway / checkout / webhooks — not implemented (correctly deferred to M3) |
| [DONE] | Subscription expiration middleware / `has_used_trial` — not implemented |
| [DONE] | Shopee API integration — not implemented |
| [DONE] | PDF normalization engine — not implemented (M2) |
| [PARTIAL] | Billing admin scaffold exists (plan CRUD UI + DB) as mockup; no live billing logic |

**Existing scaffold (mockup only — acceptable per M1 note):**

- `app/Filament/Resources/BillingPlanResource.php`
- `app/Models/BillingPlan.php`, `database/migrations/2026_05_23_120000_create_billing_plans_table.php`
- `database/seeders/BillingPlanSeeder.php`
- `app/Filament/Resources/MerchantResource.php` — `billing_plan_id` assignment

**Correctly absent:** Payment drivers, webhook handlers, trial flags, API key encryption middleware, Shopee sandbox.

---

## M. Merchant Domain Architecture Roadmap

Audit date: 2026-05-30  
Reference: [MERCHANT_DOMAIN_ARCHITECTURE.md](MERCHANT_DOMAIN_ARCHITECTURE.md)

Status legend: `[DONE]` · `[PENDING]` (implementation not started — analysis only)

### Phase 0 — Foundation cleanup

| Status | Task |
|--------|------|
| [DONE] | Remove orphaned legacy Breeze views (`resources/views/auth/`, `layouts/`, etc.) |
| [DONE] | Remove dead `config('printflow.upload.*')` fallback in merchant upload path |
| [PENDING] | Add `auth.failed` / `auth.throttle` to `lang/en/auth.php` and `lang/zh-TW/auth.php` |
| [DONE] | Move upload validation messages to lang files |
| [DONE] | Fix logout / account-delete redirect to merchant login (not `/`) |
| [DONE] | Deduplicate `access.merchant` middleware on merchant routes |
| [DONE] | Feature-gate printing nav via `MerchantConfig::feature()` |
| [PENDING] | Extract upload Alpine logic to `resources/js/merchant/modules/uploads.js` |

**Reconciliation:** See [MERCHANT_RECONCILIATION_REPORT.md](MERCHANT_RECONCILIATION_REPORT.md) (2026-05-30).

### Phase 1 — Component & namespace consolidation

| Status | Task |
|--------|------|
| [PARTIAL] | Register `<x-merchant::*>` Blade component namespace (preview components registered) |
| [PENDING] | Convert include-based components to class components |
| [DONE] | Move controllers to `App\Http\Controllers\Merchant\` |
| [PENDING] | Move `UploadService` to `App\Services\Merchant\` |
| [PARTIAL] | Split routes into `routes/merchant/*.php` (`auth.php`, `printing.php` done) |
| [PENDING] | Add `Merchant\NavigationBuilder` for sidebar DRY |

### Phase 2 — Localization & locale switcher

| Status | Task |
|--------|------|
| [DONE] | Locale switcher UI (`x-merchant.locale-switcher`, navbar) |
| [DONE] | `SetMerchantLocale` middleware (session preference) |
| [DONE] | `LocaleService` + `LocaleController` |
| [DONE] | Feature tests (`MerchantLocaleSwitchTest`) |
| [DONE] | Full-page loader during locale switch (persists across redirect) |
| [PENDING] | Optional `users.locale` column |
| [PENDING] | Pass JS i18n strings via layout |

### Phase 3 — Printing module shells

| Status | Task |
|--------|------|
| [DONE] | `merchant.layouts.printing-module` master-detail layout |
| [DONE] | Routes + controllers for 4 printing modules (`routes/merchant/printing.php`) |
| [DONE] | Feature-gate modules via `domain_features` + `printing.module` middleware |
| [DONE] | Responsive list + preview panes (mobile stack) |
| [DONE] | Services, DTOs, `PrintingModule` enum, `PrintingNavigation` helper |
| [DONE] | Sidebar + mobile nav links (feature-gated) |
| [DONE] | Localization (`lang/*/merchant.php` printing keys) |
| [DONE] | Alpine `printingWorkspace` JS module |
| [DONE] | Preview pane integrated with preview engine components |
| [DONE] | Feature tests (`PrintingModuleTest`, `PreviewEngineTest`) |

**Files:**

- Routes: `routes/merchant/printing.php`
- Controllers: `app/Http/Controllers/Merchant/Printing/*`
- Services: `app/Services/Merchant/Printing/*`
- DTOs: `app/DTOs/Merchant/Printing/*`
- Enum: `app/Enums/PrintingModule.php`
- Middleware: `app/Http/Middleware/EnsurePrintingModuleEnabled.php`
- Views: `resources/views/merchant/printing/**`, `resources/views/merchant/layouts/printing-module.blade.php`
- Preview: `app/View/Components/Merchant/Preview/`, `resources/views/merchant/components/preview/`
- JS: `resources/js/merchant/modules/printing.js`, `resources/js/merchant/preview/`
- CSS: `resources/css/merchant/preview/`

### Phase 4 — Preview engine (M1 critical)

| Status | Task |
|--------|------|
| [DONE] | 150×100 mm fixed-aspect preview canvas (`PreviewContainer`) |
| [DONE] | Responsive scaling (`ResizeObserver`, 3:2 ratio) |
| [DONE] | Reusable Blade components + CSS/JS modules |
| [DONE] | Localization + `PreviewEngineTest` |
| [DONE] | 5 mm dashed safe-zone overlay (`PreviewSafeZone`, toggle in toolbar) |
| [DONE] | Aspect ratio validation (>10% amber warning + banner + SweetAlert2) |
| [DONE] | Force adjustment toggle |
| [DONE] | Courier address auto-shrink (18 px → 14 px, delivery labels module) |
| [DONE] | Live HTML preview content injection (all four printing modules) |
| [DONE] | Delivery label CSV import + preview integration |
| [DONE] | Preview settings in `domain_settings.settings` JSON |
| [DONE] | Browser print enablement |
| [DONE] | Dark / light theme toggle |

### Phase 5 — Dashboard & uploads polish

| Status | Task |
|--------|------|
| [PENDING] | Live dashboard stats from `upload_jobs` |
| [PENDING] | Card-based upload history on mobile |
| [DONE] | Upload show page preview integration |
| [PENDING] | Lightweight guest auth JS bundle |

### Phase 6 — Testing & CI

| Status | Task |
|--------|------|
| [PENDING] | Dedicated CI job with `DOMAIN_ROUTING_ENABLED=true` |
| [PENDING] | Feature toggle tests |
| [PENDING] | Locale switcher tests in CI with domain routing |
| [PENDING] | Full merchant URL route tests |

**Gap analysis highlights:** dashboard live stats pending, logout redirects to marketing root, JS i18n bundle pending.

**DB settings:** Merchant hosts/branding/features/upload limits + preview dimensions in database (§C2 done). Future: help URLs, support email, theme defaults per region in `domain_settings.settings` JSON.

---

## Milestone 1 Summary

| Category | DONE | PARTIAL | PENDING |
|----------|------|---------|---------|
| M. Merchant Roadmap | 15 | 0 | 22 |
| A. Laravel 12 | 4 | 0 | 0 |
| B. VPS Deployment | 0 | 1 | 3 |
| C. Domain Routing | 9 | 2 | 0 |
| C2. Merchant DB Config | 11 | 0 | 0 |
| D. Security Obfuscation | 6 | 1 | 1 |
| E. Frontend Portals | 3 | 4 | 0 |
| F. Static `/tw` Page | 2 | 1 | 2 |
| G. Preview Container | 6 | 0 | 2 |
| H. Safe Print Zone | 5 | 0 | 0 |
| I. Aspect Ratio Validation | 7 | 0 | 0 |
| J. Courier Auto-Shrink | 6 | 0 | 0 |
| K. Deferred Features | 4 | 1 | 0 |

---

## Recommended Next Implementation Order

**Merchant domain (port 8001)** — follow [MERCHANT_DOMAIN_ARCHITECTURE.md](MERCHANT_DOMAIN_ARCHITECTURE.md) phases 0→6:

1. **Phase 0 — Foundation cleanup** — legacy views, lang keys, redirect fixes, JS split.
2. **Phase 1 — Components & namespaces** — `<x-merchant::*>` components, `Controllers\Merchant\`.
3. **Phase 5 — Dashboard & uploads polish** — live stats, mobile upload history cards.
4. **Phase 6 — CI** — domain-routing tests with `DOMAIN_ROUTING_ENABLED=true`.
5. **Phase 5–6 — Polish & CI** — dashboard stats, domain routing in CI.

**Cross-cutting (all surfaces):**

1. **VPS deployment pipeline** — deploy script + Git Deploy Key docs.
2. **Align admin obfuscation** — `ADMIN_PATH_PREFIX=bosslogin`; admin root 404/403.
3. **Static `/tw` zh-TW page** — dedicated marketing Blade view.
4. **`country_code` Global Scopes** — data isolation before multi-region go-live.
5. **Enable domain routing in CI** — dedicated test job for `DomainRoutingTest`.
