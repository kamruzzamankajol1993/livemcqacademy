<?php

namespace App\Imports;

use App\Models\AcademicYear;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AcademicYearImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['name_en']) || !isset($row['name_bn'])) {
            return null;
        }

        return new AcademicYear([
            'name_en'     => $row['name_en'],
            'name_bn'     => $row['name_bn'],
            'status'      => isset($row['status']) ? $row['status'] : 1,
            'serial'      => (AcademicYear::max('serial') ?? 0) + 1,
        ]);
    }
}