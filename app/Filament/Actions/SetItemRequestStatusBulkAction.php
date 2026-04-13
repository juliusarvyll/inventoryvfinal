<?php

namespace App\Filament\Actions;

use App\Enums\ItemRequestStatus;
use App\Models\ItemRequest;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

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
                        ->mapWithKeys(fn (ItemRequestStatus $case): array => [$case->value => \Illuminate\Support\Str::headline($case->name)])
                        ->all())
                    ->required()
                    ->native(false),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (Collection $records): void {
                $statusValue = $this->getData()['status'] ?? null;

                if (blank($statusValue)) {
                    $this->failure();

                    return;
                }

                try {
                    $status = ItemRequestStatus::from((string) $statusValue);
                } catch (\ValueError) {
                    $this->failure();

                    return;
                }

                foreach ($records as $record) {
                    if (! $record instanceof ItemRequest) {
                        $this->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        $record->status = $status;
                        $record->save();
                    } catch (\Throwable) {
                        $this->reportBulkProcessingFailure();
                    }
                }

                $this->success();
            })
            ->deselectRecordsAfterCompletion();
    }
}
