# Technology Stack

**Project:** University Inventory Management System
**Researched:** 2026-05-07
**Overall confidence:** MEDIUM (version-specific claims unverified due to tool limitations; well-established packages have higher confidence)

## Recommended Stack

### Core Framework
| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| PHP | ^8.3 | Server-side language | Existing version, meets Laravel 12 requirements |
| Laravel | 12.52 | Application framework | Existing version, current LTS-compatible release |
| Filament | ~4.0 | Admin/Portal panels | Existing version, provides CRUD, dashboards, RBAC for inventory management |
| Inertia Laravel | ^3.0 | SPA bridge | Existing version, enables React frontend with Laravel backend |
| Fortify | ^1.34 | Authentication backend | Existing version, handles login, registration, password reset for university staff |

### Database
| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| MySQL | 8.0+ | Primary database | Existing, reliable for relational inventory data (assets, categories, users, checkouts) |
| SQLite | 3.x | Testing | Existing, for Pest PHP feature tests |

### Infrastructure
| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Laravel Herd | latest | Local development server | Existing, per project instructions, provides `.test` domain |
| Vite | ^8.0 | Frontend bundler | Existing, supports React, Tailwind, Inertia |
| Tailwind CSS | ^4.0 | Styling | Existing, utility-first CSS for responsive inventory UI |
| React | ^19.0 | Frontend framework | Existing, Inertia-compatible, component-based UI for inventory dashboards |

### Supporting Libraries (PHP)
| Library | Version | Purpose | When to Use | Confidence |
|---------|---------|---------|-------------|------------|
| spatie/laravel-activitylog | ^4.0 | Audit trails | Track all inventory actions: asset creation, checkout, modification, deletion. Critical for university compliance. | MEDIUM (Spatie packages are well-maintained; version unverified for Laravel 12) |
| spatie/laravel-medialibrary | ^11.0 | Media attachments | Attach asset photos, warranty PDFs, manuals to inventory items. Integrates with Filament for easy uploads. | MEDIUM (Standard media library; version unverified) |
| milwad/laravel-barcode | ^2.0 | Barcode/QR generation | Generate scannable asset tags (QR, Code128, EAN) for physical inventory items. Print labels from Filament. | MEDIUM (Popular barcode package; version unverified) |
| adldap2/adldap2-laravel | ^6.0 | LDAP/AD integration | Authenticate users via university Active Directory/LDAP. Eliminates separate inventory credentials. | MEDIUM (Standard LDAP package; version unverified) |
| maatwebsite/excel | ^3.1 | Excel exports | Existing. Export inventory reports, asset lists, checkouts to Excel for university administration. | HIGH (Already installed, verified in composer.json) |
| barryvdh/laravel-dompdf | ^3.1 | PDF generation | Existing. Generate PDF asset reports, checkout receipts. | HIGH (Already installed) |
| mpdf/mpdf | ^8.3 | PDF generation | Existing. Alternative PDF generator for complex layouts (asset labels, batch reports). | HIGH (Already installed) |
| bezhansalleh/filament-shield | ^4.2 | RBAC for Filament | Existing. Role-based access control for inventory panels (admin vs staff vs student access). | HIGH (Already installed) |
| nativephp/desktop | ^2.1 | Desktop app | Existing. Package inventory system as native desktop app for offline inventory counts. | HIGH (Already installed) |
| laravel/wayfinder | ^0.1.14 | TypeScript routes | Existing. Generate typed route functions for Inertia React frontend. | HIGH (Already installed) |

### Supporting Libraries (JavaScript)
| Library | Version | Purpose | When to Use | Confidence |
|---------|---------|---------|-------------|------------|
| @ericblade/quagga2 | ^1.8.0 | Barcode scanning | Scan asset barcodes/QR codes via webcam in React frontend. For inventory counts, check-ins. | LOW (Frontend packages change rapidly; version unverified) |
| react-qr-code | ^2.0 | QR code display | Generate QR code images in React components for on-screen asset tag display. | LOW (Version unverified) |
| @inertiajs/react | ^3.0 | Inertia React | Existing. Inertia bindings for React. | HIGH (Already installed) |
| @radix-ui/react-* | ^1.x | UI components | Existing. Accessible UI primitives for inventory forms, dialogs, dropdowns. | HIGH (Already installed) |
| lucide-react | ^0.475.0 | Icons | Existing. Icon set for inventory actions (scan, checkout, report). | HIGH (Already installed) |

## Alternatives Considered

| Category | Recommended | Alternative | Why Not |
|----------|-------------|-------------|---------|
| Audit Logging | spatie/laravel-activitylog | Custom model observers | Spatie's package provides batch logging, causer tracking, and subject linking out of the box. Custom would require rebuilding these features. |
| Media Attachments | spatie/laravel-medialibrary | laravel-mediable | Spatie's package has better Filament integration (dedicated plugin available) and more extensive documentation. |
| Barcode Generation | milwad/laravel-barcode | simplesoftwareio/simple-qrcode | Milwad supports 15+ barcode formats (QR, Code128, EAN, UPC) vs simple-qrcode only QR. Inventory systems need multiple formats. |
| LDAP Integration | adldap2/adldap2-laravel | Custom LDAP driver | Adldap2 handles complex AD scenarios (nested groups, multiple domains) that custom code would struggle with. |
| Frontend Scanning | @ericblade/quagga2 | react-qr-reader | Quagga2 supports more barcode formats beyond QR, which is essential for university assets that may use legacy barcodes. |

## Installation

```bash
# PHP packages (add to existing)
composer require spatie/laravel-activitylog spatie/laravel-medialibrary milwad/laravel-barcode adldap2/adldap2-laravel

# JavaScript packages (add to existing)
npm install @ericblade/quagga2 react-qr-code
```

## Sources

- Training data (6-18 months stale; confidence levels assigned accordingly)
- Existing project files: `/home/julius/inventoryvfinal/composer.json`, `/home/julius/inventoryvfinal/package.json`
- Spatie package documentation (inferred from historical knowledge)
- Milwad Laravel Barcode GitHub repository (inferred)
- Adldap2 documentation (inferred)

## Confidence Notes

- **HIGH confidence**: Existing packages already installed in project (verified via composer.json/package.json)
- **MEDIUM confidence**: Well-established PHP packages (Spatie, Milwad, Adldap2) with historical Laravel compatibility; versions unverified for Laravel 12
- **LOW confidence**: JavaScript frontend packages (rapid ecosystem changes); version numbers based on training data cutoff

## Gaps to Verify

1. Confirm spatie/laravel-activitylog ^4.0 supports Laravel 12 (check packagist.org)
2. Confirm milwad/laravel-barcode latest version supports Laravel 12
3. Verify @ericblade/quagga2 is maintained in 2026 (check npmjs.com)
4. Check if filament-shield requires explicit spatie/laravel-permission installation (currently indirect dependency)
