<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RequestsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $requests;

    public function __construct($requests)
    {
        $this->requests = $requests;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->requests;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            'id',
            'Student Code',
            'Student Name',
            'Request Status',
            'Request Content',
            'Request Date',
            'Admin',
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($request): array
    {
        return [
            $request->pivot->id,
            $request->user_code,
            $request->student ? $request->student->ar_name : $request->full_name,
            $request->pivot->status,
            $request->pivot->content,
            $request->pivot->created_at,
            $request->pivot->admin->full_name ?? '',
        ];
    }
}