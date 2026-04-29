<?php

namespace App\Filament\Actions;

use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

final class SetUserRoleBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('setUserRole')
            ->label('Change Shield roles')
            ->icon(Heroicon::OutlinedUsers)
            ->modalHeading('Change Shield roles for selected users')
            ->modalDescription('Syncs the same Shield roles to every selected user you are allowed to update.')
            ->schema([
                Select::make('roles')
                    ->label('Shield roles')
                    ->options(fn (): array => Role::query()->orderBy('name')->pluck('name', 'name')->all())
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (BulkAction $action, Collection $records, array $data): void {
                $roles = collect($data['roles'] ?? [])
                    ->filter(fn (mixed $role): bool => filled($role))
                    ->map(fn (mixed $role): string => (string) $role)
                    ->values()
                    ->all();

                foreach ($records as $record) {
                    if (! $record instanceof User) {
                        $action->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        $record->syncRoles($roles);
                    } catch (\Throwable) {
                        $action->reportBulkProcessingFailure();
                    }
                }

                $action->success();
            })
            ->deselectRecordsAfterCompletion();
    }
}
