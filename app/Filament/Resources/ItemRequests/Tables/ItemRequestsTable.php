<?php

namespace App\Filament\Resources\ItemRequests\Tables;

use App\Actions\Inventory\ApproveItemRequest;
use App\Enums\ItemRequestStatus;
use App\Filament\Actions\ExportPdfAction;
use App\Filament\Actions\SetItemRequestStatusBulkAction;
use App\Models\ItemRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requested_by')
                    ->label('Requested By')
                    ->searchable(),
                TextColumn::make('department')
                    ->searchable(),
                TextColumn::make('items')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('source_of_fund')
                    ->label('Source of Fund')
                    ->toggleable(),
                TextColumn::make('purpose_project')
                    ->label('Purpose Project')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('remarks')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('handler.name')
                    ->label('Handled By')
                    ->toggleable(),
                TextColumn::make('handled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('fulfilled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ItemRequestStatus::cases())->mapWithKeys(
                        fn (ItemRequestStatus $status): array => [$status->value => ucfirst($status->value)],
                    )->all()),
                SelectFilter::make('handled_by')
                    ->label('Handled By')
                    ->relationship('handler', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->label('Submitted Date')
                    ->schema([
                        DatePicker::make('submitted_from'),
                        DatePicker::make('submitted_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['submitted_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['submitted_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ItemRequest $record): bool => $record->status === ItemRequestStatus::Pending
                        && filled($record->user_id)
                        && filled($record->requestable_type)
                        && filled($record->requestable_id))
                    ->action(function (ItemRequest $record): void {
                        app(ApproveItemRequest::class)($record, auth()->user());

                        Notification::make()
                            ->title('Request approved')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                ExportPdfAction::make(),
                BulkActionGroup::make([
                    SetItemRequestStatusBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['handler', 'user']));
    }
}
