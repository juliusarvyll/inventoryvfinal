# Phase 1: Audit Reporting & Compliance - Context

**Gathered:** 2026-05-08
**Status:** Ready for planning

## Phase Boundary

This phase delivers comprehensive audit reporting and compliance dashboard for the University Inventory Management System. Users can generate audit reports with filters and exports, and view audit compliance dashboards.

**In scope:**
- AuditLogResource (Filament Resource) with table view
- Filter design (date range, user, department, action type) with live filtering
- Export implementation (PDF via dompdf, Excel, CSV via Maatwebsite/Excel)
- Compliance dashboard with coverage %, gaps, anomalies
- Configurable anomaly detection rules
- Log retention policy with archival

**Out of scope:**
- Digital signature capture (Phase 2)
- Automated audit reminders (Phase 2)
- Dashboard widgets for general inventory metrics (Phase 3)

## Implementation Decisions

### Report Interface
- **D-01:** Audit reports as Filament Resource (not custom Page) — standard CRUD interface
- **D-02:** AuditLogResource uses standard table view (follows AssetResource.php pattern)
- **D-03:** Admin panel only (not both Admin + Portal) — audit logs are administrative data
- **D-04:** JSON columns show summary in table + full detail in view (not raw JSON)

### Filter Design
- **D-05:** Standard 4 filters: date range, user (with search), department dropdown, action type dropdown
- **D-06:** Live filtering (filters apply instantly using `->live()`)
- **D-07:** Saveable filter presets enabled (users can save filter combinations)
- **D-08:** Inline table filters (not separate filter panel)

### Export Implementation
- **D-09:** dompdf for PDF exports (barryvdh/laravel-dompdf, already installed)
- **D-10:** Export all filtered records (not just selected records)
- **D-11:** PDF + Excel + CSV formats (AUDIT-01 requires PDF and Excel)
- **D-12:** Include related data (user name, asset tag, department name) for readability

### Compliance Dashboard
- **D-13:** Custom page with widgets (not modifying existing admin dashboard)
- **D-14:** All 3 metrics: coverage percentage, gaps table, anomalies list (per AUDIT-02)
- **D-15:** Chart + tables visualization (coverage as chart, gaps/anomalies as tables)
- **D-16:** Drilldown enabled — clicking metrics navigates to filtered audit report

### Access Control
- **D-17:** Admins only for viewing audit reports (Shield RBAC)
- **D-18:** Admins only for exporting audit reports
- **D-19:** No special auditor role needed

### Anomaly Detection Rules
- **D-20:** Anomalies defined by: frequency (>N actions/hour), timing (10PM-6AM), bulk changes (>N at once)
- **D-21:** Separate table on compliance dashboard (not highlighted rows in main table)
- **D-22:** Automatic detection on page load (not manual button click)
- **D-23:** Configurable thresholds (stored in config, not hardcoded)

### Log Retention Policy
- **D-24:** 7 years retention + archival (archive logs >2 years old)
- **D-25:** Separate archive table (`audit_logs_archive`) for archived logs
- **D-26:** No warning before auto-archival (silent background process)
- **D-27:** Archive resource for read-only access to archived logs

### Claude's Discretion
- Layout details for compliance dashboard widgets (chart types, table columns)
- Specific threshold default values (can use recommended: 10/hour, 10PM-6AM, 5 bulk)
- Export file naming conventions

## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Requirements
- `ROADMAP.md` § Phase1 — Goal, success criteria for AUDIT-01, AUDIT-02
- `REQUIREMENTS.md` § AUDIT-01, AUDIT-02 — Full requirement descriptions
- `PROJECT.md` § AuditLog model — Existing audit logging infrastructure

### Technical References
- `app/Models/AuditLog.php` — AuditLog model with user/subject relationships, JSON casts
- `app/Exports/CustomCsvExport.php` — Maatwebsite/Excel pattern already in use
- `app/Filament/Resources/Assets/AssetResource.php` — Filament Resource pattern to follow
- `composer.json` — barryvdh/laravel-dompdf ^3.1, maatwebsite/excel ^3.1, mpdf/mpdf ^8.3 installed

### Configuration
- `config/filament-shield.php` — RBAC configuration for access control

## Existing Code Insights

### Reusable Assets
- **AuditLog model**: Has user relationship, subject polymorphic relationship, old/new values as JSON arrays
- **CustomCsvExport.php**: Maatwebsite/Excel export pattern with headings and chunking
- **AssetResource.php**: Filament Resource pattern with Schema/Table split (AssetForm, AssetsTable)

### Established Patterns
- Filament Resources use `Schemas/` and `Tables/` subdirectories for form/table definitions
- Pages live in `Pages/` subdirectory within Resource directory
- Shield RBAC protects resources via policies
- Livewire components for Filament pages/widgets

### Integration Points
- AuditLog model relationships: `user()` (BelongsTo User), `subject()` (BelongsTo polymorphic)
- Filament Admin panel registration via `app/Providers/Filament/AdminPanelProvider.php`
- Export functionality uses Filament's built-in table export with custom Export classes

## Specific Ideas

No specific "like X" references — standard Filament/Laravel patterns apply.

## Deferred Ideas

None — discussion stayed within phase scope.

---
*Phase: 1-Audit Reporting & Compliance*
*Context gathered: 2026-05-08*
