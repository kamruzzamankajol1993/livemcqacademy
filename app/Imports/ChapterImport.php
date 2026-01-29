<?php

namespace App\Imports;

use App\Models\Chapter;
use App\Models\Subject;
use App\Models\Section;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class ChapterImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['name_en']) || empty($row['name_en'])) {
            return null;
        }

        // Find Relationships
        $subject = Subject::where('name_en', 'LIKE', trim($row['subject_name']))->first();
        $section = Section::where('name_en', 'LIKE', trim($row['section_name']))->first();

        if (!$subject) {
            return null; // Subject is mandatory
        }

        // Unique Slug Logic
        $originalSlug = Str::slug($row['name_en']);
        $slug = $originalSlug;
        $count = 1;
        while (Chapter::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return new Chapter([
            'subject_id' => $subject->id,
            'section_id' => $section ? $section->id : null,
            'name_en'    => $row['name_en'],
            'name_bn'    => $row['name_bn'] ?? $row['name_en'],
            'slug'       => $slug,
            'status'     => $row['status'] ?? 1,
            'serial'     => (Chapter::max('serial') ?? 0) + 1,
        ]);
    }
}