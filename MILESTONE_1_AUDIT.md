# Milestone 1 Audit Report

**Project:** XY Cubic Shopee (Shopee Batch Print Integration SaaS)  
**Specification:** V2.4 — Laravel 12 Integrated  
**Audit date:** 2026-05-30  
**Auditor scope:** Read-only codebase review; no code changes made  
**Laravel version:** 12.60.2 (`composer.lock`, `php artisan --version`)

---

## Executive Summary

Milestone 1 (Portals & UI/UX) is **approximately 45% complete** against the V2.4 checklist provided for this audit. The foundation is strong: Laravel 12 is in place, multi-domain routing architecture is implemented and tested (when routing is enabled), RBAC surface isolation works, and both portal shells (merchant Breeze + Filament admin) exist with responsive Tailwind styling.

The largest gaps are:

1. **No VPS deployment pipeline** in the repository.
2. **Printing-module frontend workspaces** (master-detail layouts, 150×100 mm preview, safe zone, aspect ratio validation, courier auto-shrink) are **not implemented**.
3. **Static `/tw` page** exists but lacks zh-TW content.
4. **Minor spec mismatches** on admin path (`/boss` vs `/bosslogin`) and admin root behavior (redirect vs 404/403).

Backend billing **payment logic** is correctly deferred; admin plan CRUD exists as mockup scaffolding only.

---

## Completed Items

### A. Laravel 12 Migration — [DONE]

| Evidence | Detail |
|----------|--------|
| `composer.json` L12 | `"laravel/framework": "^12.0"` |
| `composer.lock` | `"version": "v12.60.2"` |
| Runtime | `php artisan --version` → `Laravel Framework 12.60.2` |
| PHP | `"php": "^8.3"` |
| Admin UI | Filament `^5.6` on Laravel 12 application skeleton |
| Tests | 36 PHPUnit tests passing |

No Laravel 11 legacy patterns or upgrade blockers were found. The project uses Laravel 12's `bootstrap/app.php` middleware registration and `RouteServiceProvider` for domain routing.

---

### C. Domain-Based Routing — Core [DONE]

**Architecture:** A single codebase serves three surfaces. `App\Providers\RouteServiceProvider` registers:

```102:114:app/Providers/RouteServiceProvider.php
            Route::domain($domain)
                ->middleware(self::MERCHANT_MIDDLEWARE)
                ->group(function () use ($isPrimary, $isActive): void {
                    if ($isPrimary && $isActive) {
                        require base_path('routes/merchant.php');

                        return;
                    }

                    Route::any('/{path?}', function () {
                        abort(Response::HTTP_FORBIDDEN, 'Region Not Activated Yet');
                    })->where('path', '.*');
                });
```

| Surface | Production host | Route file | Middleware stack |
|---------|-----------------|------------|------------------|
| Marketing | `xycubic.com` | `routes/marketing.php` | `web`, `domain.surface:marketing`, `domain.reject-unmapped` |
| Merchant TW | `tw.xycubic.com` | `routes/merchant.php` | + `region.active`, `access.merchant` |
| Admin | `manage-xy.xycubic.com` | `routes/admin.php` + Filament | + `admin.obfuscate` |

**Domain resolution:** `DomainResolver` maps `Request::getHost()` to `DomainContext` (surface, region, locale, active flag). `ResolveRegion` middleware binds context and sets `app.locale` for merchant regions.

**Local development:** Port-based hosts (`localhost:8000/8001/8002`) skip `Route::domain()` constraints; middleware enforces surface isolation instead (`DomainResolver::usesPortBasedLocalHost()`).

**Tests:** `tests/Feature/DomainRoutingTest.php` covers `/admin` → 404 and inactive PH region → 403 (skipped when `DOMAIN_ROUTING_ENABLED=false` in PHPUnit).

---

### D. Security Obfuscation — Core [DONE]

| Mechanism | Behavior | File |
|-----------|----------|------|
| Unknown host | HTTP 404 | `RejectUnmappedDomain.php` |
| Wrong surface on host | HTTP 404 | `EnsureExpectedSurface.php` |
| Non-`/boss` paths on admin domain | HTTP 404 | `ObfuscateAdminAccess.php` |
| Inactive merchant region | HTTP 403 | `EnsureRegionIsActive.php`, `RouteServiceProvider.php` |
| Admin on merchant routes | Logout + redirect | `EnsureMerchantAccess.php` |
| Merchant on admin panel | Panel denied | `User::canAccessPanel()` |
| Unmapped admin routes | Fallback 404 | `routes/admin.php` |

Automated proof: `tests/Feature/SurfaceAccessTest.php`, `tests/Feature/RbacTest.php`.

---

### E. Frontend Portal Layouts — Shell [DONE]

**TW Merchant Portal (`tw.xycubic.com`):**

- Breeze authentication (`routes/auth.php`, `routes/merchant.php`)
- Dashboard with card grid (`resources/views/dashboard.blade.php`)
- Upload create/index/show with drag-and-drop Alpine.js form (`resources/views/uploads/create.blade.php`)
- Responsive navigation (`resources/views/layouts/navigation.blade.php`)

**Management Portal (`manage-xy.xycubic.com/boss`):**

- Filament 5 panel (`app/Providers/Filament/AdminPanelProvider.php`)
- Resources: Merchants, Billing Plans, Upload Jobs, Audit Logs
- Widgets: Platform stats, recent audit logs
- Amber/slate theme, collapsible sidebar

---

### K. Deferred Features — Correctly Absent [DONE]

Verified **not implemented** (as required for M1):

- Payment gateway drivers (PayNow, SinoPac, HiTrust, etc.)
- Subscription checkout, invoice routing, billing webhooks
- `has_used_trial` flag and expiration-lock middleware
- Shopee API / OAuth / sandbox credentials
- PDF normalization (FPDI merge, thermal validation, A4 rejection)
- Queue jobs (`app/Jobs/` directory is empty)
- Temp-file shredding cron

**Mockup scaffold present (acceptable):** Filament CRUD for `BillingPlan` and merchant plan assignment — admin UI only, no payment processing.

---

## Partial Items

### B. VPS Deployment Pipeline — [PARTIAL]

**Current state:** README contains a short "Production deployment" section:

- Point DNS A/AAAA records to the app
- Set `DOMAIN_ROUTING_ENABLED=true` and production domains in `.env`
- Configure per-surface session cookies
- Enable PH/VN regions when ready

**Missing:**

- No `.github/workflows/`, `deploy.sh`, Forge/Envoyer config, or webhook receiver
- No documentation of Git Deploy Key setup on the client VPS
- No automated post-receive hook (`git pull`, `composer install --no-dev`, `npm run build`, `php artisan migrate --force`, `php artisan config:cache`, queue restart)
- No Supervisor/systemd unit files for queue workers

**Assessment:** Deployment is **documented at a high level only**. The M1 deliverable of an **automated deployment pipeline** is not present in the repository. Git Deploy Keys cannot be verified without server access.

---

### C. Domain-Based Routing — Path & Root Behavior — [PARTIAL]

| Spec (V2.4) | Implementation | Gap |
|-------------|----------------|-----|
| `manage-xy.xycubic.com/bosslogin` | `ADMIN_PATH_PREFIX=boss` → `/boss` | Path name mismatch; configurable via env but defaults to `boss` not `bosslogin` |
| Root on admin domain → 404/403 | Root redirects to `/boss` | Reveals admin entry point instead of obfuscating |

**Root redirect evidence:**

```23:28:app/Http/Middleware/ObfuscateAdminAccess.php
        // Allow root path and redirect to admin prefix
        $path = trim($request->path(), '/');
        if ($path === '') {
            $prefix = trim((string) config('domains.admin.path_prefix', 'boss'), '/');
            return redirect("/{$prefix}");
        }
```

```27:31:app/Http/Controllers/RootController.php
        if ($context->isAdmin()) {
            $prefix = trim((string) config('domains.admin.path_prefix', 'boss'), '/');

            return redirect("/{$prefix}");
        }
```

**Note:** `README.md` documents root as returning **404**, which contradicts the current code (redirect). Documentation drift should be resolved during implementation.

---

### D. Security Obfuscation — Root & Data Scopes — [PARTIAL]

**Done:** Path-level obfuscation, surface isolation, inactive region 403.

**Partial:**

- Admin root redirect (see above) — spec requires 404/403 on direct root access
- `country_code` Global Scopes — spec §2 mandates Global Scopes for cross-region data isolation; **no `addGlobalScope` usage exists anywhere in `app/`**

---

### E. Frontend Portal Layouts — Printing Modules — [PARTIAL]

**Done:** Portal shells, auth flows, upload foundation, responsive Tailwind, card grids on dashboard/marketing.

**Missing (M1 spec §4 + M1 milestone text):**

| Module | Required layout | Current state |
|--------|-----------------|---------------|
| Order Details | Master-detail: order list left, live HTML preview right | Not built |
| Logistics Labels | Center workspace with 150×100 mm preview | Placeholder text only |
| Picking List | Drag-drop spreadsheet + verification table | Upload type exists; no dedicated UI |
| Delivery Labels | Flexbox address layout with auto-shrink | Not built |

Upload workflow is a **single-column form + table history**, not the spec's printing-module master-detail workspaces.

---

### F. Static Staging Page — [PARTIAL]

**Route:**

```5:5:routes/marketing.php
Route::view('/tw', 'home')->name('marketing.tw');
```

**Issues:**

1. `/tw` renders the same English `home.blade.php` as marketing home — not Taiwan-specific staging content.
2. `config/domains.php` defines `'locale_prefixes' => ['tw' => 'zh-TW']` but no middleware or route action applies this locale to the `/tw` view.
3. No `lang/zh-TW/` translation files exist.
4. Spec notes client will supply zh-TW copy — placeholder English is interim-acceptable only if acknowledged; currently there is no separate TW template or `lang` attribute override.

**Template:** Tailwind marketing layout (`resources/views/layouts/marketing.blade.php`) — meets "standard template" requirement.

---

### K. Deferred Features — Billing Scaffold — [PARTIAL]

Admin can manage billing plans via Filament (`BillingPlanResource`). This is **UI/database scaffolding**, not backend billing logic. No checkout, webhook, or subscription enforcement exists. Aligns with M1 note that billing is "mockup/deferred" — document as intentional scaffold, not production billing.

---

## Pending Items

### G. Live Preview Container — [PENDING]

No 150×100 mm fixed-aspect-ratio canvas component exists in:

- `resources/views/`
- `resources/js/app.js` (Alpine only — no preview logic)
- `resources/css/app.css` (Tailwind directives only)

Placeholder in upload detail view:

```66:68:resources/views/uploads/show.blade.php
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                PDF preview and download links will appear here after processing is implemented.
            </div>
```

---

### H. Safe Print Zone — [PENDING]

No dashed 5 mm inset guide overlay. No CSS using mm units relative to 150×100 canvas. Spec §4 Mode (II) marks preview engine as M1/M2 mandatory.

---

### I. Aspect Ratio Validation — [PENDING]

No JavaScript validation comparing uploaded PDF/image dimensions to 150×100 mm (1.5:1 ratio). No amber animation, error banner, or "Force Adjustment" toggle.

---

### J. Courier Address Auto-Shrink — [PENDING]

No delivery-label preview component. No 18 px → 14 px shrink logic. No Flexbox layout pushing Remarks below address block. No CSV header detection for courier imports.

---

## Risks & Missing Dependencies

| Risk | Impact | Mitigation |
|------|--------|------------|
| No deployment pipeline | Cannot verify M1 on production domains; manual deploy error-prone | Add deploy script + server hook before client sign-off |
| Admin root redirect | Weakens security obfuscation; contradicts spec and README | Return 404/403 on admin root; access only via bookmarked `/bosslogin` |
| `/boss` vs `/bosslogin` | Client/docs expect specific URL | Set `ADMIN_PATH_PREFIX=bosslogin` in production `.env` |
| Printing UI not started | M1 frontend deliverable incomplete; blocks M2 PDF engine UX integration | Prioritize logistics preview shell (G→J) after portal layouts |
| `country_code` scopes absent | Cross-region data leak risk when PH/VN activate | Implement Global Scopes before enabling additional regions |
| Domain routing tests skipped in CI | PHPUnit sets `DOMAIN_ROUTING_ENABLED=false`; domain tests never run in default pipeline | Add env-specific test job or dedicated routing test suite |
| zh-TW copy dependency | `/tw` cannot launch until client provides copy | Ship locale-ready template; wire strings when copy arrives |
| `PROJECT_ARCHITECTURE.md` stale | Still lists multi-domain routing as "Excluded / deferred" | Update internal docs separately (out of audit scope) |
| Redis/Queue/Supervisor not configured | Upload processing cannot run async when M2 jobs added | Infrastructure task in TODO.md |

---

## Recommended Next Implementation Order

### Phase 1 — Infrastructure & Spec Alignment (unblock staging)

1. Create VPS deploy script + document Git Deploy Key setup on client server.
2. Align admin path to `bosslogin` and change admin root to 404 (not redirect).
3. Deploy to staging VPS; verify DNS for `xycubic.com`, `tw.xycubic.com`, `manage-xy.xycubic.com`.
4. Add CI job running tests with `DOMAIN_ROUTING_ENABLED=true`.

### Phase 2 — M1 Frontend Completion

5. Create dedicated `resources/views/marketing/tw.blade.php` with zh-TW placeholder structure and `lang="zh-TW"`.
6. Build printing-module page shells:
   - `resources/views/printing/order-details.blade.php` — master-detail grid
   - `resources/views/printing/logistics-labels.blade.php` — center preview workspace
   - `resources/views/printing/picking-list.blade.php` — dropzone + verification table
   - `resources/views/printing/delivery-labels.blade.php` — Flexbox address layout
7. Wire routes on merchant domain (auth-protected).

### Phase 3 — Logistics Preview Engine (G → J)

8. **G:** CSS aspect-ratio box (`aspect-ratio: 3/2` mapping to 150×100 mm visually) + Blade component.
9. **H:** Absolute-positioned dashed inset (~3.33% for 5 mm on 150 mm) overlay.
10. **I:** Alpine/JS dimension check on file select; amber `@keyframes` border flash; banner + force toggle.
11. **J:** Courier preview partial with `font-size` clamp 14–18 px based on character count; flex column with remarks below.

### Phase 4 — Data Isolation & M2 Prep

12. Add `country_code` Global Scopes to tenant-bound models (`UploadJob`, `Merchant`, etc.).
13. Redis queue + Supervisor config on VPS.
14. Begin M2 PDF normalization service behind logistics upload flow.

---

## Detailed Requirement Matrix

### A. Laravel 12 Migration

| Requirement | Status | Files | Evidence | Missing |
|-------------|--------|-------|----------|---------|
| Laravel 12 runtime | DONE | `composer.json`, `composer.lock` | v12.60.2 locked and verified | — |
| Upgrade work | DONE | — | Modern Laravel 12 bootstrap, Filament 5 | Operational queue/redis setup |

---

### B. VPS Deployment Pipeline

| Requirement | Status | Files | Evidence | Missing |
|-------------|--------|-------|----------|---------|
| Deploy configuration | PENDING | — | None in repo | Scripts, hooks, CI |
| Git Deploy Keys | PENDING | — | Server-side; not in repo | Key setup documentation |
| Automated deploy | PENDING | — | Manual deploy implied | post-receive automation |
| Deployment flow docs | PARTIAL | `README.md` L228–233 | DNS + `.env` bullets | Full flow diagram, rollback |

---

### C. Domain-Based Routing

| Requirement | Status | Files | Evidence | Missing |
|-------------|--------|-------|----------|---------|
| `Route::domain()` | DONE | `RouteServiceProvider.php` | Domain groups for all surfaces | — |
| `tw.xycubic.com` separation | DONE | `config/domains.php`, `routes/merchant.php` | TW merchant routes on domain | — |
| `manage-xy.xycubic.com` separation | DONE | `config/domains.php`, `AdminPanelProvider.php` | Filament on admin domain | — |
| `bosslogin` route | PARTIAL | `config/domains.php` L106 | Default prefix `boss` | Rename/default to `bosslogin` |
| bosslogin behavior | PARTIAL | `ObfuscateAdminAccess.php` | Non-prefix paths → 404 | Root should 404 not redirect |

---

### D. Security Obfuscation

| Requirement | Status | Files | Evidence | Missing |
|-------------|--------|-------|----------|---------|
| 404 unmapped paths | DONE | `RejectUnmappedDomain.php`, `routes/admin.php` | abort(404) | — |
| 403 inactive regions | DONE | `EnsureRegionIsActive.php` | Forbidden + message | — |
| Unauthorized portal access | DONE | `EnsureAdminAccess.php`, `EnsureMerchantAccess.php` | Tests pass | — |
| Admin root obfuscation | PARTIAL | `ObfuscateAdminAccess.php` | Redirects to `/boss` | Should 404/403 |

---

### E. Frontend Portal Layouts

| Requirement | Status | Files | Evidence | Missing |
|-------------|--------|-------|----------|---------|
| TW portal UI | DONE | `resources/views/*`, `routes/merchant.php` | Auth + uploads | Printing modules |
| Management portal UI | DONE | `app/Filament/*` | Full Filament panel | — |
| Responsive design | DONE | Tailwind breakpoints throughout | sm/md/lg grids | — |
| Master-detail views | PENDING | — | — | All printing modules |
| Card-based views | PARTIAL | `dashboard.blade.php`, `home.blade.php` | Card grids exist | Module workspaces |

---

### F. Static Staging Page

| Requirement | Status | Files | Evidence | Missing |
|-------------|--------|-------|----------|---------|
| `xycubic.com/tw` exists | DONE | `routes/marketing.php` | Named route `marketing.tw` | — |
| zh-TW content | PENDING | — | English only | Client copy + i18n |
| Standard template | DONE | `layouts/marketing.blade.php` | Tailwind template | TW-specific content |

---

### G–J. Preview Engine Components

| Requirement | Status | Files | Evidence | Missing |
|-------------|--------|-------|----------|---------|
| G: 150×100 mm canvas | PENDING | — | Placeholder text | Full component |
| H: 5 mm dashed guides | PENDING | — | — | CSS overlay |
| I: Aspect ratio + amber warn | PENDING | — | — | JS validation UI |
| J: Courier auto-shrink | PENDING | — | — | Flexbox + font scaling |

---

### K. Deferred Features

| Requirement | Status | Files | Evidence | Missing (intentionally) |
|-------------|--------|-------|----------|-------------------------|
| No payment billing logic | DONE | — | No gateway code | — |
| API gating mocked/deferred | DONE | — | No Shopee API | — |
| Billing admin mockup | PARTIAL | `BillingPlanResource.php` | CRUD scaffold only | Payment integration (M3) |

---

## Architecture Summary

### Domain Routing Structure

```
                    ┌─────────────────────────────────────┐
                    │         Single Laravel 12 App        │
                    └─────────────────────────────────────┘
                                      │
          ┌───────────────────────────┼───────────────────────────┐
          ▼                           ▼                           ▼
   xycubic.com              tw.xycubic.com           manage-xy.xycubic.com
   (Marketing)              (Merchant TW)              (Admin)
          │                           │                           │
   routes/marketing.php       routes/merchant.php         routes/admin.php
   /, /tw                     /login, /dashboard          /health
                              /uploads/*                  /boss/* (Filament)
          │                           │                           │
   Middleware:                 Middleware:                 Middleware:
   domain.surface:marketing     domain.surface:merchant     domain.surface:admin
   domain.reject-unmapped      region.active               admin.obfuscate
                               access.merchant             (+ Filament stack)
```

**Request lifecycle:**

1. `ResolveRegion` — resolve host → `DomainContext`; set locale for merchant regions.
2. `ConfigureDomainSession` — per-surface session cookie name.
3. `EnsureExpectedSurface` — reject cross-surface route registration mismatches (404).
4. Surface-specific gates — `region.active`, `access.merchant`, or `admin.obfuscate`.
5. `RejectUnmappedDomain` — 404 for unknown hosts.

**Filament admin** registers independently via `AdminPanelProvider` with matching domain and path (`boss` by default).

---

### VPS Deployment Structure (Current vs Expected)

**Current (in repository):**

```
Developer machine → git push → Remote repository (GitHub/etc.)
                                      │
                              (no automated hook in repo)
                                      │
                              Manual SSH + git pull (assumed)
```

**Expected per M1 spec (not yet in repo):**

```
Developer → git push → Bare repo on VPS (Git Deploy Key read-only)
                              │
                    post-receive hook
                              │
         ┌────────────────────┼────────────────────┐
         ▼                    ▼                    ▼
   git checkout          composer install      npm run build
   migrate --force       config:cache          queue:restart
```

**Files involved today:** `README.md` (manual DNS/env checklist only).  
**Match to spec:** Does not meet automated pipeline requirement.

---

### Portal Separation Strategy

| Layer | Mechanism | Purpose |
|-------|-----------|---------|
| DNS / Host | `Route::domain()` + `DomainResolver` | Route registration per hostname |
| Session | Separate cookie names per surface (`config/domains.php`) | Prevent session leakage across portals |
| Role | `users.role` enum (`admin`, `merchant`) | Primary identity |
| Middleware | `EnsureAdminAccess`, `EnsureMerchantAccess` | Hard surface boundary with forced logout |
| Filament | `User::canAccessPanel()` + Spatie `access_admin_panel` | Admin panel authorization |
| Spatie | `config/permissions.php` groups | In-panel action granularity |
| Region | `EnsureRegionIsActive` | Block inactive country portals (403) |

Merchants register on `tw.xycubic.com` only. Admins use Filament on `manage-xy.xycubic.com/boss`. Cross-login is rejected by tests.

---

### Security Strategy

| Threat | Control | Status |
|--------|---------|--------|
| Admin URL discovery | Obfuscated path; non-`/boss` → 404 | Partial (root redirects) |
| Cross-portal auth | Surface middleware + role enum | Implemented |
| Unknown/vanity hosts | `RejectUnmappedDomain` → 404 | Implemented |
| Inactive region access | 403 whitelist | Implemented |
| Cross-region data access | Global Scopes on `country_code` | **Not implemented** |
| Session fixation across subdomains | Per-surface cookie names | Implemented |
| Audit trail | `AuditLogService`, Filament Audit Logs | Implemented |
| Temp file / PII retention | Cron shredding | Deferred (M2) |

---

## Test Evidence

```
php artisan test
Tests: 2 skipped, 36 passed (84 assertions)
```

Skipped tests (require `DOMAIN_ROUTING_ENABLED=true`):

- `DomainRoutingTest::test_admin_obfuscation_blocks_legacy_admin_path_when_routing_enabled`
- `DomainRoutingTest::test_inactive_merchant_region_returns_forbidden_when_routing_enabled`

---

## Appendix: Key File Index

| Area | Path |
|------|------|
| Domain config | `config/domains.php` |
| Route registration | `app/Providers/RouteServiceProvider.php` |
| Domain resolution | `app/Support/Domains/DomainResolver.php` |
| Admin obfuscation | `app/Http/Middleware/ObfuscateAdminAccess.php` |
| Filament panel | `app/Providers/Filament/AdminPanelProvider.php` |
| Marketing `/tw` | `routes/marketing.php` |
| Merchant portal | `routes/merchant.php` |
| RBAC | `app/Enums/Role.php`, `config/permissions.php`, `database/seeders/RoleSeeder.php` |
| Upload foundation | `app/Services/UploadService.php`, `app/Http/Controllers/UploadController.php` |
| Billing mockup | `app/Filament/Resources/BillingPlanResource.php` |
| Domain tests | `tests/Feature/DomainRoutingTest.php`, `tests/Feature/SurfaceAccessTest.php` |

---

*End of Milestone 1 Audit Report*
