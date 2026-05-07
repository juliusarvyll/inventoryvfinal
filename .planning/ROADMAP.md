# ROADMAP.md: University Inventory Management System

**Granularity:** Standard (5 phases)
**Coverage:** 10/10 v1 requirements mapped ✓

## Phases

- [ ] **Phase 1: Audit Reporting & Compliance** - Generate audit reports with filters/exports and view compliance dashboards
- [ ] **Phase 2: Audit Enhancements** - Digital signature capture and automated audit reminders for department heads
- [ ] **Phase 3: Core Usability Features** - Dashboard widgets, advanced search, and bulk actions
- [ ] **Phase 4: QR/Barcode & Label Printing** - QR/barcode generation/scanning and physical label printing
- [ ] **Phase 5: University Student Checkout Support** - Student checkout with ID validation and semester-tied deadlines

## Phase Details

### Phase 1: Audit Reporting & Compliance
**Goal**: Users can generate comprehensive audit reports with filters and exports, and view audit compliance dashboards.
**Depends on**: None (first phase)
**Requirements**: AUDIT-01, AUDIT-02
**Success Criteria** (what must be TRUE):
  1. User can navigate to the audit reports page, apply filters (date range, user, department, action type), and generate a report.
  2. User can export generated audit reports to PDF and Excel formats.
  3. User can view an audit compliance dashboard showing audit coverage percentage, identified gaps, and anomaly alerts.
**Plans**: TBD
**UI hint**: yes

### Phase 2: Audit Enhancements
**Goal**: Users can capture digital signatures for asset handovers and audit confirmations, and department heads receive automated audit reminders for unaudited assets.
**Depends on**: Phase 1 (AUDIT-04 requires audit coverage data from Phase 1 reports/dashboards)
**Requirements**: AUDIT-03, AUDIT-04
**Success Criteria** (what must be TRUE):
  1. User can capture a digital signature via an on-screen signature pad during asset handover or audit confirmation workflows.
  2. Department heads receive automated email reminders listing unaudited assets assigned to their department.
  3. Audit reminders are triggered based on configurable schedules tied to audit coverage gaps.
**Plans**: TBD
**UI hint**: yes

### Phase 3: Core Usability Features
**Goal**: Administrators can view key inventory metrics via dashboard widgets, and all users can perform advanced search with saved filters and bulk actions on inventory items.
**Depends on**: None
**Requirements**: USAB-01, USAB-02, USAB-03
**Success Criteria** (what must be TRUE):
  1. Administrators can view a dashboard with widgets showing total assets, active checkouts, maintenance due, and low stock counts.
  2. User can perform global search across all inventory items with filters for category, department, status, and save search criteria for future use.
  3. User can select multiple inventory items and execute bulk actions (checkout, status change, location transfer) in a single operation.
**Plans**: TBD
**UI hint**: yes

### Phase 4: QR/Barcode & Label Printing
**Goal**: System generates QR/barcodes for assets, supports scanning for quick lookup, and enables physical label printing for asset tags.
**Depends on**: None
**Requirements**: USAB-04, USAB-05
**Success Criteria** (what must be TRUE):
  1. User can generate a unique QR or barcode for any asset, displayed on the asset's detail page.
  2. User can scan a QR/barcode using their device's camera to instantly navigate to the corresponding asset's detail page.
  3. User can generate printable labels containing asset QR/barcode, asset tag, and core details, formatted for standard label printers.
**Plans**: TBD
**UI hint**: yes

### Phase 5: University Student Checkout Support
**Goal**: Users can process student checkouts with ID validation and return deadlines tied to academic semesters.
**Depends on**: None
**Requirements**: UNIV-01
**Success Criteria** (what must be TRUE):
  1. User can process a checkout for a student by validating their student ID against university records.
  2. Student checkouts automatically set return deadlines aligned to the current academic semester dates.
  3. System flags overdue student checkouts and prevents new checkouts for students with unresolved overdue items.
**Plans**: TBD
**UI hint**: yes

## Progress Table

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Audit Reporting & Compliance | 0/3 | Not started | - |
| 2. Audit Enhancements | 0/3 | Not started | - |
| 3. Core Usability Features | 0/3 | Not started | - |
| 4. QR/Barcode & Label Printing | 0/3 | Not started | - |
| 5. University Student Checkout Support | 0/3 | Not started | - |
