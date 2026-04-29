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
                ->with(['user'])
                ->where('status', ItemRequestStatus::Pending)
                ->latest()
                ->limit(5))
            ->columns([
                TextColumn::make('requested_by')
                    ->label('Requested By')
                    ->searchable(),
                TextColumn::make('items')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state),
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
                Action::make('open')
                    ->url(fn (ItemRequest $record): string => route('filament.admin.resources.item-requests.view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
