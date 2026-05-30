# Merchant Domain Architecture Audit & Implementation Plan

**Audit date:** 2026-05-30  
**Scope:** Merchant domain only (`localhost:8001` / `tw.xycubic.com`)  
**Out of scope:** Admin (Filament), Marketing (`xycubic.com`), printing engine implementation

This document is the authoritative reference for merchant-domain architecture decisions. It analyzes the current Laravel 12 codebase and defines a priority-based roadmap. **No feature implementation is included here.**

---

## 1. Current Architecture Analysis

### 1.1 Domain routing & request lifecycle

The merchant surface is one of three isolated domains in a single Laravel codebase. Host resolution flows through middleware before any route handler runs.

```
HTTP Request (host: localhost:8001 / tw.xycubic.com)
    │
    ▼
ResolveRegion ──► DomainResolver.resolve() ──► DomainContext (surface, region_key, locale)
    │                      │
    │                      └── merchantRegionsForRouting() ← DomainConfigurationService (DB cache)
    ▼
ConfigureDomainSession ──► per-region session cookie name
    ▼
EnsureExpectedSurface:merchant ──► 404 if wrong surface (skipped when DOMAIN_ROUTING_ENABLED=false)
    ▼
RejectUnmappedDomain ──► 404 for unknown hosts
    ▼
EnsureRegionIsActive ──► 403 if region inactive
    ▼
EnsureMerchantAccess ──► guests pass; logged-in non-merchants denied
    ▼
Route handler (auth / verified as needed)
```

| Component | Path | Role |
| --- | --- | --- |
| `RouteServiceProvider` | `app/Providers/RouteServiceProvider.php` | Registers merchant route group with `MERCHANT_MIDDLEWARE` |
| `DomainResolver` | `app/Support/Domains/DomainResolver.php` | Maps host → surface/region/locale |
| `ResolveRegion` | `app/Http/Middleware/ResolveRegion.php` | Binds `DomainContext`, `MerchantDomainConfig`, sets `app.locale` |
| `DomainConfigurationService` | `app/Services/Domain/DomainConfigurationService.php` | DB-backed config, cache, feature checks |
| `MerchantConfig` | `app/Support/MerchantConfig.php` | Static accessor for views/controllers |

**Local port 8001:** When `APP_ENV=local`, TW merchant host is `localhost:8001` (seeded in `domain_settings`). Port-based local routing registers merchant routes without `Route::domain()` so `artisan serve --port=8001` works.

**PHPUnit:** `DOMAIN_ROUTING_ENABLED=false` in `phpunit.xml` — surface middleware bypassed; domain-specific tests use full merchant URLs (e.g. `http://tw.xycubic.com/login`).

### 1.2 Routes

**Registration:** `routes/merchant.php` + `require routes/auth.php`, loaded via `RouteServiceProvider::registerMerchantRoutes()`.

| Method | URI | Name | Extra middleware | Handler |
| --- | --- | --- | --- | --- |
| GET | `/dashboard` | `dashboard` | `auth`, `verified` | Closure → `merchant.dashboard.index` |
| GET | `/uploads` | `uploads.index` | `auth`, `verified` | `UploadController@index` |
| GET | `/uploads/create` | `uploads.create` | `auth`, `verified` | `UploadController@create` |
| POST | `/uploads` | `uploads.store` | `auth`, `verified` | `UploadController@store` |
| GET | `/uploads/{upload}` | `uploads.show` | `auth`, `verified` | `UploadController@show` |
| GET/PATCH/DELETE | `/profile` | `profile.*` | `auth` (no `verified`) | `ProfileController` |
| Auth routes | `/login`, `/register`, etc. | Breeze names | `guest` / `auth` | `Auth\*Controller` |

**Note:** `access.merchant` is applied at the RouteServiceProvider group level **and** again on protected routes in `merchant.php` (redundant).

**Missing routes:** No printing module routes (`/printing/*`). Placeholder nav items only.

### 1.3 Controllers & validation

| Controller | Path | Views |
| --- | --- | --- |
| `UploadController` | `app/Http/Controllers/UploadController.php` | `merchant.pages.uploads.*` |
| `ProfileController` | `app/Http/Controllers/ProfileController.php` | `merchant.pages.profile.edit` |
| `Auth\*` (8 controllers) | `app/Http/Controllers/Auth/` | `merchant.auth.*` |

| Form request | Merchant usage |
| --- | --- |
| `StoreUploadRequest` | Upload limits from `MerchantConfig` |
| `LoginRequest` | Blocks non-merchant roles on merchant login |
| `RegisterRequest` | Registration validation |
| `ProfileUpdateRequest` | Profile update |

**Policy:** `UploadJobPolicy` — merchant-scoped; admins allowed by policy but blocked by `access.merchant` middleware.

**Services:** `UploadService` (job creation, temp storage, audit), `AuditLogService`, `DomainConfigurationService`.

### 1.4 Views (27 files)

```
resources/views/merchant/
├── layouts/          app.blade.php, guest.blade.php
├── partials/         header, sidebar, mobile-nav, footer
├── components/       breadcrumb, page-header, empty-state, loading-state, upload-status-badge
├── auth/             6 Breeze-derived auth pages
├── dashboard/        index.blade.php (placeholder stats)
├── pages/
│   ├── uploads/      index, create, show
│   ├── profile/      edit + 3 partials
│   └── partials/     empty-upload-action
└── printing/         .gitkeep only
```

**Branding:** Views read `\App\Support\MerchantConfig::get('brand.*')` with `__('merchant.*')` fallbacks.

**Legacy duplication:** Orphaned Breeze views remain outside `merchant/` (`resources/views/auth/`, `layouts/`, `uploads/`, `dashboard.blade.php`, `components/*`) — not referenced by merchant controllers.

### 1.5 JavaScript architecture

**Entry:** `resources/js/merchant.js` — imports modules, registers Alpine components on `DOMContentLoaded`.

| Module | Path | Exports / globals |
| --- | --- | --- |
| `http.js` | `resources/js/merchant/http.js` | Axios instance (CSRF, X-Requested-With) |
| `ajax.js` | `resources/js/merchant/ajax.js` | `merchantGet/Post/Put/Delete` → `window.MerchantAjax` |
| `toast.js` | `resources/js/merchant/toast.js` | `showToast`, `initFlashToasts` → `window.MerchantToast` |
| `sweetalert.js` | `resources/js/merchant/sweetalert.js` | `confirmDialog`, `initDeleteAccountConfirmation` → `window.MerchantAlert` |
| `shell.js` | `resources/js/merchant/shell.js` | `merchantShell`, `uploadForm` Alpine data, `startAlpine()` |

**Stack:** Alpine.js 3, axios, SweetAlert2. Vite builds merchant bundle separately from Breeze `app.js`.

**Gap:** No module-specific JS for printing preview engine. Upload form logic lives inline in `shell.js` (should move to `modules/uploads.js`).

### 1.6 CSS / Tailwind

**Entry:** `resources/css/merchant.css` — SweetAlert2 import, Tailwind layers, merchant component classes.

| Layer | Classes |
| --- | --- |
| `@layer components` | `.merchant-sidebar-link*`, `.merchant-card`, `.merchant-btn-*`, `.merchant-input`, `.merchant-label`, `.merchant-dropzone*` |
| `@layer utilities` | Toast container/types, loading overlay, spinner |

**Tailwind config:** Scans all `resources/views/**/*.blade.php` (merchant + legacy). Palette: amber primary, slate neutrals. `@tailwindcss/forms` enabled.

**Gap:** No print-preview-specific CSS (150×100 mm canvas, safe zone, aspect-ratio warning).

### 1.7 Localization

| File | Purpose |
| --- | --- |
| `lang/en/merchant.php` | ~200 keys: brand, nav, dashboard, uploads, profile, printing, flash, ajax, sweetalert |
| `lang/zh-TW/merchant.php` | Traditional Chinese mirror |
| `lang/en/auth.php` | Auth page copy only |
| `lang/zh-TW/auth.php` | TW auth page copy |

**Locale resolution:** `ResolveRegion` sets `app.locale` from region default (`zh-TW` for TW). No user-facing locale switcher. No `lang/en.json` / JSON translations.

**Gap:** Custom `auth.php` lacks Laravel framework keys (`auth.failed`, `auth.throttle`) — falls back to vendor lang. Validation messages in `StoreUploadRequest` are hardcoded English.

### 1.8 Database schema (merchant-relevant)

| Table | Migration | Purpose |
| --- | --- | --- |
| `users` | `0001_*`, `2026_05_27_*` | Auth + `role` column |
| `merchants` | `2026_05_23_120001_*` | Seller profile linked to user |
| `upload_jobs` | `2026_05_23_120002_*`, `140000_*` | Upload workflow |
| `pdf_uploads` | `2026_05_23_120003_*` | Stored PDF metadata |
| `picking_lists` | `2026_05_23_120004_*` | Future printing |
| `delivery_labels` | `2026_05_23_120005_*` | Future printing |
| `audit_logs` | `2026_05_23_120006_*` | Activity trail |
| `domain_settings` | `2026_05_30_000001_*` | Region host, branding, JSON settings |
| `domain_locales` | `2026_05_30_000002_*` | Locales per region |
| `domain_features` | `2026_05_30_000003_*` | Feature toggles |
| `billing_plans` | `2026_05_23_120000_*` | Future subscription (mockup) |

**Seeder:** `DomainSettingSeeder` bootstraps TW/PH/VN from `config/domains.php` → `fallback_merchants`.

### 1.9 Configuration management (current state)

| Source | Merchant settings |
| --- | --- |
| **Database** (`domain_settings`) | Host, active flag, session cookie, branding, upload limits (JSON), sort order |
| **Database** (`domain_locales`) | Supported locales, default locale |
| **Database** (`domain_features`) | `uploads`, `printing_*` toggles |
| **`config/domains.php`** | Fallback definitions for seeder; infrastructure (marketing/admin) |
| **`.env`** | Infrastructure only (see §3) |

**Access pattern:** `MerchantConfig::get('brand.name')`, `MerchantConfig::feature('uploads')`, `MerchantConfig::get('upload.max_file_size_kb')`.

---

## 2. Gap Analysis

### 2.1 Architecture gaps

| Area | Current | Target (M1 spec) | Severity |
| --- | --- | --- | --- |
| Printing modules | Sidebar placeholders, `.gitkeep` | Master-detail workspaces (Order Details, Logistics Labels, Picking List, Delivery Labels) | **High** |
| Preview engine | Placeholder text on upload show | 150×100 mm canvas, safe zone, aspect ratio validation | **High** |
| Dashboard | Static placeholder cards | Live stats from upload jobs | Medium |
| Locale switcher | Region default only (`zh-TW`) | User-selectable EN / zh-TW per region config | Medium |
| Controller namespace | Shared `App\Http\Controllers` | Merchant-scoped namespace optional but recommended | Low |
| Legacy views | Orphaned Breeze views in repo | Removed or archived | Low |
| Middleware redundancy | `access.merchant` double-applied | Single application point | Low |
| Logout/delete redirect | Redirect to `/` (marketing root) | Redirect to merchant login | Medium |
| Profile `verified` gap | Profile routes skip `verified` | Consistent verification policy | Low |
| Filament admin UI for domains | None | Future: manage `domain_settings` without code deploy | Future |

### 2.2 Code quality gaps

| Issue | Location | Recommendation |
| --- | --- | --- |
| Dead config fallback | `StoreUploadRequest` → `config('printflow.upload.*')` | Remove; use `MerchantConfig` only |
| Legacy view references | `resources/views/uploads/create.blade.php` | Delete with legacy cleanup |
| Hardcoded validation messages | `StoreUploadRequest::messages()` | Move to `lang/*/merchant.php` |
| Missing `auth.failed` keys | `lang/en/auth.php` | Add framework-compatible keys |
| Printing nav not feature-gated | `sidebar.blade.php` | Gate each item via `MerchantConfig::feature()` |
| Guest layout loads full JS | `guest.blade.php` | Split `merchant-auth.js` lightweight bundle |
| Upload Alpine in shell.js | `shell.js` | Extract to `modules/uploads.js` |
| Blade components as includes | `merchant/components/*.blade.php` | Promote to `<x-merchant::*>` class components |

### 2.3 Test coverage gaps

| Scenario | Covered? |
| --- | --- |
| Upload CRUD | Yes (`UploadTest`) |
| Surface isolation | Yes (`SurfaceAccessTest`) |
| DB domain config | Yes (`DomainConfigurationTest`) |
| Domain routing (403 inactive) | Skipped in default PHPUnit env |
| Locale switching | No |
| Feature toggle UI hiding | No |
| Printing modules | N/A (not built) |

---

## 3. Database-Driven Settings Strategy

### 3.1 Already migrated (done)

These were previously `.env`-driven and now live in the database:

| Setting | DB location | Access |
| --- | --- | --- |
| Merchant host per region | `domain_settings.host` | `DomainConfigurationService` |
| Region active flag | `domain_settings.is_active` | Routing + `EnsureRegionIsActive` |
| Session cookie name | `domain_settings.session_cookie` | `ConfigureDomainSession` |
| Brand name/tagline/logo/favicon | `domain_settings.brand_*` | `MerchantConfig::get('brand.*')` |
| Default locale | `domain_locales.is_default` | `ResolveRegion` → `app.locale` |
| Supported locales | `domain_locales` rows | Future locale switcher |
| Upload max size / max files | `domain_settings.settings` JSON | `MerchantConfig::get('upload.*')` |
| Feature toggles | `domain_features` | `MerchantConfig::feature()` |

### 3.2 Remain in `.env` (infrastructure)

These affect the merchant domain but are correctly infrastructure-level:

| Variable | Merchant impact | Keep in `.env`? |
| --- | --- | --- |
| `APP_NAME` | Fallback app name | Yes |
| `APP_ENV` | Local vs production host defaults in seeder | Yes |
| `APP_URL` | Default URL generation (`http://localhost:8001` local) | Yes |
| `APP_KEY` | Encryption | Yes |
| `APP_DEBUG` | Error pages on merchant | Yes |
| `APP_TIMEZONE` | Timestamps in upload history | Yes |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | Global fallback before region resolve | Yes |
| `DOMAIN_ROUTING_ENABLED` | Route registration mode | Yes |
| `DB_*` | All merchant data | Yes |
| `REDIS_*` / `CACHE_STORE` | Config cache, queues | Yes |
| `SESSION_*` | Base session driver; per-domain cookie name from DB | Yes |
| `QUEUE_CONNECTION` | Upload processing (future) | Yes |
| `MAIL_*` | Verification emails | Yes |
| `FILESYSTEM_DISK` | Upload temp storage | Yes |
| `VITE_APP_NAME` | Vite manifest | Yes |
| `LOCAL_PORT_MERCHANT_TW` | Documentation / dev tooling only | Optional |

### 3.3 Recommended future DB migrations

| Setting | Proposed location | When |
| --- | --- | --- |
| Preview canvas dimensions | `domain_settings.settings.preview` JSON | Printing module phase |
| Safe zone margin (mm) | `domain_settings.settings.preview.safe_zone_mm` | Printing module phase |
| Aspect ratio tolerance (%) | `domain_settings.settings.preview.aspect_tolerance` | Printing module phase |
| Supported upload types | `domain_features` or settings JSON | When regions differ |
| Help/privacy/terms URLs | `domain_settings.settings.links` JSON | Before production |
| Support contact email | `domain_settings.settings.support_email` | Before production |
| Maintenance banner message | `domain_settings.settings.maintenance` | Optional ops feature |

### 3.4 Config access rules (canonical)

```
Infrastructure (.env → config/domains.php marketing/admin blocks)
        │
        ▼
DomainSettingSeeder ← fallback_merchants (bootstrap only)
        │
        ▼
domain_settings + domain_locales + domain_features
        │
        ▼
DomainConfigurationService (cached)
        │
        ▼
MerchantConfig / MerchantDomainConfig DTO
        │
        ▼
Views · FormRequests · Middleware · Services
```

**Rule:** Merchant-facing runtime values must not read new `.env` keys. Change merchant behavior via database or `DomainSettingSeeder` defaults.

---

## 4. Localization Strategy

### 4.1 Supported locales (TW merchant)

| Locale | Code | Role |
| --- | --- | --- |
| English | `en` | Fallback, developer default, optional user choice |
| Traditional Chinese (Taiwan) | `zh-TW` | Default for TW region per `domain_locales` |

PH/VN regions are seeded but inactive; add `lang/en-PH`, `lang/vi-VN` when those regions activate.

### 4.2 File organization (proposed)

```
lang/
├── en/
│   ├── merchant.php          # UI strings (existing)
│   ├── auth.php              # Auth page copy (existing)
│   └── validation.php        # NEW: merchant-specific validation messages
├── zh-TW/
│   ├── merchant.php
│   ├── auth.php
│   └── validation.php
└── vendor/                   # Framework fallbacks (auth.failed, pagination)
```

**Optional future:** Split large `merchant.php` into namespaced files loaded via `Lang::addNamespace()`:

```
lang/en/merchant/nav.php
lang/en/merchant/uploads.php
lang/en/merchant/printing.php
```

### 4.3 Locale resolution flow (proposed)

```
1. ResolveRegion sets locale from domain_settings default (zh-TW for TW)
2. Authenticated user preference (future: users.locale column or session)
3. ?lang=en query / cookie override (future: SetLocale middleware)
4. APP_FALLBACK_LOCALE (en) for missing keys
```

### 4.4 Implementation requirements

| Task | Priority |
| --- | --- |
| Add `auth.failed`, `auth.throttle` to custom `lang/*/auth.php` | P1 |
| Move `StoreUploadRequest` messages to lang files | P1 |
| Add locale switcher component (respects `domain_locales`) | P2 |
| Add `users.locale` nullable column + middleware | P3 |
| Split `merchant.php` when printing keys exceed ~300 lines | P3 |

### 4.5 Blade usage convention

```blade
{{ __('merchant.nav.dashboard') }}
{{ trans_choice('merchant.uploads.count', $count) }}
```

Never hardcode user-facing strings in Blade, JS, or validation. JS strings: pass via `@json(__('merchant.ajax'))` in layout or use `data-*` attributes.

---

## 5. Merchant Folder Structure Proposal

Goal: scale to printing modules and multi-region without colliding with admin/marketing code.

### 5.1 Application layer

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Merchant/                    # NEW namespace (gradual migration)
│   │       ├── DashboardController.php
│   │       ├── UploadController.php     # move from root
│   │       ├── ProfileController.php
│   │       └── Printing/                  # future
│   │           ├── OrderDetailsController.php
│   │           ├── LogisticsLabelsController.php
│   │           ├── PickingListController.php
│   │           └── DeliveryLabelsController.php
│   ├── Middleware/                      # existing (shared infrastructure)
│   └── Requests/
│       └── Merchant/                    # NEW
│           ├── StoreUploadRequest.php
│           └── Printing/                # future
├── Services/
│   ├── Domain/                          # existing
│   └── Merchant/                        # NEW
│       ├── UploadService.php            # move from root
│       └── Printing/                    # future preview/normalization
├── View/
│   └── Components/
│       └── Merchant/                    # NEW class-based Blade components
│           ├── PageHeader.php
│           ├── EmptyState.php
│           └── PreviewCanvas.php        # future
└── Support/
    └── MerchantConfig.php               # existing
```

### 5.2 Views

```
resources/views/merchant/
├── layouts/
│   ├── app.blade.php
│   ├── guest.blade.php
│   └── printing.blade.php               # NEW: master-detail shell
├── partials/
├── components/
│   ├── ui/                              # generic UI
│   │   ├── breadcrumb.blade.php
│   │   ├── page-header.blade.php
│   │   ├── empty-state.blade.php
│   │   └── loading-state.blade.php
│   ├── forms/                           # form primitives
│   │   ├── input.blade.php
│   │   └── dropzone.blade.php
│   └── domain/                          # domain-aware
│       ├── upload-status-badge.blade.php
│       └── locale-switcher.blade.php    # future
├── modules/
│   ├── dashboard/
│   ├── uploads/
│   ├── profile/
│   └── printing/                        # replace .gitkeep
│       ├── order-details/
│       ├── logistics-labels/
│       ├── picking-list/
│       └── delivery-labels/
└── auth/
```

### 5.3 Frontend assets

```
resources/
├── css/
│   └── merchant/
│       ├── index.css                    # entry (replaces flat merchant.css)
│       ├── components/
│       └── modules/
│           └── printing/
│               ├── preview-canvas.css
│               └── safe-zone.css
└── js/
    └── merchant/
        ├── index.js                     # entry (replaces merchant.js)
        ├── core/
        │   ├── http.js
        │   ├── ajax.js
        │   ├── toast.js
        │   └── sweetalert.js
        ├── shell.js
        └── modules/
            ├── uploads.js
            └── printing/
                ├── preview-engine.js
                └── aspect-ratio.js
```

### 5.4 Routes (proposed)

```
routes/
└── merchant/
    ├── web.php              # dashboard, profile (move from merchant.php)
    ├── uploads.php
    ├── auth.php             # move from routes/auth.php (merchant-only)
    └── printing.php         # future
```

`RouteServiceProvider` loads `routes/merchant/*.php` in order.

---

## 6. UI Architecture Proposal

### 6.1 Layout hierarchy

| Layout | Use |
| --- | --- |
| `merchant.layouts.guest` | Login, register, password reset |
| `merchant.layouts.app` | Dashboard, uploads, profile |
| `merchant.layouts.printing` | Master-detail printing workspaces (new) |

### 6.2 Authenticated app shell

```
┌─────────────────────────────────────────────────────────┐
│ Header (brand tagline, welcome, mobile menu)            │
├──────────┬──────────────────────────────────────────────┤
│ Sidebar  │ Main content                                 │
│ (nav)    │ ┌─ Breadcrumb ─────────────────────────────┐ │
│          │ │ Page header (title + actions)              │ │
│          │ ├──────────────────────────────────────────┤ │
│          │ │ Page body (cards / tables / modules)     │ │
│          │ └──────────────────────────────────────────┘ │
├──────────┴──────────────────────────────────────────────┤
│ Footer                                                  │
└─────────────────────────────────────────────────────────┘
Mobile: sidebar → off-canvas; `mobile-nav.blade.php`
```

### 6.3 Printing module shell (master-detail)

Per M1 spec §4 — each printing module uses a dedicated layout:

```
┌─────────────────────────────────────────────────────────┐
│ Module header + breadcrumb                              │
├─────────────────┬───────────────────────────────────────┤
│ Left pane       │ Right pane                            │
│ (list/cards)    │ (live HTML preview)                   │
│ - job items     │ ┌─────────────────────────┐           │
│ - filters       │ │ 150×100 mm canvas       │           │
│                 │ │ + 5 mm dashed safe zone │           │
│                 │ └─────────────────────────┘           │
│                 │ [Print] [Force adjustment]            │
├─────────────────┴───────────────────────────────────────┤
│ Mobile: list collapses to single column cards           │
└─────────────────────────────────────────────────────────┘
```

### 6.4 Design tokens (existing — keep)

| Token | Value |
| --- | --- |
| Primary | Amber 600/500 |
| Neutrals | Slate |
| Cards | `.merchant-card` |
| Buttons | `.merchant-btn-primary`, `.merchant-btn-secondary` |
| Forms | `.merchant-input`, `.merchant-label` |

Extend with `.merchant-preview-canvas`, `.merchant-safe-zone`, `.merchant-aspect-warning` in printing phase.

---

## 7. Reusable Components Strategy

### 7.1 Blade components

| Current (include) | Proposed (class component) | Props |
| --- | --- | --- |
| `components/breadcrumb.blade.php` | `<x-merchant::ui.breadcrumb :items="$items" />` | `items: array` |
| `components/page-header.blade.php` | `<x-merchant::ui.page-header />` | `title`, `subtitle`, slot actions |
| `components/empty-state.blade.php` | `<x-merchant::ui.empty-state />` | `title`, `description`, slot |
| `components/loading-state.blade.php` | `<x-merchant::ui.loading-state />` | `message` |
| `components/upload-status-badge.blade.php` | `<x-merchant::domain.upload-status-badge />` | `status` |
| — | `<x-merchant::ui.alert />` | `type`, `message` |
| — | `<x-merchant::domain.feature-gate />` | `feature`, slot |

Register namespace in `AppServiceProvider`: `Blade::componentNamespace('App\\View\\Components\\Merchant', 'merchant')`.

### 7.2 Service classes

| Service | Responsibility |
| --- | --- |
| `DomainConfigurationService` | Region config (existing) |
| `Merchant\UploadService` | Upload job lifecycle (move) |
| `Merchant\UploadLimitResolver` | Centralize max size/files from `MerchantConfig` |
| `Merchant\NavigationBuilder` | Build sidebar items from features + routes |
| `Merchant\Printing\PreviewEngine` | Canvas HTML generation (future) |
| `Merchant\Printing\AspectRatioValidator` | Tolerance check (future) |

### 7.3 Helper classes

| Helper | Purpose |
| --- | --- |
| `MerchantConfig` | Static config access (existing) |
| `merchant_config()` / `merchant_feature()` | Global helpers (existing) |
| `MerchantUrl` (future) | Region-aware absolute URLs |
| `MerchantAssets` (future) | Brand logo/favicon URLs from DB |

### 7.4 JavaScript modules

| Module | API | Usage |
| --- | --- | --- |
| `core/http.js` | axios instance | Base for all AJAX |
| `core/ajax.js` | `MerchantAjax.get/post/put/delete` | CRUD + auto toast on error |
| `core/toast.js` | `MerchantToast.show()`, `initFlashToasts()` | Notifications |
| `core/sweetalert.js` | `MerchantAlert.confirm()`, `initDeleteAccountConfirmation()` | Destructive actions |
| `modules/uploads.js` | `registerUploadForm()` | Alpine upload dropzone |
| `modules/printing/preview-engine.js` | `MerchantPreview.init(canvasEl, options)` | Future |

**Convention:** All modules attach to `window.Merchant*` for Blade-initiated calls; prefer ES module exports for internal imports.

### 7.5 CSS structure

| File | Contents |
| --- | --- |
| `merchant/index.css` | Tailwind entry + imports |
| `merchant/components/sidebar.css` | Sidebar link states |
| `merchant/components/cards.css` | Card, button, form primitives |
| `merchant/components/toast.css` | Toast utilities |
| `merchant/modules/printing/preview.css` | Canvas, safe zone, warnings |

Use `@layer components` for reusable patterns; avoid inline Tailwind repetition in Blade.

---

## 8. Priority-Based Implementation Plan

### Phase 0 — Foundation cleanup (1–2 days)

**Goal:** Remove debt before building printing modules.

| # | Task | Files |
| --- | --- | --- |
| 0.1 | Delete legacy Breeze views not referenced by merchant | `resources/views/auth/`, `layouts/`, `uploads/`, etc. |
| 0.2 | Remove dead `config('printflow.upload.*')` fallback | `StoreUploadRequest`, legacy views |
| 0.3 | Add missing `auth.failed` / `auth.throttle` lang keys | `lang/en/auth.php`, `lang/zh-TW/auth.php` |
| 0.4 | Move validation messages to lang files | `StoreUploadRequest`, `lang/*/merchant.php` |
| 0.5 | Fix logout/profile-delete redirect to merchant login | `AuthenticatedSessionController`, `ProfileController` |
| 0.6 | Deduplicate `access.merchant` middleware application | `routes/merchant.php`, `RouteServiceProvider` |
| 0.7 | Gate printing nav items via `MerchantConfig::feature()` | `sidebar.blade.php`, `mobile-nav.blade.php` |
| 0.8 | Split `uploadForm` Alpine from `shell.js` | `resources/js/merchant/modules/uploads.js` |

### Phase 1 — Component & namespace consolidation (2–3 days)

| # | Task |
| --- | --- |
| 1.1 | Register `<x-merchant::*>` Blade component namespace |
| 1.2 | Convert includes to class components (breadcrumb, page-header, empty-state, loading-state) |
| 1.3 | Move controllers to `App\Http\Controllers\Merchant\` namespace |
| 1.4 | Move `UploadService` to `App\Services\Merchant\` |
| 1.5 | Restructure routes into `routes/merchant/*.php` |
| 1.6 | Add `Merchant\NavigationBuilder` for sidebar/nav DRY |

### Phase 2 — Localization & locale switcher (1–2 days)

| # | Task |
| --- | --- |
| 2.1 | Add locale switcher UI (respects `domain_locales`) |
| 2.2 | Add `SetLocale` middleware (session/cookie preference) |
| 2.3 | Pass JS i18n strings via layout `@json` |
| 2.4 | Optional: `users.locale` column |

### Phase 3 — Printing module shells (3–5 days)

| # | Task |
| --- | --- |
| 3.1 | Create `merchant.layouts.printing` master-detail layout |
| 3.2 | Add routes + controllers for 4 printing modules (shell only) |
| 3.3 | Feature-gate modules via `domain_features` |
| 3.4 | Mobile single-column collapse |
| 3.5 | Placeholder preview pane markup |

### Phase 4 — Preview engine (5–8 days, M1 critical)

| # | Task |
| --- | --- |
| 4.1 | 150×100 mm fixed-aspect canvas component |
| 4.2 | 5 mm dashed safe-zone overlay |
| 4.3 | Aspect ratio validation (>10% → amber warning + banner) |
| 4.4 | Force adjustment toggle |
| 4.5 | DB settings for preview dimensions/tolerance |
| 4.6 | Courier address auto-shrink (18 px → 14 px) |

### Phase 5 — Dashboard & uploads polish (2–3 days)

| # | Task |
| --- | --- |
| 5.1 | Live dashboard stats from `upload_jobs` |
| 5.2 | Card-based upload history (mobile) |
| 5.3 | Upload show page preview integration |
| 5.4 | Guest layout lightweight JS bundle |

### Phase 6 — Testing & CI (1–2 days)

| # | Task |
| --- | --- |
| 6.1 | Enable `DOMAIN_ROUTING_ENABLED=true` in dedicated CI job |
| 6.2 | Feature toggle tests |
| 6.3 | Locale switcher tests |
| 6.4 | Full merchant URL tests for all routes |

---

## Appendix A — Environment variable inventory (merchant impact)

| Variable | Category | Notes |
| --- | --- | --- |
| `APP_URL` | Infrastructure | Local: `http://localhost:8001` |
| `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_TIMEZONE` | Infrastructure | Shared |
| `APP_LOCALE`, `APP_FALLBACK_LOCALE` | Infrastructure | Overridden per-request by `ResolveRegion` |
| `DOMAIN_ROUTING_ENABLED` | Infrastructure | PHPUnit disables |
| `MARKETING_DOMAIN`, `ADMIN_DOMAIN`, `ADMIN_PATH_PREFIX` | Infrastructure | Not merchant |
| `DB_*`, `REDIS_*`, `CACHE_STORE`, `QUEUE_CONNECTION` | Infrastructure | Shared |
| `SESSION_*` | Infrastructure | Cookie **name** from DB per region |
| `MAIL_*` | Infrastructure | Verification emails |
| `FILESYSTEM_DISK` | Infrastructure | Temp uploads |
| `VITE_APP_NAME` | Infrastructure | Asset build |
| `LOCAL_PORT_MERCHANT_TW` | Dev tooling | Optional, default 8001 |
| ~~`MERCHANT_TW_DOMAIN`~~ | **Removed** | → `domain_settings.host` |
| ~~`REGION_*_ACTIVE`~~ | **Removed** | → `domain_settings.is_active` |
| ~~`PRINTFLOW_BRAND_*`~~ | **Removed** | → `domain_settings.brand_*` |
| ~~`PRINTFLOW_UPLOAD_*`~~ | **Removed** | → `domain_settings.settings` JSON |

---

## Appendix B — Key file index

| Area | Paths |
| --- | --- |
| Routes | `routes/merchant.php`, `routes/auth.php`, `app/Providers/RouteServiceProvider.php` |
| Config | `config/domains.php`, `app/Services/Domain/DomainConfigurationService.php` |
| Views | `resources/views/merchant/**` |
| Assets | `resources/css/merchant.css`, `resources/js/merchant.js`, `resources/js/merchant/**` |
| i18n | `lang/en/merchant.php`, `lang/zh-TW/merchant.php`, `lang/*/auth.php` |
| DB | `database/migrations/2026_05_30_*`, `database/seeders/DomainSettingSeeder.php` |
| Tests | `tests/Feature/DomainConfigurationTest.php`, `SurfaceAccessTest.php`, `UploadTest.php` |

---

*This document should be updated when implementation phases complete. Do not modify Admin or Marketing domains without explicit scope change.*
