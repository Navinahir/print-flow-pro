# Project TODO — Shopee Batch Print Integration SaaS V2.4

Audit date: 2026-06-06  
Reference: V2.4 specification · Milestone tracking

Status legend: `[DONE]` · `[PARTIAL]` · `[PLANNED]` · `[PENDING]` · `[M2]` · `[M3+]`

---

## Milestone summary

| Milestone | Scope | Status |
| --- | --- | --- |
| **M1** | Portals & UI/UX | **Complete (~98%)** — see `MILESTONE_1_AUDIT.md` |
| **M2** | PDF Normalization Engine & Secure Shredding | **Foundation done** — see `MILESTONE_2_IMPLEMENTATION_REPORT.md` · module processors pending |
| **M3+** | Billing, Shopee Sandbox, payments | Deferred |

---

## M1 — remaining (non-blocking)

| Item | Status | Notes |
| --- | --- | --- |
| Client subscription / action links on `/tw` | [PENDING] | Awaiting client copy |
| Marketing contact form / live chat | [PENDING] | Future |

---

## M2 — Phase 0: Architecture & planning

| Item | Status | Notes |
| --- | --- | --- |
| Review V2.4 specification (M2 scope) | [DONE] | Mapped in `MILESTONE_2_AUDIT.md` |
| Review Merchant domain + printing modules | [DONE] | Four modules, upload intake, preview engine |
| PDF engine architecture document | [DONE] | `MILESTONE_2_AUDIT.md` §A |
| Storage + shredding architecture | [DONE] | `MILESTONE_2_AUDIT.md` §B |
| PrintJob model + service design | [DONE] | `MILESTONE_2_AUDIT.md` §C |
| Security architecture | [DONE] | `MILESTONE_2_AUDIT.md` §D |
| Per-module processing strategy | [DONE] | `MILESTONE_2_AUDIT.md` §E |
| Linux cron + monitoring design | [DONE] | `MILESTONE_2_AUDIT.md` §F |
| README M2 section | [DONE] | Developer onboarding + infra env keys |

---

## M2 — Phase 1: Foundation (framework — no module normalization)

| Item | Status | Notes |
| --- | --- | --- |
| `config/pdf.php` (temp disk, TTL, limits) | [DONE] | `config/pdf.php` |
| `PdfProcessingMode`, `PdfProcessingStatus`, `PdfValidationCode` enums | [DONE] | |
| Contracts under `app/Contracts/Merchant/Pdf/` | [DONE] | 9 interfaces |
| DTOs under `app/DTOs/Merchant/Pdf/` | [DONE] | Context, result, canvas, boundary, etc. |
| `PdfEngineService` + pipeline stages | [DONE] | Logistics normalization wired |
| `PdfNormalizationService` (stub) | [DONE] | Returns deferred result |
| `PdfCanvasService`, `PdfValidationService`, `PdfBoundaryDetectionService` | [DONE] | FPDI boundary read |
| `PdfConfigurationService`, `PdfTempStorageService` | [DONE] | |
| `FpdiDocumentAdapter` + `setasign/fpdf ^1.8.6` | [DONE] | Page inspection |
| Actions: `PreparePdfProcessingContext`, `RunPdfProcessingPipeline`, etc. | [DONE] | |
| Exception hierarchy `app/Exceptions/Merchant/Pdf/` | [DONE] | |
| `PdfServiceProvider` + localization (`merchant.pdf.*`) | [DONE] | en + zh-TW |
| Unit tests (`tests/Unit/Services/Merchant/Pdf/`) | [DONE] | 7 tests |
| `PrintJob` model + migration | [DONE] | `print_jobs` table |
| `PrintJobPolicy` | [DONE] | view + download |

---

## M2 — Phase 2: Queue & job lifecycle

| Item | Status | Notes |
| --- | --- | --- |
| `ProcessUploadJob` (ShouldQueue) | [DONE] | Thermal labels; sync in tests |
| Actions: `DispatchUploadProcessing`, `MarkUploadProcessing`, `CompleteUploadProcessing`, `FailUploadProcessing` | [DONE] | Status + audit transitions |
| Dispatch from `UploadService::createJob()` | [DONE] | `UploadJobType::ThermalLabel` only |
| Dispatch from `DeliveryLabelCsvImportService` (post-import) | [PLANNED] | CSV → PDF pipeline |
| `UploadJob` status machine tests | [PLANNED] | pending → processing → completed/failed |
| VPS: `QUEUE_CONNECTION=redis` + Supervisor config | [PLANNED] | Document in deploy; uncomment `.env` |
| Stuck-job sweeper (`upload:fail-stuck`) | [PLANNED] | Optional scheduled command |

---

## M2 — Phase 3: Logistics Labels processor (FPDI vertical slice)

| Item | Status | Notes |
| --- | --- | --- |
| `ThermalPageValidator` — reject A4 | [DONE] | `ThermalPdfValidationService` |
| `ThermalAlignmentService` — 150×100 mm + 5 mm inset | [DONE] | `ThermalPdfNormalizationService` (FPDI) |
| `LogisticsLabelsProcessor` | [DONE] | One `PrintJob` per thermal page |
| Download route + shred on download | [PARTIAL] | Download route done; delayed shred deferred |
| Replace sample preview in `UploadPreviewService` | [DONE] | Uses ready `PrintJob` when present |
| Feature tests with fixture PDFs | [DONE] | `tests/Support/PdfFixtureFactory.php` |

---

## M2 — Phase 4: Delivery Labels PDF

| Item | Status | Notes |
| --- | --- | --- |
| `HtmlToPdfRenderer` (Browsershot wrapper) | [PLANNED] | Chrome path via env |
| `DeliveryLabelsProcessor` | [PLANNED] | One PDF per `DeliveryLabel` row |
| Reuse `CourierAddressTypographyService` in Blade render | [PLANNED] | Match M1 preview |
| Fix upload detail preview for `delivery_labels` type | [PLANNED] | `preview-content.blade.php` template gap |
| Browsershot VPS install in `scripts/deploy.sh` notes | [PLANNED] | Node + Chrome dependencies |

---

## M2 — Phase 5: Picking List processor

| Item | Status | Notes |
| --- | --- | --- |
| `PickingListSpreadsheetReader` | [DONE] | Parse CSV/XLS/XLSX from temp |
| `PickingListProcessor` + PDF output | [DONE] | Combined/separate modes; A4 picking sheet |
| `SpreadsheetBatchRowParser` + per-file status | [DONE] | Partial batch success on upload detail |
| `PickingList` model rows on import | [DONE] | Per source file in combined mode |
| Wire `PickingListPreviewService` to upload print jobs | [DONE] | Upload detail uses real `PrintJob` preview |
| Wire printing workspace to upload outputs | [PLANNED] | Workspace still uses samples |

---

## M2 — Phase 6: Order PDF processor

| Item | Status | Notes |
| --- | --- | --- |
| `OrderPdfProcessor` | [DONE] | Shopee spreadsheet → A4 order PDF (2 orders/page) |
| `OrderPdfDocumentBuilder` + `OrderPdfRenderer` | [DONE] | Per-file order grouping; mPDF `WriteFixedPosHTML` layout |
| `SpreadsheetBatchRowParser` partial errors | [DONE] | `completed_with_errors` + per-file status |
| Regenerate (job + print output) | [DONE] | `RegenerateUploadProcessing`, detail overlay |
| Streamed download (privacy) | [PLANNED] | V2.4 stateless stream preference |
| Wire printing workspace to upload outputs | [PLANNED] | Workspace still uses samples |

---

## M2 — Phase 7: Secure shredding & ops

| Item | Status | Notes |
| --- | --- | --- |
| `storage:shred-expired` Artisan command | [PLANNED] | Purge temp every 10 min (V2.4) |
| Schedule in `routes/console.php` | [PLANNED] | `*/10 * * * *` |
| Cron entry documentation (VPS) | [PLANNED] | README + audit §F |
| `ShredExpiredTempFiles` action | [PLANNED] | DB + filesystem walk |
| Audit events: `print.downloaded`, `storage.shredded` | [PLANNED] | Extend `AuditLogService` |
| `PrintJobObserver` | [PLANNED] | Status change logging |
| Shred integration tests | [PLANNED] | Assert file absent post-download/cron |
| Disk usage monitoring notes | [PLANNED] | Ops runbook in audit |

---

## M2 — Phase 8: Preview & list pane wiring

| Item | Status | Notes |
| --- | --- | --- |
| `UploadJobListMapper` reads `PrintJob` rows | [DONE] | `PrintJobListMapper` for logistics labels |
| All four modules show processing status badges | [PLANNED] | pending / ready / failed |
| Remove sample-only code paths when jobs exist | [PLANNED] | `*PreviewService::buildSamplePreview` fallback only |

---

## M3+ — deferred (V2.4)

| Item | Status |
| --- | --- |
| Taiwan payment gateway (driver architecture) | [M3+] |
| Subscription checkout + webhook billing | [M3+] |
| Dual-track ticketing + Redis debounce | [M3+] |
| Shopee Sandbox + ISV/DPP audit datasets | [M3+] |
| `security_audit_logs` expansion | [M3+] |
| Shopee API integration | [M3+] |

---

## Test evidence (M1 baseline)

```
php artisan test
Tests: 4 skipped, 122 passed (338 assertions)
```

M2 logistics labels processor tests: `ThermalPdfValidationServiceTest`, `LogisticsLabelsProcessingTest`.

---

## Recommended implementation order

1. Phase 1 — Foundation (schema, contracts, config)  
2. Phase 2 — Queue lifecycle  
3. Phase 3 — Logistics Labels (FPDI core)  
4. Phase 4 — Delivery Labels (Browsershot)  
5. Phase 5 — Picking List (Excel)  
6. Phase 6 — Order Details  
7. Phase 7 — Cron shredding + VPS workers  
8. Phase 8 — Preview wiring cleanup  

**Do not skip Phase 0 review.** Full detail: [`MILESTONE_2_AUDIT.md`](MILESTONE_2_AUDIT.md).
