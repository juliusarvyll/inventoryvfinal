<?php

namespace App\Filament\Resources\PreventiveMaintenanceSchedules\Schemas;

use App\Models\PreventiveMaintenanceChecklist;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

class PreventiveMaintenanceExecutionForm
{
    /**
     * @return array{items: list<array{id: int, task: string, input_label: ?string}>}
     */
    public static function executionFormData(PreventiveMaintenanceChecklist $checklist): array
    {
        return [
            'items' => $checklist->items
                ->map(fn ($item): array => [
                    'id' => $item->getKey(),
                    'task' => $item->task,
                    'input_label' => $item->input_label,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<int, Repeater|Textarea>
     */
    public static function executionSchema(): array
    {
        return [
            Repeater::make('items')
                ->label('Checklist')
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->collapsed(false)
                ->schema([
                    Hidden::make('id')
                        ->required(),
                    Hidden::make('input_label'),
                    Textarea::make('task')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(2)
                        ->columnSpanFull(),
                    Select::make('is_passed')
                        ->label('Result')
                        ->options([
                            '1' => 'Pass',
                            '0' => 'Fail',
                        ])
                        ->placeholder('Pending'),
                    TextInput::make('input_value')
                        ->label(fn (Get $get): string => (string) $get('input_label'))
                        ->visible(fn (Get $get): bool => filled($get('input_label'))),
                    FileUpload::make('evidence_path')
                        ->label('Evidence')
                        ->disk('public')
                        ->directory('preventive-maintenance/evidence')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            Textarea::make('general_notes')
                ->label('General notes')
                ->rows(4)
                ->columnSpanFull(),
        ];
    }
}
