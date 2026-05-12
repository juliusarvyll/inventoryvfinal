<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\InventoryCategoryType;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Department;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Scopes\DepartmentScope;
use App\Models\StatusLabel;
use App\Models\Supplier;
use Carbon\CarbonImmutable;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AssetImporter extends Importer
{
    protected static ?string $model = Asset::class;

    private const ASSET_TAG_PREFIX = 'IMP-';

    private const ASSET_TAG_MAX_LENGTH = 12;

    private const MAX_FILE_SIZE_KB = 5120;

    private const EXCEL_DATE_MIN = 25567;

    private const EXCEL_DATE_MAX = 80000;

    private const MAX_FLOAT_PRECISION = 0.00001;

    /**
     * @var list<string>
     */
    protected const IMPORT_ONLY_COLUMNS = [
        'assetModel',
        'description_specification',
        'import_category',
        'import_location',
        'import_status_label',
        'import_supplier',
        'qty',
        'remarks',
        'unit',
    ];

    /**
     * @var array<int, string>
     */
    protected array $rowWarnings = [];

    /**
     * @var array<string, int>
     */
    protected array $createdCounts = [];

    /**
     * @var array<string, int>
     */
    protected array $reusedCounts = [];

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('default_category_id')
                ->label('Default Category')
                ->options(fn (): array => Category::query()
                    ->ofType(InventoryCategoryType::Asset)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable()
                ->preload()
                ->helperText('Optional. Used when a CSV row does not include a category.'),
            Select::make('import_department_id')
                ->label('Import Department')
                ->options(fn (): array => Department::query()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable()
                ->preload()
                ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                ->helperText('Super admins can select the department for imported assets. Others use their assigned department.'),
        ];
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('asset_tag')
                ->label('Asset Tag')
                ->guess(['Asset Tag', 'Asset Tag No', 'Property No'])
                ->helperText('Optional for legacy files. If blank, a tag is generated.')
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('name')
                ->label('Asset Name')
                ->guess(['Name of Equipment', 'Asset Name'])
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('assetModel')
                ->label('Asset Model')
                ->guess(['Asset Model', 'Description/Specification', 'Description Specification'])
                ->helperText('If blank, the description or asset name is used.')
                ->validationAttribute('asset model')
                ->requiredMappingForNewRecordsOnly()
                ->fillRecordUsing(fn (): null => null)
                ->rules(['required', 'max:255']),
            ImportColumn::make('import_category')
                ->label('Category')
                ->guess(['Category'])
                ->helperText('If blank, the importer infers it from the asset name.')
                ->validationAttribute('category')
                ->rules(['required'])
                ->fillRecordUsing(fn (): null => null),
            ImportColumn::make('import_status_label')
                ->label('Status Label')
                ->guess(['Status Label'])
                ->helperText('If blank, the importer defaults to Available.')
                ->validationAttribute('status label')
                ->rules(['required'])
                ->fillRecordUsing(fn (): null => null),
            ImportColumn::make('import_supplier')
                ->label('Supplier')
                ->guess(['Supplier'])
                ->ignoreBlankState()
                ->fillRecordUsing(fn (): null => null)
                ->helperText('Optional. New suppliers are created when needed.'),
            ImportColumn::make('import_location')
                ->label('Location')
                ->guess(['Location', 'Location/Room'])
                ->ignoreBlankState()
                ->fillRecordUsing(fn (): null => null)
                ->helperText('Optional. New locations are created when needed.'),
            ImportColumn::make('serial')
                ->label('Serial')
                ->guess(['Serial', 'Serial No.', 'Serial No', 'Serial Number'])
                ->ignoreBlankState()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('purchase_cost')
                ->label('Purchase Cost')
                ->guess(['Purchase Cost'])
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('purchase_date')
                ->label('Purchase Date')
                ->guess(['Purchase Date', 'Date Delivered'])
                ->ignoreBlankState()
                ->helperText('Accepts many formats (e.g. YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, month names, Excel serial numbers).')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('warranty_expires')
                ->label('Warranty Expires')
                ->guess(['Warranty Expires'])
                ->ignoreBlankState()
                ->helperText('Accepts many formats (e.g. YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, month names, Excel serial numbers).')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('eol_date')
                ->label('EOL Date')
                ->guess(['EOL Date'])
                ->ignoreBlankState()
                ->helperText('Accepts many formats (e.g. YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, month names, Excel serial numbers).')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->guess(['Notes'])
                ->ignoreBlankState(),
            ImportColumn::make('requestable')
                ->label('Requestable')
                ->guess(['Requestable'])
                ->boolean()
                ->helperText('Optional for legacy files. Defaults to No.')
                ->rules(['required', 'boolean']),
            ImportColumn::make('description_specification')
                ->label('Description / Specification')
                ->guess(['Description/Specification', 'Description Specification'])
                ->ignoreBlankState()
                ->fillRecordUsing(fn (): null => null)
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('remarks')
                ->label('Remarks')
                ->guess(['Remarks'])
                ->ignoreBlankState()
                ->fillRecordUsing(fn (): null => null)
                ->rules(['nullable', 'max:65535']),
            ImportColumn::make('qty')
                ->label('Quantity')
                ->guess(['Qty', 'Quantity'])
                ->ignoreBlankState()
                ->fillRecordUsing(fn (): null => null)
                ->rules(['nullable', 'integer', 'min:1']),
            ImportColumn::make('unit')
                ->label('Unit')
                ->guess(['Unit'])
                ->ignoreBlankState()
                ->fillRecordUsing(fn (): null => null)
                ->rules(['nullable', 'max:255']),
        ];
    }

    public function resolveRecord(): Asset
    {
        $this->prepareData();

        if (filled($this->data['asset_tag'])) {
            $existing = Asset::query()
                ->where('asset_tag', $this->data['asset_tag'])
                ->first();

            if ($existing) {
                $this->reusedCounts['assets'] = ($this->reusedCounts['assets'] ?? 0) + 1;

                return $existing;
            }
        }

        if (filled($this->data['serial'])) {
            $existingRecord = Asset::query()
                ->where('serial', $this->data['serial'])
                ->first();

            if ($existingRecord) {
                $this->reusedCounts['assets'] = ($this->reusedCounts['assets'] ?? 0) + 1;

                return $existingRecord;
            }
        }

        $this->createdCounts['assets'] = ($this->createdCounts['assets'] ?? 0) + 1;

        return new Asset([
            'asset_tag' => $this->data['asset_tag'],
        ]);
    }

    public function getValidationMessages(): array
    {
        return [
            'name.required' => 'Asset name is required. Check the "Name of Equipment" or "Asset Name" column.',
            'assetModel.required' => 'Asset model could not be determined. Provide "Asset Model" or "Description/Specification" column.',
            'import_category.required' => 'Category could not be determined. Provide a "Category" column or set a default category in import options.',
            'import_status_label.required' => 'Status label could not be determined. Provide a "Status Label" column.',
            'requestable.required' => 'Requestable value must be Yes/No or true/false.',
            'serial.duplicate' => 'Serial number already exists in the system. Each serial must be unique.',
            'purchase_date.invalid' => 'Invalid purchase date format. Use YYYY-MM-DD, DD/MM/YYYY, or MM/DD/YYYY.',
            'warranty_expires.invalid' => 'Invalid warranty date format. Use YYYY-MM-DD, DD/MM/YYYY, or MM/DD/YYYY.',
            'eol_date.invalid' => 'Invalid EOL date format. Use YYYY-MM-DD, DD/MM/YYYY, or MM/DD/YYYY.',
        ];
    }

    protected function beforeSave(): void
    {
        $department = $this->resolveDepartment();
        $this->record->department()->associate($department);

        $categoryName = $this->data['import_category'] ?? '';
        $category = Category::query()
            ->withoutGlobalScopes([DepartmentScope::class])
            ->where('type', InventoryCategoryType::Asset)
            ->whereRaw('LOWER(name) = LOWER(?)', [$categoryName])
            ->first();

        if (! $category) {
            $category = Category::create([
                'name' => $categoryName,
                'type' => InventoryCategoryType::Asset,
            ]);
            $this->createdCounts['categories'] = ($this->createdCounts['categories'] ?? 0) + 1;
        } else {
            $this->reusedCounts['categories'] = ($this->reusedCounts['categories'] ?? 0) + 1;
        }

        $this->record->category()->associate($category);

        $statusLabelName = $this->data['import_status_label'] ?? '';
        $statusLabel = StatusLabel::query()
            ->withoutGlobalScopes([DepartmentScope::class])
            ->whereRaw('LOWER(name) = LOWER(?)', [$statusLabelName])
            ->where('type', 'deployable')
            ->first();

        if (! $statusLabel) {
            $statusLabel = StatusLabel::create([
                'name' => $statusLabelName,
                'type' => 'deployable',
            ]);
            $this->createdCounts['status_labels'] = ($this->createdCounts['status_labels'] ?? 0) + 1;
        } else {
            $this->reusedCounts['status_labels'] = ($this->reusedCounts['status_labels'] ?? 0) + 1;
        }

        $this->record->statusLabel()->associate($statusLabel);

        if (filled($this->data['import_supplier'] ?? null)) {
            $supplierName = $this->data['import_supplier'];
            $supplier = Supplier::query()
                ->withoutGlobalScopes([DepartmentScope::class])
                ->whereRaw('LOWER(name) = LOWER(?)', [$supplierName])
                ->first();

            if (! $supplier) {
                $supplier = Supplier::create([
                    'name' => $supplierName,
                    'department_id' => $department->getKey(),
                ]);
                $this->createdCounts['suppliers'] = ($this->createdCounts['suppliers'] ?? 0) + 1;
            } else {
                if (! $supplier->department_id) {
                    $supplier->department_id = $department->getKey();
                    $supplier->save();
                }
                $this->reusedCounts['suppliers'] = ($this->reusedCounts['suppliers'] ?? 0) + 1;
            }

            $this->record->supplier()->associate($supplier);
        }

        if (filled($this->data['import_location'] ?? null)) {
            $locationName = $this->data['import_location'];
            $location = Location::query()
                ->withoutGlobalScopes([DepartmentScope::class])
                ->whereRaw('LOWER(name) = LOWER(?)', [$locationName])
                ->first();

            if (! $location) {
                $location = Location::create([
                    'name' => $locationName,
                    'department_id' => $department->getKey(),
                ]);
                $this->createdCounts['locations'] = ($this->createdCounts['locations'] ?? 0) + 1;
            } else {
                if (! $location->department_id) {
                    $location->department_id = $department->getKey();
                    $location->save();
                }
                $this->reusedCounts['locations'] = ($this->reusedCounts['locations'] ?? 0) + 1;
            }

            $this->record->location()->associate($location);
        }

        if (! $category) {
            throw ValidationException::withMessages([
                'import_category' => 'A valid asset category is required. This row will be skipped.',
            ]);
        }

        $this->record->assetModel()->associate($this->resolveAssetModel($category, $department));
        $this->record->notes = $this->buildNotes();
        $this->removeImportOnlyAttributesFromRecord();

        if (
            filled($this->record->serial)
            && tap(Asset::query()->where('serial', $this->record->serial), function ($query): void {
                if ($this->record->exists) {
                    $query->whereKeyNot($this->record->getKey());
                }
            })->exists()
        ) {
            throw new RowImportFailedException("The serial [{$this->record->serial}] is already assigned to another asset.");
        }
    }

    protected function removeImportOnlyAttributesFromRecord(): void
    {
        foreach (self::IMPORT_ONLY_COLUMNS as $column) {
            unset($this->record->{$column});
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your asset import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        $failedRowsCount = $import->getFailedRowsCount();

        if ($failedRowsCount > 0) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' were skipped due to validation errors.';
            $body .= ' Common issues: missing required fields (name, model, category), duplicate serial numbers, or invalid date formats.';
            $body .= ' Download the failed rows file to see specific error messages for each row.';
        }

        $data = $import->data ?? [];
        $created = $data['created_counts'] ?? [];
        $reused = $data['reused_counts'] ?? [];

        if (! empty($created) || ! empty($reused)) {
            $body .= ' Summary:';

            foreach (['assets', 'categories', 'status_labels', 'suppliers', 'locations', 'departments'] as $key) {
                $createdCount = $created[$key] ?? 0;
                $reusedCount = $reused[$key] ?? 0;

                if ($createdCount > 0 || $reusedCount > 0) {
                    $label = str($key)->replace('_', ' ')->title();
                    $parts = [];

                    if ($createdCount > 0) {
                        $parts[] = "{$createdCount} new";
                    }
                    if ($reusedCount > 0) {
                        $parts[] = "{$reusedCount} existing";
                    }

                    $body .= " {$label}: ".implode(', ', $parts).'.';
                }
            }
        }

        return $body;
    }

    public function afterSave(): void
    {
        $import = $this->getImport();
        if ($import) {
            $data = $import->data ?? [];
            $data['created_counts'] = $this->createdCounts;
            $data['reused_counts'] = $this->reusedCounts;
            $import->data = $data;
            $import->save();
        }
    }

    protected function prepareData(): void
    {
        $this->rowWarnings = [];

        $description = $this->normalizeText($this->data['description_specification'] ?? null);
        $remarks = $this->normalizeText($this->data['remarks'] ?? null);
        $serial = $this->normalizeText($this->data['serial'] ?? null);
        $name = $this->normalizeText($this->data['name'] ?? null);

        if (blank($name)) {
            $name = $description ?: ($serial ? "Imported Asset {$serial}" : 'Imported Asset');
            $this->rowWarnings[] = 'asset name was generated from the available row data';
        }

        $this->data['name'] = Str::limit($name, 255, '');

        if (blank($this->data['import_category'] ?? null)) {
            if ($defaultCategory = $this->resolveDefaultCategory()) {
                $this->data['import_category'] = $defaultCategory->name;
                $this->rowWarnings[] = 'category defaulted from the import option';
            } else {
                $this->data['import_category'] = $this->data['name'];
                $this->rowWarnings[] = 'category was inferred from the asset name';
            }
        }

        if (blank($this->data['assetModel'] ?? null)) {
            $this->data['assetModel'] = $description ?: $this->data['name'];
            $this->rowWarnings[] = 'asset model was inferred from the description or asset name';
        }

        if (blank($this->data['import_status_label'] ?? null)) {
            $this->data['import_status_label'] = $this->inferStatusLabel($remarks);
            $this->rowWarnings[] = 'status label defaulted from the row remarks';
        }

        if (blank($this->data['asset_tag'] ?? null)) {
            $this->data['asset_tag'] = $this->generateAssetTag();
            $this->rowWarnings[] = 'asset tag was generated because none was provided';
        }

        if (! array_key_exists('requestable', $this->data) || $this->data['requestable'] === null) {
            $this->data['requestable'] = false;
            $this->rowWarnings[] = 'requestable defaulted to No';
        }

        foreach (['purchase_date', 'warranty_expires', 'eol_date'] as $dateKey) {
            $this->normalizeMappedDateColumn($dateKey);
        }
    }

    /**
     * Parses a single cell value into Y-m-d for Eloquent date casting, or null when blank.
     *
     * @throws ValidationException when a non-empty value cannot be interpreted as a calendar date
     */
    protected function normalizeMappedDateColumn(string $column): void
    {
        if (! array_key_exists($column, $this->data)) {
            return;
        }

        $raw = $this->data[$column];

        if ($raw === null || $raw === '') {
            $this->data[$column] = null;

            return;
        }

        $parsed = $this->parseFlexibleImportDate($raw);

        if ($parsed === null) {
            $display = is_scalar($raw) ? (string) $raw : json_encode($raw);

            throw ValidationException::withMessages([
                $column => "Could not parse date [{$display}]. Try a format like 2026-04-15, 15/04/2026, or April 15, 2026.",
            ]);
        }

        $this->data[$column] = $parsed;
    }

    /**
     * @return non-empty-string|null Y-m-d or null when $value is empty
     */
    protected function parseFlexibleImportDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            $parsed = $this->parseExcelOrTimestampDate((float) $value);

            if ($parsed !== null) {
                return $parsed;
            }
        }

        $string = $this->normalizeText(is_scalar($value) ? (string) $value : null);

        if ($string === null) {
            return null;
        }

        if (is_numeric($string)) {
            $parsed = $this->parseExcelOrTimestampDate((float) $string);

            if ($parsed !== null) {
                return $parsed;
            }
        }

        $formats = [
            '!Y-m-d',
            '!Y-m-d H:i:s',
            '!d/m/Y',
            '!m/d/Y',
            '!d-m-Y',
            '!m-d-Y',
            '!d.m.Y',
            '!m.d.Y',
            '!Y/m/d',
            '!Ymd',
            '!j F Y',
            '!F j, Y',
            '!M j, Y',
            '!j M Y',
        ];

        foreach ($formats as $format) {
            try {
                $parsed = CarbonImmutable::createFromFormat($format, $string);
            } catch (\Throwable) {
                continue;
            }

            if ($parsed !== false) {
                return $parsed->toDateString();
            }
        }

        try {
            return CarbonImmutable::parse($string)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Excel stores dates as serial days (often exported as plain numbers in CSV).
     *
     * @return non-empty-string|null
     */
    protected function parseExcelOrTimestampDate(float $value): ?string
    {
        if ($value <= 0 || $value >= 1_000_000) {
            return null;
        }

        $whole = (int) round($value);

        if (abs($value - $whole) > self::MAX_FLOAT_PRECISION) {
            return null;
        }

        // Typical Excel calendar serial range for real-world asset dates (~1970–2190).
        if ($whole >= self::EXCEL_DATE_MIN && $whole <= self::EXCEL_DATE_MAX) {
            try {
                $dateTime = ExcelDate::excelToDateTimeObject($whole);

                return CarbonImmutable::instance($dateTime)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function resolveAssetModel(Category $category, Department $department): AssetModel
    {
        $state = Str::limit($this->normalizeText($this->data['assetModel'] ?? null) ?: $this->record->name, 255, '');

        $existingRecord = AssetModel::withoutGlobalScope(DepartmentScope::class)
            ->where('category_id', $category->getKey())
            ->where('department_id', $department->getKey())
            ->where(function ($query) use ($state): void {
                $query->where('name', $state)
                    ->orWhere('model_number', $state);
            })
            ->first();

        if ($existingRecord) {
            return $existingRecord;
        }

        return AssetModel::create([
            'name' => $state,
            'manufacturer_id' => $this->resolveImportedManufacturer()->getKey(),
            'category_id' => $category->getKey(),
            'department_id' => $department->getKey(),
            'model_number' => null,
        ]);
    }

    protected function resolveDepartment(): Department
    {
        $user = auth()->user();

        if ($user && $user->hasRole('super_admin') && filled($this->options['import_department_id'] ?? null)) {
            $department = Department::query()->find($this->options['import_department_id']);
            $this->reusedCounts['departments'] = ($this->reusedCounts['departments'] ?? 0) + 1;

            return $department;
        }

        if ($user && $user->primaryDepartment()) {
            $department = $user->primaryDepartment();
            $this->reusedCounts['departments'] = ($this->reusedCounts['departments'] ?? 0) + 1;

            return $department;
        }

        $department = Department::query()->firstOrCreate(['name' => 'Unassigned']);

        if ($department->wasRecentlyCreated) {
            $this->createdCounts['departments'] = ($this->createdCounts['departments'] ?? 0) + 1;
        } else {
            $this->reusedCounts['departments'] = ($this->reusedCounts['departments'] ?? 0) + 1;
        }

        return $department;
    }

    protected function resolveImportedManufacturer(): Manufacturer
    {
        return Manufacturer::query()->firstOrCreate([
            'name' => 'Imported',
        ]);
    }

    protected function buildNotes(): ?string
    {
        $notes = array_filter([
            $this->normalizeText($this->record->notes),
            $this->normalizeText($this->data['description_specification'] ?? null)
                ? 'Imported description/specification: '.$this->normalizeText($this->data['description_specification'])
                : null,
            $this->normalizeText($this->data['remarks'] ?? null)
                ? 'Imported remarks: '.$this->normalizeText($this->data['remarks'])
                : null,
            filled($this->data['qty'] ?? null)
                ? 'Imported quantity: '.$this->data['qty'].(filled($this->data['unit'] ?? null) ? ' '.$this->data['unit'] : '')
                : null,
            filled($this->rowWarnings)
                ? 'Import warnings: '.implode('; ', $this->rowWarnings).'.'
                : null,
        ]);

        return blank($notes) ? null : implode(PHP_EOL, $notes);
    }

    protected function inferStatusLabel(?string $remarks): string
    {
        $remarks = Str::lower($remarks ?? '');

        return match (true) {
            Str::contains($remarks, ['repair', 'broken', 'defective']) => 'In Repair',
            Str::contains($remarks, ['retired', 'obsolete']) => 'Retired',
            Str::contains($remarks, ['lost', 'stolen']) => 'Lost/Stolen',
            Str::contains($remarks, ['deployed', 'issued', 'assigned']) => 'Deployed',
            default => 'Available',
        };
    }

    protected function generateAssetTag(): string
    {
        $base = $this->normalizeText($this->data['serial'] ?? null)
            ?: $this->normalizeText($this->data['name'] ?? null)
            ?: Str::uuid()->toString();

        $normalizedBase = Str::upper(Str::of($base)->replaceMatches('/[^A-Za-z0-9]+/', '')->substr(0, self::ASSET_TAG_MAX_LENGTH));

        if ($normalizedBase) {
            $assetTag = self::ASSET_TAG_PREFIX.$normalizedBase;

            if (! Asset::query()->where('asset_tag', $assetTag)->exists()) {
                return $assetTag;
            }
        }

        do {
            $assetTag = self::ASSET_TAG_PREFIX.Str::upper(Str::random(self::ASSET_TAG_MAX_LENGTH));
        } while (Asset::query()->where('asset_tag', $assetTag)->exists());

        return $assetTag;
    }

    protected function normalizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = trim((string) $value);

        return $normalizedValue === '' ? null : $normalizedValue;
    }

    protected function resolveDefaultCategory(): ?Category
    {
        $categoryId = $this->options['default_category_id'] ?? null;

        if (blank($categoryId)) {
            return null;
        }

        return Category::query()
            ->ofType(InventoryCategoryType::Asset)
            ->find($categoryId);
    }
}
