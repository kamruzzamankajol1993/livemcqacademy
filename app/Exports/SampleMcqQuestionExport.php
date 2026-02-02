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
            'category_name',    // ১
            'class_name',       // ২
            'section_name',     // ৩
            'department_name',  // ৪
            'subject_name',     // ৫
            'chapter_name',     // ৬
            'topic_name',       // ৭
            'mcq_type',         // ৮ (text / image)
            'question',         // ৯ (টেক্সট অথবা ইমেজ ড্রপ করবেন)
            'option_1',         // ১০ (টেক্সট অথবা ইমেজ ড্রপ করবেন)
            'option_2',         // ১১ (টেক্সট অথবা ইমেজ ড্রপ করবেন)
            'option_3',         // ১২ (টেক্সট অথবা ইমেজ ড্রপ করবেন)
            'option_4',         // ১৩ (টেক্সট অথবা ইমেজ ড্রপ করবেন)
            'answer',           // ১৪ (১, ২, ৩, ৪)
            'tags',             // ১৫
            'short_description',// ১৬
            'upload_type',      // ১৭
            'status',           // ১৮
            'institute_names',  // ১৯ (Comma separated)
            'board_names'       // ২০ (Comma separated)
        ];
    }

    public function array(): array
    {
        return [
            [
                // Row 1: Text Type MCQ
                'মাধ্যমিক স্তর', 'Class 10', 'গদ্য/কবিতা', 'Science', 'Physics', 'Force', 'Newton Law', 
                'text', 'What is F?', 'ma', 'mv', 'mg', 'mh', 1, 
                'physics', 'Force equals mass times acceleration', 'subject_wise', 1, 
                'Dhaka College', 'Dhaka Board'
            ],
            [
                // Row 2: Image Type MCQ (Text columns will be empty, just drop images in excel)
                'উচ্চ-মাধ্যমিক স্তর', 'HSC', '', 'General', 'Math', 'Geometry', 'Circle', 
                'image', '', '', '', '', '', 2, 
                'geometry', 'See image for solution', 'subject_wise', 1, 
                'BUET', 'All Board'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e2939']]],
        ];
    }
}