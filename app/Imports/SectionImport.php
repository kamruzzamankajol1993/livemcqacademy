<?php

namespace App\Imports;

use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class SectionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['name_en']) || empty($row['name_en'])) {
            return null;
        }

        // নাম থেকে ID খুঁজে বের করা (Case Insensitive Search)
        $class = SchoolClass::where('name_en', 'LIKE', trim($row['class_name']))->first();
        $subject = Subject::where('name_en', 'LIKE', trim($row['subject_name']))->first();
        $category = Category::where('name', 'LIKE', trim($row['category_name']))->first();

        // Unique Slug Generation
        $slug = Str::slug($row['name_en']);
        $count = 1;
        while (Section::where('slug', $slug)->exists()) {
            $slug = Str::slug($row['name_en']) . '-' . $count++;
        }

        return new Section([
            'name_en'     => $row['name_en'],
            'name_bn'     => $row['name_bn'] ?? $row['name_en'],
            'class_id'    => $class ? $class->id : null,
            'subject_id'  => $subject ? $subject->id : null,
            'category_id' => $category ? $category->id : null,
            'slug'        => $slug,
            'status'      => $row['status'] ?? 1,
            'serial'      => (Section::max('serial') ?? 0) + 1,
        ]);
    }
}