<?php

namespace App\Filament\Admin\Widgets;

use App\Actions\Inventory\ApproveItemRequest;
use App\Enums\ItemRequestStatus;
use App\Models\ItemRequest;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentRequestsWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Requests';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ItemRequest::query()
                ->with(['user', 'requestable'])
                ->where('status', ItemRequestStatus::Pending)
                ->latest()
                ->limit(5))
            ->columns([
                TextColumn::make('requester_name')
                    ->label('Requester')
                    ->formatStateUsing(fn (?string $state, ItemRequest $record): string => $state ?: $record->requester_display_name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $requestQuery) use ($search): void {
                            $requestQuery->where('requester_name', 'like', "%{$search}%")
                                ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('requestable_display_name')
                    ->label('Asset'),
                TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Submitted'),
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
                Action::make('open')
                    ->url(fn (ItemRequest $record): string => route('filament.admin.resources.item-requests.view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
