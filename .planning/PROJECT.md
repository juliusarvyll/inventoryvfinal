# University Inventory Management System

## What This Is

A comprehensive university asset management system built with Laravel 12 and Filament v4. It tracks all university assets (equipment, accessories, licenses, consumables, components) across departments and locations, with full audit trails, checkout/checkin workflows, preventive maintenance, and item request approvals. Built for university administrators, department heads, and staff who need to track, audit, and manage inventory at scale.

## Core Value

Every asset movement is fully auditable — any administrator can trace who had what, when, and why, with zero gaps in the audit trail.

## Requirements

### Validated

<!-- Shipped and confirmed valuable. -->

- ✓ Asset management (create, edit, view, tag, serial tracking) — existing
- ✓ Asset checkout/checkin with user tracking — existing
- ✓ Accessory management with checkouts — existing
- ✓ License management with seat assignments — existing
- ✓ Consumable management with assignments — existing
- ✓ Component management (parts attached to assets) — existing
- ✓ Department management with heads and user assignments — existing
- ✓ Location management with hierarchical structure — existing
- ✓ Category, Manufacturer, Supplier, StatusLabel management — existing
- ✓ Item Requests with approval workflow, fund sources, purposes — existing
- ✓ Preventive Maintenance (schedules, checklists, executions) — existing
- ✓ Audit Logging (user actions, old/new values, IP, user agent) — existing
- ✓ Two Filament panels (Admin + Portal) with Shield RBAC — existing
- ✓ AssetModel management for standardizing asset specs — existing

### Active

<!-- Current scope. Building toward these. -->

- [ ] **AUDIT-01**: Comprehensive audit reports with filters (date range, user, department, action type) and export (PDF/Excel)
- [ ] **AUDIT-02**: Audit compliance dashboard showing audit coverage, gaps, and anomalies
- [ ] **AUDIT-03**: Digital signature capture for asset handovers and audit confirmations
- [ ] **AUDIT-04**: Automated audit reminders for department heads (unaudited assets notifications)
- [ ] **USAB-01**: Responsive Inertia React SPA frontend for mobile-friendly inventory access
- [ ] **USAB-02**: Dashboard widgets showing key metrics (total assets, checkouts, maintenance due, low stock)
- [ ] **USAB-03**: Advanced search and filtering across all inventory items with saved searches
- [ ] **USAB-04**: Bulk actions (bulk checkout, bulk status change, bulk location transfer)
- [ ] **USAB-05**: QR code/barcode generation and scanning for quick asset lookup
- [ ] **UNIV-01**: Academic session/term tracking for asset assignments tied to semesters
- [ ] **UNIV-02**: Faculty-specific views showing assets by college/faculty/department hierarchy
- [ ] **UNIV-03**: Student checkout support with student ID validation and return deadlines
- [ ] **UNIV-04**: Grant/fund code tracking for assets purchased with specific funding sources

### Out of Scope

<!-- Explicit boundaries. Includes reasoning to prevent re-adding. -->

- Equipment reservation system — not needed for current university workflow; use item requests instead
- Financial depreciation calculations — out of scope for v1; audit focuses on physical tracking
- Integration with external ERP systems — deferred to future phase after validating core system
- Public-facing asset catalog — internal use only; no public API planned for v1
- Mobile app (native) — Inertia React SPA covers mobile browser access; native app deferred

## Context

**Technical Environment:**
- Laravel 12 backend with Filament v4 admin panels (Admin + Portal)
- Inertia React v3 SPA planned but not yet implemented
- SQLite (default) / MySQL supported
- Pest PHP v4 for testing
- Filament Shield for RBAC
- Maatwebsite Excel for exports

**Existing Architecture:**
- Two isolated Filament panels with separate auth and middleware
- Eloquent ORM with comprehensive model relationships
- AuditLog model tracking all user actions with old/new values
- Department → Location → Asset hierarchy
- Preventive maintenance system with schedules linked to categories

**User Roles (inferred from Shield + panels):**
- Admins (full access via Admin panel)
- Department heads (via Department.head_id)
- Staff/users (restricted portal access)
- Auditors (read access to audit trails)

## Constraints

- **Tech stack**: Must use existing Laravel 12 + Filament v4 + Inertia React v3 stack — project initialized with these
- **Database**: Must support both SQLite (dev) and MySQL (prod) — no MySQL-specific features
- **Performance**: Asset tables may grow to 10,000+ rows; queries must be optimized with eager loading and indexes
- **Security**: Audit trails must be tamper-evident; AuditLog records cannot be edited or deleted
- **Usability**: Must work on mobile browsers (university staff may use phones/tablets in the field)

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Two-panel architecture (Admin + Portal) | Separate admin functions from end-user portal | — Pending |
| AuditLog with old/new values | Full change tracking for compliance | — Pending |
| Filament Shield for RBAC | Standard Laravel package, well-supported | — Pending |
| Inertia React SPA (planned) | Modern UX, reusable components, mobile-friendly | — Pending |

---
*Last updated: 2026-05-07 after initialization*
