<?php

namespace App\Filament\Resources\PreventiveMaintenances\Schemas;

use App\Enums\InventoryCategoryType;
use App\Models\Category;
use App\Models\PreventiveMaintenance;
use App\Models\PreventiveMaintenanceItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PreventiveMaintenanceForm
{
    public const CHECKLIST_HELPER_TEXT = 'Task order is saved automatically.';

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('location_id')
                ->relationship('location', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->live(),
            DatePicker::make('scheduled_for'),
            Select::make('category_ids')
                ->label('Categories')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn (Get $get): array => static::categoryOptionsForLocation($get('location_id')))
                ->helperText('Assets for this PM are derived from the selected location and categories.')
                ->columnSpanFull(),
            Repeater::make('items')
                ->label('Checklist items')
                ->defaultItems(1)
                ->reorderableWithButtons()
                ->helperText('Add all preventive maintenance tasks here. '.static::CHECKLIST_HELPER_TEXT)
                ->schema(static::checklistItemFields())
                ->columnSpanFull()
                ->collapsed(false),
        ]);
    }

    /**
     * @return array<int, Hidden|Textarea|TextInput|Toggle>
     */
    public static function checklistItemFields(bool $includeId = true): array
    {
        $fields = [];

        if ($includeId) {
            $fields[] = Hidden::make('id');
        }

        $fields[] = Textarea::make('task')
            ->required()
            ->rows(2)
            ->columnSpanFull();

        $fields[] = TextInput::make('input_label')
            ->label('Optional input label')
            ->helperText('Example: Serial checked, Temperature reading, Rack label.')
            ->maxLength(255);

        $fields[] = Toggle::make('is_required')
            ->default(true)
            ->required();

        return $fields;
    }

    public static function checklistRepeater(bool $includeId = true): Repeater
    {
        return Repeater::make('items')
            ->label('Checklist items')
            ->defaultItems(1)
            ->reorderableWithButtons()
            ->schema(static::checklistItemFields($includeId))
            ->columnSpanFull()
            ->collapsed(false)
            ->helperText(static::CHECKLIST_HELPER_TEXT);
    }

    /**
     * @return array{category_ids: list<int>, items: list<array{id: int, task: string, input_label: ?string, is_required: bool}>}
     */
    public static function editFormData(PreventiveMaintenance $record): array
    {
        return [
            'category_ids' => $record->categories()->pluck('categories.id')->all(),
            'items' => static::editableChecklistItems($record),
        ];
    }

    public static function categoryOptionsForLocation(int|string|null $locationId): array
    {
        $locationId = (int) $locationId;

        if ($locationId < 1) {
            return [];
        }

        return Category::query()
            ->where('type', InventoryCategoryType::Asset)
            ->whereHas('assets', fn ($query) => $query->where('location_id', $locationId))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return list<array{id: int, task: string, input_label: ?string, is_required: bool}>
     */
    public static function editableChecklistItems(PreventiveMaintenance $record): array
    {
        return $record->items
            ->map(fn (PreventiveMaintenanceItem $item): array => [
                'id' => $item->getKey(),
                'task' => $item->task,
                'input_label' => $item->input_label,
                'is_required' => $item->is_required,
            ])
            ->all();
    }

    /**
     * @return array{items: list<array{id: int, task: string, input_label: ?string}>}
     */
    public static function sessionFormData(PreventiveMaintenance $record): array
    {
        return [
            'items' => $record->items
                ->map(fn (PreventiveMaintenanceItem $item): array => [
                    'id' => $item->getKey(),
                    'task' => $item->task,
                    'input_label' => $item->input_label,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<int, Repeater|Textarea>
     */
    public static function sessionExecutionSchema(): array
    {
        return [
            Repeater::make('items')
                ->label('Checklist')
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->collapsed(false)
                ->schema([
                    Hidden::make('id')
                        ->required(),
                    Hidden::make('input_label'),
                    Textarea::make('task')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(2)
                        ->columnSpanFull(),
                    Select::make('is_passed')
                        ->label('Result')
                        ->options([
                            '1' => 'Pass',
                            '0' => 'Fail',
                        ])
                        ->placeholder('Pending'),
                    TextInput::make('input_value')
                        ->label(fn (Get $get): string => (string) $get('input_label'))
                        ->visible(fn (Get $get): bool => filled($get('input_label'))),
                    Textarea::make('item_notes')
                        ->label('Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                    FileUpload::make('evidence_path')
                        ->label('Evidence')
                        ->disk('public')
                        ->directory('preventive-maintenance/evidence')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            Textarea::make('general_notes')
                ->label('General notes')
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  array<int, int|string>|null  $categoryIds
     * @return list<int>
     */
    public static function sanitizeSelectedCategoryIds(int|string|null $locationId, ?array $categoryIds): array
    {
        $availableCategoryIds = array_map(
            'intval',
            array_keys(static::categoryOptionsForLocation($locationId)),
        );

        return collect($categoryIds ?? [])
            ->map(fn (mixed $categoryId): int => (int) $categoryId)
            ->filter(fn (int $categoryId): bool => in_array($categoryId, $availableCategoryIds, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{id?: int|string|null, task?: string|null, input_label?: string|null, is_required?: bool|null}>|null  $items
     * @return list<array{id?: int, task: string, input_label?: ?string, sort_order: int, is_required: bool}>
     */
    public static function normalizeChecklistItems(?array $items): array
    {
        return collect($items ?? [])
            ->values()
            ->map(function (array $item, int $index): array {
                $normalized = [
                    'task' => trim((string) ($item['task'] ?? '')),
                    'input_label' => filled($item['input_label'] ?? null)
                        ? trim((string) $item['input_label'])
                        : null,
                    'sort_order' => $index + 1,
                    'is_required' => (bool) ($item['is_required'] ?? true),
                ];

                if (isset($item['id']) && filled($item['id'])) {
                    $normalized['id'] = (int) $item['id'];
                }

                return $normalized;
            })
            ->filter(fn (array $item): bool => $item['task'] !== '')
            ->values()
            ->all();
    }
}
