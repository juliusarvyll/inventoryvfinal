# Requirements: University Inventory Management System

**Defined:** 2026-05-07
**Core Value:** Every asset movement is fully auditable — any administrator can trace who had what, when, and why, with zero gaps in the audit trail.

## v1 Requirements

Requirements for initial release. Each maps to roadmap phases.

### Audit

- [ ] **AUDIT-01**: User can generate comprehensive audit reports with filters (date range, user, department, action type) and export to PDF/Excel
- [ ] **AUDIT-02**: User can view audit compliance dashboard showing audit coverage, gaps, and anomalies
- [ ] **AUDIT-03**: User can capture digital signatures for asset handovers and audit confirmations
- [ ] **AUDIT-04**: Department heads receive automated audit reminders for unaudited assets

### Usability

- [ ] **USAB-01**: Administrators can view dashboard widgets showing key metrics (total assets, active checkouts, maintenance due, low stock)
- [ ] **USAB-02**: User can perform advanced search and filtering across all inventory items with saved searches
- [ ] **USAB-03**: User can perform bulk actions (bulk checkout, bulk status change, bulk location transfer)
- [ ] **USAB-04**: System generates QR/barcode for assets and supports scanning for quick lookup
- [ ] **USAB-05**: System supports label printing for physical asset tags

### University

- [ ] **UNIV-01**: User can process student checkouts with ID validation and return deadlines tied to academic semesters

## v2 Requirements

Deferred to future release. Tracked but not in current roadmap.

### University (Deferred)

- **UNIV-02**: Academic session/term tracking for asset assignments — deferred, not core to current audit/usability milestone
- **UNIV-03**: Faculty-specific views showing assets by college/faculty/department hierarchy — deferred
- **UNIV-04**: Grant/fund code tracking for assets purchased with specific funding sources — deferred

### Advanced (Deferred)

- **USAB-06**: Inertia React SPA for mobile-friendly inventory access — deferred, user chose to stick with Laravel Filament
- **AUDIT-05**: Offline mode (PWA) for inventory counts in basements with poor connectivity — deferred
- **AUDIT-06**: Saved searches for power users — deferred

## Out of Scope

Explicitly excluded. Documented to prevent scope creep.

| Feature | Reason |
|---------|--------|
| Inertia React SPA | User chose to stick with Laravel Filament for all UI |
| Financial Depreciation Calculator | Accounting function; auditors want purchase cost, not calculated depreciation |
| Native Mobile App | Filament responsive design covers mobile browsers; PWA deferred to v2 |
| Public Asset Catalog | Internal use only; security risk if public |
| ERP Integration (v1) | Complex, vendor-specific; validate core system first |
| Blockchain Audit Trail | Overkill for university compliance needs |
| AI-Powered Asset Recognition | Photo upload + manual categorization works fine |
| Real-time WebSocket Updates | Physical assets don't change in real-time |
| Multi-tenant SaaS Architecture | Single university deployment; over-engineering |
| Equipment Reservation System | Duplicate of item requests workflow |

## Traceability

Which phases cover which requirements. Updated during roadmap creation.

| Requirement | Phase | Status |
|-------------|-------|--------|
| AUDIT-01 | Phase 1 | Pending |
| AUDIT-02 | Phase 1 | Pending |
| AUDIT-03 | Phase 2 | Pending |
| AUDIT-04 | Phase 2 | Pending |
| USAB-01 | Phase 3 | Pending |
| USAB-02 | Phase 3 | Pending |
| USAB-03 | Phase 3 | Pending |
| USAB-04 | Phase 4 | Pending |
| USAB-05 | Phase 4 | Pending |
| UNIV-01 | Phase 5 | Pending |

**Coverage:**
- v1 requirements: 10 total
- Mapped to phases: 10
- Unmapped: 0 ✓

---
*Requirements defined: 2026-05-07*
*Last updated: 2026-05-07 after initial definition*
