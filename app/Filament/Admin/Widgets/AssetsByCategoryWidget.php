<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AssetsByCategoryWidget extends TableWidget
{
    protected static ?string $heading = 'Assets by Category';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Category::query()
                ->withCount('assets')
                ->orderByDesc('assets_count')
                ->orderBy('name')
                ->limit(10))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->url(fn (Category $record): string => CategoryResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('assets_count')
                    ->label('Assets')
                    ->numeric()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}

