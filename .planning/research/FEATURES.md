# Feature Landscape

**Domain:** University inventory/asset management
**Researched:** 2026-05-07
**Overall confidence:** HIGH (based on existing codebase analysis + university inventory domain expertise)

## Executive Summary

The existing system already implements core inventory tracking (Assets, Accessories, Licenses, Consumables, Components) with Filament v4 resources, audit logging, and two-panel architecture. This research focuses on the current milestone: **comprehensive auditing and usability enhancements**.

For auditing, table stakes include tamper-evident logs, change diffs, and compliance reporting. Differentiators include digital signatures, automated reminders, and anomaly detection. For usability, table stakes include responsive design, bulk operations, and search. Differentiators include QR/barcode scanning, saved searches, and dashboard widgets.

---

## Table Stakes

Features users expect. Missing = product feels incomplete.

| Feature | Why Expected | Complexity | Status | Notes |
|---------|--------------|------------|--------|-------|
| **Audit Trail (CRUD tracking)** | Compliance requirement; track who changed what | Low | ✓ Exists | AuditLog model with old/new values, IP, user agent |
| **Audit Report Export** | Universities need PDF/Excel reports for compliance audits | Medium | Pending | Filter by date range, user, department, action type |
| **Responsive UI** | Staff use phones/tablets in field | High | Pending | Inertia React SPA planned (USAB-01) |
| **Advanced Search** | Users need to find assets quickly across 10,000+ records | Medium | Pending | Full-text search across all inventory items |
| **Bulk Operations** | Efficiency: update 50 assets at once vs one-by-one | Medium | Pending | Bulk checkout, status change, location transfer |
| **Dashboard with Metrics** | Administrators need at-a-glance status overview | Medium | Pending | Total assets, checkouts, maintenance due, low stock |
| **QR/Barcode Support** | Physical inventory counts require scanning | Medium | Pending | Generate codes, scan for lookup/checkin |
| **User-friendly Checkin/Checkout** | Core workflow; must be fast and error-proof | Medium | ✓ Exists | AssetCheckout model with active checkout tracking |
| **Role-based Access (RBAC)** | Different permissions for admins, dept heads, staff | Low | ✓ Exists | Filament Shield installed, two-panel architecture |
| **Asset Tags/Numbers** | Unique identifiers required for physical tracking | Low | ✓ Exists | asset_tag field in Asset model |
| **Location Hierarchy** | Universities have campuses → buildings → rooms | Low | ✓ Exists | Location model with hierarchical structure |
| **Department Assignments** | Assets tracked by department for accountability | Low | ✓ Exists | Department model with head and user assignments |

---

## Differentiators

Features that set product apart. Not expected, but valued.

| Feature | Value Proposition | Complexity | Status | Notes |
|---------|-------------------|------------|--------|-------|
| **Digital Signatures** | Legally binding handover confirmations; reduces disputes | High | Pending (AUDIT-03) | Capture signatures on mobile for asset transfers |
| **Audit Compliance Dashboard** | Visual audit coverage gaps; proactive compliance | Medium | Pending (AUDIT-02) | Show unaudited assets, anomaly detection |
| **Automated Audit Reminders** | Department heads get nudges for overdue audits | Medium | Pending (AUDIT-04) | Scheduled notifications via Laravel scheduler |
| **Academic Session Tracking** | Tie assets to semesters for student checkouts | Medium | Pending (UNIV-01) | Track which term an asset was assigned |
| **Faculty/College Views** | Hierarchy-aware views (College → Faculty → Dept) | Medium | Pending (UNIV-02) | Nested resource views in Filament |
| **Student ID Validation** | Validate student status before checkout | Medium | Pending (UNIV-03) | Integrate with student information system or manual validation |
| **Grant/Fund Code Tracking** | Track assets purchased with specific grants | Medium | Pending (UNIV-04) | Critical for grant compliance reporting |
| **Saved Searches** | Power users save common filters | Low | New | Store filter presets per user |
| **Preventive Maintenance** | Proactive servicing extends asset life | Medium | ✓ Exists | Schedules, checklists, executions implemented |
| **Item Request Workflow** | Users request assets; admins approve | Medium | ✓ Exists | Approval workflow with fund sources, purposes |
| **Audit Log Diff Viewer** | Visual before/after comparison of changes | Medium | New | Rich diff display for JSON old/new values |
| **Offline Mode (PWA)** | Inventory counts in basements with poor connectivity | High | New | Service workers for offline scanning/counting |

---

## University-Specific Features

Features specific to higher education environments.

| Feature | Why Needed | Complexity | Notes |
|---------|------------|------------|-------|
| **Student Checkout with Deadlines** | Students must return equipment by semester end | Medium | Auto-reminders as deadline approaches |
| **Course/Section Attribution** | Link assets to specific courses (e.g., "Cameras for JOUR 101") | Medium | Many-to-many with course codes |
| **Faculty Load Tracking** | Track assets assigned to faculty (research vs teaching) | Low | Add purpose field to checkouts |
| **Grant Compliance Reports** | Show all assets purchased with NSF Grant #12345 | High | Requires fund code tracking + reporting |
| **Department Head Dashboard** | Dept heads see only their department's assets | Low | Filament scoping by department head |
| **Asset Transfer Between Depts** | Move assets when faculty change departments | Medium | Transfer workflow with approval |
| **Surplus/Disposal Workflow** | Formal process for retiring assets | Medium | States: active → surplus → disposed |
| **Asset Valuation Reports** | Total value by department for insurance/budget | Medium | Aggregate purchase_cost with filters |

---

## Anti-Features

Features to explicitly NOT build.

| Anti-Feature | Why Avoid | What to Do Instead |
|--------------|-----------|-------------------|
| **Financial Depreciation Calculator** | Accounting function; auditors want purchase cost, not calculated depreciation | Track purchase_cost; let finance department calculate depreciation |
| **Native Mobile App** | Inertia React SPA + responsive design covers mobile browsers | Use PWA/offline mode if native features needed |
| **Public Asset Catalog** | Internal use only; security risk if public | Use Portal panel with appropriate auth |
| **ERP Integration (v1)** | Complex, vendor-specific; validate core system first | Defer to future phase after core validated |
| **Equipment Reservation System** | Duplicate of item requests workflow | Enhance item requests instead |
| **Multi-tenant SaaS Architecture** | Single university deployment; over-engineering | Keep simple single-tenant architecture |
| **Real-time WebSocket Updates** | Physical assets don't change in real-time | Standard HTTP requests sufficient |
| **Blockchain Audit Trail** | Overkill for university compliance needs | Standard append-only AuditLog is sufficient |
| **AI-Powered Asset Recognition** | Photo upload + manual categorization works fine | Use media library for photos |

---

## Feature Dependencies

```
Academic Session Tracking → Student Checkout (tie to semesters)
  ↓
Faculty/College Views → Department Assignments (hierarchical views)

Grant/Fund Code Tracking → Grant Compliance Reports
  ↓
Asset Valuation Reports (filter by fund code)

Digital Signatures → Asset Handover Confirmations
  ↓
Audit Compliance Dashboard (signature coverage metrics)

Automated Audit Reminders → Audit Compliance Dashboard
  ↓
  ↓
Unaudited Assets Report (identify gaps)

QR/Barcode Support → Offline Mode (scan without connectivity)
  ↓
Bulk Operations (scan multiple items for batch actions)

Saved Searches → Advanced Search (save filter presets)
  ↓
Dashboard Widgets (show saved search results)

Responsive UI (Inertia React) → All frontend features
  ↓
Mobile-friendly Checkin/Checkout, Bulk Operations, Search
```

---

## MVP Recommendation for Current Milestone

Prioritize for AUDIT + USAB milestone:

1. **AUDIT-01: Comprehensive audit reports** - Table stakes for compliance; export critical
2. **AUDIT-02: Audit compliance dashboard** - Shows audit coverage gaps visually
3. **USAB-02: Dashboard widgets** - Immediate value for administrators
4. **USAB-05: QR/barcode generation and scanning** - Huge usability win for field staff
5. **USAB-04: Bulk actions** - Efficiency multiplier for large inventories

Defer:
- **Digital Signatures (AUDIT-03):** Requires mobile UI first; defer until Inertia React SPA exists
- **Offline Mode:** Complex; only needed if QR scanning in basements is requested
- **Grant/Fund Tracking (UNIV-04):** Requires new fields + reports; valuable but not core to audit/usability

---

## Complexity Assessment

### Low Complexity (can build quickly)
- Saved Searches (store filter state in DB)
- Audit Log Diff Viewer (JSON comparison UI)
- Department Head Dashboard (Filament scoping)

### Medium Complexity (require design thought)
- Audit Compliance Dashboard (anomaly detection logic)
- Automated Audit Reminders (scheduler + notifications)
- Academic Session Tracking (new model + relationships)
- Student Checkout with Deadlines (validation + reminders)
- QR/Barcode Generation (library integration)
- Bulk Operations (Filament bulk actions)
- Dashboard Widgets (Filament widgets)

### High Complexity (need careful planning)
- Digital Signatures (canvas capture + storage + legal validity)
- Offline Mode/PWA (service workers + sync logic)
- Grant Compliance Reports (complex aggregation queries)
- Faculty/College Hierarchy Views (nested resource relationships)

---

## Sources

- **Existing Codebase Analysis:** Asset.php, AuditLog.php, PROJECT.md review (HIGH confidence)
- **Snipe-IT Feature Set:** Leading open-source asset management for universities (HIGH confidence)
- **University Compliance Requirements:** FERPA, audit trail requirements for public institutions (MEDIUM confidence)
- **Filament v4 Documentation:** via existing project context (HIGH confidence)
- **Laravel 12 Ecosystem:** via existing project context (HIGH confidence)

---

*Confidence Legend: HIGH = verified from code/docs, MEDIUM = domain knowledge, LOW = unverified assumption*
