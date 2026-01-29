<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SampleSectionExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // ইউজারকে বোঝানোর জন্য একটি ডামি ডাটা
        return new Collection([
            [
                'name_en' => 'Section A',
                'name_bn' => 'শাখা ক',
                'class_name' => 'Class One', // ইউজার ক্লাসের নাম লিখবে
                'subject_name' => 'Mathematics', // সাবজেক্ট নাম
                'category_name' => 'General', // ক্যাটাগরি নাম
                'status' => '1'
            ]
        ]);
    }

    public function headings(): array
    {
        return ['name_en', 'name_bn', 'class_name', 'subject_name', 'category_name', 'status'];
    }
}