<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SampleBoardExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        // এক্সেল ফাইলের হেডলাইন
        return [
            'name_en',
            'name_bn',
            'status',
        ];
    }

    public function array(): array
    {
        // স্যাম্পল ডাটা (ইউজার যেন বুঝতে পারে কিভাবে ডাটা পূরণ করতে হবে)
        return [
            ['Dhaka Board', 'ঢাকা বোর্ড', 1],
            ['Rajshahi Board', 'রাজশাহী বোর্ড', 1],
            ['Technical Board', 'কারিগরি বোর্ড', 1],
        ];
    }
}