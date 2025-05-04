<?php

namespace App\Http\Controllers\Admin\traits;


use App\Exports\InstallmentPurchasesExport;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

trait InstallmentPurchasesTrait
{
    public function purchases()
    {
        $this->authorize('admin_installments_purchases');

        $orders = InstallmentOrder::query()
            ->where('status', '!=', 'paying')
            ->orderBy('created_at', 'desc')
            ->with([
                'selectedInstallment' => function ($query) {
                    $query->with(['steps']);
                    $query->withCount([
                        'steps'
                    ]);
                }
            ])
            ->paginate(10);

        $orders = $this->handlePurchasedOrders($orders);

        $data = [
            'pageTitle' => trans('update.purchases'),
            'orders' => $orders
        ];

        return view('admin.financial.installments.purchases.index', $data);
    }

    private function handlePurchasedOrders($orders)
    {
        foreach ($orders as $order) {
            $overdueOrderInstallments = $this->getOverdueOrderInstallments($order);
            $getUpcomingInstallment = $this->getUpcomingInstallment($order);

            $order->overdue_count = $overdueOrderInstallments['total'];
            $order->overdue_amount = $overdueOrderInstallments['amount'];
            $order->upcoming_date = !empty($order->bundle)
                ? (!empty($getUpcomingInstallment)
                    ? dateTimeFormat(($order->selectedInstallment->deadline_type == 'days') ? (($getUpcomingInstallment->deadline * 86400) + $order->bundle->start_date) : $getUpcomingInstallment->deadline, 'j M Y')
                    : '')
                : '';
            // dd($order->bundle->start_date);
            $lastStep = $order->selectedInstallment->steps()->orderBy('deadline', 'desc')->first();

            $order->days_left = 0;

            if (!empty($lastStep) && !empty($order->bundle)) {
                if ($order->selectedInstallment->deadline_type == 'days') {
                    $dueAt = $lastStep->deadline * 86400 + $order->bundle->start_date;
                } else {
                    $dueAt =$lastStep->deadline;
                }

                $daysLeft = ($dueAt - time()) / 86400;

                if ($daysLeft > 0) {
                    $order->days_left = (int)$daysLeft;
                }
            }
        }

        return $orders;
    }

    private function getOverdueOrderInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $time = time();
        $itemPrice = $order->getItemPrice();

        foreach ($order->selectedInstallment->steps as $step) {
            if (!empty($order->bundle)) {
                if ($order->selectedInstallment->deadline_type == 'days') {
                    $dueAt = $step->deadline * 86400 + $order->bundle->start_date;
                } else {
                    $dueAt = $step->deadline;
                }
             
                if ($dueAt < $time) {
                    $payment = InstallmentOrderPayment::query()
                        ->where('installment_order_id', $order->id)
                        ->where('selected_installment_step_id', $step->id)
                        ->where('status', 'paid')
                        ->first();

                    if (empty($payment)) {
                        $total += 1;
                        $amount += $step->getPrice($itemPrice);
                    }
                }
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getUpcomingInstallment($order)
    {
        $result = null;
        $deadline = 0;

        foreach ($order->selectedInstallment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('selected_installment_step_id', $step->id)
                ->where('status', 'paid')
                ->first();

            if (empty($payment) and ($deadline == 0 or $deadline > $step->deadline)) {
                $deadline = $step->deadline;
                $result = $step;
            }
        }

        return $result;
    }


    public function purchasesExportExcel(Request $request)
    {
        $this->authorize('admin_installments_purchases');

        $orders = InstallmentOrder::query()
            ->where('status', '!=', 'paying')
            ->orderBy('created_at', 'desc')
            ->with([
                'selectedInstallment' => function ($query) {
                    $query->with(['steps']);
                    $query->withCount([
                        'steps'
                    ]);
                }
            ])
            ->get();

        $orders = $this->handlePurchasedOrders($orders);

        $export = new InstallmentPurchasesExport($orders);
        return Excel::download($export, 'InstallmentPurchases.xlsx');
    }
}
