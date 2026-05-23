# PrintFlow Pro - Phase 1 TODO

## Core Setup
- [ ] Configure multi-domain routing *(deferred)*
- [ ] Configure RegionMiddleware *(deferred)*
- [x] Configure Filament admin panel (`/admin`)
- [x] Configure Spatie roles & permissions
- [x] Frontend landing page & auth UI
- [x] Upload workflow foundation
- [ ] Configure Redis queues
- [ ] Configure Scheduler
- [ ] Configure Supervisor

---

# Roles & Admin
- [x] super_admin, regional_partner, merchant
- [x] Permission seeders & default super admin
- [x] Domain models & Filament resources
- [x] UploadJobPolicy & merchant registration flow
- [x] Audit log & activity logging foundation

---

# PDF Engine
- [ ] Thermal PDF validation
- [ ] Reject A4 uploads
- [ ] FPDI merge engine
- [ ] A4 2x2 thermal layout
- [ ] Safe print padding
- [ ] Barcode preservation
- [ ] PDF normalization service

---

# Order Merge Engine
- [ ] Process uploaded PDFs (queue job)
- [ ] Merge PDFs
- [ ] Preserve formatting
- [ ] Download merged PDF

---

# Picking List Engine
- [ ] Excel parser
- [ ] CSV parser
- [ ] Product/variant splitting
- [ ] Quantity aggregation
- [ ] Generate picking output

---

# Delivery Labels
- [ ] Address renderer
- [ ] Auto font shrink
- [ ] Dynamic spacing
- [ ] No overlap validation

---

# Frontend
- [x] Upload UI (drag/drop, history, status badges)
- [ ] PDF preview (post-processing)
- [x] Mobile-responsive layouts
- [x] AlpineJS upload interactions
- [ ] LocalStorage persistence

---

# Security
- [ ] Temp file shredding
- [ ] Auto cleanup scheduler
- [x] Audit logs (structure + service)
- [ ] Session region validation *(deferred)*

---

# Sandbox
- [ ] Mock Shopee environment
- [ ] Demo PDF datasets
- [ ] Stable upload flow
- [ ] Audit-safe environment
