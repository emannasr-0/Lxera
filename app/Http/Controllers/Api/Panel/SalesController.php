<?php

namespace App\Http\Controllers\Api\Panel;

use App\Exports\salesExport;
use App\Http\Controllers\Api\Controller;
use App\Models\Api\Organization;
use App\Models\Api\Sale;
use App\Models\Bundle;
use App\Models\SaleLog;
use App\Models\StudyClass;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $user = apiAuth();
        $query = Sale::where('seller_id', $user->id)
            ->whereNull('refund_at');

        $studentIds = deepClone($query)->pluck('buyer_id')->toArray();
       
        $getStudentCount = count($studentIds);
        $getWebinarsCount = count(array_filter(deepClone($query)->pluck('webinar_id')->toArray()));
        $getMeetingCount = count(array_filter(deepClone($query)->pluck('meeting_id')->toArray()));

 
        $sales = $query->handleFilters()->orderBy('created_at', 'desc')
            ->get()->map(function ($sale) {
               
                return $sale->details ;
            });

        return apiResponse2(1, 'retrieved', trans('public.retrieved'), [
            'sales' => $sales,
            'students_count' => $getStudentCount,
            'webinars_count' => $getWebinarsCount,
            'meetings_count' => $getMeetingCount,
            'total_sales' => $user->getSaleAmounts(),
            'class_sales'=>$user->classesSaleAmount() ,
            'meeting_sales'=>$user->meetingsSaleAmount()

        ]);
       
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
        $bundles = Bundle::all();
        $data = [
            'sales' => $sales,
            'studyClasses' => $studyClasses,
            'bundles' => $bundles
        ];
 
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
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
 
    public function toggleAccess($url_name, $sale_id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
 
        if (!$organization) {
            return response()->json(['message' => 'This organization is not found'], 404);
        }
 
        $sale = Sale::where('id', $sale_id)->first();
 
        if (!$sale) {
            return response()->json(['message' => 'Cannot find this User'], 404);
        }
 
        $sale->access_to_purchased_item = !$sale->access_to_purchased_item;
        $sale->save();
 
        return response()->json([
            'message' => 'Access status updated successfully.',
            'user_id' => $sale_id,
            'access_to_purchased_item' => $sale->access_to_purchased_item
        ], 200);
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
 
}
