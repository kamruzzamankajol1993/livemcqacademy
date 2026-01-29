<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SampleSubjectExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return new Collection([
            [
                'name_en' => 'Mathematics',
                'name_bn' => 'গণিত',
                'color'   => '#3357FF',
                'status'  => '1'
            ]
        ]);
    }

    public function headings(): array
    {
        return ['name_en', 'name_bn', 'color', 'status'];
    }
}