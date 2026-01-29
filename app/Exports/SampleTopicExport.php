<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SampleTopicExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return new Collection([
            [
                'name_en' => 'Solving Linear Equations',
                'name_bn' => 'সরল সমীকরণ সমাধান',
                'subject_name' => 'Mathematics', // Must match exact name in Subject table
                'chapter_name' => 'Introduction to Algebra', // Must match exact name in Chapter table
                'status' => '1'
            ]
        ]);
    }

    public function headings(): array
    {
        return ['name_en', 'name_bn', 'subject_name', 'chapter_name', 'status'];
    }
}