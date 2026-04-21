<?php

namespace App\Filament\Actions;

use App\Exports\CustomHeaderCsvExport;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportCsvAction
{
    public static function make(string $name = 'export'): Action
    {
        return Action::make($name)
            ->label('Export CSV')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('success')
            ->form(function (HasTable $livewire): array {
                $table = $livewire->getTable();
                $columns = collect($table->getColumns())
                    ->filter(fn ($column) => !$column->isHidden())
                    ->map(fn ($column) => [
                        'name' => $column->getName(),
                        'label' => $column->getLabel(),
                    ])
                    ->values();

                return [
                    Select::make('category_id')
                        ->label('Category')
                        ->options(Category::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->placeholder('All Categories'),
                    Select::make('asset_model_id')
                        ->label('Asset Model')
                        ->options(AssetModel::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->placeholder('All Asset Models'),
                    Select::make('asset_id')
                        ->label('Asset')
                        ->options(Asset::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->placeholder('All Assets'),
                    DatePicker::make('date_from')
                        ->label('Date From'),
                    DatePicker::make('date_to')
                        ->label('Date To'),
                    Checkbox::make('select_all')
                        ->label('Select All Columns')
                        ->default(true)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) use ($columns) {
                            foreach ($columns as $column) {
                                $set('columns.' . $column['name'], $state);
                            }
                        }),
                    CheckboxList::make('columns')
                        ->label('Columns')
                        ->options($columns->pluck('label', 'name')->toArray())
                        ->default($columns->pluck('name')->toArray())
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $allSelected = count($state) === count(array_keys($state));
                            $set('select_all', $allSelected);
                        }),
                ];
            })
            ->action(function (HasTable $livewire, array $data): BinaryFileResponse {
                $table = $livewire->getTable();
                // Get query with all current filters, search, and sorting applied
                $query = clone $table->getQuery();
                
                // Get the model from the query to check which columns exist
                $model = $query->getModel();
                $tableColumns = \Schema::getColumnListing($model->getTable());
                
                // Apply additional export filters only if the column exists
                if (!empty($data['category_id']) && in_array('category_id', $tableColumns)) {
                    $query->where('category_id', $data['category_id']);
                }
                
                if (!empty($data['asset_model_id']) && in_array('asset_model_id', $tableColumns)) {
                    $query->where('asset_model_id', $data['asset_model_id']);
                }
                
                if (!empty($data['asset_id']) && in_array('asset_id', $tableColumns)) {
                    $query->where('asset_id', $data['asset_id']);
                }
                
                if (!empty($data['date_from']) && in_array('created_at', $tableColumns)) {
                    $query->where('created_at', '>=', $data['date_from']);
                }
                
                if (!empty($data['date_to']) && in_array('created_at', $tableColumns)) {
                    $query->where('created_at', '<=', $data['date_to']);
                }
                
                $allColumns = $table->getColumns();
                $selectedColumnNames = $data['columns'] ?? [];
                
                $selectedColumns = collect($allColumns)
                    ->filter(fn ($column) => in_array($column->getName(), $selectedColumnNames))
                    ->values()
                    ->toArray();
                
                $export = new CustomHeaderCsvExport();
                $export->setQuery($query);
                $export->setColumns($selectedColumns);
                
                return Excel::download(
                    $export,
                    'export-' . now()->format('Y-m-d-His') . '.csv'
                );
            });
    }
}
