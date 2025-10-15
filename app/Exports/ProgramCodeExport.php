<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProgramCodeExport implements FromCollection, WithHeadings, WithMapping
{
    protected $programs;

    public function __construct($programs)
    {
        $this->programs = $programs;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->programs;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            'اسم التخصص',
            'اسم البرنامج',
            'كود البرنامج'
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($program): array
    {
        return [
            $program->category->title,
            $program->title,
            $program->id
        ];
    }
}
