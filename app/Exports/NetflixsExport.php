<?php

namespace App\Exports;

use App\Models\Netflix;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NetflixsExport implements FromArray, WithHeadings
{

    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'Email',
            'Password',
            'Token 2FA',
            'Ngày hết hạn',
        ];
    }
}
