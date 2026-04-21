<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class CustomHeaderCsvExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    protected string $header = 'St. Paul University Philippines, ICT Department';
    protected Builder $query;
    protected array $columns;

    public function setQuery(Builder $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function setColumns(array $columns): self
    {
        $this->columns = $columns;
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
        $columnHeadings = collect($this->columns)
            ->map(fn ($column): string => $column->getLabel())
            ->toArray();

        return array_merge([$this->header], $columnHeadings);
    }

    public function collection(): Collection
    {
        return $this->query->get();
    }
}
