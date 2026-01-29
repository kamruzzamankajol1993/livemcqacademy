<?php

namespace App\Imports;

use App\Models\Board;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BoardImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // এক্সেল ফাইলে অবশ্যই 'name_en' এবং 'name_bn' কলাম থাকতে হবে
        if (!isset($row['name_en']) || !isset($row['name_bn'])) {
            return null;
        }

        return new Board([
            'name_en'     => $row['name_en'],
            'name_bn'     => $row['name_bn'],
            'status'      => isset($row['status']) ? $row['status'] : 1,
            // নতুন ডাটা ইম্পোর্ট হলে সিরিয়াল অটো ইনক্রিমেন্ট হবে
            'serial'      => (Board::max('serial') ?? 0) + 1,
        ]);
    }
}