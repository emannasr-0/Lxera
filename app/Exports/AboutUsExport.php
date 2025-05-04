<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AboutUsExport implements FromCollection, WithHeadings, WithMapping
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
            'كودالطالب',
           'عرفونا منين'
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($student): array
    {
        return [
            $student->registeredUser->user_code??'--',
            $student->about_us,
            
        ];
    }
}
