<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DirectRegisterExport implements FromCollection, WithHeadings, WithMapping
{
    protected $bundlstudents;
    protected $batchId;
    protected $currency;

    public function __construct($bundlstudents, $batchId)
    {
        $this->bundlstudents = $bundlstudents;
        $this->batchId = $batchId;
        $this->currency = currencySign();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->bundlstudents;
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
    public function map($bundlstudent): array
    {
        if ($bundlstudent->student) {
            $diploma = '';
            $created_at='';
            $userBundles = $bundlstudent->student->bundleStudent()->whereNull('class_id')->whereHas('bundle', function ($query) {
                    $query->where('batch_id', $this->batchId);
                })->get();


            if ($userBundles) {
                foreach ($userBundles as $userBundle) {
                        $diploma .= ($userBundle->bundle->title . " , " );
                        $created_at.= (dateTimeFormat(strtotime($userBundle->created_at), 'j M Y | H:i') . " , " );

                }
                $diploma = preg_replace('/,(?!.*,)/u', '', $diploma);
                $created_at = preg_replace('/,(?!.*,)/u', '', $created_at);

            }


            return [
                $bundlstudent->student->registeredUser->user_code,
                $bundlstudent->student->ar_name,
                $bundlstudent->student->en_name,
                $bundlstudent->student->registeredUser->email,
                $bundlstudent->bundle->title,
              // $created_at,
               $bundlstudent->created_at,
               $bundlstudent->student->registeredUser->status,
               $bundlstudent->student->registeredUser->mobile,
               $bundlstudent->student->about_us
            ];
        } else {
            return [
                '',
                '',
                '',
                '',
                'غير مسجل بعد',
                '',
                '',
                '',
                ''
            ];
        }

    }
}
