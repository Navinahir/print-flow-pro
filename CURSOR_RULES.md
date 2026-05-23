# Cursor Development Rules

## General Rules

- Use Laravel best practices
- Use Service classes
- Keep controllers thin
- Use Form Requests
- Use DTOs where needed
- Use Actions for business logic
- Use strict typing
- Use clean architecture

---

# Frontend Rules

- Blade + Tailwind only
- AlpineJS for interactions
- Mobile-first approach
- Reusable UI components

---

# Filament Rules

- Use Resources
- Use Relation Managers
- Use Policies
- Use Spatie Permission

---

# PDF Rules

- Preserve barcode quality
- Never rasterize thermal labels
- Use FPDI directly
- Avoid unnecessary transformations

---

# Security Rules

- Delete temp files immediately
- Never expose storage paths
- Validate all uploads
- Enforce region isolation

---

# Code Quality

- SOLID principles
- No duplicated logic
- Reusable services
- Typed methods
- Clear naming

---

# Forbidden

- Fat controllers
- Business logic in Blade
- Inline SQL
- Hardcoded paths
- Unvalidated uploads
- Overengineered abstractions

---

# Priority Focus

1. PDF stability
2. Barcode integrity
3. Thermal print consistency
4. Mobile usability
5. Secure temp handling