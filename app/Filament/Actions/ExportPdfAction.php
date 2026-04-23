<?php

namespace App\Filament\Actions;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportPdfAction
{
    private const PDF_MEMORY_LIMIT = '512M';

    private const MAX_EXPORT_ROWS = 1000;

    public static function make(string $name = 'export'): Action
    {
        return Action::make($name)
            ->label('Export PDF')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->color('success')
            ->form(function (HasTable $livewire): array {
                $table = $livewire->getTable();
                $tableColumns = Schema::getColumnListing($table->getQuery()->getModel()->getTable());
                $columns = collect($table->getColumns())
                    ->filter(fn ($column): bool => ! $column->isHidden())
                    ->map(fn ($column): array => [
                        'name' => $column->getName(),
                        'label' => self::sanitizeString((string) $column->getLabel()),
                    ])
                    ->values();

                return [
                    Select::make('category_id')
                        ->label('Category')
                        ->options(self::optionsForColumn($table->getQuery()->getModel(), 'category_id', Category::pluck('name', 'id')->toArray()))
                        ->searchable()
                        ->placeholder('All Categories')
                        ->visible(in_array('category_id', $tableColumns, true)),
                    Select::make('asset_model_id')
                        ->label('Asset Model')
                        ->options(self::optionsForColumn($table->getQuery()->getModel(), 'asset_model_id', AssetModel::pluck('name', 'id')->toArray()))
                        ->searchable()
                        ->placeholder('All Asset Models')
                        ->visible(in_array('asset_model_id', $tableColumns, true)),
                    Select::make('asset_id')
                        ->label('Asset')
                        ->options(self::optionsForColumn($table->getQuery()->getModel(), 'asset_id', Asset::pluck('name', 'id')->toArray()))
                        ->searchable()
                        ->placeholder('All Assets')
                        ->visible(in_array('asset_id', $tableColumns, true)),
                    Select::make('date_column')
                        ->label('Date Field')
                        ->options(self::dateFieldOptions($tableColumns))
                        ->default(self::defaultDateField($tableColumns))
                        ->visible(count(self::dateFieldOptions($tableColumns)) > 0),
                    DatePicker::make('date_from')
                        ->label('Date From')
                        ->visible(fn (Get $get): bool => filled($get('date_column'))),
                    DatePicker::make('date_to')
                        ->label('Date To')
                        ->visible(fn (Get $get): bool => filled($get('date_column'))),
                    Checkbox::make('select_all')
                        ->label('Select All Columns')
                        ->default(true)
                        ->reactive()
                        ->afterStateUpdated(function (bool $state, callable $set) use ($columns): void {
                            foreach ($columns as $column) {
                                $set('columns.'.$column['name'], $state);
                            }
                        }),
                    CheckboxList::make('columns')
                        ->label('Columns')
                        ->options($columns->pluck('label', 'name')->toArray())
                        ->default($columns->pluck('name')->toArray()),
                ];
            })
            ->action(function (HasTable $livewire, array $data): StreamedResponse {
                ini_set('memory_limit', self::PDF_MEMORY_LIMIT);

                $table = $livewire->getTable();
                $query = clone $table->getQuery();
                $model = $query->getModel();
                $tableColumns = Schema::getColumnListing($model->getTable());

                if (! empty($data['category_id']) && in_array('category_id', $tableColumns, true)) {
                    $query->where('category_id', $data['category_id']);
                }

                if (! empty($data['asset_model_id']) && in_array('asset_model_id', $tableColumns, true)) {
                    $query->where('asset_model_id', $data['asset_model_id']);
                }

                if (! empty($data['asset_id']) && in_array('asset_id', $tableColumns, true)) {
                    $query->where('asset_id', $data['asset_id']);
                }

                $dateColumn = $data['date_column'] ?? null;

                if (
                    filled($dateColumn) &&
                    in_array($dateColumn, $tableColumns, true) &&
                    ! empty($data['date_from'])
                ) {
                    $query->whereDate($dateColumn, '>=', $data['date_from']);
                }

                if (
                    filled($dateColumn) &&
                    in_array($dateColumn, $tableColumns, true) &&
                    ! empty($data['date_to'])
                ) {
                    $query->whereDate($dateColumn, '<=', $data['date_to']);
                }

                $selectedColumnNames = self::normalizeSelectedColumnNames($data['columns'] ?? []);
                $selectedColumns = collect($table->getColumns())
                    ->filter(fn ($column): bool => in_array($column->getName(), $selectedColumnNames, true))
                    ->values();

                if ($selectedColumns->isEmpty()) {
                    $selectedColumns = collect($table->getColumns())
                        ->filter(fn ($column): bool => ! $column->isHidden())
                        ->values();
                }

                $records = $query
                    ->limit(self::MAX_EXPORT_ROWS + 1)
                    ->get();

                $isTruncated = $records->count() > self::MAX_EXPORT_ROWS;

                if ($isTruncated) {
                    $records = $records->take(self::MAX_EXPORT_ROWS);
                }

                $headings = $selectedColumns
                    ->map(fn ($column): string => self::sanitizeString((string) $column->getLabel()))
                    ->toArray();

                $rows = $records
                    ->map(function (Model $record) use ($selectedColumns): array {
                        $row = $selectedColumns
                            ->map(function ($column) use ($record): string {
                                $column->record($record);
                                $state = $column->getState();

                                if (is_array($state) || $state instanceof Collection) {
                                    return self::sanitizeString(
                                        collect($state)
                                            ->map(fn ($item): string => self::stringifyValue($item))
                                            ->join(', ')
                                    );
                                }

                                if ($state instanceof BackedEnum) {
                                    return self::sanitizeString((string) $state->value);
                                }

                                return filled($state) ? self::stringifyValue($state) : '-';
                            })
                            ->toArray();

                        foreach ($selectedColumns as $column) {
                            if (method_exists($column, 'clearCachedState')) {
                                $column->clearCachedState();
                            }
                        }

                        return $row;
                    })
                    ->toArray();

                $pdf = Pdf::loadView('exports.table-pdf', [
                    'header' => 'St. Paul University Philippines',
                    'generatedAt' => now(),
                    'headings' => $headings,
                    'rows' => $rows,
                    'exportNotice' => $isTruncated
                        ? self::sanitizeString('This export was limited to the first '.number_format(self::MAX_EXPORT_ROWS).' records to prevent memory errors.')
                        : null,
                ])->setPaper('a4', 'landscape');

                return response()->streamDownload(
                    function () use ($pdf): void {
                        echo $pdf->output();
                    },
                    'export-'.now()->format('Y-m-d-His').'.pdf',
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    /**
     * @param  array<int|string, string>  $options
     * @return array<int|string, string>
     */
    private static function optionsForColumn(Model $model, string $column, array $options): array
    {
        $tableColumns = Schema::getColumnListing($model->getTable());

        if (! in_array($column, $tableColumns, true)) {
            return [];
        }

        return collect($options)
            ->map(fn ($label): string => self::sanitizeString((string) $label))
            ->toArray();
    }

    /**
     * @param  list<string>|array<int|string, bool|string>  $selectedColumns
     * @return list<string>
     */
    private static function normalizeSelectedColumnNames(array $selectedColumns): array
    {
        if (array_is_list($selectedColumns)) {
            return collect($selectedColumns)
                ->filter(fn ($value): bool => is_string($value) && filled($value))
                ->values()
                ->all();
        }

        return collect($selectedColumns)
            ->filter(fn ($isSelected): bool => (bool) $isSelected)
            ->keys()
            ->filter(fn ($key): bool => is_string($key) && filled($key))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $tableColumns
     * @return array<string, string>
     */
    private static function dateFieldOptions(array $tableColumns): array
    {
        $supportedColumns = [
            'created_at',
            'updated_at',
            'scheduled_for',
            'started_at',
            'completed_at',
            'purchase_date',
            'expiration_date',
            'warranty_expires',
            'eol_date',
        ];

        return collect($supportedColumns)
            ->filter(fn (string $column): bool => in_array($column, $tableColumns, true))
            ->mapWithKeys(fn (string $column): array => [$column => self::sanitizeString(str($column)->replace('_', ' ')->title()->toString())])
            ->toArray();
    }

    /**
     * @param  list<string>  $tableColumns
     */
    private static function defaultDateField(array $tableColumns): ?string
    {
        foreach (['created_at', 'scheduled_for', 'started_at', 'updated_at'] as $candidate) {
            if (in_array($candidate, $tableColumns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function stringifyValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return self::sanitizeString((string) $value->value);
        }

        if (is_scalar($value)) {
            return self::sanitizeString((string) $value);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return self::sanitizeString((string) $value);
        }

        return '-';
    }

    private static function sanitizeString(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);

        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
