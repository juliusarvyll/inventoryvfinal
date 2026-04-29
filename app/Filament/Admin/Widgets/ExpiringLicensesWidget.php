<?php

namespace App\Filament\Admin\Widgets;

use App\Models\License;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ExpiringLicensesWidget extends TableWidget
{
    protected static ?string $heading = 'Expiring Licenses';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => License::query()
                ->with('manufacturer')
                ->withCount('licenseSeats')
                ->whereDate('expiration_date', '>=', Carbon::today())
                ->whereDate('expiration_date', '<=', Carbon::today()->addDays(30))
                ->orderBy('expiration_date'))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('manufacturer.name')
                    ->label('Manufacturer')
                    ->default('N/A'),
                TextColumn::make('expiration_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('seats')
                    ->label('Seats Available')
                    ->formatStateUsing(fn (License $record): string => sprintf('%d / %d', $record->seatsAvailable(), $record->seats)),
            ])
            ->recordActions([
                Action::make('open')
                    ->url(fn (License $record): string => route('filament.admin.resources.licenses.view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
