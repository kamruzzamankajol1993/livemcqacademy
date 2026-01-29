<?php

namespace App\Imports;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class SchoolClassImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // ১. নাম না থাকলে ইম্পোর্ট করবে না
        if (!isset($row['name_en']) || empty($row['name_en'])) {
            return null;
        }

        // ২. ইউনিক স্লাগ জেনারেটর লজিক
        $name_en = $row['name_en'];
        $originalSlug = Str::slug($name_en);
        $slug = $originalSlug;
        $count = 1;

        // চেক করবে এই স্লাগ ডাটাবেসে আছে কিনা, থাকলে -1, -2 যোগ করবে
        while (SchoolClass::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        // ৩. সিরিয়াল জেনারেট (সবার শেষে যোগ হবে)
        $maxSerial = SchoolClass::max('serial') ?? 0;

        return new SchoolClass([
            'name_en' => $row['name_en'],
            'name_bn' => $row['name_bn'] ?? $row['name_en'], // বাংলা নাম না থাকলে ইংলিশটাই বসবে
            'slug'    => $slug, // ইউনিক স্লাগ
            'color'   => $row['color'] ?? null,
            'status'  => isset($row['status']) ? $row['status'] : 1, // ডিফল্ট স্ট্যাটাস ১
            'serial'  => $maxSerial + 1,
        ]);
    }
}