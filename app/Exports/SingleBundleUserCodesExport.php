<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SingleBundleUserCodesExport implements FromCollection, WithHeadings
{
    protected $bundle;

    public function __construct($bundle)
    {
        $this->bundle = $bundle;
    }

    public function collection()
    {
        $formFee = $this->bundle->formFeeSales2
            ->map(fn($sale) => [
                'code' => $sale->buyer->user_code ?? '',
                'name' => $sale->buyer->full_name ?? '',
                'mobile' => $sale->buyer->mobile ?? '',
                'date' => $sale->buyer && $sale->buyer->created_at
                    ? dateTimeFormat($sale->buyer->created_at, 'j M Y | H:i')
                    : '----',
                'about_us' => $sale->buyer->student->about_us ?? '',
            ])->values();

        $bundleSales = $this->bundle->bundleSales
            ->map(fn($sale) => [
                'code' => $sale->buyer->user_code ?? '',
                'name' => $sale->buyer->full_name ?? '',
                'mobile' => $sale->buyer->mobile ?? '',
                'date' => $sale->buyer && $sale->buyer->created_at
                    ? dateTimeFormat($sale->buyer->created_at, 'j M Y | H:i')
                    : '----',
                'about_us' => $sale->buyer->student->about_us ?? '',
            ])->values();

        $direct = $this->bundle->directRegister
            ->map(fn($bs) => [
                'code' => $bs->student->registeredUser->user_code ?? '',
                'name' => $bs->student->registeredUser->full_name ?? '',
                'mobile' => $bs->student->registeredUser->mobile ?? '',
                'date' => $bs->student->registeredUser && $bs->student->registeredUser->created_at
                    ? dateTimeFormat($bs->student->registeredUser->created_at, 'j M Y | H:i')
                    : '----',
                'about_us' => $bs->student->about_us ?? '',
            ])->values();

        $scholar = $this->bundle->scholarshipSales
            ->map(fn($sale) => [
                'code' => $sale->buyer->user_code ?? '',
                'name' => $sale->buyer->full_name ?? '',
                'mobile' => $sale->buyer->mobile ?? '',
                'date' => $sale->buyer && $sale->buyer->created_at
                    ? dateTimeFormat($sale->buyer->created_at, 'j M Y | H:i')
                    : '----',
                'about_us' => $sale->buyer->student->about_us ?? '',
            ])->values();

        $max = max($formFee->count(), $bundleSales->count(), $direct->count(), $scholar->count());

        $rows = [];

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                $this->bundle->title,

                $formFee[$i]['code'] ?? '',
                $formFee[$i]['name'] ?? '',
                $formFee[$i]['mobile'] ?? '',
                $formFee[$i]['date'] ?? '',
                $formFee[$i]['about_us'] ?? '',

                $bundleSales[$i]['code'] ?? '',
                $bundleSales[$i]['name'] ?? '',
                $bundleSales[$i]['mobile'] ?? '',
                $bundleSales[$i]['date'] ?? '',
                $bundleSales[$i]['about_us'] ?? '',

                $direct[$i]['code'] ?? '',
                $direct[$i]['name'] ?? '',
                $direct[$i]['mobile'] ?? '',
                $direct[$i]['date'] ?? '',
                $direct[$i]['about_us'] ?? '',

                $scholar[$i]['code'] ?? '',
                $scholar[$i]['name'] ?? '',
                $scholar[$i]['mobile'] ?? '',
                $scholar[$i]['date'] ?? '',
                $scholar[$i]['about_us'] ?? '',
            ];
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'اسم البرنامج',

            'حجز مقعد - الكود',
            'حجز مقعد - الاسم',
            'حجز مقعد - الجوال',
            'حجز مقعد - تاريخ التسجيل',
            'حجز مقعد - عرفتنا منين',

            'تسجيل برامج - الكود',
            'تسجيل برامج - الاسم',
            'تسجيل برامج - الجوال',
            'تسجيل برامج - تاريخ التسجيل',
            'تسجيل برامج - عرفتنا منين',

            'تسجيل مباشر - الكود',
            'تسجيل مباشر - الاسم',
            'تسجيل مباشر - الجوال',
            'تسجيل مباشر - تاريخ التسجيل',
            'تسجيل مباشر - عرفتنا منين',

            'منحة دراسية - الكود',
            'منحة دراسية - الاسم',
            'منحة دراسية - الجوال',
            'منحة دراسية - تاريخ التسجيل',
            'منحة دراسية - عرفتنا منين',
        ];
    }
}