<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class RequirementsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $requirements;
    protected $currency;

    public function __construct($requirements)
    {
        $this->requirements = $requirements;
        $this->currency = currencySign();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->requirements;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {

        return [

            'كود الطالب',
            'اسم الطالب',
            'بريد الطالب',
            'هاتف الطالب',
            'الدبلومه',
            'مرفق الهوية',
            'مرفق متطلبات القبول',
            'حاله الطالب',
            'سبب الرفض',
            'الأدمن',
            'تاريخ ارسال الطلب'

        ];
    }

    /**
     * @inheritDoc
     */
    public function map($requirement): array
    {

        return [
            $requirement->bundleStudent->student->registeredUser->user_code,
            $requirement->bundleStudent->student ? $requirement->bundleStudent->student->ar_name : $requirement->bundleStudent->student->registeredUser->full_name,
            $requirement->bundleStudent->student->registeredUser->email,
            $requirement->bundleStudent->student->registeredUser->mobile,
            $requirement->bundleStudent->bundle->title,
            "https://lms.anasacademy.uk/store/". $requirement->identity_attachment,
            "https://lms.anasacademy.uk/store/".$requirement->admission_attachment,
            $requirement->status,
            $requirement->status == 'rejected' ? $requirement->message : '',
            $requirement->admin ? $requirement->admin->full_name : '',
            Carbon::parse($requirement->created_at)->translatedFormat(handleDateAndTimeFormat('Y M j | H:i'))
        ];


    }
}
