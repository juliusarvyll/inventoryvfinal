<?php

namespace App\Exports;

use Filament\Tables\Exports\Export;
use Filament\Tables\Exports\Concerns\WithChunking;
use Filament\Tables\Exports\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings as WithHeadingsInterface;

class CustomCsvExport extends Export implements FromCollection, WithHeadingsInterface, WithCustomCsvSettings, WithChunking
{
    protected string $header = 'St. Paul University Philippines, ICT Department';

    protected array $headings = [];

    public function setHeadings(array $headings): self
    {
        $this->headings = $headings;
        return $this;
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
        ];
    }

    public function headings(): array
    {
        return array_merge([$this->header], $this->headings);
    }

    public function collection()
    {
        return $this->getRecords();
    }
}
