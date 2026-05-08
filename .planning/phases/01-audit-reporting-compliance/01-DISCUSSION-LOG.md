# Phase 1: Audit Reporting & Compliance - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-05-08
**Phase:** 1-Audit Reporting & Compliance
**Areas discussed:** Report Interface, Filter Design, Export Implementation, Compliance Dashboard, Access Control, Anomaly Detection Rules, Log Retention Policy

---

## Report Interface

| Option | Description | Selected |
|--------|-------------|----------|
| Filament Resource | Standard CRUD interface with table view, filters, actions | ✓ |
| Custom Page | More flexible layout with custom widgets/forms | |
| You decide | Let Claude decide | |

**User's choice:** Filament Resource (Recommended)
**Notes:** Matches existing AssetResource pattern

---

| Option | Description | Selected |
|--------|-------------|----------|
| Standard table view | Follows AssetResource.php pattern | ✓ |
| Customized table + summary | Custom layout with summary cards | |
| You decide | Let Claude decide | |

**User's choice:** Standard table view (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Admin panel only | Audit logs are administrative data | ✓ |
| Both Admin + Portal | Department heads may want audit logs | |
| You decide | Let Claude decide | |

**User's choice:** Admin panel only (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Formatted JSON in table | Raw JSON formatting | |
| Summary in table + detail view | Concise table, full detail on view | ✓ |
| Detail view only | Only in detail page | |
| You decide | Let Claude decide | |

**User's choice:** Summary in table + detail view (Recommended)

---

## Filter Design

| Option | Description | Selected |
|--------|-------------|----------|
| Standard 4 filters | Date range, user, department, action type | ✓ |
| Extended filters | All standard + asset/model/location fields | |
| You decide | Let Claude decide | |

**User's choice:** Standard 4 filters (Recommended)
**Notes:** Matches AUDIT-01 requirements exactly

---

| Option | Description | Selected |
|--------|-------------|----------|
| Live filtering | Filters apply instantly. Uses Filament's ->live() | ✓ |
| Manual apply button | User clicks Apply. More explicit but extra click | |

**User's choice:** Live filtering (Recommended)
**Notes:** Matches existing AssetResource pattern

---

| Option | Description | Selected |
|--------|-------------|----------|
| Yes, saveable presets | Users can save filter combos (e.g., 'Q1 Audits') | ✓ |
| No presets | Filters are always manual. Simpler | |

**User's choice:** Yes, saveable presets

---

| Option | Description | Selected |
|--------|-------------|----------|
| Inline table filters | Filters appear in table header. Compact | ✓ |
| Separate filter panel | Dedicated panel above table with more space | |

**User's choice:** Inline table filters (Recommended)

---

## Export Implementation

| Option | Description | Selected |
|--------|-------------|----------|
| dompdf | barryvdh/laravel-dompdf already installed. Lightweight | ✓ |
| mpdf | mpdf/mpdf already installed. Better Unicode support | |
| Both | Use each where appropriate | |

**User's choice:** dompdf (Recommended)
**Notes:** Both libraries installed in composer.json

---

| Option | Description | Selected |
|--------|-------------|----------|
| All filtered records | Export only records matching current filters | ✓ |
| Selected records only | Export only records selected via checkboxes | |
| Let user choose | Export records based on user choice at export | |

**User's choice:** All filtered records (Recommended)
**Notes:** Most useful for targeted audits

---

| Option | Description | Selected |
|--------|-------------|----------|
| PDF + Excel + CSV | PDF (dompdf), Excel via Maatwebsite/Excel, CSV export | ✓ |
| PDF + Excel only | Most common formats | |
| You decide | Let Claude decide | |

**User's choice:** PDF + Excel + CSV (Recommended)
**Notes:** AUDIT-01 requires PDF and Excel

---

| Option | Description | Selected |
|--------|-------------|----------|
| Yes, include related data | User name, asset tag, department name. More readable | ✓ |
| No, main fields only | Only export AuditLog fields. Simpler | |

**User's choice:** Yes, include related data (Recommended)

---

## Compliance Dashboard

| Option | Description | Selected |
|--------|-------------|----------|
| Custom page with widgets | Dedicated Filament page with audit-specific widgets | ✓ |
| Modify existing dashboard | Add audit widgets to main admin dashboard | |

**User's choice:** Custom page with widgets (Recommended)
**Notes:** Clean separation of concerns

---

| Option | Description | Selected |
|--------|-------------|----------|
| All 3 metrics | Coverage %, gaps table, anomalies list. Matches AUDIT-02 | ✓ |
| Coverage + gaps only | Anomalies detected but not shown prominently | |
| You decide | Let Claude decide | |

**User's choice:** All 3 metrics (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Chart + tables | Coverage % as chart, gaps/anomalies as tables. Best readability | ✓ |
| Tables only | All metrics as tables. Simpler but less visual | |
| Charts only | All metrics as charts/graphs. Visual but may lose detail | |

**User's choice:** Chart + tables (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Yes, drilldown to report | Click metric → filtered audit report. Great for investigating | ✓ |
| No drilldown | Metrics are display-only. User navigates manually | |

**User's choice:** Yes, drilldown to report (Recommended)

---

## Access Control

| Option | Description | Selected |
|--------|-------------|----------|
| Admins only | Only admins with appropriate Shield permissions | ✓ |
| Admins + Dept Heads | Department heads can view their department's audit logs | |
| You decide | Let Claude decide | |

**User's choice:** Admins only (Recommended)
**Notes:** Matches checkpoint decision (Admin panel only)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Admins only | Only admins with export permissions. Consistent with view | ✓ |
| Same as view permissions | Department heads can export (if they can view) | |

**User's choice:** Admins only (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Yes, auditor read-only role | View-only role for auditors. Requires new Shield role | |
| No special auditor role | Admins handle all audit viewing/exporting | ✓ |

**User's choice:** No special auditor role (Recommended)

---

## Anomaly Detection Rules

| Option | Description | Selected |
|--------|-------------|----------|
| Frequency + timing + bulk | Unusual frequency, off-hours, bulk changes. Covers common audit anomalies | ✓ |
| Frequency only | Unusual action frequency only. Simpler detection | |
| You decide | Let Claude decide | |

**User's choice:** Frequency + timing + bulk (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Separate table on dashboard | Clear separation from normal audit logs | ✓ |
| Highlighted rows in main table | Less navigation but may clutter | |

**User's choice:** Separate table on dashboard (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Automatic on page load | Scan runs automatically when dashboard loads. Always up-to-date | ✓ |
| Manual button click | User clicks 'Scan for Anomalies'. More control | |

**User's choice:** Automatic on page load (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Default thresholds | >10 actions/hour, 10PM-6AM, >5 bulk changes. Balanced | |
| Configurable thresholds | Lets user adjust thresholds in settings. More flexible | ✓ |
| You decide | Let Claude decide | |

**User's choice:** Configurable thresholds

---

## Log Retention Policy

| Option | Description | Selected |
|--------|-------------|----------|
| 7 years + archival | Keep logs for 7 years. Archive to separate table after 2 years | ✓ |
| Indefinite retention | Keep logs forever. No deletion, but table grows | |
| You decide | Let Claude decide | |

**User's choice:** 7 years + archival (Recommended)
**Notes:** University compliance standard

---

| Option | Description | Selected |
|--------|-------------|----------|
| Separate archive table | Move logs >2 years to audit_logs_archive table. Keeps main table fast | ✓ |
| Export then delete | Export old logs to CSV/Excel then delete. Free up space | |

**User's choice:** Separate archive table (Recommended)

---

| Option | Description | Selected |
|--------|-------------|----------|
| Yes, 30-day warning | Email warning before auto-archival. Gives admins time to export | |
| No warning | Auto-archive runs silently | ✓ |

**User's choice:** No warning

---

| Option | Description | Selected |
|--------|-------------|----------|
| Yes, archive resource | Separate AuditLogArchiveResource for viewing old logs. Read-only | ✓ |
| No UI for archived logs | Archived logs moved to archive table but not accessible via UI | |

**User's choice:** Yes, archive resource (Recommended)

---

## Claude's Discretion

- Layout details for compliance dashboard widgets (chart types, table columns)
- Specific threshold default values (can use recommended: 10/hour, 10PM-6AM, 5 bulk)
- Export file naming conventions

## Deferred Ideas

None — discussion stayed within phase scope.
