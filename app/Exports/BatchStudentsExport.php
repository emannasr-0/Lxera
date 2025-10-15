<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchStudentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $users;
    protected $batchId;
    protected $currency;

    public function __construct($users, $batchId)
    {
        $this->users = $users;
        $this->batchId = $batchId;
        $this->currency = currencySign();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->users;
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
    public function map($user): array
    {
        if ($user->student) {
            $diploma = '';
            $created_at = '';

            $purchasedBundles = $user->bundleSales($this->batchId)->get();

            if ($purchasedBundles) {
                foreach ($purchasedBundles as $purchasedBundle) {
                    $diploma .= ($purchasedBundle->bundle->title .' , ') ;
                    $created_at .= (dateTimeFormat($purchasedBundle->created_at, 'j M Y | H:i') . " , ");
                }
                $diploma = preg_replace('/,(?!.*,)/u', '', $diploma);
                $created_at = preg_replace('/,(?!.*,)/u', '', $created_at);
            }


            return [
                $user->user_code,
                $user->student->ar_name,
                $user->student->en_name,
                $diploma,
                $created_at,
                $user->status,
                $user->mobile,
                $user->email,
                $user->student->about_us
            ];
        } else {
            return [
                $user->user_code,
                ($user->student ? $user->student->ar_name : $user->full_name),
                ($user->student ? $user->student->en_name : $user->full_name),
                $user->program_id? $user->appliedProgram->title :'غير مسجل بعد',
                dateTimeFormat($user->created_at, 'j M Y - H:i'),
                $user->status,
                $user->mobile,
                $user->email,
                ''

            ];
        }

    }
}
