<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SampleAcademicYearExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['name_en', 'name_bn', 'status'];
    }

    public function array(): array
    {
        return [
            ['2025-2026', '২০২৫-২০২৬', 1],
            ['2026-2027', '২০২৬-২০২৭', 1],
        ];
    }
}