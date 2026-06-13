# Milestone 2 — PDF Engine Foundation Implementation Report

**Date:** 2026-06-06  
**Scope:** Core PDF engine framework only (no module normalization logic)  
**Surface:** Merchant domain only  
**Reference:** `MILESTONE_2_AUDIT.md`, V2.4 §3 (M2)

---

## Summary

The PDF Engine **foundation and processing framework** is implemented. FPDI is integrated for page inspection (boundary detection). A six-stage pipeline validates inputs, detects PDF page geometry, prepares the 150×100 mm canvas spec, and **defers normalization** until module-specific processors are built in later M2 phases.

**Not implemented (by design):**

- Logistics label normalization  
- Order PDF merge  
- Picking list export  
- Delivery label HTML→PDF  
- Queue dispatch from `UploadService`  
- Download routes / shredding cron  

---

## Deliverables

### Configuration

| File | Purpose |
| --- | --- |
| `config/pdf.php` | Temp disk, TTL, validation thresholds, path templates, processing modes |
| `app/Support/pdf_fpdf_bootstrap.php` | Loads global `FPDF` class for FPDI |
| `composer.json` | Added `setasign/fpdf ^1.8.6` (PHP 8.3 compatible) |

### Contracts (`app/Contracts/Merchant/Pdf/`)

| Interface | Role |
| --- | --- |
| `PdfEngineInterface` | Pipeline entry point |
| `PdfNormalizationInterface` | Normalization contract (deferred impl) |
| `PdfCanvasInterface` | Canvas geometry |
| `PdfValidationInterface` | Source validation |
| `PdfBoundaryDetectionInterface` | FPDI page metrics |
| `PdfConfigurationInterface` | Merged engine config |
| `PdfTempStorageInterface` | Temp path lifecycle |
| `PdfPipelineStageInterface` | Pipeline stage contract |
| `PdfProcessorInterface` | Future module processors |

### Services (`app/Services/Merchant/Pdf/`)

| Service | Status |
| --- | --- |
| `PdfEngineService` | Runs full pipeline; returns `PdfProcessingResult` |
| `PdfNormalizationService` | Returns `PdfNormalizationResult::deferred()` |
| `PdfCanvasService` | Builds canvas from domain preview + `config/pdf.php` |
| `PdfValidationService` | Framework validation; A4 reject hook for thermal mode |
| `PdfBoundaryDetectionService` | FPDI page count + `PdfBoundaryBox` |
| `PdfConfigurationService` | Merges preview settings + PDF config |
| `PdfTempStorageService` | Job/work/output paths on `temp` disk |
| `Support/FpdiDocumentAdapter` | FPDI wrapper for inspection |

### Pipeline stages

1. `InitializeProcessingStage` — create work/output dirs  
2. `ValidateInputStage` — `PdfValidationService`  
3. `DetectBoundariesStage` — FPDI per PDF page  
4. `PrepareCanvasStage` — 150×100 mm + safe zone  
5. `DeferNormalizationStage` — records deferred normalization  
6. `FinalizeProcessingStage` — marks framework complete  

### Actions (`app/Actions/Merchant/Pdf/`)

| Action | Purpose |
| --- | --- |
| `PreparePdfProcessingContext` | Build context from `UploadJob` |
| `RunPdfProcessingPipeline` | Execute engine for a job |
| `HandlePdfProcessingFailure` | Audit log on framework failure |
| `ResolvePdfEngineConfiguration` | Resolve merged config DTO |

### DTOs, enums, exceptions

- **DTOs:** `PdfProcessingContext`, `PdfProcessingResult`, `PdfEngineConfiguration`, `PdfCanvasSpec`, `PdfBoundaryBox`, `PdfPageDimensions`, `PdfValidationResult`, `PdfNormalizationResult`, `PdfTempPath`  
- **Enums:** `PdfProcessingMode`, `PdfProcessingStatus`, `PdfValidationCode`  
- **Exceptions:** `PdfEngineException`, `PdfProcessingException`, `PdfValidationException`, `PdfNormalizationException`, `PdfBoundaryDetectionException`, `PdfStorageException`  

### Localization

User-facing strings: `lang/en/merchant.php` and `lang/zh-TW/merchant.php` under `merchant.pdf.*`

### Provider

`App\Providers\PdfServiceProvider` — binds all interfaces; registered in `bootstrap/providers.php`

---

## FPDI integration

- **Packages:** `setasign/fpdi ^2.6`, `setasign/fpdf ^1.8.6`  
- **Usage:** Read-only in foundation phase (`FpdiDocumentAdapter`, `PdfBoundaryDetectionService`)  
- **Future:** `PdfMergerService` / module processors will use `FpdiDocumentAdapter::instance()` for merge/normalize  

---

## How to use (developers)

```php
use App\Actions\Merchant\Pdf\RunPdfProcessingPipeline;
use App\Models\UploadJob;

$result = app(RunPdfProcessingPipeline::class)->execute($uploadJob);

if ($result->success) {
    // Framework complete; normalization intentionally deferred
    $boundaries = $result->context->detectedBoundaries;
    $canvas = $result->context->canvas;
}
```

**Not wired yet:** `UploadService` does not auto-dispatch — connect in Phase 2 (queue + status transitions).

---

## Tests

```
tests/Unit/Services/Merchant/Pdf/
tests/Fixtures/Pdf/thermal_sample.pdf
```

| Test | Covers |
| --- | --- |
| `PdfCanvasServiceTest` | Canvas + safe area |
| `PdfConfigurationServiceTest` | Config merge |
| `FpdiDocumentAdapterTest` | FPDI open + page size |
| `PdfTempStorageServiceTest` | Temp paths |
| `PdfEngineServiceTest` | Full pipeline success/failure |

Run: `php artisan test --filter=Pdf`

---

## Next steps (M2 Phase 2+)

1. `PrintJob` model + migration  
2. `ProcessUploadJob` queue + dispatch from `UploadService`  
3. `LogisticsLabelsProcessor` — first real FPDI normalization  
4. Download route + `ShredPrintJob` action  
5. `storage:shred-expired` scheduled command  

See `TODO.md` for phased checklist.

---

## Addendum — Module processors (2026-06)

Subsequent to the foundation report above, the following merchant upload processors are **implemented and wired**:

| Processor | Upload type | Input | Output |
| --- | --- | --- | --- |
| `LogisticsLabelsProcessor` | `thermal_label` | PDF | A4 sheets with normalized 100×150 mm label slots |
| `OrderPdfProcessor` | `order_pdf` | Spreadsheet | A4 order PDF (2 orders per page) |
| `PickingListProcessor` | `picking_list` | Spreadsheet | A4 picking sheet PDF |

**Shared spreadsheet layer:** `SpreadsheetBatchRowParser`, `SpreadsheetProcessingMetadataWriter`, `CompleteUploadProcessing` (partial success + per-file metadata).

**Merchant UI:** Type-specific upload detail pages (`UploadShowViewService`), per-file status, spreadsheet preview modal, regenerate with full-page overlay, combined/separate output modes.

**Tests:** `OrderPdfProcessingTest`, `PickingListProcessingTest`, `LogisticsLabelsProcessingTest`, `UploadShowViewServiceTest`.

**Still planned:** `DeliveryLabelsProcessor`, temp shred cron, printing workspace wiring to live upload data.

---

*Architecture plan: `MILESTONE_2_AUDIT.md`*
