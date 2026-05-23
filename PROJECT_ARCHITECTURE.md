# PrintFlow Pro - Architecture

## Tech Stack

- Laravel 12
- PHP 8.3
- MySQL 8
- Redis
- Filament 5
- TailwindCSS
- AlpineJS
- FPDI
- Laravel Excel
- Browsershot

---

# Application Purpose

Shopee sellers manually upload:

- Thermal labels
- Order PDFs
- Picking lists

The platform:

- validates files
- merges PDFs
- normalizes layouts
- generates optimized print-ready outputs

---

# Phase 1 Scope

## Included

- Local file processing
- Thermal label normalization
- PDF merging
- Picking list aggregation
- Delivery label generation
- Admin panel
- Security cleanup
- Mobile workflows

## Excluded

- Shopee API
- OAuth
- Webhooks
- Realtime sync
- Queue concurrency engine
- 429 retry logic

---

# Main Modules

## 1. Admin Panel
Filament admin panel with:
- merchants
- billing plans
- audit logs
- support tickets

---

## 2. Region Isolation
- Route::domain()
- RegionMiddleware
- country_code global scope

---

## 3. PDF Engine
Core normalization engine using:
- FPDI
- FPDF

Features:
- thermal validation
- barcode protection
- A4 2x2 layout
- merge engine

---

## 4. Picking Engine
Excel/CSV aggregation:
- parse product_info
- split variants
- group items
- sum quantities

---

## 5. Delivery Labels
Dynamic HTML → PDF rendering:
- flexbox layout
- auto shrink
- spacing protection

---

## 6. Security Layer
- temp shredding
- scheduled cleanup
- audit logs
- session validation

---

# Folder Structure

app/
├── Actions
├── DTOs
├── Enums
├── Filament
├── Http
├── Jobs
├── Models
├── Services
├── Support

---

# Development Principles

- Service-based architecture
- Thin controllers
- Reusable actions
- Queue-ready structure
- Mobile-first UI
- Strict validation
- No overengineering