<?php

namespace App\Filament\Actions;

use App\Models\Location;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class ChangeLocationBulkAction
{
    public static function make(string $recordLabel = 'records'): BulkAction
    {
        return BulkAction::make('changeLocation')
            ->label('Change location')
            ->icon(Heroicon::OutlinedMapPin)
            ->modalHeading('Change location for selected '.$recordLabel)
            ->modalDescription('Sets the same location on every selected '.$recordLabel.' you are allowed to update. Leave empty to unassign.')
            ->schema([
                Select::make('location_id')
                    ->label('Location')
                    ->options(fn (): array => Location::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder('Unassigned'),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (BulkAction $action, Collection $records, array $data): void {
                $locationId = $data['location_id'] ?? null;

                $location = filled($locationId)
                    ? Location::query()->whereKey($locationId)->first()
                    : null;

                if (filled($locationId) && ! $location) {
                    $action->failure();

                    return;
                }

                foreach ($records as $record) {
                    if (! $record instanceof Model) {
                        $action->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        if ($location) {
                            $record->location()->associate($location);
                        } else {
                            $record->location()->dissociate();
                        }

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
