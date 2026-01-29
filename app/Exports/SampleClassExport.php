<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SampleClassExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // একটি ডামি ডাটা রো (User বুঝতে পারবে ফরম্যাট কেমন হবে)
        return new Collection([
            [
                'name_en' => 'Class One',
                'name_bn' => 'প্রথম শ্রেণি',
                'color'   => '#FF5733',
                'status'  => '1' // 1 for Active, 0 for Inactive
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'name_en',
            'name_bn',
            'color',
            'status',
        ];
    }
}