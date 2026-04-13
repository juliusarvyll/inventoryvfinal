<?php

namespace App\Filament\Imports;

use App\Actions\Inventory\BulkCreateAssetsForModel;
use App\Enums\InventoryCategoryType;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Manufacturer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Illuminate\Support\Number;

class AssetModelImporter extends Importer
{
    protected static ?string $model = AssetModel::class;

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
            ImportColumn::make('name')
                ->label('Model Name')
                ->guess(['Name', 'Model Name', 'Asset Model'])
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('manufacturer')
                ->label('Manufacturer')
                ->guess(['Manufacturer', 'Brand'])
                ->helperText('If blank, the importer defaults to Imported.')
                ->fillRecordUsing(fn (): null => null)
                ->rules(['required', 'max:255']),
            ImportColumn::make('category')
                ->label('Category')
                ->guess(['Category'])
                ->helperText('Required. Categories are created when needed.')
                ->fillRecordUsing(fn (): null => null)
                ->rules(['required', 'max:255']),
            ImportColumn::make('model_number')
                ->label('Model Number')
                ->guess(['Model Number', 'Model No', 'Model #'])
                ->ignoreBlankState()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('serial_numbers')
                ->label('Serial Numbers')
                ->guess(['Serial Numbers', 'Serials', 'Serial Numbers (comma separated)'])
                ->helperText('Optional. Separate serial numbers with commas or new lines.')
                ->fillRecordUsing(fn (): null => null)
                ->ignoreBlankState()
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): AssetModel
    {
        $this->prepareData();

        $category = $this->resolveCategory();

        if (filled($this->data['model_number'] ?? null)) {
            $existingRecord = AssetModel::query()
                ->where('category_id', $category->getKey())
                ->where('model_number', $this->data['model_number'])
                ->first();

            if ($existingRecord) {
                return $existingRecord;
            }
        }

        return AssetModel::query()->firstOrNew([
            'category_id' => $category->getKey(),
            'name' => $this->data['name'],
        ]);
    }

    public function getValidationMessages(): array
    {
        return [
            'name.required' => 'The asset model name column is required. This row will be skipped.',
            'manufacturer.required' => 'The manufacturer could not be determined. This row will be skipped.',
            'category.required' => 'The category could not be determined. This row will be skipped.',
        ];
    }

    protected function beforeSave(): void
    {
        $this->record->category()->associate($this->resolveCategory());
        $this->record->manufacturer()->associate($this->resolveManufacturer());
    }

    protected function afterSave(): void
    {
        app(BulkCreateAssetsForModel::class)(
            $this->record,
            $this->normalizeText($this->data['serial_numbers'] ?? null),
        );
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your asset model import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Import completed with warnings: ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' were skipped because of missing or invalid data.';
        }

        return $body;
    }

    protected function prepareData(): void
    {
        $this->data['name'] = $this->normalizeText($this->data['name'] ?? null);
        $this->data['category'] = $this->normalizeText($this->data['category'] ?? null)
            ?: $this->resolveDefaultCategory()?->name;
        $this->data['manufacturer'] = $this->normalizeText($this->data['manufacturer'] ?? null) ?: 'Imported';
        $this->data['model_number'] = $this->normalizeText($this->data['model_number'] ?? null);
        $this->data['serial_numbers'] = $this->normalizeText($this->data['serial_numbers'] ?? null);
    }

    protected function resolveCategory(): Category
    {
        return Category::query()->firstOrCreate([
            'name' => $this->data['category'],
            'type' => InventoryCategoryType::Asset,
        ]);
    }

    protected function resolveManufacturer(): Manufacturer
    {
        return Manufacturer::query()->firstOrCreate([
            'name' => $this->data['manufacturer'],
        ]);
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
