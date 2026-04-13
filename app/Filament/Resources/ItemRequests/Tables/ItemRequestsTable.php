<?php

namespace App\Filament\Resources\ItemRequests\Tables;

use App\Actions\Inventory\ApproveItemRequest;
use App\Filament\Actions\SetItemRequestStatusBulkAction;
use App\Enums\ItemRequestStatus;
use App\Models\ItemRequest;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requester_name')
                    ->label('Requestor')
                    ->formatStateUsing(fn (?string $state, ItemRequest $record): string => $state ?: $record->requester_display_name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $requestQuery) use ($search): void {
                            $requestQuery->where('requester_name', 'like', "%{$search}%")
                                ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('requestable_display_name')
                    ->label('Asset'),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reason')
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
                //
            ])
            ->recordActions([
                Action::make('approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ItemRequest $record): bool => $record->status === ItemRequestStatus::Pending && filled($record->user_id))
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
                BulkActionGroup::make([
                    SetItemRequestStatusBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
