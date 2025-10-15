<?php

namespace App\Http\Controllers\Admin\traits;


use App\Exports\InstallmentOverdueExport;
use App\Exports\InstallmentOverdueHistoriesExport;
use App\Models\Accounting;
use App\Models\Category;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

trait InstallmentOverdueTrait
{
    public function overdueLists(Request $request)
    {
        $this->authorize('admin_installments_overdue_lists');

        $query = $this->getOverdueListsQuery($request);
        $orders = $this->getInstallmentFilter($query, $request)->paginate(20);

        $categories = Category::whereNull('parent_id')
            ->where(function ($query) {
                $query->whereHas('bundles')
                    ->orWhereHas('subCategories', function ($query) {
                        $query->whereHas('bundles');
                    });
            })->get();

        $data = [
            'pageTitle' => trans('update.overdue_installments'),
            'orders' => $orders,
            'categories' => $categories
        ];

        return view('admin.financial.installments.overdue_installments', $data);
    }

    private function getOverdueListsQuery(Request $request)
    {
        $time = time();

        $query = InstallmentOrder::query()
            ->join('selected_installments', 'installment_orders.id', 'selected_installments.installment_order_id')
            ->join('selected_installment_steps', 'selected_installments.id', 'selected_installment_steps.selected_installment_id')
            ->join('bundles', 'bundles.id', 'installment_orders.bundle_id')
            ->leftJoin('installment_order_payments', 'installment_order_payments.selected_installment_step_id', 'selected_installment_steps.id')
            ->select(
                'installment_orders.*',
                'selected_installment_steps.amount',
                'selected_installment_steps.amount_type',
                'bundles.start_date as bundle_start_date',
                DB::raw('
            CASE
                WHEN selected_installments.deadline_type = "days" THEN ((selected_installment_steps.deadline * 86400) + bundles.start_date)
                ELSE selected_installment_steps.deadline
            END as overdue_date')
            )
            ->whereRaw("(
            CASE
                WHEN selected_installments.deadline_type = 'days' THEN ((selected_installment_steps.deadline * 86400) + bundles.start_date)
                ELSE selected_installment_steps.deadline
            END) < {$time}")
            ->where(function ($query) {
                $query->whereNull("installment_order_payments.id")
                    ->orWhere("installment_order_payments.status", "paying");
            })
            ->where('installment_orders.status', 'open')
            ->orderBy('overdue_date', 'asc');

        return $query;
    }

    public function overdueListsExportExcel(Request $request)
    {
        $this->authorize('admin_installments_overdue_lists');

        $query = $this->getOverdueListsQuery($request);
        $orders = $this->getInstallmentFilter($query, $request)->get();

        $export = new InstallmentOverdueExport($orders);
        return Excel::download($export, 'InstallmentOverdue.xlsx');
    }

    public function overdueHistories(Request $request)
    {
        $this->authorize('admin_installments_overdue_lists');

        $query = $this->getOverdueHistoriesQuery($request);
        $orders = $this->getInstallmentFilter($query, $request)->paginate(20);

        // dd( $orders);
        $categories = Category::whereNull('parent_id')
            ->where(function ($query) {
                $query->whereHas('bundles')
                    ->orWhereHas('subCategories', function ($query) {
                        $query->whereHas('bundles');
                    });
            })->get();


        $data = [
            'pageTitle' => trans('update.overdue_installments'),
            'orders' => $orders,
            'categories' => $categories
        ];

        return view('admin.financial.installments.overdue_history', $data);
    }

    private function getOverdueHistoriesQuery(Request $request)
    {
        $time = time();

        $query = InstallmentOrder::query()
            ->join('selected_installments', 'installment_orders.id', 'selected_installments.installment_order_id')
            ->join('selected_installment_steps', 'selected_installments.id', 'selected_installment_steps.selected_installment_id')
            ->join('bundles', 'bundles.id', 'installment_orders.bundle_id') // Join the 'bundles' table
            ->leftJoin('installment_order_payments', 'installment_order_payments.selected_installment_step_id', 'selected_installment_steps.id')
            ->select(
                'installment_orders.*',
                'selected_installment_steps.amount',
                'selected_installment_steps.amount_type',
                'bundles.start_date as bundle_start_date', // Include bundles.start_date in the select
                DB::raw('
            CASE
                WHEN selected_installments.deadline_type = "days" THEN ((selected_installment_steps.deadline * 86400) + bundles.start_date)
                ELSE selected_installment_steps.deadline
            END as overdue_date'),

                DB::raw('installment_order_payments.status as status'),
                DB::raw('installment_order_payments.created_at as paid_at'),
                DB::raw('selected_installment_steps.deadline as deadline')
            )
            ->whereRaw("(
            CASE
                WHEN selected_installments.deadline_type = 'days' THEN ((selected_installment_steps.deadline * 86400) + bundles.start_date)
                ELSE selected_installment_steps.deadline
            END) < {$time}") // Use bundles.start_date in the where clause
            ->where('installment_orders.status', 'open')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereRaw("installment_order_payments.id < 1");
                    $query->orWhereRaw("installment_order_payments.id is null");
                });
                $query->orWhereRaw(
                    "installment_order_payments.created_at > (
                    CASE
                        WHEN selected_installments.deadline_type = 'days' THEN ((selected_installment_steps.deadline * 86400) + bundles.start_date)
                        ELSE selected_installment_steps.deadline
                    END)"
                );
            })
            ->orderBy('overdue_date', 'asc');
        return $query;
    }

    public function overdueHistoriesExportExcel(Request $request)
    {
        $this->authorize('admin_installments_overdue_lists');

        $query = $this->getOverdueHistoriesQuery($request);

        $orders = $this->getInstallmentFilter($query, $request)->paginate(20);

        $export = new InstallmentOverdueHistoriesExport($orders);
        return Excel::download($export, 'InstallmentOverdueHistories.xlsx');
    }



    public function getInstallmentFilter($query, Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $userName = $request->get('user_name');
        $email = $request->get('email');
        $user_code = $request->get('user_code');
        $bundle = $request->get('bundle_id');

        // Wrap the subquery with a derived table
        // $query = DB::table(DB::raw("({$query->toSql()}) as sub"))
        // ->mergeBindings($query->getQuery()) // Merge bindings from the subquery
        // ->select('*'); // Select all columns from the derived table

        // Use the subquery in the main query
        // $query = InstallmentOrder::query()
        // ->selectSub($query, 'sub')
        // ->select('*');
        // $query = fromAndToDateFilter($from, $to, $query, 'overdue_date');

        // if (!empty($from) and !empty($to)) {
        //     $from = strtotime($from) ;
        //     $to = strtotime($to) ;

        //     $query->whereBetween($column, [$from, $to]);
        // } else {
        //     if (!empty($from)) {
        //         $from = strtotime($from) ;

        //         $query->where($column, '>=', $from);
        //     }

        //     if (!empty($to)) {
        //         $to = strtotime($to) ;

        //         $query->where($column, '<', $to);
        //     }
        // }

        if (!empty($userName)) {
            $query->when($userName, function ($query) use ($userName) {
                $query->whereHas('user', function ($q) use ($userName) {
                    $q->where('full_name', 'like', "%$userName%");
                });
            });
        }

        if (!empty($email)) {
            $query->when($email, function ($query) use ($email) {
                $query->whereHas('user', function ($q) use ($email) {
                    $q->where('email', 'like', "%$email%");
                });
            });
        }
        if (!empty($user_code)) {
            $query->when($user_code, function ($query) use ($user_code) {
                $query->whereHas('user', function ($q) use ($user_code) {
                    $q->where('user_code', 'like', "%$user_code%");
                });
            });
        }
        if (!empty($bundle)) {
            $query->when($bundle, function ($query) use ($bundle) {
                $query->whereHas('bundle', function ($q) use ($bundle) {
                    $q->where('id', $bundle);
                });
            });
        }

        return $query;
    }
}
