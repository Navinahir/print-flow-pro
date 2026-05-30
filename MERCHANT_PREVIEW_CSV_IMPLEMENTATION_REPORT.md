# Merchant Preview & Delivery Label CSV — Implementation Report

**Date:** 2026-05-30  
**Scope:** Merchant domain only (`localhost:8001`)  
**Phase:** Live preview content injection + delivery label CSV workflow

---

## 1. Files modified

### Backend

| File | Change |
| --- | --- |
| `routes/merchant/printing.php` | Added `POST printing/preview`, `POST printing/delivery-labels/csv` |
| `app/Enums/UploadJobType.php` | Delivery label jobs accept CSV mime types |
| `app/Enums/PrintingPreviewType.php` | Preview type enum (new) |
| `app/DTOs/Merchant/Printing/PrintingListItemData.php` | Optional embedded `preview` payload |
| `app/Services/Merchant/Printing/PrintingModuleService.php` | `previewListItems()` helper |
| `app/Services/Merchant/Printing/OrderDetailsService.php` | Sample items with preview payloads |
| `app/Services/Merchant/Printing/LogisticsLabelsService.php` | Sample items with preview payloads |
| `app/Services/Merchant/Printing/PickingListService.php` | Sample items with preview payloads |
| `app/Services/Merchant/Printing/DeliveryLabels/DeliveryLabelsService.php` | DB-backed labels + sample fallback |
| `app/Services/Merchant/Printing/DeliveryLabels/DeliveryLabelPreviewService.php` | Tracking/carrier support |
| `app/Services/Merchant/Printing/DeliveryLabels/DeliveryLabelPreviewData.php` | Implements `PrintingPreviewPayload` |
| `app/Services/Merchant/Printing/DeliveryLabels/CourierCsvHeaderDetector.php` | Tracking/carrier column detection |
| `lang/en/merchant.php` | Preview + CSV translation keys |
| `lang/zh-TW/merchant.php` | Preview + CSV translation keys (Traditional Chinese) |

### Frontend

| File | Change |
| --- | --- |
| `resources/views/merchant/printing/components/preview-pane.blade.php` | Renders dynamic preview content |
| `resources/views/merchant/printing/components/previews/preview-content.blade.php` | Module-specific preview partials |
| `resources/views/merchant/printing/delivery-labels/components/*` | CSV upload UI, workspace config |
| `resources/css/merchant/preview/index.css` | Imports printing preview styles |
| `resources/css/merchant/printing/previews/index.css` | Order/logistics/picking preview CSS |
| `resources/js/merchant/preview/index.js` | Exports preview-fetch |
| `resources/js/merchant/preview/preview-fetch.js` | AJAX preview refresh |
| `resources/js/merchant/modules/printing.js` | Triggers preview refresh on select |
| `resources/js/merchant/printing/printing-workspace-shared.js` | `previewUrl`, `refreshSelectedPreview()` |
| `resources/js/merchant/printing/delivery-labels/workspace.js` | CSV upload merge flow |
| `resources/js/merchant/printing/delivery-labels/csv-upload.js` | CSV AJAX upload helper |

### Tests & docs

| File | Change |
| --- | --- |
| `tests/Unit/Services/Merchant/Printing/DeliveryLabels/CourierAddressTypographyServiceTest.php` | Tracking/carrier detection assertions |
| `README.md` | Preview architecture + CSV workflow |
| `TODO.md` | Marked preview/CSV items done |

---

## 2. New files created

| File | Purpose |
| --- | --- |
| `app/Contracts/Merchant/Preview/PrintingPreviewPayload.php` | Preview DTO contract |
| `app/DTOs/Merchant/Preview/OrderDetailsPreviewData.php` | Order details preview payload |
| `app/DTOs/Merchant/Preview/LogisticsLabelsPreviewData.php` | Logistics label preview payload |
| `app/DTOs/Merchant/Preview/PickingListPreviewData.php` | Picking list preview payload |
| `app/Services/Merchant/Preview/OrderDetailsPreviewService.php` | Builds order preview DTOs |
| `app/Services/Merchant/Preview/LogisticsLabelsPreviewService.php` | Builds logistics preview DTOs |
| `app/Services/Merchant/Preview/PickingListPreviewService.php` | Builds picking list preview DTOs |
| `app/Services/Merchant/Preview/PrintingPreviewResolver.php` | Resolves preview by module + item |
| `app/Http/Controllers/Merchant/Printing/PrintingPreviewController.php` | AJAX preview endpoint |
| `app/Http/Requests/Merchant/Printing/FetchPrintingPreviewRequest.php` | Preview request validation |
| `app/Services/Merchant/Printing/DeliveryLabels/CourierCsvReaderService.php` | CSV parse |
| `app/Services/Merchant/Printing/DeliveryLabels/DeliveryLabelCsvRowMapper.php` | Row → label field mapping |
| `app/Services/Merchant/Printing/DeliveryLabels/DeliveryLabelCsvImportService.php` | Import orchestration |
| `app/DTOs/Merchant/Printing/DeliveryLabels/DeliveryLabelCsvImportResult.php` | Import response DTO |
| `app/Http/Controllers/Merchant/Printing/DeliveryLabelCsvUploadController.php` | CSV upload endpoint |
| `app/Http/Requests/Merchant/Printing/StoreDeliveryLabelCsvRequest.php` | CSV file validation |
| `database/factories/DeliveryLabelFactory.php` | Test factory |
| `tests/Feature/PrintingPreviewContentTest.php` | Live preview feature tests |
| `tests/Feature/DeliveryLabelCsvImportTest.php` | CSV import feature tests |

---

## 3. Architecture decisions

### Preview pipeline

1. **Module services** attach optional `preview` arrays to list items via dedicated preview services (no Blade business logic).
2. **Initial page load** embeds preview HTML from server-rendered `preview-content.blade.php` using list item payloads.
3. **AJAX refresh** (`POST /printing/preview`) uses `PrintingPreviewResolver` → module preview service → JSON payload re-rendered client-side.
4. **`PrintingPreviewPayload` contract** keeps preview DTOs consistent across modules; delivery labels reuse `DeliveryLabelPreviewData`.

### CSV import pipeline

1. **Upload** → `StoreDeliveryLabelCsvRequest` (size/type from `MerchantConfig`).
2. **Parse** → `CourierCsvReaderService` (native `fgetcsv`).
3. **Detect** → `CourierCsvHeaderDetector` (normalized header matching).
4. **Map** → `DeliveryLabelCsvRowMapper` (recipient, address, remarks, tracking, carrier).
5. **Persist** → `UploadJob` + `DeliveryLabel` rows in a DB transaction.
6. **Respond** → JSON with list items including embedded preview payloads for immediate UI merge.

### Separation of concerns

- Controllers delegate to services only.
- Blade partials render structured DTO data; no hardcoded sample strings in views.
- JavaScript handles UX (loading, toast, SweetAlert2) via dedicated modules (`csv-upload.js`, `preview-fetch.js`).

---

## 4. Reused services & components

| Component | Reused for |
| --- | --- |
| `PreviewContainer`, `PreviewSafeZone`, `PreviewWrapper`, `PreviewToolbar` | All four printing modules |
| `AspectRatioValidationService` + AJAX endpoint | Dimension warnings unchanged |
| `CourierAddressTypographyService` | Delivery label preview + auto-shrink |
| `DeliveryLabelPreviewService` | Sample labels, DB labels, CSV imports |
| `PrintingModuleService` base | List items + preview delegation |
| `MerchantConfig` | Upload size limits for CSV |
| `AuditLogService` | CSV import audit trail |
| `printingWorkspace` Alpine module | Item selection, preview refresh |
| `registerMerchantPreview()` | Canvas scaling on resize |
| Existing toast/SweetAlert2/axios stack | Upload feedback |

---

## 5. Remaining technical debt

| Item | Notes |
| --- | --- |
| Sample data only (order/logistics/picking) | Real Shopee order/PDF integration deferred to M2 |
| Delivery labels sample fallback | Shows demo items when merchant has no DB records |
| Print action disabled | Preview toolbar print button awaits print phase |
| Upload show page preview | Legacy upload detail view not integrated |
| Preview settings in DB | Canvas dimensions still hardcoded (150×100 mm) |
| Locale switcher | Explicitly out of scope for this phase |
| CSV column aliases | Detector covers common headers; exotic formats may need extension |
| Unit tests for CSV reader/mapper | Covered by feature test; dedicated unit tests optional |

---

## 6. Future recommendations

1. **Wire real data sources** — Replace sample preview services with upload-job/order models when M2 PDF pipeline lands.
2. **Print execution** — Enable toolbar print action using browser print CSS scoped to preview canvas.
3. **CSV enhancements** — Column mapping UI for unrecognized headers; batch delete/re-import.
4. **Preview caching** — Cache rendered preview HTML server-side for large picking lists.
5. **DB-driven preview dimensions** — Move 150×100 mm settings into `domain_settings.settings` JSON.
6. **CI coverage** — Add domain-routing tests with `DOMAIN_ROUTING_ENABLED=true`.

---

## Validation summary

| Check | Result |
| --- | --- |
| `php artisan test` | 65 passed, 2 skipped |
| `npm run build` | Success |
| Preview in all 4 modules | Order details, logistics, picking list, delivery labels |
| Localization | en + zh-TW keys for preview and CSV |
| Multi-domain | No admin/marketing changes; merchant-only routes |
| CSV upload | Validates, imports, renders preview with auto-shrink |

---

## API endpoints (merchant printing)

| Method | Path | Route name |
| --- | --- | --- |
| POST | `/printing/preview` | `printing.preview.show` |
| POST | `/printing/delivery-labels/csv` | `printing.delivery_labels.csv.store` |
| POST | `/printing/aspect-ratio/validate` | `printing.aspect_ratio.validate` |
