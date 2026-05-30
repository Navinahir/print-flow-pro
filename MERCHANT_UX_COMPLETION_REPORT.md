# Merchant UX Completion — Implementation Report

**Date:** 2026-05-30  
**Scope:** Merchant domain only (`localhost:8001`)

---

## 1. Files modified

| Area | Files |
| --- | --- |
| Upload preview | `UploadController.php`, `uploads/show.blade.php`, `routes/merchant.php` |
| Preview config | `config/domains.php`, `DomainSettingSeeder.php`, `MerchantDomainConfig.php`, `DomainConfigurationService.php`, `PreviewContainer.php`, `AspectRatioValidationService.php`, `scale.js` |
| Print | `preview-pane.blade.php`, `delivery-labels/.../preview-pane.blade.php`, `toolbar.blade.php`, `printing-workspace-shared.js` |
| Locale | `bootstrap/app.php`, `lang/en/merchant.php`, `lang/zh-TW/merchant.php` |
| Theme / navbar | `layouts/app.blade.php`, `partials/header.blade.php`, `partials/mobile-nav.blade.php`, `partials/sidebar.blade.php`, `merchant.css`, `tailwind.config.js`, `merchant.js` |
| Docs | `README.md`, `TODO.md` |

---

## 2. New files created

| File | Purpose |
| --- | --- |
| `UploadPreviewService.php` | Maps upload jobs → existing preview DTOs |
| `UploadPreviewController.php` | AJAX preview refresh for upload detail |
| `PreviewConfigurationService.php` + `PreviewConfiguration.php` | DB-driven preview dimensions |
| `LocaleService.php`, `SetMerchantLocale.php`, `LocaleController.php` | Locale switching |
| `ThemeService.php`, `ThemeHelper.php`, `ThemeController.php` | Theme persistence |
| `LocaleSwitcher.php`, `ThemeSwitch.php`, `UserMenu.php`, `PrintButton.php` | Blade components |
| `uploads/partials/preview-section.blade.php` | Upload detail preview pane |
| `partials/nav-controls.blade.php` | Navbar right-side controls |
| `resources/js/merchant/theme.js` | Theme manager |
| `resources/js/merchant/modules/upload-preview.js` | Upload preview Alpine module |
| `resources/js/merchant/preview/print.js` | Browser print service |
| `resources/css/merchant/preview/print.css` | Print-only stylesheet |
| `resources/css/merchant/nav.css` | Navbar dropdown styles |
| `database/factories/UploadJobFactory.php` | Test factory |
| `tests/Feature/UploadPreviewIntegrationTest.php` | Upload preview tests |
| `tests/Feature/MerchantLocaleSwitchTest.php` | Locale switcher tests |
| `tests/Feature/PreviewConfigurationTest.php` | DB preview config tests |

---

## 3. Architecture decisions

### Upload preview

Reuses `UploadPreviewService` → existing module preview services (order, logistics, picking, delivery). No duplicate Blade preview logic; shares `preview-content.blade.php` and `uploadPreview` Alpine state mirroring `selectedPreview()`.

### Print

`printPreview()` adds `merchant-print-active` to `body`; `@media print` CSS isolates `[data-print-area]`. Safe zone and toolbar hidden during print.

### Preview configuration

Settings live in `domain_settings.settings.preview` JSON (no new migration). `PreviewConfigurationService` is the single read path; components and validation consume it.

### Locale

Session key `merchant_locale`; middleware runs **after** `StartSession` (appended to web group). Supported locales validated against `domain_locales` for the current region.

### Theme

Cookie `merchant_theme` + `localStorage` for client persistence; Tailwind `class` strategy on `<html>`. System preference via `prefers-color-scheme`. Print styles ignore theme (white canvas).

### Navbar

Order: Locale → Theme → User menu (desktop header + mobile menu). Logout moved into user menu dropdown.

---

## 4. Reused services & components

- Preview engine: `PreviewWrapper`, `PreviewContainer`, `PreviewToolbar`, `PreviewSafeZone`
- Preview DTOs and module preview services
- `preview-content.blade.php`, `registerMerchantPreview()`, `scale.js`
- `MerchantConfig`, `DomainConfigurationService`, `domain_locales`
- Existing toast/SweetAlert2/AJAX stack

---

## 5. Database changes

**No new migrations.** Extended `domain_settings.settings` JSON:

```json
"preview": {
  "width_mm": 150,
  "height_mm": 100,
  "aspect_ratio": 1.5,
  "safe_zone_inset_mm": 5,
  "default_zoom": 1.0,
  "scaling_behavior": "fit"
}
```

Re-seed or run `DomainSettingSeeder` to merge into existing regions.

---

## 6. Localization changes

New keys under `merchant.locale.*`, `merchant.theme.*`, `merchant.user_menu.*`, `merchant.uploads.preview.*` (en + zh-TW). Updated print toolbar hints and flash messages.

---

## 7. Theme system changes

- `tailwind.config.js`: `darkMode: 'class'`
- Component classes updated with `dark:` variants in `merchant.css` and `nav.css`
- Preview wrapper body uses dark background
- Theme toggle: Light / Dark / System with AJAX cookie sync

---

## 8. Remaining technical debt

| Item | Notes |
| --- | --- |
| Upload preview uses sample data | Real PDF/CSV processing deferred to M2 |
| `users.locale` column | Session-only persistence today |
| JS i18n bundle | Alpine strings still mixed server/client |
| Theme defaults per region in DB | Cookie/localStorage only for now |
| Marketing `/tw` locale | Out of scope (merchant only) |
| Full dark mode on every legacy partial | Core surfaces covered; edge pages may need polish |

---

## 9. Readiness for next phase

| Check | Status |
| --- | --- |
| `php artisan test` | 70 passed, 2 skipped |
| `npm run build` | Success |
| Upload preview | Integrated |
| Print | Enabled on printing + upload pages |
| DB preview config | Loaded from seeder |
| Locale switcher | Session + navbar UI |
| Dark mode | Global merchant UI |
| Multi-domain | Unchanged; region-aware locales |

**Recommended next:** M2 PDF normalization, live upload processing, dashboard stats from `upload_jobs`.

---

## API routes (new)

| Method | Path | Route name |
| --- | --- | --- |
| POST | `/uploads/{upload}/preview` | `uploads.preview.show` |
| POST | `/locale` | `locale.update` |
| POST | `/theme` | `theme.update` |
