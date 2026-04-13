<?php

namespace App\Filament\Imports;

use App\Enums\InventoryCategoryType;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Supplier;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Illuminate\Support\Number;

class ConsumableImporter extends Importer
{
    protected static ?string $model = Consumable::class;

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('default_category_id')
                ->label('Default Category')
                ->options(fn (): array => Category::query()
                    ->ofType(InventoryCategoryType::Consumable)
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
                ->label('Consumable Name')
                ->guess(['Name', 'Consumable Name'])
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('category')
                ->label('Category')
                ->guess(['Category'])
                ->fillRecordUsing(fn (): null => null)
                ->rules(['required', 'max:255']),
            ImportColumn::make('supplier')
                ->label('Supplier')
                ->guess(['Supplier'])
                ->fillRecordUsing(fn (): null => null)
                ->ignoreBlankState()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('location')
                ->label('Location')
                ->guess(['Location', 'Location/Room'])
                ->fillRecordUsing(fn (): null => null)
                ->ignoreBlankState()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('qty')
                ->label('Quantity')
                ->guess(['Qty', 'Quantity'])
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('min_qty')
                ->label('Minimum Quantity')
                ->guess(['Min Qty', 'Minimum Quantity', 'Minimum Qty'])
                ->numeric()
                ->helperText('Optional. Defaults to 0.')
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('model_number')
                ->label('Model Number')
                ->guess(['Model Number', 'Model No', 'Model #'])
                ->ignoreBlankState()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('item_no')
                ->label('Item Number')
                ->guess(['Item No', 'Item Number', 'SKU'])
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
                ->guess(['Purchase Date'])
                ->ignoreBlankState()
                ->rules(['nullable', 'date']),
            ImportColumn::make('order_number')
                ->label('Order Number')
                ->guess(['Order Number', 'PO Number'])
                ->ignoreBlankState()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('requestable')
                ->label('Requestable')
                ->guess(['Requestable'])
                ->boolean()
                ->helperText('Optional. Defaults to No.')
                ->rules(['required', 'boolean']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->guess(['Notes'])
                ->ignoreBlankState(),
        ];
    }

    public function resolveRecord(): Consumable
    {
        $this->prepareData();

        $category = $this->resolveCategory();

        if (filled($this->data['item_no'] ?? null)) {
            $existingRecord = Consumable::query()
                ->where('item_no', $this->data['item_no'])
                ->first();

            if ($existingRecord) {
                return $existingRecord;
            }
        }

        $query = Consumable::query()
            ->where('name', $this->data['name'])
            ->where('category_id', $category->getKey());

        if (filled($this->data['model_number'] ?? null)) {
            $query->where('model_number', $this->data['model_number']);
        }

        $existingRecord = $query->first();

        if ($existingRecord) {
            return $existingRecord;
        }

        return new Consumable();
    }

    public function getValidationMessages(): array
    {
        return [
            'name.required' => 'The consumable name column is required. This row will be skipped.',
            'category.required' => 'The category could not be determined. This row will be skipped.',
            'qty.required' => 'The quantity column is required. This row will be skipped.',
            'min_qty.required' => 'The minimum quantity could not be determined. This row will be skipped.',
            'requestable.required' => 'The requestable value could not be determined. This row will be skipped.',
        ];
    }

    protected function beforeSave(): void
    {
        $this->record->category()->associate($this->resolveCategory());

        if (filled($this->data['supplier'] ?? null)) {
            $this->record->supplier()->associate($this->resolveSupplier());
        } else {
            $this->record->supplier()->dissociate();
        }

        if (filled($this->data['location'] ?? null)) {
            $this->record->location()->associate($this->resolveLocation());
        } else {
            $this->record->location()->dissociate();
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your consumable import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

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
        $this->data['supplier'] = $this->normalizeText($this->data['supplier'] ?? null);
        $this->data['location'] = $this->normalizeText($this->data['location'] ?? null);
        $this->data['model_number'] = $this->normalizeText($this->data['model_number'] ?? null);
        $this->data['item_no'] = $this->normalizeText($this->data['item_no'] ?? null);
        $this->data['order_number'] = $this->normalizeText($this->data['order_number'] ?? null);
        $this->data['notes'] = $this->normalizeText($this->data['notes'] ?? null);

        if (! array_key_exists('min_qty', $this->data) || $this->data['min_qty'] === null) {
            $this->data['min_qty'] = 0;
        }

        if (! array_key_exists('requestable', $this->data) || $this->data['requestable'] === null) {
            $this->data['requestable'] = false;
        }
    }

    protected function resolveCategory(): Category
    {
        return Category::query()->firstOrCreate([
            'name' => $this->data['category'],
            'type' => InventoryCategoryType::Consumable,
        ]);
    }

    protected function resolveSupplier(): Supplier
    {
        return Supplier::query()->firstOrCreate([
            'name' => $this->data['supplier'],
        ]);
    }

    protected function resolveLocation(): Location
    {
        return Location::query()->firstOrCreate([
            'name' => $this->data['location'],
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
            ->ofType(InventoryCategoryType::Consumable)
            ->find($categoryId);
    }
}
