# Milestone 2 Audit & Architecture Plan

**Project:** XY Cubic Shopee (Shopee Batch Print Integration SaaS)  
**Specification:** V2.4 — Laravel 12 Integrated  
**Audit date:** 2026-06-06  
**Phase:** Architecture analysis & PDF engine foundation planning (**no processing implementation yet**)  
**Laravel version:** 12.x · **Test baseline (M1):** 105 passed, 4 skipped

---

## Executive Summary

Milestone 1 delivered the Merchant portal UI shell, four printing module workspaces, the 150×100 mm preview engine (safe zone, aspect ratio validation, courier auto-shrink), upload intake, CSV delivery-label import, multi-domain routing, and tenant isolation. **No PDF processing, queue jobs, or secure shredding exist in application code today** — only Composer dependencies (`setasign/fpdi`, `codedge/laravel-fpdf`, `spatie/browsershot`, `maatwebsite/excel`) and database columns (`output_disk`, `output_path`, job status fields) are scaffolded.

Milestone 2 (per V2.4 §3) must deliver:

1. **Stateless PDF Normalization Engine** — FPDI merge, thermal alignment, module-specific pipelines  
2. **Secure Shredding Core** — immediate delete on download + 10-minute cron purge of `storage/app/temp/`  
3. **Four module backends** — Order Details, Logistics Labels, Delivery Labels, Picking List  
4. **Async processing** — Redis queue workers + Supervisor on VPS (infrastructure; no Admin/Marketing changes)

This document is the **implementation blueprint**. It maps V2.4 requirements to the existing Merchant codebase, proposes service-layer architecture, and defines phased delivery. **Do not begin PDF byte manipulation until Phase 2 of this plan.**

---

## V2.4 Specification Mapping (M2 Scope)

| V2.4 requirement | M1 status | M2 deliverable |
| --- | --- | --- |
| Stateless PDF local processor & normalizer | UI preview only | Server-side normalization services |
| FPDI merging + alignment algorithms | Dependency installed, unused | `PdfNormalizationEngine` + module processors |
| Order Details — HTML table → PDF stream | HTML preview + sample DTOs | Browsershot or FPDF pipeline; privacy-aware streaming |
| Logistics — thermal 10×15 cm only; reject A4 | Aspect ratio UI warns; no PDF parse | Page dimension detection; exclude/reject A4 |
| Logistics — 150×100 mm canvas + 5 mm safe zone | Preview engine done | FPDI scale/place onto normalized canvas |
| Delivery Labels — Flexbox auto-shrink | `CourierAddressTypographyService` + HTML preview | HTML→PDF via Browsershot using same typography rules |
| Picking List — Excel parse + `groupBy()->sum()` | Spreadsheet stored in metadata; no parse | Maatwebsite Excel + aggregation service |
| 10-minute temp purge (Linux cron) | Not implemented | `storage:shred-temp` scheduled command |
| Immediate unlink on download | Not implemented | Download action + shredding hook |
| Shopee Sandbox E2E lifecycle | Deferred to M3 | M2 must produce auditable upload→process→output→delete flow |

**Out of scope for M2 (V2.4 M3+):** payment gateway, subscription gating, Shopee API, dual-track ticketing, `security_audit_logs` expansion beyond existing `AuditLogService`.

**Surfaces frozen for M2:** Admin domain (Filament), Marketing domain — no modifications unless required for shared config keys already in `domain_settings`.

---

## M1 Baseline — What Exists Today

### Merchant printing modules (reuse as-is for UI)

| Module | Route | Service | Upload type | List data source |
| --- | --- | --- | --- | --- |
| Order Details | `/printing/order-details` | `OrderDetailsService` | `order_pdf` | `UploadJobListMapper` → sample fallback |
| Logistics Labels | `/printing/logistics-labels` | `LogisticsLabelsService` | `thermal_label` | Same |
| Picking List | `/printing/picking-list` | `PickingListService` | `picking_list` | Same |
| Delivery Labels | `/printing/delivery-labels` | `DeliveryLabelsService` | `delivery_label` / CSV | `DeliveryLabel` rows + CSV import |

Shared stack: `PrintingModuleController` → `PrintingModuleService::buildWorkspace()` → master-detail Blade + Alpine `printingWorkspace` / `deliveryLabelsWorkspace`.

### Upload intake (hook point for M2 dispatch)

```
UploadController::store
  → UploadService::createJob()
      → UploadJob (status: pending)
      → PdfUpload on temp disk OR metadata.spreadsheet_files
      → AuditLog: upload.received
      → [M2] dispatch ProcessUploadJob
```

**Gap:** Jobs remain `pending` forever for PDF uploads. `started_at`, `completed_at`, `error_message` are never set by processing.

### Preview engine (M1 — keep; extend with real data in M2)

| Layer | Location |
| --- | --- |
| Config | `PreviewConfigurationService` ← `domain_settings.settings.preview` |
| Canvas | `PreviewContainer` (150×100 mm, safe zone) |
| Validation | `AspectRatioValidationService` (images only today) |
| Typography | `CourierAddressTypographyService` (delivery labels) |
| Resolver | `PrintingPreviewResolver`, `UploadPreviewService` |

**Gap:** Previews use **sample DTOs**, not parsed file content. M2 processors must feed real dimensions and payload into existing DTOs.

### Storage today

| Disk | Root | Usage |
| --- | --- | --- |
| `temp` | `storage/app/temp` | All uploads: `merchants/{merchant_id}/jobs/{job_id}/{uuid}.ext` |
| `local` | `storage/app/private` | Reserved for future promoted outputs |
| `public` | `storage/app/public` | Profile photos, public assets |

**Gap:** No TTL, no shredding, no download endpoints, `output_*` columns unused.

### Dependencies (installed, zero `app/` usage)

- `setasign/fpdi` — PDF import/merge  
- `codedge/laravel-fpdf` — vector drawing / thermal placement  
- `spatie/browsershot` — headless Chrome HTML→PDF  
- `maatwebsite/excel` — picking list spreadsheets  

---

## A. PDF Engine Architecture

### Design principles (V2.4 + codebase conventions)

1. **Stateless workers** — no session state in processors; all context from `UploadJob` + `PrintJob` + DTOs  
2. **Thin controllers** — orchestration in Actions; logic in Services; module specifics in Processors  
3. **Tenant isolation** — every query scoped by `merchant_id` + `country_code` (`BelongsToCountry`)  
4. **English codebase** — classes, logs, PR comments in English (V2.4 developer notes)  
5. **Barcode integrity** — logistics pipeline must not rasterize/resample barcodes (FPDI template import, no downscaling distortion)

### Proposed namespace layout

```
app/
├── Contracts/Merchant/Pdf/
│   ├── PdfProcessorInterface.php          # Module-specific processor contract
│   ├── PdfNormalizerInterface.php         # Canvas normalization contract
│   ├── PdfMergerInterface.php             # FPDI merge contract
│   └── PrintableOutputInterface.php         # Stream/path output contract
├── DTOs/Merchant/Pdf/
│   ├── PdfPageDimensions.php              # width/height mm, page index
│   ├── NormalizedPageResult.php           # output path or stream handle
│   ├── ThermalValidationResult.php          # pass/fail, rejection reason (A4)
│   ├── ProcessingContext.php              # job id, merchant, paths, options
│   └── ProcessingResult.php               # success, errors, print job ids
├── Enums/
│   └── PrintJobStatus.php                 # pending, ready, downloaded, shredded, failed
├── Actions/Merchant/Pdf/
│   ├── DispatchUploadProcessing.php       # pending → queue job
│   ├── MarkUploadProcessing.php           # status transition + started_at
│   ├── CompleteUploadProcessing.php       # completed_at + audit
│   └── FailUploadProcessing.php           # error_message + audit
├── Jobs/Merchant/
│   └── ProcessUploadJob.php               # ShouldQueue — routes to processor
├── Services/Merchant/Pdf/
│   ├── PdfNormalizationEngine.php         # Facade orchestrator
│   ├── PdfMergerService.php               # FPDI merge implementation
│   ├── ThermalPageValidator.php           # Reject A4 / wrong dimensions
│   ├── ThermalAlignmentService.php        # Scale/place onto 150×100 mm + 5 mm inset
│   ├── PdfDimensionReader.php             # Read page box from PDF (FPDI)
│   ├── HtmlToPdfRenderer.php              # Browsershot wrapper (order details, labels)
│   ├── MemoryGuard.php                    # Page-by-page limits, logging
│   └── Processors/
│       ├── OrderPdfProcessor.php
│       ├── LogisticsLabelsProcessor.php
│       ├── DeliveryLabelsProcessor.php
│       └── PickingListProcessor.php
└── Models/
    └── PrintJob.php                       # See §C
```

Register bindings in `AppServiceProvider` or dedicated `PdfServiceProvider`.

### FPDI integration strategy

| Use case | Library | Approach |
| --- | --- | --- |
| Import thermal PDF pages | `setasign/fpdi` (`Fpdi` trait) | Import page as template; no re-encoding of vector/barcode content |
| Merge multiple PDFs (order details batch) | FPDI | Loop pages → `AddPage` → `useTemplate` → `Output('S')` or temp file |
| Normalize to 150×100 mm canvas | FPDI + `codedge/laravel-fpdf` | Create target page at exact mm dimensions; place imported template with computed scale/offset respecting 5 mm safe zone |
| Reject A4 pages | `PdfDimensionReader` | Read MediaBox/CropBox; if width×height ≈ A4 (210×297 mm ± tolerance) → `ThermalValidationResult::rejected` |
| HTML layouts (order table, delivery label) | Browsershot | Render Blade preview partial to PDF at 150×100 mm; reuse M1 CSS typography rules |

**FPDI memory note:** Use `Fpdi` per page, `unset()` after each page, write incremental output to temp file rather than holding full merged PDF in memory for large batches.

**Alignment algorithm (logistics):**

1. Read source page dimensions (mm)  
2. Validate thermal range (e.g. 90–110 mm width, 140–160 mm height for 10×15 cm family)  
3. Compute scale to fit **content area** = canvas minus 2× safe zone inset (140×90 mm drawable)  
4. Center content within safe zone  
5. Emit normalized single-page PDF per thermal unit  

### Processing pipeline (end-to-end)

```
┌─────────────┐     ┌──────────────────┐     ┌─────────────────────┐
│ UploadService│────▶│ DispatchUpload   │────▶│ ProcessUploadJob     │
│ createJob()  │     │ Processing       │     │ (queue, Redis)       │
└─────────────┘     └──────────────────┘     └──────────┬──────────┘
                                                        │
                        ┌───────────────────────────────┘
                        ▼
              ┌─────────────────────┐
              │ PdfNormalizationEngine│
              │  → resolve processor   │
              │    by UploadJobType    │
              └──────────┬────────────┘
                         │
     ┌───────────────────┼───────────────────┐
     ▼                   ▼                   ▼
 OrderDetails      LogisticsLabels      DeliveryLabels / PickingList
 Processor         Processor            Processor
     │                   │                   │
     └───────────────────┴───────────────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │ Create PrintJob rows │
              │ Write output to temp │
              │ Update UploadJob     │
              │ AuditLog events      │
              └─────────────────────┘
```

### Stateless processing flow

| Step | State stored | Stateless rule |
| --- | --- | --- |
| 1. Worker picks job | Read `upload_jobs` + files from disk | Worker holds no merchant session |
| 2. Process | Temp files only | DTOs passed between services |
| 3. Complete | DB: `PrintJob`, status columns | Worker exits; no in-memory cache |
| 4. Download | Signed URL or authorized stream | One-shot token optional (M2.1) |
| 5. Shred | Delete paths from disk | Idempotent delete |

**Order Details privacy nuance (V2.4):** Spec calls for “zero local disk storage” via streaming. **Recommended hybrid:** process in temp during merge, stream response to client, enqueue immediate shred — satisfies Shopee audit while enabling multi-order merge. Document in privacy notes for auditors.

### Memory usage strategy

| Technique | Implementation |
| --- | --- |
| Page-at-a-time FPDI | Never load full multi-hundred-page PDF into array |
| Stream download | `StreamedResponse` from `PrintJob` path; avoid `file_get_contents` on large files |
| Queue concurrency | Supervisor `numprocs=2` initially; tune per VPS RAM |
| MemoryGuard | Before processing: check `memory_get_usage()`; fail job with clear error if file size > configurable limit (`MerchantConfig` / `domain_settings`) |
| Browsershot timeout | Cap render time; kill Chrome child processes |
| PHP `memory_limit` | Document 512M minimum on workers for M2 |

### Error handling strategy

| Failure | UploadJob | PrintJob | User-facing |
| --- | --- | --- | --- |
| Invalid file type | `failed` | — | Validation message (existing FormRequest) |
| A4 thermal upload | `failed` or partial | `failed` per page | Module-specific error in list pane |
| FPDI parse error | `failed` | `failed` | Generic “Could not read PDF” + support id (job id) |
| OOM / timeout | `failed` | `failed` | Retry button (re-dispatch) |
| Partial success (multi-file) | `completed` with metadata warnings | mixed statuses | Show per-item status in list pane |

**Implementation pattern:**

- Domain exceptions: `PdfProcessingException`, `ThermalValidationException`, `SpreadsheetParseException`  
- `FailUploadProcessing` action sets `error_message`, `completed_at`, audit `upload.failed`  
- Queue: `$tries = 3`, `$backoff = [30, 120, 300]`, failed job table for ops  
- Never expose stack traces to merchant UI  

---

## B. Storage Architecture

### Disk roles (M2 target)

| Disk | Purpose | Lifecycle |
| --- | --- | --- |
| `temp` | Uploads, in-progress outputs, pre-download artifacts | Short TTL; cron + post-download shred |
| `local` | Optional long-lived merchant exports (if product requires) | M2 default: **avoid**; keep everything in `temp` until downloaded |
| `public` | Non-PII assets only | Unchanged |

**New config file (planned):** `config/pdf.php`

```php
// Planned keys — not implemented yet
'temp_disk' => 'temp',
'output_ttl_minutes' => 10,           // V2.4 cron interval
'download_grace_seconds' => 30,       // allow stream to finish before shred
'max_upload_bytes' => ...,            // mirror domain_settings
'shred_on_download' => true,
```

### Temporary file lifecycle

```
UPLOAD          PROCESSING           READY              DOWNLOAD           SHRED
  │                  │                  │                   │                 │
  ▼                  ▼                  ▼                   ▼                 ▼
temp/.../uuid.pdf  same path or      temp/.../out/     stream response   unlink()
                   temp/.../work/    {print_job}.pdf   + mark downloaded  + audit
```

**Path conventions (extend M1):**

```
storage/app/temp/
  merchants/{merchant_id}/
    jobs/{upload_job_id}/
      sources/{uuid}.pdf              # existing UploadService paths
      work/{print_job_id}/            # intermediate normalization
      outputs/{print_job_id}.pdf      # final printable artifact
```

### Processing lifecycle

| Phase | DB | Files |
| --- | --- | --- |
| Received | `UploadJob: pending` | sources on temp |
| Queued | `pending` (metadata `queued_at`) | unchanged |
| Processing | `processing`, `started_at` | work dirs created |
| Ready | `completed`, `completed_at` | outputs linked on `PrintJob` |
| Failed | `failed`, `error_message` | sources retained until cron (for debug) or immediate purge (production toggle) |

### Download lifecycle (new Merchant routes — planned)

```
GET /printing/print-jobs/{printJob}/download   (authorized, merchant only)
  → Verify PrintJob belongs to merchant + country_code
  → Stream file
  → Mark PrintJob downloaded_at
  → ShredPrintJobAction (immediate unlink per V2.4)
  → AuditLog: print.downloaded
```

Use `Storage::disk()->readStream()` + `response()->streamDownload()`.

### Secure deletion lifecycle

| Trigger | Action | Scope |
| --- | --- | --- |
| Successful download | Immediate `unlink` on output + work files | Single `PrintJob` |
| Upload job cancelled | Delete all related paths | Whole job directory |
| Cron every 10 min | Delete files older than TTL OR jobs in `shredded` state | Entire `temp` tree walk |
| Merchant account delete | Cascade via FK + directory purge | `merchants/{id}/` |

**Safety rules:**

- Never delete if `PrintJob.status === processing`  
- Log every deletion to `AuditLog` (event: `storage.shredded`)  
- Cron uses file mtime **and** DB `expires_at` on `PrintJob` for consistency  
- Dry-run mode: `--dry-run` flag for ops verification  

---

## C. Print Job Architecture

### Why `PrintJob` (new model)

`UploadJob` remains the **batch aggregate** (one merchant upload action). `PrintJob` represents each **printable output unit** — one normalized thermal page, one delivery label PDF, one merged order PDF, one aggregated picking list PDF.

This maps cleanly to:

- List pane items (already `PrintingListItemData` with `id`)  
- Per-item download  
- Per-item shred  
- Logistics multi-file uploads → many `PrintJob` rows  

### Proposed schema (migration in M2 Phase 1 — not created yet)

```sql
print_jobs
  id
  upload_job_id       FK → upload_jobs
  merchant_id         FK → merchants
  country_code        string(2)
  module              string(50)     -- mirrors PrintingModule enum
  status              string(30)     -- PrintJobStatus enum
  source_type         string(30)     -- pdf_upload | delivery_label | picking_list | generated
  source_id           nullable unsignedBigInteger  -- polymorphic optional
  output_disk         string(20)     default 'temp'
  output_path         nullable string
  page_count          unsignedSmallInteger default 1
  width_mm            decimal nullable
  height_mm           decimal nullable
  checksum            string(64) nullable
  error_message       text nullable
  downloaded_at       timestamp nullable
  shredded_at         timestamp nullable
  expires_at          timestamp nullable   -- created_at + 10 min default
  metadata            json nullable
  timestamps

  index (merchant_id, status)
  index (upload_job_id)
  index (expires_at)
```

**Relationship to existing models:**

| Existing | M2 role |
| --- | --- |
| `PdfUpload` | Source file for logistics/order PDFs; link from `PrintJob.source_*` |
| `DeliveryLabel` | Row data for HTML→PDF; `output_*` deprecated in favor of `PrintJob` OR synced mirror |
| `PickingList` | Aggregation result metadata; output path on `PrintJob` |

### Processing services (by responsibility)

| Service | Responsibility |
| --- | --- |
| `PdfNormalizationEngine` | Entry point; selects processor |
| `{Module}Processor` | Module business rules |
| `PrintJobFactory` | Creates `PrintJob` rows + paths |
| `PrintJobRepository` (optional) | Query helpers for list panes |
| `UploadJobListMapper` | **Extend** to read `PrintJob` instead of sample data |

### DTOs (extend existing preview DTOs)

| DTO | Purpose |
| --- | --- |
| `ProcessingContext` | Immutable input to all processors |
| `ProcessingResult` | Processor output summary |
| `PrintJobSummaryData` | List pane item enrichment (dimensions, status badge) |
| Existing `*PreviewData` | Fill from parsed/processed data, not samples |

### Actions (single-purpose)

| Action | Trigger |
| --- | --- |
| `DispatchUploadProcessing` | End of `UploadService::createJob` |
| `MarkUploadProcessing` | Start of `ProcessUploadJob` |
| `CompleteUploadProcessing` | All print jobs ready |
| `FailUploadProcessing` | Unrecoverable error |
| `CreatePrintJob` | Processor emits output |
| `ShredPrintJob` | Download complete or cron |
| `ShredExpiredTempFiles` | Scheduled command |

### Interfaces

```php
// Contracts/Merchant/Pdf/PdfProcessorInterface.php (planned)
public function supports(UploadJobType $type): bool;
public function process(ProcessingContext $context): ProcessingResult;
```

```php
// Contracts/Merchant/Pdf/PdfNormalizerInterface.php (planned)
public function normalizePage(string $sourcePath, PreviewConfiguration $config): NormalizedPageResult;
```

---

## D. Security Architecture

### Temporary file handling

- All PII-bearing files **only** on `temp` disk (private, outside `public/`)  
- No direct public URLs to upload paths  
- UUID filenames (already in `UploadService`)  
- `checksum` on `PdfUpload` / `PrintJob` for integrity verification  
- Worker processes run as unprivileged `www-data`; temp dir `750` permissions  

### Download handling

- Merchant auth + `access.merchant` + `EnsurePrintingModuleEnabled`  
- Policy: `PrintJobPolicy::download()` checks `merchant_id`, `country_code`, `status === ready`  
- Optional signed temporary URLs (M2.1 hardening)  
- `Content-Disposition: attachment` with sanitized filename  
- Rate limit: `throttle:downloads` on download route  

### Secure cleanup

- **Immediate:** post-download shred (V2.4 §5)  
- **Scheduled:** 10-minute cron purge (V2.4 §5)  
- **Defense in depth:** DB `expires_at` even if file mtime wrong  
- Failed jobs: configurable retention (0 min prod / 60 min dev for debugging)  

### Future audit requirements (M3 Shopee Sandbox)

| Auditor concern | M2 preparation |
| --- | --- |
| Data lifecycle proof | Audit events: `upload.received`, `upload.processed`, `print.ready`, `print.downloaded`, `storage.shredded` |
| No PII residue | Cron + download shred + tests asserting file absent after shred |
| Region isolation | Existing `country_code` scopes on `PrintJob` |
| End-to-end demo | Standardized test datasets in `tests/Fixtures/Pdf/` (Phase 4) |

Existing `AuditLogService` + `UploadJobObserver` — extend for `PrintJob` observer.

---

## E. Module Processing Architecture

### Shared integration pattern (all four modules)

1. Merchant uploads via `/uploads/create` OR module-specific CSV (delivery labels)  
2. `UploadService` or `DeliveryLabelCsvImportService` creates `UploadJob`  
3. **[M2]** `DispatchUploadProcessing` enqueues `ProcessUploadJob`  
4. Processor creates `PrintJob`(s) + updates statuses  
5. `UploadJobListMapper` / `PrintingPreviewResolver` read real data  
6. Merchant downloads → shred  

### 1. Order Details (`order_pdf`)

| Aspect | Plan |
| --- | --- |
| V2.4 logic | Shopee picking spreadsheet → A4 order PDF |
| **Implemented (2026-06)** | `OrderPdfProcessor` reads spreadsheet via `PickingListSpreadsheetReader`; builds orders per source file; renders 2 orders/page with mPDF |
| Input | CSV, XLS, XLSX (Shopee export format) |
| Output | Combined or separate A4 PDFs via `PrintJob` |
| Partial errors | `SpreadsheetBatchRowParser` skips bad files; `UploadStatus::CompletedWithErrors` |
| Preview | Upload detail uses `OrderDetailsPreviewService::buildFromPrintJob()` |
| Regenerate | Whole job or single print output; UI overlay on detail page |
| Privacy | Stream download; shred cron still planned |

### 2. Logistics Labels (`thermal_label`)

| Aspect | Plan |
| --- | --- |
| V2.4 logic | Thermal 10×15 cm only; reject A4; single-unit processing; FPDI normalize to 150×100 mm + 5 mm padding |
| M1 state | Sample label preview; aspect ratio UI |
| M2 processor | `LogisticsLabelsProcessor` |
| Input | `PdfUpload` files on temp disk |
| Validation | `ThermalPageValidator` — reject A4; warn/block per aspect UI rules |
| Normalize | `ThermalAlignmentService` + FPDI per page → one `PrintJob` per page |
| Barcode | Template import only; no rasterization |
| Preview | Pass real dimensions to `LogisticsLabelsPreviewData`; thumbnail optional Phase 3 |
| List pane | One list item per `PrintJob` linked to source page index |

### 3. Delivery Labels (`delivery_label`)

| Aspect | Plan |
| --- | --- |
| V2.4 logic | Flexbox auto-shrink; CSV import; 150×100 mm thermal layout |
| M1 state | **Most complete path** — CSV import → `DeliveryLabel` rows → HTML preview with `CourierAddressTypographyService` |
| M2 processor | `DeliveryLabelsProcessor` |
| Input paths | (A) CSV via existing `DeliveryLabelCsvImportService` — enqueue processing after import; (B) PDF upload — future |
| Render | Browsershot snapshot of delivery label Blade (reuse M1 layout + server typography vars) |
| Output | One `PrintJob` per `DeliveryLabel` row |
| Shrink | Apply `CourierAddressTypographyService` server-side before render (already implemented) |
| Preview | Already real for CSV — link preview to `PrintJob.status` |
| Fix | Add `delivery_labels` template to upload detail `preview-content.blade.php` (small M2 UI fix) |

### 4. Picking List (`picking_list`)

| Aspect | Plan |
| --- | --- |
| V2.4 logic | Laravel-Excel + regex variant splitting + aggregation |
| **Implemented (2026-06)** | `PickingListProcessor` via `PickingListSpreadsheetReader`; combined/separate output modes; A4 PDF via `PickingListPdfRenderer` |
| Input | CSV/XLS/XLSX from temp |
| Parse | `SpreadsheetBatchRowParser` with per-file partial errors |
| Output | Combined or per-file A4 picking sheet + `PickingList` rows |
| Preview | `PickingListPreviewService::buildFromPrintJob()` on upload detail |
| Regenerate | Whole job or single print output |
| Mobile UX | M1 LocalStorage checklist — unchanged |

### Module priority order (implementation)

1. **Logistics Labels** — Done  
2. **Order PDF** — Done  
3. **Picking List** — Done  
4. **Delivery Labels** — CSV path exists; upload processor + Browsershot HTML→PDF still planned  

---

## F. Linux Cron Strategy

V2.4 requires a **hardened cron job every 10 minutes** to purge `storage/app/temp/`.

### Scheduled commands (planned)

| Command | Schedule | Purpose |
| --- | --- | --- |
| `storage:shred-expired` | `*/10 * * * *` | Delete expired `PrintJob` outputs + orphaned files |
| `queue:health` (optional) | `*/5 * * * *` | Alert if queue depth > threshold |
| `upload:fail-stuck` (optional) | `*/15 * * * *` | Mark `processing` jobs stuck > N minutes as `failed` |

### Shredding process (`storage:shred-expired`)

```
1. Query print_jobs WHERE expires_at < now() AND shredded_at IS NULL
2. For each: unlink output_path + work dirs; set shredded_at
3. Walk temp/merchants/* — delete files with mtime > TTL and no active PrintJob reference
4. AuditLog aggregate: storage.shredded { count, bytes_freed }
5. Exit 0 (cron-friendly)
```

**Cron entry (VPS `/etc/cron.d/xycubic-shopee` — document in README, do not commit secrets):**

```cron
*/10 * * * * www-data cd /var/www/xycubic-shopee && php artisan storage:shred-expired --quiet >> /var/log/xycubic-shred.log 2>&1
```

### Cleanup process

- Job directory removed when all child `PrintJob` shredded AND `UploadJob` terminal state  
- `UploadJob` metadata flag `storage_cleaned_at` prevents re-walk  
- Failed upload retention: env `PDF_FAILED_RETENTION_MINUTES=0` (production)  

### Monitoring process

| Signal | Tool |
| --- | --- |
| Queue depth | Redis `LLEN` + Laravel Horizon (optional M2.1) or custom `queue:health` |
| Disk usage | Alert if `storage/app/temp` > 80% quota |
| Shred failures | Log channel `stack` + daily review |
| Failed jobs | `failed_jobs` table + Filament read-only (Admin — future, not M2 scope change) |
| Processing duration | `started_at` → `completed_at` metrics in audit properties |

### Supervisor (queue workers — VPS infra)

Document in README (already planned in `.env.example`):

```ini
[program:xycubic-worker]
command=php /var/www/xycubic-shopee/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
numprocs=2
autostart=true
autorestart=true
user=www-data
```

Enable when M2 Phase 2 ships: `QUEUE_CONNECTION=redis`.

---

## Reusable Architecture Opportunities (from M1)

| M1 asset | M2 reuse |
| --- | --- |
| `PreviewConfiguration` / 150×100 mm / 5 mm inset | Single source of truth for normalization target size |
| `AspectRatioValidationService` | Extend with PDF page dimensions via `PdfDimensionReader` |
| `CourierAddressTypographyService` | Same rules in Browsershot HTML render |
| `PrintingPreviewPayload` DTO contract | Processors populate DTOs for preview API |
| `UploadJobListMapper` | Swap sample fallback for `PrintJob` queries |
| `UploadJob` status enum + observer | Extend status machine + audit |
| `AuditLogService` | Full lifecycle logging |
| `BelongsToCountry` trait | Apply to `PrintJob` |
| Module feature flags | Gate processing endpoints same as UI |
| Delivery CSV pipeline | First non-mock data path — ideal M2 vertical slice |

---

## Implementation Phases (recommended)

| Phase | Scope | Deliverables |
| --- | --- | --- |
| **0 — Planning** | This document | ✅ MILESTONE_2_AUDIT.md, README, TODO |
| **1 — Foundation** | Schema + contracts + config + empty services | Migration `print_jobs`, enums, interfaces, `config/pdf.php`, bind in provider |
| **2 — Queue + status** | `ProcessUploadJob`, dispatch from `UploadService`, status transitions | Jobs move pending → processing → completed/failed |
| **3 — Logistics processor** | FPDI normalization vertical slice | Thermal validate + normalize + download + shred |
| **4 — Delivery labels PDF** | Browsershot + existing CSV | HTML→PDF per row |
| **5 — Picking list** | Excel aggregation + PDF output | groupBy sum |
| **6 — Order details** | Merge/render pipeline | Stream download |
| **7 — Cron + hardening** | `storage:shred-expired`, tests, VPS Supervisor | Shopee-ready lifecycle |
| **8 — Preview wiring** | Replace all sample previews | Real dimensions/content |

**Explicitly not in Phase 0–1:** FPDI byte manipulation, Browsershot calls, cron execution on production.

---

## Risk Register

| Risk | Mitigation |
| --- | --- |
| Browsershot missing Chrome on VPS | Document `BROWSERSHOT_*` env; fallback queue driver; install script in deploy |
| FPDI encrypted PDFs | Catch exception → clear merchant error |
| Large batch OOM | Page-at-a-time + MemoryGuard + queue concurrency limits |
| Temp disk fills | 10-min cron + monitoring |
| Sample preview confusion | Phase 8 removes samples when real jobs exist (keep fallback for empty state only) |
| Order PDF parsing complexity | Start merge-only; parsing as enhancement |

---

## Test Strategy (planned)

| Layer | Tests |
| --- | --- |
| Unit | `ThermalPageValidator`, `CourierAddressTypographyService` (exists), aggregation service |
| Feature | Upload → queue fake → status transitions |
| Integration | Fixture PDFs in `tests/Fixtures/Pdf/thermal_10x15.pdf`, `a4.pdf` |
| Shred | Assert `Storage::disk('temp')->exists()` false after download + command |
| Isolation | `CountryCodeScopeTest` pattern for `PrintJob` |

---

## Milestone 2 Completion Criteria (definition of done)

- [ ] All four modules produce downloadable normalized PDF output via `PrintJob`  
- [ ] Queue workers process uploads asynchronously on VPS  
- [ ] Download triggers immediate shred; cron purges temp every 10 minutes  
- [ ] Audit log covers full lifecycle for Shopee DPP review  
- [ ] No Admin/Marketing domain code changes required for core flow  
- [ ] Automated tests cover processors, shred, and tenant isolation  
- [ ] README + TODO reflect operational runbook  

**Current M2 progress: 0% implementation — 100% architecture planning (this document).**

---

*See `TODO.md` for phased checklist. See `README.md` § Milestone 2 for developer onboarding. M1 status: `MILESTONE_1_AUDIT.md`.*
