<?php

namespace App\Filament\Imports;

use App\Enums\InventoryCategoryType;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\StatusLabel;
use App\Models\Supplier;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Carbon\CarbonImmutable;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AssetImporter extends Importer
{
    protected static ?string $model = Asset::class;

    /**
     * @var array<int, string>
     */
    protected array $rowWarnings = [];

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
            ImportColumn::make('category')
                ->label('Category')
                ->guess(['Category'])
                ->relationship(resolveUsing: fn (string $state): Category => Category::query()->firstOrCreate([
                    'name' => $state,
                    'type' => InventoryCategoryType::Asset,
                ]))
                ->helperText('If blank, the importer infers it from the asset name.')
                ->validationAttribute('category')
                ->rules(['required']),
            ImportColumn::make('statusLabel')
                ->label('Status Label')
                ->guess(['Status Label'])
                ->relationship(resolveUsing: fn (string $state): StatusLabel => StatusLabel::query()->firstOrCreate(
                    ['name' => $state],
                    ['type' => 'deployable']
                ))
                ->helperText('If blank, the importer defaults to Available.')
                ->validationAttribute('status label')
                ->rules(['required']),
            ImportColumn::make('supplier')
                ->label('Supplier')
                ->guess(['Supplier'])
                ->relationship(resolveUsing: fn (string $state): Supplier => Supplier::query()->firstOrCreate([
                    'name' => $state,
                ]))
                ->ignoreBlankState()
                ->helperText('Optional. New suppliers are created when needed.'),
            ImportColumn::make('location')
                ->label('Location')
                ->guess(['Location', 'Location/Room'])
                ->relationship(resolveUsing: fn (string $state): Location => Location::query()->firstOrCreate([
                    'name' => $state,
                ]))
                ->ignoreBlankState()
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
            return Asset::firstOrNew([
                'asset_tag' => $this->data['asset_tag'],
            ]);
        }

        if (filled($this->data['serial'])) {
            $existingRecord = Asset::query()
                ->where('serial', $this->data['serial'])
                ->first();

            if ($existingRecord) {
                return $existingRecord;
            }
        }

        return new Asset([
            'asset_tag' => $this->data['asset_tag'],
        ]);
    }

    public function getValidationMessages(): array
    {
        return [
            'name.required' => 'The asset name column is required. This row will be skipped.',
            'assetModel.required' => 'The asset model could not be determined. This row will be skipped.',
            'category.required' => 'The category could not be determined. This row will be skipped.',
            'statusLabel.required' => 'The status label could not be determined. This row will be skipped.',
            'requestable.required' => 'The requestable value could not be determined. This row will be skipped.',
        ];
    }

    protected function beforeSave(): void
    {
        $category = Category::query()->firstOrCreate([
            'name' => $this->data['category'],
            'type' => InventoryCategoryType::Asset,
        ]);

        $this->record->category()->associate($category);

        $statusLabel = StatusLabel::query()->firstOrCreate(
            ['name' => $this->data['statusLabel']],
            ['type' => 'deployable']
        );

        $this->record->statusLabel()->associate($statusLabel);

        if (filled($this->data['supplier'] ?? null)) {
            $supplier = Supplier::query()->firstOrCreate([
                'name' => $this->data['supplier'],
            ]);

            $this->record->supplier()->associate($supplier);
        } else {
            $this->record->supplier()->dissociate();
        }

        if (filled($this->data['location'] ?? null)) {
            $location = Location::query()->firstOrCreate([
                'name' => $this->data['location'],
            ]);

            $this->record->location()->associate($location);
        } else {
            $this->record->location()->dissociate();
        }

        if (! $category) {
            throw ValidationException::withMessages([
                'category' => 'A valid asset category is required. This row will be skipped.',
            ]);
        }

        $this->record->assetModel()->associate($this->resolveAssetModel($category));
        $this->record->notes = $this->buildNotes();

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

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your asset import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Import completed with warnings: ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' were skipped because of missing or invalid data.';
        }

        return $body;
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

        if (blank($this->data['category'] ?? null)) {
            if ($defaultCategory = $this->resolveDefaultCategory()) {
                $this->data['category'] = $defaultCategory->name;
                $this->rowWarnings[] = 'category defaulted from the import option';
            } else {
                $this->data['category'] = $this->data['name'];
                $this->rowWarnings[] = 'category was inferred from the asset name';
            }
        }

        if (blank($this->data['assetModel'] ?? null)) {
            $this->data['assetModel'] = $description ?: $this->data['name'];
            $this->rowWarnings[] = 'asset model was inferred from the description or asset name';
        }

        if (blank($this->data['statusLabel'] ?? null)) {
            $this->data['statusLabel'] = $this->inferStatusLabel($remarks);
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
                $column => "Could not parse date [{$display}]. Try a format like 2026-04-15, 15/04/2026, or April 15 2026.",
            ]);
        }

        $this->data[$column] = $parsed;
    }

    /**
     * @return non-empty-string|null  Y-m-d or null when $value is empty
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

        if (abs($value - $whole) > 0.00001) {
            return null;
        }

        // Typical Excel calendar serial range for real-world asset dates (~1970–2190).
        if ($whole >= 25_567 && $whole <= 80_000) {
            try {
                $dateTime = ExcelDate::excelToDateTimeObject($whole);

                return CarbonImmutable::instance($dateTime)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function resolveAssetModel(Category $category): AssetModel
    {
        $state = Str::limit($this->normalizeText($this->data['assetModel'] ?? null) ?: $this->record->name, 255, '');

        $existingRecord = AssetModel::query()
            ->where('category_id', $category->getKey())
            ->where(function ($query) use ($state): void {
                $query->where('name', $state)
                    ->orWhere('model_number', $state);
            })
            ->first();

        if ($existingRecord) {
            return $existingRecord;
        }

        return AssetModel::query()->create([
            'name' => $state,
            'manufacturer_id' => $this->resolveImportedManufacturer()->getKey(),
            'category_id' => $category->getKey(),
            'model_number' => null,
        ]);
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
                ? 'Imported description/specification: ' . $this->normalizeText($this->data['description_specification'])
                : null,
            $this->normalizeText($this->data['remarks'] ?? null)
                ? 'Imported remarks: ' . $this->normalizeText($this->data['remarks'])
                : null,
            filled($this->data['qty'] ?? null)
                ? 'Imported quantity: ' . $this->data['qty'] . (filled($this->data['unit'] ?? null) ? ' ' . $this->data['unit'] : '')
                : null,
            filled($this->rowWarnings)
                ? 'Import warnings: ' . implode('; ', $this->rowWarnings) . '.'
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

        $normalizedBase = Str::upper(Str::of($base)->replaceMatches('/[^A-Za-z0-9]+/', '')->substr(0, 12));
        $assetTag = 'IMP-' . ($normalizedBase ?: Str::upper(Str::random(12)));

        if (! Asset::query()->where('asset_tag', $assetTag)->exists()) {
            return $assetTag;
        }

        do {
            $assetTag = 'IMP-' . Str::upper(Str::random(12));
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
