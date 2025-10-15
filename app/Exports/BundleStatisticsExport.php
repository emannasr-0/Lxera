<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BundleStatisticsExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($bundle) {
            //dd($bundle->formFeeSales()->count());
            return [
                'اسم البرنامج'       => $bundle->title,
                'رقم الدفعه'        => $bundle->batch->title ?? '-',
                'عدد طلبة حجز مقعد'  =>  $bundle->formFeeSales()->count() ,
                'عدد طلبة تسجيل برامج'    => $bundle->bundleSales->count(),
                'عدد طلبة تسجيل مباشر'       => $bundle->directRegister->count(),
                'عدد طلبة منح دراسية'       => $bundle->scholarshipSales->count(),

            ];
        });
    }

    public function headings(): array
    {
        return [
            'اسم البرنامج',
            'رقم الدفعه',
            'عدد طلبة حجز مقعد',
            'عدد طلبة تسجيل برامج',
            'عدد طلبة تسجيل مباشر',
            'عدد طلبة منح دراسية',

        ];
    }
}