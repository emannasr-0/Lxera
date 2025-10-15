<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class salesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->sales;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            trans('admin/main.id'),
            'كود الطالب',
            trans('admin/main.student'),
            'البريد الالكتروني',
            'ايميل الدفع',
            // trans('admin/main.student') . ' ' . trans('admin/main.id'),
            // trans('admin/main.instructor'),
            // trans('admin/main.instructor') . ' ' . trans('admin/main.id'),
            trans('admin/main.paid_total_amount'),
            trans('admin/main.paid_amount'),
            trans('admin/main.charge_amount'),
            trans('admin/main.discount'),
            'نسبةالخصم',
            trans('admin/main.item'),
            //  trans('admin/main.item') . ' ' . trans('admin/main.id'),
             'رقم الدفعة',
            trans('admin/main.sale_type'),
            trans('admin/main.date'),
            trans('admin/main.status'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($sale): array
    {

        if ($sale->payment_method == \App\Models\Sale::$subscribe) {
            $paidAmount = trans('admin/main.subscribe');
        } else {
            if (!empty($sale->total_amount)) {
                $paidAmount = handlePrice($sale->total_amount, false);
            } else {
                $paidAmount = trans('public.free');
            }
        }

        $status = (!empty($sale->refund_at)) ? trans('admin/main.refund') : trans('admin/main.success');

        $type = trans('update.'.$sale->type);

        $totalPaidAmount = $sale->total_amount + ($sale->discount ?? 0) + ($sale->order->charge_amount ?? 0);
        if ($sale->type == \App\Models\Sale::$bundle)
            $type = ($sale->payment_method == 'scholarship') ?  "منحة دراسية" : "دفع كامل الرسوم";
        else if ($sale->type == \App\Models\Sale::$installmentPayment)
            $type =   $sale->order->orderItems->first()->installmentPayment->step->installmentStep->title ?? 'قسط التسجيل';
        else if ($sale->type == 'form_fee')
            $type = "رسوم حجز مقعد";
        else if ($sale->type == 'certificate')
            $type = "شراء شهادة";
        else if ($sale->type == 'webinar')
            $type = "دورة";
        else if ($sale->type == 'service')
            $type =  "خدمة الكترونية";
        else if ($sale->type == 'charge')
            $type =  "شحن حساب";

         $studyClassTitle = $sale->class ? $sale->class->title : 'N/A';
        return [
            $sale->id,
            !empty($sale->buyer) ? $sale->buyer->user_code : 'Deleted User',
            !empty($sale->buyer) ? $sale->buyer->full_name : 'Deleted User',
            !empty($sale->buyer) ? $sale->buyer->email : 'Deleted User',
            $sale->payment_email,
            // $sale->item_seller,
            // $sale->seller_id,
            (string)$totalPaidAmount,
            (string)$paidAmount,
            (string)($sale?->order?->charge_amount ?? 0),
            (string)$sale->discount,
            $sale->discount> 0 ? $sale?->order?->orderItems[0]?->getDiscount?->percent . " % ": '0',
            $sale->type == 'charge' ? 'شحن حساب' : $sale->item_title,
            //  $sale->item_id,
            $studyClassTitle,
            $type,

            dateTimeFormat($sale->created_at, 'j M Y H:i'),
            $status
        ];
    }
}
