<?php

namespace App\Imports;

use App\Models\Topic;
use App\Models\Subject;
use App\Models\Chapter;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class TopicImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['name_en']) || empty($row['name_en'])) {
            return null;
        }

        // Find Relationships
        $subject = Subject::where('name_en', 'LIKE', trim($row['subject_name']))->first();
        $chapter = Chapter::where('name_en', 'LIKE', trim($row['chapter_name']))->first();

        if (!$subject || !$chapter) {
            return null; // Both are mandatory
        }

        // Unique Slug Logic
        $originalSlug = Str::slug($row['name_en']);
        $slug = $originalSlug;
        $count = 1;
        while (Topic::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return new Topic([
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'name_en'    => $row['name_en'],
            'name_bn'    => $row['name_bn'] ?? $row['name_en'],
            'slug'       => $slug,
            'status'     => $row['status'] ?? 1,
            'serial'     => (Topic::max('serial') ?? 0) + 1,
        ]);
    }
}