<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\salesExport;
use App\Http\Controllers\Controller;
use App\Models\AccountCharge;
use App\Models\Accounting;
use App\Models\Api\Organization;
use App\Models\Bundle;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleLog;
use App\Models\StudyClass;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_sales_list');

        $query = Sale::whereNull('product_order_id')->where('manual_added', 0);

        $totalDiscounts = [
            'count' => deepClone($query)->where('discount', '>', 0)->whereNull('refund_at')->count(),
            'amount' => deepClone($query)->where('discount', '>', 0)->whereNull('refund_at')->sum('discount'),
        ];

        $totalSales = [
            'count' => deepClone($query)->whereNull('refund_at')->count(),
            'amount' => deepClone($query)->whereNull('refund_at')->sum('total_amount'),
        ];

        $totalSales2 = [
            'count' => deepClone($query)->whereNull('refund_at')->count() - $totalDiscounts['count'],
            'amount' =>  $totalSales['amount'] + $totalDiscounts['amount'],
        ];
        $classesSales = [
            'count' => deepClone($query)->whereNotNull('webinar_id')->whereNull('refund_at')->count(),
            'amount' => deepClone($query)->whereNotNull('webinar_id')->whereNull('refund_at')->sum('total_amount'),
        ];

        $formFeeSales = [
            'count' => deepClone($query)->whereNotNull('form_fee')->whereNull('refund_at')->count(),
            'amount' => deepClone($query)->whereNotNull('form_fee')->whereNull('refund_at')->sum('total_amount')
        ];

        $bundlesSales = [
            'count' => deepClone($query)->whereNotNull('bundle_id')->whereNull('form_fee')->whereNull('refund_at')->count(),
            'amount' => deepClone($query)->whereNotNull('bundle_id')->whereNull('form_fee')->whereNull('refund_at')->sum('total_amount'),
        ];

        $servicesSales = [
            // 'count' => deepClone($query)->whereNotNull('service_id')->count(),
            // 'amount' => deepClone($query)->whereNotNull('service_id')->sum('total_amount'),
            'count' => deepClone($query)->where('type', 'service')->whereNull('refund_at')->count(),
            'amount' => deepClone($query)->where('type', 'service')->whereNull('refund_at')->sum('total_amount'),
        ];

        $appointmentSales = [
            'count' => deepClone($query)->whereNotNull('meeting_id')->whereNull('refund_at')->count(),
            'amount' => deepClone($query)->whereNotNull('meeting_id')->whereNull('refund_at')->sum('total_amount'),
        ];
        $refundedSales = [
            'count' => deepClone($query)->whereNotNull('refund_at')->count(),
            'amount' => deepClone($query)->whereNotNull('refund_at')->sum('amount'),
        ];

        $failedSales = Order::where('status', Order::$fail)->count();

        $salesQuery = $this->getSalesFilters($query, $request);

        $sales = $salesQuery->orderBy('created_at', 'desc')
            ->with([
                'buyer',
                'webinar',
                'meeting',
                'subscribe',
                'promotion',
            ])
            ->get();

        foreach ($sales as $sale) {
            $sale = $this->makeTitle($sale);

            if (empty($sale->saleLog)) {
                SaleLog::create([
                    'sale_id' => $sale->id,
                    'viewed_at' => time(),
                ]);
            }
        }
        $studyClasses = StudyClass::all();

        $data = [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalSales2' => $totalSales2,
            'totalDiscounts' => $totalDiscounts,
            'classesSales' => $classesSales,
            'appointmentSales' => $appointmentSales,
            'failedSales' => $failedSales,
            'refundedSales' => $refundedSales,
            'formFeeSales' => $formFeeSales,
            'bundlesSales' => $bundlesSales,
            'servicesSales' => $servicesSales,
            'studyClasses' => $studyClasses,
            'bundles' => Bundle::get()
        ];

        $teacher_ids = $request->get('teacher_ids');
        $student_ids = $request->get('student_ids');
        $webinar_ids = $request->get('webinar_ids');

        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')
                ->whereIn('id', $teacher_ids)->get();
        }

        if (!empty($student_ids)) {
            $data['students'] = User::select('id', 'full_name')
                ->whereIn('id', $student_ids)->get();
        }

        if (!empty($webinar_ids)) {
            $data['webinars'] = Webinar::select('id')
                ->whereIn('id', $webinar_ids)->get();
        }

        return response()->json([$data], 200,  [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    public function index2(Request $request)
    {
        $query = Sale::whereNull('product_order_id')->where('manual_added', 0)->whereNull('service_id');

        $salesQuery = $this->getSalesFilters($query, $request);

        $sales = $salesQuery->orderBy('created_at', 'desc')
            ->with([
                'buyer',
                'webinar',
                'meeting',
                'subscribe',
                'promotion',
            ])
            ->get();

        foreach ($sales as $sale) {
            $sale = $this->makeTitle($sale);

            if (empty($sale->saleLog)) {
                SaleLog::create([
                    'sale_id' => $sale->id,
                    'viewed_at' => time(),
                ]);
            }
        }
        $studyClasses = StudyClass::all();

        $data = [
            'pageTitle' => trans('admin/pages/financial.sales_page_title'),
            'sales' => $sales,
            'studyClasses' => $studyClasses,
            'bundles' => Bundle::get()
        ];

        return view('admin.financial.sales.useraccess', $data);
    }
    public function toggleAccess($url_name, $saleId, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $sale = Sale::find($saleId);

        if ($sale) {
            $sale->access_to_purchased_item = !$sale->access_to_purchased_item;
            $sale->save();
            return response()->json([
                'success' => true,
                'access_to_purchased_item' => $sale->access_to_purchased_item
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Sale not found'], 400);
    }


    public function makeTitle($sale)
    {
        if (!empty($sale->webinar_id) or !empty($sale->bundle_id)) {
            $item = !empty($sale->webinar_id) ? $sale->webinar : $sale->bundle;

            $sale->item_title = $item ? $item->title : trans('update.deleted_item');
            $sale->item_id = $item ? $item->id : '';
            $sale->item_seller = ($item and $item->creator) ? $item->creator->full_name : trans('update.deleted_item');
            $sale->seller_id = ($item and $item->creator) ? $item->creator->id : '';
            $sale->sale_type = ($item and $item->creator) ? $item->creator->id : '';
        } else if (!empty($sale->service_id)) {
            $item = !empty($sale->service_id) ? $sale->service : null;

            $sale->item_title = $item ? $item->title : trans('update.deleted_item');
            $sale->item_id = $item ? $item->id : '';
            $sale->item_seller = '---';
            $sale->seller_id = '';
            $sale->sale_type = "";
        } elseif (!empty($sale->meeting_id)) {
            $sale->item_title = trans('panel.meeting');
            $sale->item_id = $sale->meeting_id;
            $sale->item_seller = ($sale->meeting and $sale->meeting->creator) ? $sale->meeting->creator->full_name : trans('update.deleted_item');
            $sale->seller_id = ($sale->meeting and $sale->meeting->creator) ? $sale->meeting->creator->id : '';
        } elseif (!empty($sale->subscribe_id)) {
            $sale->item_title = !empty($sale->subscribe) ? $sale->subscribe->title : trans('update.deleted_subscribe');
            $sale->item_id = $sale->subscribe_id;
            $sale->item_seller = 'Admin';
            $sale->seller_id = '';
        } elseif (!empty($sale->promotion_id)) {
            $sale->item_title = !empty($sale->promotion) ? $sale->promotion->title : trans('update.deleted_promotion');
            $sale->item_id = $sale->promotion_id;
            $sale->item_seller = 'Admin';
            $sale->seller_id = '';
        } elseif (!empty($sale->registration_package_id)) {
            $sale->item_title = !empty($sale->registrationPackage) ? $sale->registrationPackage->title : 'Deleted registration Package';
            $sale->item_id = $sale->registration_package_id;
            $sale->item_seller = 'Admin';
            $sale->seller_id = '';
        } elseif (!empty($sale->gift_id) and !empty($sale->gift)) {
            $gift = $sale->gift;
            $item = !empty($gift->webinar_id) ? $gift->webinar : (!empty($gift->bundle_id) ? $gift->bundle : $gift->product);

            $sale->item_title = $gift->getItemTitle();
            $sale->item_id = $item->id;
            $sale->item_seller = $item->creator->full_name;
            $sale->seller_id = $item->creator_id;
        } elseif (!empty($sale->installment_payment_id) and !empty($sale->installmentOrderPayment)) {
            $installmentOrderPayment = $sale->installmentOrderPayment;
            $installmentOrder = $installmentOrderPayment->installmentOrder;
            $installmentItem = $installmentOrder->getItem();

            $sale->item_title = !empty($installmentItem) ? $installmentItem->title : '--';
            $sale->item_id = !empty($installmentItem) ? $installmentItem->id : '--';
            $sale->item_seller = !empty($installmentItem) ? $installmentItem->creator->full_name : '--';
            $sale->seller_id = !empty($installmentItem) ? $installmentItem->creator->id : '--';
        } else {
            $sale->item_title = '---';
            $sale->item_id = '---';
            $sale->item_seller = '---';
            $sale->seller_id = '';
        }

        return $sale;
    }

    public function getSalesFilters($query, $request)
    {
        $item_title = $request->get('item_title');
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');
        $webinar_ids = $request->get('webinar_ids', []);
        $teacher_ids = $request->get('teacher_ids', []);
        $student_ids = $request->get('student_ids', []);
        $userIds = array_merge($teacher_ids, $student_ids);

        // $from = $request->input('from');
        // $to = $request->input('to');
        $userName = $request->get('user_name');
        $fullName = $request->get('full_name');
        $ar_name = $request->get('ar_name');
        $mobile = $request->get('mobile');
        $type = $request->get('type');
        $email = $request->get('email');
        $user_code = $request->get('user_code');
        $bundle_title = $request->get('bundle_title');
        $total_amount = $request->get('total_amount');

        $class_id = $request->get('class_id');
        if (!empty($class_id)) {
            $query->where('class_id', '=', $class_id); // Ensure 'class_id' is the correct field in the Sale model
        }

        if (!empty($total_amount)) {
            $query->where('total_amount', '=', $total_amount);
        }

        if (!empty($item_title)) {
            $ids = Webinar::whereTranslationLike('title', "%$item_title%")->pluck('id')->toArray();
            $webinar_ids = array_merge($webinar_ids, $ids);
        }

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($fullName)) {
            $query->whereHas('buyer', function ($q) use ($fullName) {
                $q->where('full_name', 'like', "%$fullName%");
            });
        }

        if (!empty($ar_name)) {
            $query->whereHas('buyer', function ($q1) use ($ar_name) {
                $q1->whereHas('student', function ($q) use ($ar_name) {
                    $q->where('ar_name', 'like', "%$ar_name%")
                        ->orWhere('en_name', 'like', "%$ar_name%");
                });
            });
        }

        if (!empty($userName)) {
            $query->whereHas('buyer', function ($q) use ($userName) {
                $q->where('full_name', 'like', "%$userName%")
                    ->orWhereHas('student', function ($q) use ($userName) {
                        $q->where('ar_name', 'like', "%$userName%")
                            ->orWhere('en_name', 'like', "%$userName%");
                    });
            });
        }

        if (!empty($mobile)) {
            $query->whereHas('buyer', function ($q) use ($mobile) {
                $q->where('mobile', 'like', "%$mobile%")
                    ->orWhereHas('student', function ($q) use ($mobile) {
                        $q->where('mobile', 'like', "%$mobile%")
                            ->orWhere('phone', 'like', "%$mobile%");
                    });
            });
        }

        if (!empty($email)) {
            $query->when($email, function ($query) use ($email) {
                $query->whereHas('buyer', function ($q) use ($email) {
                    $q->where('email', 'like', "%$email%");
                });
            });
        }

        if (!empty($user_code)) {
            $query->when($user_code, function ($query) use ($user_code) {
                $query->whereHas('buyer', function ($q) use ($user_code) {
                    $q->where('user_code', 'like', "%$user_code%");
                });
            });
        }

        if (!empty($bundle_title)) {
            $query->when($bundle_title, function ($query) use ($bundle_title) {
                $query->where(function ($query2) use ($bundle_title) {
                    $query2->whereHas(
                        'bundle',
                        function ($q) use ($bundle_title) {
                            $q->whereTranslationLike('title', '%' . $bundle_title . '%')
                                ->orWhere('slug', 'like', "%$bundle_title%");
                        }
                    )
                        ->orWhereHas(
                            'webinar',
                            function ($q) use ($bundle_title) {
                                $q->whereTranslationLike('title', '%' . $bundle_title . '%')
                                    ->orWhere('slug', 'like', "%$bundle_title%");
                            }
                        );
                });
            });
        }

        if (!empty($type)) {

            if ($type == 'upfront') {

                $query->when($type, function ($query) {
                    $query->whereHas('order.orderItems', function ($item) {

                        $item->whereHas('installmentPayment', function ($payment) {
                            $payment->where("type", "upfront");
                        });
                    });
                })->where('type', 'installment_payment');
            } else if ($type == 'installment_payment') {
                $query->when($type, function ($query) {
                    $query->whereHas('order.orderItems', function ($item) {

                        $item->whereHas('installmentPayment', function ($payment) {
                            $payment->where("type", "step");
                        });
                    });
                })->where('type', 'installment_payment');
            } else if ($type == 'scholarship') {
                $query->where('payment_method', 'scholarship');
            } else {
                $query->when($type, function ($query) use ($type) {
                    $query->whereHas('order.orderItems')->where('type', $type)->where('payment_method', "!=", 'scholarship');
                });
            }
        }

        if (!empty($status)) {
            if ($status == 'success') {
                $query->whereNull('refund_at');
            } elseif ($status == 'refund') {
                $query->whereNotNull('refund_at');
            } elseif ($status == 'blocked') {
                $query->where('access_to_purchased_item', false);
            }
        }

        if (!empty($webinar_ids) and count($webinar_ids)) {
            $query->whereIn('webinar_id', $webinar_ids);
        }

        if (!empty($userIds) and count($userIds)) {
            $query->where(function ($query) use ($userIds) {
                $query->whereIn('buyer_id', $userIds);
                $query->orWhereIn('seller_id', $userIds);
            });
        }


        return $query;
    }

    public function refund($url_name, $id, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $request->validate([
            'message' => 'required',
        ]);


        $this->authorize('admin_sales_refund');

        $sale = Sale::findOrFail($id);

        if ($sale->type == Sale::$subscribe) {
            $salesWithSubscribe = Sale::whereNotNull('webinar_id')
                ->where('buyer_id', $sale->buyer_id)
                ->where('subscribe_id', $sale->subscribe_id)
                ->whereNull('refund_at')
                ->with('webinar', 'subscribe')
                ->get();

            foreach ($salesWithSubscribe as $saleWithSubscribe) {
                $saleWithSubscribe->update([
                    'refund_at' => time(),
                ]);

                if (!empty($saleWithSubscribe->webinar) and !empty($saleWithSubscribe->subscribe)) {
                    Accounting::refundAccountingForSaleWithSubscribe($saleWithSubscribe->webinar, $saleWithSubscribe->subscribe);
                }
            }
        }

        if (!empty($sale->total_amount)) {
            Accounting::refundAccounting($sale);
        }

        if (!empty($sale->meeting_id) and $sale->type == Sale::$meeting) {
            $appointment = ReserveMeeting::where('meeting_id', $sale->meeting_id)
                ->where('sale_id', $sale->id)
                ->first();

            if (!empty($appointment)) {
                $appointment->update([
                    'status' => ReserveMeeting::$canceled,
                ]);
            }
        }

        $sale->update(['refund_at' => time(), 'total_amount' => 0, 'message' => $request->message . "<br>"]);
        $toastData = [
            'title' => 'طلب استيرداد مبلغ',
            'msg' => 'تم الاستيرداد بنجاح',
            'status' => 'success'
        ];
        return response()->json(['toast' => $toastData]);
    }

    public function invoice($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_sales_invoice');

        $sale = Sale::where('id', $id)
            ->with([
                'order',

                'buyer' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'webinar' => function ($query) {
                    $query->with([
                        'teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                        'creator' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                        'webinarPartnerTeacher' => function ($query) {
                            $query->with([
                                'teacher' => function ($query) {
                                    $query->select('id', 'full_name');
                                },
                            ]);
                        },
                    ]);
                },
            ])
            ->first();

        if (!empty($sale)) {
            $webinar = $sale->webinar;

            if (!empty($webinar)) {
                $data = [
                    'invoiceURL' => 'http://127.0.0.1:8000/admin/financial/sales/' . $id . '/invoice',
                    'sale' => $sale,
                    'webinar' => $webinar,
                ];

                return response()->json($data);
            }
        }

        abort(404);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_sales_export');

        $query = Sale::query()->where('manual_added', 0);

        $salesQuery = $this->getSalesFilters($query, $request);

        $sales = $salesQuery->orderBy('created_at', 'desc')
            ->with([
                'buyer',
                'webinar',
                'meeting',
                'subscribe',
                'promotion',
            ])
            ->get();

        foreach ($sales as $sale) {
            $sale = $this->makeTitle($sale);
        }

        $export = new salesExport($sales);

        return Excel::download($export, 'sales.xlsx');
    }


    public function getAccountsForCharge(Request $request)
    {

        $query = User::query()->whereIn('role_id', Role::$students);
        $usersQuery = $this->getUsersFilters($query, $request);

        $users = $usersQuery->orderBy('created_at', 'desc')
            ->with([
                'student',
            ])
            ->paginate(20);


        $data = [
            'pageTitle' => "شحن محفظة",
            'users' => $users
        ];
        return view('admin.financial.charge.list', $data);
    }
    public function chargeAccount(Request $request, User $user)
    {

        $rules = [
            'charge_amount' => 'required|numeric|min:1',
        ];

        $this->validate($request, $rules);


        $amount = $request->input('charge_amount');

        if ($amount <= 0) {
            return back()->withErrors([
                'amount' => trans('update.the_amount_must_be_greater_than_0')
            ]);
        }

        $amount = convertPriceToDefaultCurrency($amount);
        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$paid,
            'payment_method' => Order::$paymentChannel,
            'is_charge_account' => true,
            'total_amount' => $amount,
            'amount' => $amount,
            'created_at' => time(),
            'type' => Order::$charge,
        ]);

        OrderItem::updateOrCreate([
            'user_id' => $user->id,
            'order_id' => $order->id,
        ], [
            'amount' => $amount,
            'total_amount' => $amount,
            'tax' => 0,
            'tax_price' => 0,
            'commission' => 0,
            'commission_price' => 0,
            'created_at' => time(),
        ]);

        AccountCharge::charge($order);

        $toastData = [
            'title' => 'شحن محفظة',
            'msg' => 'تم شحن محفظة الطالب بنجاح',
            'status' => 'success'
        ];

        return redirect()->back()->with(['toast' => $toastData]);
    }

    public function getUsersFilters($query, $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');

        $userName = $request->get('user_name');
        $fullName = $request->get('full_name');
        $ar_name = $request->get('ar_name');
        $mobile = $request->get('mobile');
        $email = $request->get('email');
        $user_code = $request->get('user_code');

        if (
            empty($from) && empty($to) && empty($status)
            && empty($userName) && empty($fullName) && empty($ar_name)
            && empty($mobile) && empty($email) && empty($user_code)
        ) {
            $query->whereRaw('1 = 0'); // This ensures no results are returned
        }

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($fullName)) {
            $query->where('full_name', 'like', "%$fullName%");
        }

        if (!empty($ar_name)) {
            $query->where('full_name', 'like', "%$fullName%")
                ->orWhereHas('student', function ($q) use ($ar_name) {
                    $q->where('ar_name', 'like', "%$ar_name%")
                        ->orWhere('en_name', 'like', "%$ar_name%");
                });
        }

        if (!empty($userName)) {
            $query->where('full_name', 'like', "%$userName%")
                ->orWhereHas('student', function ($q) use ($userName) {
                    $q->where('ar_name', 'like', "%$userName%")
                        ->orWhere('en_name', 'like', "%$userName%");
                });
        }

        if (!empty($mobile)) {

            $query->where('mobile', 'like', "%$mobile%")
                ->orWhereHas('student', function ($q) use ($mobile) {
                    $q->where('mobile', 'like', "%$mobile%")
                        ->orWhere('phone', 'like', "%$mobile%");
                });
        }

        if (!empty($email)) {
            $query->where('email', 'like', "%$email%");
        }

        if (!empty($user_code)) {
            $query->where('user_code', 'like', "%$user_code%");
        }

        return $query;
    }
}
