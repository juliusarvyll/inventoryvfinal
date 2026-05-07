# Project Research Summary

**Project:** University Inventory Management System
**Domain:** University inventory/asset management (auditing and usability focus)
**Researched:** 2026-05-07
**Confidence:** MEDIUM

## Executive Summary

The University Inventory Management System is a Laravel 12-based application for tracking university assets (equipment, licenses, consumables) with compliance audit requirements and field usability needs. The recommended approach builds on the existing stack (Laravel 12, Filament v4, Inertia React v3) by expanding audit coverage, adding a mobile-friendly SPA, and integrating QR/barcode scanning for physical inventory counts.

Key risks include mutable audit logs that would fail compliance audits, incomplete audit coverage on related models (checkouts, maintenance), and performance degradation as asset counts exceed 10,000. These are mitigated by making audit logs immutable, adding model observers for all inventory-related models, and adding database indexes with eager loading to prevent N+1 queries. The current milestone prioritizes audit compliance and usability enhancements, deferring complex features like digital signatures and offline mode until the SPA foundation is in place.

## Key Findings

### Recommended Stack

The stack builds on the existing project's Laravel 12, PHP 8.3, Filament v4, Inertia Laravel v3, and Fortify v1 authentication. New PHP packages include Spatie laravel-activitylog (audit trails), Spatie laravel-medialibrary (asset attachments), milwad/laravel-barcode (QR/barcode generation), and adldap2/adldap2-laravel (LDAP/AD integration). Existing packages like maatwebsite/excel (exports), barryvdh/laravel-dompdf (PDFs), and filament-shield (RBAC) are already installed and verified. JavaScript additions include @ericblade/quagga2 (barcode scanning) and react-qr-code (QR display).

**Core technologies:**
- Laravel 12: Application framework — existing version, current LTS-compatible release
- Filament v4: Admin/portal panels — existing version, provides CRUD, dashboards, RBAC out of the box
- Inertia Laravel v3: SPA bridge — existing version, enables React frontend with Laravel backend
- React 19: Frontend framework — existing, component-based UI for mobile-friendly inventory dashboards
- MySQL 8.0+: Primary database — existing, reliable for relational inventory data

### Expected Features

The system requires table stakes features including audit report exports, responsive UI, advanced search, bulk operations, dashboard metrics, and QR/barcode support. Differentiators include digital signatures for asset handovers, audit compliance dashboards, automated audit reminders, and university-specific features like academic session tracking and student checkout with semester deadlines.

**Must have (table stakes):**
- Audit Report Export — universities need PDF/Excel reports for compliance audits
- Responsive UI — staff use phones/tablets in field for inventory counts
- Advanced Search — find assets quickly across 10,000+ records
- Bulk Operations — update 50+ assets at once for efficiency
- Dashboard with Metrics — at-a-glance status overview for administrators
- QR/Barcode Support — physical inventory counts require scanning

**Should have (competitive):**
- Digital Signatures — legally binding handover confirmations for asset transfers
- Audit Compliance Dashboard — visual audit coverage gaps and anomaly detection
- Automated Audit Reminders — nudge department heads for overdue audits
- Academic Session Tracking — tie student checkouts to semesters
- Student Checkout with Deadlines — auto-reminders for semester returns

**Defer (v2+):**
- Offline Mode (PWA) — only needed for basements with poor connectivity
- Grant/Fund Code Tracking — valuable but not core to current audit/usability milestone
- ERP Integration — complex, vendor-specific; validate core system first
- Blockchain Audit Trail — overkill for university compliance needs

### Architecture Approach

The system uses three frontend interfaces sharing a Laravel 12 backend: Admin Filament Panel (primary inventory management), Portal Filament Panel (restricted user asset requests), and Inertia React SPA (mobile-friendly field use). Eloquent models serve as the data access layer, with Filament resources providing CRUD interfaces. Key patterns include Inertia-Filament coexistence (non-overlapping routes, shared auth guard), audit logging via model observers, and department-scoped access via policies and global scopes.

**Major components:**
1. Inertia React SPA — mobile-friendly UI for asset lookup, audits, check-in/out
2. Audit Log Service — track all asset changes, generate compliance reports
3. Barcode/QR Service — generate and scan asset tags for physical inventory
4. Department Hierarchy — manage Department->Location->Asset relationships for access control

### Critical Pitfalls

1. **Mutable Audit Logs** — audit records editable/deletable, causing compliance failure — prevent by removing edit/delete actions from AuditLog Filament resource, add model-level protection, use Spatie activitylog immutable features
2. **Missing Audit Coverage on Related Models** — checkouts, maintenance not logged, incomplete trail — prevent by adding Spatie trait to all models affecting asset state, create base audit trait, test all models
3. **Performance Degradation with 10K+ Assets** — slow queries from missing indexes, N+1 issues — prevent by adding indexes to asset_tag, serial, department_id, paginate listings, eager load relationships
4. **QR/Barcode Format Fragmentation** — only supporting one format, can't scan legacy labels — prevent by using milwad/laravel-barcode (15+ formats), allow per-asset format config, test with university labels
5. **Student Checkout Without Return Enforcement** — unreturned equipment, budget overruns — prevent by setting semester-based due dates, automated reminders, integration with student info system for registration holds

## Implications for Roadmap

Based on research, suggested phase structure:

### Phase 1: Foundation — Inertia SPA Base + Audit Log Expansion
**Rationale:** Inertia SPA is required for all frontend features; complete audit coverage is required for all audit features. No dependencies for SPA setup; audit expansion uses existing AuditLog model.
**Delivers:** Working Inertia React SPA shell with base layout, Vite configuration, Wayfinder generated routes, audit logging via model observers for all inventory-related models
**Addresses:** USAB-01 (Responsive UI), AUDIT-01 (Audit Coverage)

### Phase 2: Audit Compliance Features
**Rationale:** Audit log expansion is complete, enabling audit report generation and compliance tracking. Top priority for current milestone.
**Delivers:** Audit report export (PDF/Excel) with filters; audit compliance dashboard; audit checklist UI
**Addresses:** AUDIT-01 (Audit Reports), AUDIT-02 (Compliance Dashboard)

### Phase 3: Usability Enhancements
**Rationale:** SPA base exists, enabling user-facing usability features. Second priority for current milestone.
**Delivers:** QR/barcode generation, mobile scanning via webcam in SPA; bulk operations; admin/portal dashboard widgets
**Addresses:** USAB-02 (Dashboard Widgets), USAB-04 (Bulk Actions), USAB-05 (QR/Barcode)

### Phase 4: University-Specific Features
**Rationale:** Core audit and usability features are complete, enabling university-specific workflows that depend on base functionality.
**Delivers:** Academic session tracking; student checkout with due dates and automated reminders; department head dashboard
**Addresses:** UNIV-01 (Academic Sessions), UNIV-02 (Faculty/College Views), UNIV-03 (Student Checkout), UNIV-04 (Grant Tracking)

### Phase 5: Advanced Features (Deferred)
**Rationale:** Nice-to-have features that require mature core system, deferred until v2 validation.
**Delivers:** Digital signatures for asset handovers; offline mode/PWA for basement inventory counts
**Addresses:** AUDIT-03 (Digital Signatures), AUDIT-04 (Audit Reminders)

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | MEDIUM | Existing packages HIGH confidence; new PHP packages MEDIUM (versions unverified for Laravel 12); JS packages LOW (rapid ecosystem changes) |
| Features | HIGH | Based on existing codebase analysis + university inventory domain expertise |
| Architecture | MEDIUM | Existing components HIGH confidence; Inertia-Filament integration MEDIUM; scalability patterns MEDIUM |
| Pitfalls | HIGH | Based on existing codebase analysis + common inventory system failure patterns |

**Overall confidence:** MEDIUM (stack version verification pending, but features and pitfalls are high confidence with actionable mitigations)

## Gaps to Address

- Verify spatie/laravel-activitylog ^4.0 supports Laravel 12
- Verify milwad/laravel-barcode latest version supports Laravel 12
- Check if @ericblade/quagga2 is maintained in 2026
- Confirm filament-shield requires explicit spatie/laravel-permission installation
- Test LDAP/AD integration with university IT before building authentication features
- Add accessibility (WCAG compliance) review for government-funded institution requirements
- Validate student ID format with university registrar before implementing student checkout

---
*Research completed: 2026-05-07*
*Ready for roadmap: yes*
