<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GroupStudentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $enrollments;
    protected $currency;

    public function __construct($enrollments)
    {
        $this->enrollments = $enrollments;
        $this->currency = currencySign();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->enrollments;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {

        return [
            'Course',
            'Group',
            'Student code',
            'Arabic Name',
            'English Name',
            'Email',
            'Mobile',
            'Status',
            'registration date',

        ];
    }

    /**
     * @inheritDoc
     */
    public function map($enrollment): array
    {
            return [
                $enrollment->group->webinar->title,
                $enrollment->group->name,
                $enrollment->user->user_code,
                // ($enrollment->user->student ? $enrollment->user->student->ar_name : $enrollment->user->full_name),
                ($enrollment->user->student ? $enrollment->user->student->ar_name : $enrollment->user->full_name),
                ($enrollment->user->en_name ? $enrollment->user->en_name : $enrollment->user->full_name),
                $enrollment->user->email,
                $enrollment->user->mobile,
                $enrollment->user->status,
                $enrollment->created_at,
            ];
    }
}
