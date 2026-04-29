<?php

namespace App\Filament\Actions;

use App\Enums\ItemRequestStatus;
use App\Models\ItemRequest;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class SetItemRequestStatusBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('setItemRequestStatus')
            ->label('Change status')
            ->icon(Heroicon::OutlinedClipboardDocumentList)
            ->modalHeading('Change status for selected requests')
            ->modalDescription('Sets the same status on every selected request you are allowed to update. Use individual actions for approvals that check out assets.')
            ->schema([
                Select::make('status')
                    ->label('Status')
                    ->options(collect(ItemRequestStatus::cases())
                        ->mapWithKeys(fn (ItemRequestStatus $case): array => [$case->value => Str::headline($case->name)])
                        ->all())
                    ->required()
                    ->native(false),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (BulkAction $action, Collection $records, array $data): void {
                $statusValue = $data['status'] ?? null;

                if (blank($statusValue)) {
                    $action->failure();

                    return;
                }

                try {
                    $status = ItemRequestStatus::from((string) $statusValue);
                } catch (\ValueError) {
                    $action->failure();

                    return;
                }

                foreach ($records as $record) {
                    if (! $record instanceof ItemRequest) {
                        $action->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        $record->status = $status;
                        $record->save();
                    } catch (\Throwable) {
                        $action->reportBulkProcessingFailure();
                    }
                }

                $action->success();
            })
            ->deselectRecordsAfterCompletion();
    }
}
