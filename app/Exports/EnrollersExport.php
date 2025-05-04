<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EnrollersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sales;
    protected $batchId;
    protected $currency;

    public function __construct($sales, $batchId = null)
    {
        $this->sales = $sales;
        $this->batchId = $batchId;
        $this->currency = currencySign();
    }

    /**
     * @return Collection
     */
    public function collection()
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
            'Email',
            'diploma',
            'created at',
            'Status',
            'Mobile',
            'about_us'


        ];
    }

    /**
     * @inheritDoc
     */
    public function map($sale): array
    {
        if ($sale?->buyer) {
            // $diploma = '';
            // $created_at='';
            // $purchasedBundles = $sale->buyer->purchasedBundles($this->batchId)->get();

            // if ($purchasedBundles) {
            //     foreach ($purchasedBundles as $purchasedBundle) {
            //             $diploma .= ($purchasedBundle->bundle->title . " , ") ;
            //             $created_at .=(dateTimeFormat($purchasedBundle->created_at, 'j M Y | H:i') . " , ");;

            //     }
            //     $diploma = preg_replace('/,(?!.*,)/u', '', $diploma);
            //     $created_at = preg_replace('/,(?!.*,)/u', '', $created_at);
            // }


            return [
                $sale?->buyer?->user_code,
                $sale?->buyer?->student?->ar_name ?? $sale?->buyer?->full_name,
                $sale?->buyer?->student?->en_name,
                $sale?->buyer?->email,
                $sale?->bundle?->title,
                dateTimeFormat($sale->created_at, 'j M Y | H:i'),
                $sale?->buyer?->status,
                $sale?->buyer?->mobile ?? $sale?->buyer?->student?->mobile,
                $sale?->buyer?->student?->about_us
            ];
        } else {
            return [
               'Deleted User',
               'Deleted User',
               'Deleted User',
               'Deleted User',
                $sale?->bundle?->title ?? 'Deleted Program',
                dateTimeFormat($sale->created_at, 'j M Y | H:i'),
               'Deleted User',
               'Deleted User',
               'Deleted User'
            ];
        }

    }
}
