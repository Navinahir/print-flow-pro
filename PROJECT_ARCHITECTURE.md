# XY Cubic Shopee - Architecture

## Tech Stack

- Laravel 12, PHP 8.3, MySQL 8, Redis
- Filament 5 (admin at `/admin`)
- Breeze (merchant auth)
- Spatie Laravel Permission
- Blade, Tailwind CSS 3, Alpine.js, Vite
- FPDI, Laravel Excel, Browsershot (planned processing)

---

# Application Purpose

Shopee sellers upload thermal labels, order PDFs, and picking lists. The platform validates, merges, and prepares print-ready outputs locally (no Shopee API in Phase 1).

---

# Phase 1 Scope (Current Focus)

**Included:** local file upload foundation, admin panel, RBAC, audit logging, SaaS landing page, merchant auth UI, upload history.

**Excluded / deferred:** Shopee API, multi-domain routing, region middleware, full PDF processing pipeline.

---

# User Roles & Access (V2.4)

| Role | Merchant portal | Admin dashboard (`/boss`) |
| --- | --- | --- |
| `merchant` | Yes | No |
| `admin` | No | Yes (subset Spatie permissions) |

Surface access: `users.role` + `EnsureMerchantAccess` / `EnsureAdminAccess`.  
In-panel actions: Spatie permissions from `config/permissions.php` + `UploadJobPolicy` + Filament resources.

---

# Upload Workflow

1. Merchant registers → `merchant` role + `merchants` record created.
2. Merchant visits `/uploads/create`, selects type, drops files.
3. `StoreUploadRequest` validates type, MIME, and size.
4. `UploadService` creates `UploadJob` (`uploaded_by`, `type`, `status`) and stores files on `temp` disk.
5. PDFs → `pdf_uploads` table; spreadsheets → job `metadata`.
6. `UploadJobObserver` + `AuditLogService` record upload events.
7. Processing queue jobs *(planned)* will update status and outputs.

**Upload types:** `order_pdf`, `thermal_label`, `picking_list`, `delivery_label`

---

# Authentication Flow

- **Public:** `/` landing (marketing layout)
- **Guest:** `/login`, `/register`, password reset, email verification (Breeze)
- **Merchant:** `/dashboard`, `/uploads/*` (auth + verified)
- **Admin:** `/admin` (Filament login, requires `access_admin_panel`)

Default super admin: `admin@example.com` / `password` (seed locally only).

---

# Data Model

```
User ──┬── hasOne Merchant ──┬── hasMany UploadJob
       │                     ├── PdfUpload, PickingList, DeliveryLabel
       └── hasMany AuditLog

UploadJob: uploaded_by → User, type (enum), status (UploadStatus)
```

---

# Folder Structure

```
app/
├── Enums/           # Roles, permissions, upload types, statuses
├── Filament/        # Admin resources, widgets, FormFields helper
├── Http/
│   ├── Controllers/ # Thin (UploadController, Auth)
│   └── Requests/    # StoreUploadRequest, RegisterRequest, etc.
├── Policies/        # UploadJobPolicy
├── Services/        # UploadService, AuditLogService
├── Observers/       # UploadJobObserver
└── Listeners/       # LogAuthenticationActivity

resources/views/
├── home.blade.php   # SaaS landing
├── auth/            # Modernized Breeze forms
├── uploads/         # create, index, show
└── layouts/         # marketing, guest, app
```

---

# Admin Panel (Filament)

- Path: `config('printflow.admin.path')` → default `/admin`
- Groups: Overview, Merchants & Billing, Operations, System
- Resources: Merchants, Billing Plans, Upload Jobs, Audit Logs
- Forms use `App\Filament\Support\FormFields` for labels, placeholders, required fields

---

# Development Principles

- Service-based architecture, thin controllers
- Form requests for validation
- Strict typing, no overengineering
- Region/multi-domain deferred to a later phase
