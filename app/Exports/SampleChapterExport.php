<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SampleChapterExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return new Collection([
            [
                'name_en' => 'Introduction to Algebra',
                'name_bn' => 'বীজগণিতের পরিচিতি',
                'subject_name' => 'Mathematics', // Must match exact name in Subject table
                'section_name' => 'Section A',   // Must match exact name in Section table
                'status' => '1'
            ]
        ]);
    }

    public function headings(): array
    {
        return ['name_en', 'name_bn', 'subject_name', 'section_name', 'status'];
    }
}