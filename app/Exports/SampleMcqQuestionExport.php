<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SampleMcqQuestionExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'institute_name',   // Optional
            'board_name',       // Optional
            'year_name',        // Optional (Academic Year)
            'category_name',    // Optional
            'class_name',       // Mandatory
            'department_name',  // Optional
            'subject_name',     // Mandatory
            'chapter_name',     // Optional
            'topic_name',       // Optional
            'question',         // Mandatory
            'option_1',         // Mandatory
            'option_2',         // Mandatory
            'option_3',         // Mandatory
            'option_4',         // Mandatory
            'answer',           // Mandatory (1, 2, 3, or 4)
            'tags',             // Optional (Comma separated)
            'short_description',// Optional
            'upload_type',      // Optional (Default: subject_wise)
            'status'            // Optional (Default: 1)
        ];
    }

    public function array(): array
    {
        // স্যাম্পল ডাটা (ইউজারকে বোঝানোর জন্য)
        return [
            [
                'Dhaka College',       // institute_name
                'Dhaka Board',         // board_name
                '2025-2026',           // year_name
                'Academic',            // category_name
                'Class 10',            // class_name (Must exist in DB)
                'Science',             // department_name
                'Physics',             // subject_name (Must exist in DB)
                'Motion',              // chapter_name
                'Velocity',            // topic_name
                'What is the unit of velocity?', // question
                'm/s',                 // option_1
                'm/s^2',               // option_2
                'kg',                  // option_3
                'N',                   // option_4
                1,                     // answer (Correct Option Number: 1)
                'physics, motion, science', // tags
                'Velocity is the rate of change of displacement.', // short_description
                'subject_wise',        // upload_type
                1                      // status
            ]
        ];
    }

    // হেডার স্টাইল (অপশনাল - সুন্দর দেখানোর জন্য)
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e2939']]],
        ];
    }
}