<?php

namespace App\Filament\Actions;

use App\Models\Manufacturer;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class ChangeManufacturerBulkAction
{
    public static function make(string $recordLabel = 'records'): BulkAction
    {
        return BulkAction::make('changeManufacturer')
            ->label('Change manufacturer')
            ->icon(Heroicon::OutlinedBuildingOffice2)
            ->modalHeading('Change manufacturer for selected '.$recordLabel)
            ->modalDescription('Sets the same manufacturer on every selected '.$recordLabel.' you are allowed to update. Leave empty to unassign.')
            ->schema([
                Select::make('manufacturer_id')
                    ->label('Manufacturer')
                    ->options(fn (): array => Manufacturer::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder('None'),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (Collection $records): void {
                $manufacturerId = $this->getData()['manufacturer_id'] ?? null;

                $manufacturer = filled($manufacturerId)
                    ? Manufacturer::query()->whereKey($manufacturerId)->first()
                    : null;

                if (filled($manufacturerId) && ! $manufacturer) {
                    $this->failure();

                    return;
                }

                foreach ($records as $record) {
                    if (! $record instanceof Model) {
                        $this->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        if ($manufacturer) {
                            $record->manufacturer()->associate($manufacturer);
                        } else {
                            $record->manufacturer()->dissociate();
                        }

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
