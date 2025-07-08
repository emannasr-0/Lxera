<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsWebinarExport implements FromCollection, WithHeadings, WithMapping
{
    protected $students;

    public function __construct($students)
    {
        $this->students = $students;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->students;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Mobile',
            'Rate',
            'Learning Progress (%)',
            'User Group',
            'Purchase Date',
            'Status',
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($student): array
    {
        return [
            $student->id ?? '-',
            $student->full_name,
            $student->email ?? '-',
            $student->mobile ?? '-',
            $student->rates ?? '-',
            $student->learning ?? '0',
            $student->getUserGroup()->name ?? '-',
            $student->purchase_date ? date('Y-m-d H:i', strtotime($student->purchase_date)) : '-',
            $this->getStatus($student),
        ];
    }

    /**
     * Get the student's status.
     */
    private function getStatus($student)
    {
        if (empty($student->id)) {
            return 'Unregistered';
        } elseif (!empty($student->webinar->access_days) && !$student->webinar->checkHasExpiredAccessDays($student->purchase_date, $student->gift_id)) {
            return 'Expired';
        } elseif (!$student->access_to_purchased_item) {
            return 'Access Blocked';
        } else {
            return 'Active';
        }
    }
}