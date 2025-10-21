<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentStatisticsExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Referrer Name' => $item->referrer_name,
                'Friend Code' => $item->friend_code,
                'Student Count' => $item->student_count,
            ];
        });
    }

    public function headings(): array
    {
        return ['اسم الطالب', 'كود الطالب', 'عدد طالاب مستخدمي الكود'];
    }
}
