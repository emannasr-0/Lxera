<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchStudentsExportV2 implements FromCollection, WithHeadings, WithMapping
{
    protected $sales;


    public function __construct($sales)
    {
        $this->sales = $sales;

    }

    /**
     * @return Collection
     */
    public function collection(): mixed
    {
        return $this->sales;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {

        return [

            'Student code',
            'Arabic Name',
            'English Name',
            'program',
            'created at',
            'Status',
            'Mobile',
            'Email',
            'about_us'

        ];
    }

    /**
     * @inheritDoc
     */
    public function map($sale): array
     {

        return [

            $sale->buyer?->user_code,
            $sale->buyer?->student->ar_name ??$sale->buyer?->full_name,
            $sale->buyer?->student->en_name ??$sale->buyer?->full_name,
            $sale->bundle->title ,
            dateTimeFormat($sale->created_at, 'j M Y - H:i'),
            $sale->buyer?->status,
            $sale->buyer?->mobile,
            $sale->buyer?->email,
            $sale->buyer?->student->about_us ??''
         ] ;

     }
}
