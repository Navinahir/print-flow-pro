# Merchant Portal UI/UX Improvements — Completion Report

**Date:** 2026-05-30  
**Scope:** Merchant domain only (`localhost:8001`) — Blade templates, no Admin/Marketing/Filament changes.

---

## Summary

Implemented sidebar collapse with `localStorage` persistence, a pinned sidebar footer for Profile/Logout, a full-page locale-switch loader, and standardized merchant forms (localized placeholders, red required asterisks, consistent validation errors).

**Tests:** 70 passed, 2 skipped (`php artisan test`)

---

## Modified Files

### Layout & navigation

| File | Changes |
| --- | --- |
| `resources/views/merchant/layouts/app.blade.php` | Sidebar collapse class binding, early layout script, page loader |
| `resources/views/merchant/partials/sidebar.blade.php` | Collapse-ready structure, removed Account/Profile nav, added footer include |
| `resources/views/merchant/partials/sidebar-footer.blade.php` | **New** — Profile + Logout pinned footer |
| `resources/views/merchant/partials/header.blade.php` | Desktop collapse/expand toggle button |
| `resources/views/merchant/partials/printing-nav-items.blade.php` | Label spans + collapse tooltips |
| `resources/views/merchant/partials/mobile-nav.blade.php` | Removed duplicate Profile/Logout (use sidebar overlay footer) |
| `resources/views/merchant/components/user-menu.blade.php` | Static user display (actions moved to sidebar footer) |
| `resources/views/merchant/components/locale-switcher.blade.php` | Triggers locale loader on submit |

### Forms (auth, profile, uploads)

| File |
| --- |
| `resources/views/merchant/auth/login.blade.php` |
| `resources/views/merchant/auth/register.blade.php` |
| `resources/views/merchant/auth/forgot-password.blade.php` |
| `resources/views/merchant/auth/reset-password.blade.php` |
| `resources/views/merchant/auth/confirm-password.blade.php` |
| `resources/views/merchant/pages/profile/partials/update-profile-information-form.blade.php` |
| `resources/views/merchant/pages/profile/partials/update-password-form.blade.php` |
| `resources/views/merchant/pages/uploads/create.blade.php` |

### JavaScript & CSS

| File | Changes |
| --- | --- |
| `resources/js/merchant/shell.js` | `sidebarCollapsed`, `localeSwitching`, localStorage sync |
| `resources/js/merchant/storage.js` | **New** — storage key helpers |
| `resources/css/merchant.css` | Imports sidebar, form, page-loader CSS |
| `resources/css/merchant/sidebar.css` | **New** |
| `resources/css/merchant/form.css` | **New** |
| `resources/css/merchant/page-loader.css` | **New** — spinner + overlay |

### PHP components & provider

| File |
| --- |
| `app/View/Components/Merchant/Form/FormLabel.php` |
| `app/View/Components/Merchant/Form/FormError.php` |
| `app/View/Components/Merchant/Form/FormField.php` |
| `app/View/Components/Merchant/PageLoader.php` |
| `app/Providers/AppServiceProvider.php` |

### Localization

| File |
| --- |
| `lang/en/merchant.php`, `lang/zh-TW/merchant.php` |
| `lang/en/auth.php`, `lang/zh-TW/auth.php` |

### Documentation

| File |
| --- |
| `README.md` |
| `TODO.md` |

---

## Reusable Components Created

| Component | Tag | Purpose |
| --- | --- | --- |
| Form label | `x-merchant.form.label` | Label + red `*` required indicator + screen-reader text |
| Form error | `x-merchant.form.error` | Standardized validation message below field |
| Form field | `x-merchant.form.field` | Label + input slot + error wrapper |
| Page loader | `x-merchant.page-loader` | Full-page blur overlay with spinner |
| Sidebar footer | `@include('merchant.partials.sidebar-footer')` | Pinned Profile/Logout actions |

### JavaScript modules

- `resources/js/merchant/storage.js` — `MERCHANT_STORAGE_KEYS`, read/write flags, pending layout classes

### CSS modules

- `sidebar.css` — collapse width, icon-only mode, footer layout
- `form.css` — `.merchant-form-required`, `.merchant-form-error`
- `page-loader.css` — `.merchant-spinner`, `.merchant-page-loader`

---

## Validation Improvements

- Removed all HTML `required` attributes from merchant form inputs (login, register, password flows, profile, uploads).
- Laravel backend validation unchanged (form requests / controllers).
- All validation errors render via `x-merchant.form.error` directly below the field in red with consistent spacing.
- Named error bags supported (`updatePassword` on profile password form).
- File upload errors (`files`, `files.*`) use the same error component.

---

## UI/UX Improvements

| Feature | Behavior |
| --- | --- |
| **Sidebar collapse** | Header toggle (desktop); icons-only when collapsed; `lg:pl-20` content offset |
| **localStorage** | `merchant_sidebar_collapsed` persists across refresh, navigation, login |
| **Sidebar footer** | Profile + Logout always pinned at sidebar bottom; tooltips when collapsed |
| **Locale loader** | Blur overlay on language change until `window.load`; flag `merchant_locale_switching` |
| **Required fields** | Red asterisk beside label via `x-merchant.form.label` |
| **Placeholders** | Localized in `lang/*/merchant.php` and `lang/*/auth.php` |
| **User menu** | Displays name/avatar only; account actions in sidebar footer |

---

## Verification Checklist

| Page | Verified via tests / render |
| --- | --- |
| Dashboard | Layout extends `app` with shell |
| Uploads (index/create) | `UploadTest`, form components on create |
| Profile | `ProfileTest` |
| Printing modules (4) | `PrintingModuleTest`, `PreviewEngineTest` |
| Auth pages | `AuthenticationTest`, `RegistrationTest`, password tests |
| Sidebar collapse persistence | JS + CSS + inline head script |
| Locale loader | Locale switcher `@submit` + shell state |
| Mobile responsiveness | Existing mobile nav + sidebar overlay unchanged |

---

## Remaining Recommendations

1. **NavigationBuilder** — Extract sidebar link definitions to a PHP helper to reduce Blade duplication.
2. **Guest auth locale switcher** — Auth guest layout has no language dropdown; add if merchants need to switch language before login.
3. **Mobile profile/logout** — Mobile users open the hamburger sidebar (left) for footer actions; consider a shortcut in mobile nav if UX testing shows confusion.
4. **JS i18n bundle** — Pass collapse/loader strings to JS via layout data attributes if client-side messages expand beyond Blade.
5. **Component tests** — Optional Blade/component snapshot tests for form label/error markup.

---

## Build & Test Commands

```bash
npm run build
php artisan test
```
