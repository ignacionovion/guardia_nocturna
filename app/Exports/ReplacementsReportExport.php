<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReplacementsReportExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @param array<int, array<int, mixed>> $rows
     * @param array<int, string> $headings
     */
    public function __construct(
        private array $rows,
        private array $headings
    ) {
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }
}
