# Merchant Domain Reconciliation Report

**Date:** 2026-05-30  
**Scope:** Merchant domain only (`localhost:8001` / TW merchant host)  
**Out of scope:** Admin (Filament), Marketing, new features

This report documents the audit and reconciliation pass performed after implementation phases 1–8 (UI foundation through courier auto-shrink). It compares the codebase against [MERCHANT_DOMAIN_ARCHITECTURE.md](MERCHANT_DOMAIN_ARCHITECTURE.md) and records fixes applied.

---

## 1. Issues Found

### Architecture & structure

| Issue | Severity | Location |
| --- | --- | --- |
| Controllers outside `App\Http\Controllers\Merchant\` | High | Upload, Profile, Auth (11 controllers) |
| Dashboard implemented as route closure | Medium | `routes/merchant.php` |
| Legacy Breeze views duplicated merchant UI | Medium | `resources/views/auth/`, `uploads/`, `profile/`, `layouts/app`, `components/*` |
| `AppLayout` / `GuestLayout` orphaned | Low | `app/View/Components/` |
| Auth routes at `routes/auth.php` instead of `routes/merchant/auth.php` | Low | Route organization |
| Delivery labels layout duplicated full HTML document | Medium | `delivery-labels-module.blade.php` |
| Delivery labels CSS bundle duplicated `merchant.css` (~95 lines) | High | `merchant-delivery-labels.css` |
| Printing workspace JS duplicated in delivery labels | High | `printing.js` vs `delivery-labels/workspace.js` |
| `access.merchant` middleware applied twice | Low | Route files + `MERCHANT_MIDDLEWARE` |
| `CourierCsvHeaderDetector` not wired to upload flow | Low | Service exists; integration deferred |
| `printingWorkspace` registered on all merchant pages | Low | `merchant.js` (harmless overhead) |
| `MERCHANT_DOMAIN_ARCHITECTURE.md` stale (pre-printing) | Medium | Documentation |

### Localization

| Issue | Severity | Location |
| --- | --- | --- |
| `StoreUploadRequest` hardcoded English messages | Medium | `app/Http/Requests/StoreUploadRequest.php` |
| `UploadJobType::label()` hardcoded English | Medium | `app/Enums/UploadJobType.php` |
| Upload create view used `config('printflow.upload.*')` fallbacks | Medium | `uploads/create.blade.php` |
| Inactive region 403 message hardcoded | Low | Middleware / RouteServiceProvider |
| Upload show used literal em dash | Low | `uploads/show.blade.php` |
| Logout / account delete redirected to marketing `/` | Medium | Auth / Profile controllers |

### Database configuration

| Issue | Severity | Location |
| --- | --- | --- |
| Merchant upload limits fell back to `config('printflow.upload.*')` | Medium | Request + Blade |
| Acceptable: `config/domains.php` fallback for seeding only | Info | Infrastructure |

---

## 2. Issues Fixed

| Fix | Files changed |
| --- | --- |
| Moved Upload, Profile, Dashboard to `App\Http\Controllers\Merchant\` | New controllers; deleted root copies |
| Moved Auth to `App\Http\Controllers\Merchant\Auth\` | 9 controllers; deleted legacy Auth namespace |
| Added `DashboardController` invokable | `app/Http/Controllers/Merchant/DashboardController.php` |
| Relocated auth routes to `routes/merchant/auth.php` | `routes/merchant.php`, deleted `routes/auth.php` |
| Removed redundant `access.merchant` from route groups | `routes/merchant.php`, `routes/merchant/printing.php` |
| Consolidated delivery-labels CSS to import `merchant.css` | `resources/css/merchant-delivery-labels.css` |
| Extracted shared printing workspace Alpine logic | `printing-workspace-shared.js` |
| Delivery labels layout extends app via `@section('vite')` | `app.blade.php`, `delivery-labels-module.blade.php` |
| Removed legacy Breeze view tree (~30 files) | `resources/views/auth`, `uploads`, `profile`, etc. |
| Removed orphaned `AppLayout` / `GuestLayout` | Deleted component classes |
| Localized upload validation, types, errors, region 403 | `lang/en/merchant.php`, `lang/zh-TW/merchant.php` |
| Merchant upload limits use `MerchantConfig` only | `StoreUploadRequest`, `uploads/create.blade.php` |
| Logout / account delete redirect to `route('login')` | Merchant Auth + Profile controllers |

---

## 3. Remaining Concerns (Technical Debt)

| Item | Priority | Notes |
| --- | --- | --- |
| Live HTML preview for order details / logistics / picking list | High | Placeholder text in shared `preview-pane.blade.php` |
| Connect `DeliveryLabel` Eloquent model to workspace | High | Service still uses sample data |
| Wire `CourierCsvHeaderDetector` into upload CSV parsing | Medium | Detector tested; no upload pipeline hook yet |
| Lazy-load printing Alpine on non-printing pages | Low | `printingWorkspace` registered globally |
| Locale switcher UI (Phase 2) | Medium | DB locales seeded; no user preference UI |
| Profile routes omit `verified` middleware | Low | Documented gap; email verification optional path |
| `config/printflow.php` env keys | Low | May still serve admin/marketing; merchant path no longer reads them |
| Dashboard stats are placeholders | Medium | Phase 5 |
| `MERCHANT_DOMAIN_ARCHITECTURE.md` full refresh | Medium | Partially addressed; full rewrite recommended |
| Preview settings in `domain_settings.settings` JSON | Low | Tolerance/aspect config still code defaults |
| CI job with `DOMAIN_ROUTING_ENABLED=true` | Medium | Phase 6 |

---

## 4. Architecture Compliance Status

| Area | Status | Notes |
| --- | --- | --- |
| Controller namespace (`Merchant\`) | **Compliant** | All merchant HTTP handlers under `Merchant\` |
| Route organization | **Mostly compliant** | `routes/merchant.php`, `routes/merchant/printing.php`, `routes/merchant/auth.php` |
| View namespace (`merchant.*`) | **Compliant** | Legacy Breeze tree removed |
| Service layer | **Compliant** | Domain, Printing, Preview, DeliveryLabels services separated |
| Preview engine reusability | **Compliant** | Shared `x-merchant.preview.*` components |
| Delivery labels module isolation | **Compliant** | Dedicated layout, Vite entry, workspace Alpine |
| DB-driven config | **Mostly compliant** | Branding, features, upload limits via `MerchantConfig` |
| Multi-domain routing | **Compliant** | TW/PH/VN in `domain_settings`; inactive regions 403 |

---

## 5. Localization Compliance Status

| Area | Status |
| --- | --- |
| Merchant Blade views | **Compliant** — user-facing strings use `__()` |
| Upload validation messages | **Compliant** — `merchant.uploads.validation.*` |
| Upload type labels | **Compliant** — `merchant.uploads.types.*` |
| Printing / preview / delivery labels | **Compliant** — en + zh-TW |
| Region error messages | **Compliant** — `merchant.errors.region_inactive` |
| JS fallbacks (ajax/sweetalert) | **Acceptable** — layouts provide `data-*`; fallbacks are last resort |

---

## 6. Database Configuration Compliance Status

| Setting | Source | Status |
| --- | --- | --- |
| Merchant hosts | `domain_settings.host` | DB |
| Branding | `domain_settings` columns | DB |
| Feature flags | `domain_features` | DB |
| Locales | `domain_locales` | DB |
| Upload limits | `domain_settings.settings.upload` | DB via `MerchantConfig` |
| Session cookie per region | `domain_settings.session_cookie` | DB |
| Domain routing toggle | `.env` `DOMAIN_ROUTING_ENABLED` | Infrastructure (OK) |
| Admin path | `.env` / `config/domains.php` | Infrastructure (OK) |

---

## 7. Multi-Domain Readiness Status

| Requirement | Status |
| --- | --- |
| No hardcoded TW-only hosts in merchant code | **Ready** |
| Feature gating per region | **Ready** — `EnsurePrintingModuleEnabled`, nav helper |
| Inactive region handling | **Ready** — 403 with localized message |
| PH/VN regions in seeder (inactive) | **Ready** — enable via `is_active` |
| Per-region session cookies | **Ready** |
| Locale per domain | **Partial** — DB seeded; switcher UI pending |

---

## 8. Recommendations Before Next Development Phase

1. **Live HTML preview** — Inject real upload/job content into `PreviewContainer` for order details, logistics, and picking list (shared preview pane).
2. **Delivery label data pipeline** — Query `DeliveryLabel` model; wire CSV upload + `CourierCsvHeaderDetector`.
3. **Locale switcher** — Phase 2 from architecture plan; use `domain_locales` + user preference.
4. **Refresh MERCHANT_DOMAIN_ARCHITECTURE.md** — Update §1 inventory to reflect printing routes, preview engine, and controller namespaces.
5. **Phase 6 CI** — Dedicated job with domain routing enabled; full merchant URL tests.
6. **Extract upload form Alpine** — Split `uploadForm` from `shell.js` per architecture plan (optional polish).

---

## Folder Structure (Post-Reconciliation)

```
app/Http/Controllers/Merchant/
├── Auth/                    # Breeze auth (9 controllers)
├── Printing/                # Module + aspect-ratio API
├── DashboardController.php
├── ProfileController.php
└── UploadController.php

app/Services/Merchant/
├── Printing/
│   ├── DeliveryLabels/      # Typography, CSV detection, preview
│   └── *Service.php         # Module services
└── Preview/                 # Aspect ratio validation

resources/views/merchant/
├── layouts/                 # app, guest, printing-module, delivery-labels-module
├── components/preview/      # Reusable preview engine
├── printing/                # Module shells + delivery-labels components
└── pages/                   # uploads, profile

resources/js/merchant/
├── modules/                 # printing.js, printing-workspace-shared.js
├── preview/                 # Engine, scale, aspect-ratio, safe-zone
└── printing/delivery-labels/

routes/
├── merchant.php
└── merchant/
    ├── auth.php
    └── printing.php
```

---

*Reconciliation pass complete. All existing tests should pass; run `php artisan test` and `npm run build` before continuing development.*
