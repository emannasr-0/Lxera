<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $users;
    protected $batchId;
    protected $currency;

    public function __construct($users, $batchId = null)
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
            'Email',
            'program',
            'created at',
            'Status',
            'Mobile',
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

            $purchases = $user->purchases($this->batchId)->get();

            if (!empty($purchases) and count($purchases) > 0) {
                foreach ($purchases as $purchase) {
                    $program = $purchase?->bundle?->title ?? $purchase?->webinar?->title;
                    if($program){
                        $diploma .= ($program .' , ') ;
                    }
                    $created_at .= (dateTimeFormat(strtotime($purchase->created_at), 'j M Y | H:i') . " , ");
                }
                $diploma = preg_replace('/,(?!.*,)/u', '', $diploma);
                $created_at = preg_replace('/,(?!.*,)/u', '', $created_at);
            }


            return [
                $user->user_code ?? '----',
                $user->student->ar_name,
                $user->student->en_name,
                $user->email,
                !empty($diploma) ? $diploma : 'غير مسجل بعد',
                dateTimeFormat($user->created_at, 'j M Y - H:i'),
                $user->status,
                $user->mobile,
                $user->student->about_us
            ];
        } else {
            return [
                $user->user_code ?? '----',
                ($user->student ? $user->student->ar_name : $user->full_name),
                ($user->student ? $user->student->en_name : $user->full_name),
                $user->email,
                $user->program_id? $user->appliedProgram->title :'غير مسجل بعد',
                dateTimeFormat($user->created_at, 'j M Y - H:i'),
                $user->status,
                $user->mobile,
                ''

            ];
        }

    }
}
