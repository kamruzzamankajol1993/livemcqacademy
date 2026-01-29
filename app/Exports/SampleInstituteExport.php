<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SampleInstituteExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['name_en', 'name_bn', 'type'];
    }

    public function array(): array
    {
        return [
            ['Dhaka College', 'ঢাকা কলেজ', 'college'],
            ['Ideal School', 'আইডিয়াল স্কুল', 'school'],
            ['Dhaka University', 'ঢাকা বিশ্ববিদ্যালয়', 'university'],
        ];
    }
}