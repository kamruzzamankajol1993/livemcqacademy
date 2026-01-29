<?php

namespace App\Imports;

use App\Models\Institute;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InstituteImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['name_en']) || !isset($row['type'])) {
            return null;
        }

        // টাইপ ভ্যালিডেশন (ছোট হাতের অক্ষরে কনভার্ট করে চেক করা হচ্ছে)
        $type = strtolower($row['type']);
        if (!in_array($type, ['school', 'college', 'university'])) {
            $type = 'school'; // ডিফল্ট
        }

        return new Institute([
            'name_en' => $row['name_en'],
            'name_bn' => $row['name_bn'] ?? $row['name_en'],
            'type'    => $type,
            'status'  => 1,
            'serial'  => (Institute::where('type', $type)->max('serial') ?? 0) + 1,
        ]);
    }
}