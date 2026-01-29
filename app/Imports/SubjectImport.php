<?php

namespace App\Imports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class SubjectImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['name_en']) || empty($row['name_en'])) {
            return null;
        }

        // Unique Slug Logic
        $originalSlug = Str::slug($row['name_en']);
        $slug = $originalSlug;
        $count = 1;
        while (Subject::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return new Subject([
            'name_en' => $row['name_en'],
            'name_bn' => $row['name_bn'] ?? $row['name_en'],
            'slug'    => $slug,
            'color'   => $row['color'] ?? null,
            'status'  => $row['status'] ?? 1,
            'serial'  => (Subject::max('serial') ?? 0) + 1,
        ]);
    }
}