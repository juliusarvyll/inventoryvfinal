<?php

namespace App\Filament\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

final class SetUserRoleBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('setUserRole')
            ->label('Change role')
            ->icon(Heroicon::OutlinedUsers)
            ->modalHeading('Change role for selected users')
            ->modalDescription('Sets the same role on every selected user you are allowed to update.')
            ->schema([
                Select::make('role')
                    ->label('Role')
                    ->options(collect(UserRole::cases())
                        ->mapWithKeys(fn (UserRole $case): array => [$case->value => $case->value])
                        ->all())
                    ->required()
                    ->native(false),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (Collection $records): void {
                $roleValue = $this->getData()['role'] ?? null;

                if (blank($roleValue)) {
                    $this->failure();

                    return;
                }

                try {
                    $role = UserRole::from((string) $roleValue);
                } catch (\ValueError) {
                    $this->failure();

                    return;
                }

                foreach ($records as $record) {
                    if (! $record instanceof User) {
                        $this->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        $record->role = $role;
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
