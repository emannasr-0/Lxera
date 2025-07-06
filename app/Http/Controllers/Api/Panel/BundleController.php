<?php

namespace App\Http\Controllers\Api\Panel;

use App\BundleStudent;
use App\Http\Controllers\Api\Controller;
use App\Http\Controllers\Api\Web\PaymentController;
use App\Http\Resources\BundleResource;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Api\Bundle;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderAttachment;
use App\Models\InstallmentOrderPayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\RewardAccounting;
use App\Models\Api\Sale;
use App\Models\Enrollment;
use App\Models\SelectedInstallment;
use App\Models\SelectedInstallmentStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Gift;
use App\Models\Webinar;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class BundleController extends Controller
{
    public function buyWithPoint($id)
    {
        $user = apiAuth();

        $bundle = Bundle::where('id', $id)
            ->where('status', 'active')
            ->first();
        if (!$bundle) {
            abort(404);
        }

        if (empty($bundle->points)) {
            return apiResponse2(0, 'no_points', trans('update.can_not_buy_this_bundle_with_point'));
        }

        if ($user->getRewardPoints() < $bundle->points) {
            return apiResponse2(0, 'no_enough_points', trans('update.you_have_no_enough_points_for_this_bundle'));
        }

        $checkCourseForSale = $bundle->checkWebinarForSale($user);

        if ($checkCourseForSale != 'ok') {
            return $checkCourseForSale;
        }

        Sale::create([
            'buyer_id' => $user->id,
            'seller_id' => $bundle->creator_id,
            'bundle_id' => $bundle->id,
            'type' => Sale::$bundle,
            'payment_method' => Sale::$credit,
            'amount' => 0,
            'total_amount' => 0,
            'created_at' => time(),
        ]);

        RewardAccounting::makeRewardAccounting($user->id, $bundle->points, 'withdraw', null, false, RewardAccounting::DEDUCTION);

        return apiResponse2(1, 'paid', trans('update.success_pay_bundle_with_point_msg'));
    }

    public function free(Request $request, $id)
    {
        $user = apiAuth();

        $bundle = Bundle::where('id', $id)
            ->where('status', 'active')
            ->first();
        if (!$bundle) {
            abort(404);
        }

        $checkCourseForSale = $bundle->checkWebinarForSale($user);

        if ($checkCourseForSale != 'ok') {
            return $checkCourseForSale;
        }

        if (!empty($bundle->price) and $bundle->price > 0) {
            return apiResponse2(0, 'not_free', trans('update.bundle_not_free'));
        }

        Sale::create([
            'buyer_id' => $user->id,
            'seller_id' => $bundle->creator_id,
            'bundle_id' => $bundle->id,
            'type' => Sale::$bundle,
            'payment_method' => Sale::$credit,
            'amount' => 0,
            'total_amount' => 0,
            'created_at' => time(),
        ]);
        return apiResponse2(1, 'enrolled', trans('cart.success_pay_msg_for_free_course'));
    }

    public function programPaymentStep1()
    {
        $user = auth('api')->user();
        $student = $user->Student;

        // return response()->json(1);
        $studentBundles = BundleStudent::where('student_id', $student?->id ?? null)->groupBy('bundle_id')->get()->reverse();

        /* Installments */
        $bundleInstallments = [];
        $programs = [];

        $egp_exchange = \App\Models\Currency::where('currency', 'EGP')->first();
        $sar_exchange = \App\Models\Currency::where('currency', 'SAR')->first();

        foreach ($studentBundles as $studentBundle) {
            $hasBought = $studentBundle->bundle->checkUserHasBought($user);
            $canSale = ($studentBundle->bundle->canSale() && !$hasBought);
            //currency 
            $country_currency = Session::get('country_currency');
            if ($country_currency == "EGP") {
                $new_price = (int)($studentBundle->bundle->price * $egp_exchange->exchange_rate);
            } elseif ($country_currency == "SAR") {
                $new_price = (int)($studentBundle->bundle->price * $sar_exchange->exchange_rate);
            }


            //
            $program = [
                'id' => $studentBundle->bundle->id,
                'title' => $studentBundle->bundle->title,
                'price' => $studentBundle->bundle->price,
                'country_currency' => Session::get('country_currency'),
                'new_price' => $new_price ?? "",
                'has_bought' => $hasBought,
                "bought_type" => !empty($studentBundle->bundle->getInstallmentOrder()) ? 'installment' : 'cache',
                'installment_plan' => null,
            ];
            // Check if the bundle meets the conditions
            if ($canSale && !empty($studentBundle->bundle->price) && $studentBundle->bundle->price > 0 && getInstallmentsSettings('status') && (empty($user) || $user->enable_installments)) {
                $installmentPlans = new InstallmentPlans($user);
                $installment = $installmentPlans->getPlans('bundles', $studentBundle->bundle->id, $studentBundle->bundle->type, $studentBundle->bundle->category_id, $studentBundle->bundle->teacher_id)->last();

                // $bundleInstallments[$studentBundle->id] = [
                //     'bundle' => $studentBundle->bundle,
                //     'installments' => $installments,
                // ];

                if ($installment) {
                    if ($country_currency == "EGP") {
                        $new_price = (int)($installment->totalPayments() * $egp_exchange->exchange_rate);

                        $new_upfront = (int)handlePrice($installment->getUpfront());


                        $new_upfront = (int)($new_upfront * $egp_exchange->exchange_rate);
                    } elseif ($country_currency == "SAR") {
                        $new_price = (int)($installment->totalPayments() * $sar_exchange->exchange_rate);
                        $new_upfront = (int)handlePrice($installment->getUpfront());
                        $new_upfront = (int)($new_upfront * $sar_exchange->exchange_rate);
                    } else {
                        $new_price = '';
                        $new_upfront = '';
                    }

                    $program['installment_plan'] = [
                        'id' => $installment->id,
                        'title' => $installment->main_title,
                        'description' => $installment->main_title,
                        'totalPayments' => $installment->totalPayments(),
                        'total_new_price' => $new_price,
                        'upfront' => !empty($installment->upfront) ? trans('update.amount_upfront', ['amount' => handlePrice($installment->getUpfront())]) . ($installment->upfront_type == 'percent' ? " ({$installment->upfront}%)" : '') : trans('update.no_upfront'),
                        'new_upfront' => $new_upfront,
                        'last_step_date' => $installment->steps->last()->getDeadlineTitle(1, $studentBundle->bundle->id),
                        'steps' => [],
                    ];

                    foreach ($installment->steps as $installmentStep) {
                        $step_id = SelectedInstallmentStep::where('installment_step_id', $installmentStep->id)->first()->id;
                        $program['installment_plan']['steps'][] = $installmentStep->getDeadlineTitle($studentBundle->bundle->price, $studentBundle->bundle->id) . '=> Step ID : ' . $step_id;
                    }
                }
            } else {

                $bundleInstallments[$studentBundle->id] = [
                    'bundle' => $studentBundle,
                    'installments' => null,
                ];
            }


            $programs[] = $program;
        }



        return sendResponse($programs, "programs that user has applied");
    }
    public function programPaymentStep()
    {
        $user = auth('api')->user();
        $student = $user->Student;

        // Get student bundles (paid programs)
        $studentBundles = BundleStudent::where('student_id', $student?->id ?? null)
            ->groupBy('bundle_id')
            ->get()
            ->reverse();

        // Get student webinars (courses)
        $studentWebinars = Webinar::whereHas('groups.enrollments', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->get()
            ->reverse();
        // return response()->json($studentWebinars);
        $programs = [];
        $egp_exchange = \App\Models\Currency::where('currency', 'EGP')->first();
        $sar_exchange = \App\Models\Currency::where('currency', 'SAR')->first();

        // Process bundles (paid programs with possible installments)
        foreach ($studentBundles as $studentBundle) {
            $hasBought = $studentBundle->bundle->checkUserHasBought($user);
            $canSale = ($studentBundle->bundle->canSale() && !$hasBought);

            $country_currency = Session::get('country_currency');
            if ($country_currency == "EGP") {
                $new_price = (int)($studentBundle->bundle->price * $egp_exchange->exchange_rate);
            } elseif ($country_currency == "SAR") {
                $new_price = (int)($studentBundle->bundle->price * $sar_exchange->exchange_rate);
            }

            $program = [
                'type' => 'program',
                'id' => $studentBundle->bundle->id,
                'title' => $studentBundle->bundle->title,
                'price' => $studentBundle->bundle->price,
                'country_currency' => $country_currency,
                'new_price' => $new_price ?? "",
                'has_bought' => $hasBought,
                "bought_type" => !empty($studentBundle->bundle->getInstallmentOrder()) ? 'installment' : 'cache',
                'installment_plan' => null,
            ];

            if ($canSale && !empty($studentBundle->bundle->price) && $studentBundle->bundle->price > 0 && getInstallmentsSettings('status') && (empty($user) || $user->enable_installments)) {
                $installmentPlans = new InstallmentPlans($user);
                $installment = $installmentPlans->getPlans('bundles', $studentBundle->bundle->id, $studentBundle->bundle->type, $studentBundle->bundle->category_id, $studentBundle->bundle->teacher_id)->last();

                if ($installment) {
                    if ($country_currency == "EGP") {
                        $new_price = (int)($installment->totalPayments() * $egp_exchange->exchange_rate);
                        $new_upfront = (int)handlePrice($installment->getUpfront());
                        $new_upfront = (int)($new_upfront * $egp_exchange->exchange_rate);
                    } elseif ($country_currency == "SAR") {
                        $new_price = (int)($installment->totalPayments() * $sar_exchange->exchange_rate);
                        $new_upfront = (int)handlePrice($installment->getUpfront());
                        $new_upfront = (int)($new_upfront * $sar_exchange->exchange_rate);
                    } else {
                        $new_price = '';
                        $new_upfront = '';
                    }

                    $program['installment_plan'] = [
                        'id' => $installment->id,
                        'title' => $installment->main_title,
                        'description' => $installment->main_title,
                        'totalPayments' => $installment->totalPayments(),
                        'total_new_price' => $new_price,
                        'upfront' => !empty($installment->upfront) ? trans('update.amount_upfront', ['amount' => handlePrice($installment->getUpfront())]) . ($installment->upfront_type == 'percent' ? " ({$installment->upfront}%)" : '') : trans('update.no_upfront'),
                        'new_upfront' => $new_upfront,
                        'last_step_date' => $installment->steps->last()->getDeadlineTitle(1, $studentBundle->bundle->id),
                        'steps' => [],
                    ];

                    foreach ($installment->steps as $installmentStep) {
                        $step_id = SelectedInstallmentStep::where('installment_step_id', $installmentStep->id)->first()->id;
                        $program['installment_plan']['steps'][] = $installmentStep->getDeadlineTitle($studentBundle->bundle->price, $studentBundle->bundle->id) . '=> Step ID : ' . $step_id;
                    }
                }
            }

            $programs[] = $program;
        }

        // Process webinars (courses)
        foreach ($studentWebinars as $webinar) {
            // Check if user has purchased the webinar
            $hasBought = $webinar->checkUserHasBought($user); // Assuming Webinar model has this method
            $canSale = $webinar->canSale() && !$hasBought;

            $country_currency = Session::get('country_currency');
            if ($country_currency == "EGP") {
                $new_price = (int)($webinar->price * $egp_exchange->exchange_rate);
            } elseif ($country_currency == "SAR") {
                $new_price = (int)($webinar->price * $sar_exchange->exchange_rate);
            }

            $programs[] = [
                'type' => 'course',
                'id' => $webinar->id,
                'title' => $webinar->title,
                'price' => $webinar->price,
                'country_currency' => $country_currency,
                'new_price' => $new_price ?? "",
                'has_bought' => $hasBought,
                'bought_type' => 'cache', // Webinars are always paid in full
                'start_date' => $webinar->start_date ?? null,
                'duration' => $webinar->duration ?? null,
            ];
        }

        return sendResponse($programs, "Programs and courses that user has applied");
    }

    public function purchase_bundle(Request $request, $installmentId = null, $carts = null)
    {
        $user = apiAuth();
        $data = $request->all();
        $itemType   = $request->get('item_type') ?? 'bundles';   // default bundle
        $itemId     = $request->get('item_id');
        if (!empty($installmentId)) {
            $itemId = $request->get('item_id');
            $itemType = $request->get('item_type') ?? 'bundles';

            if (empty($user) or !$user->enable_installments) {

                return sendError([], trans('update.you_cannot_use_installment_plans'));
            }


            $installment = Installment::query()->where('id', $installmentId)
                ->where('enable', true)
                ->withCount([
                    'steps'
                ])
                ->first();

            if (!empty($installment)) {
                if (!$installment->hasCapacity()) {
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => trans('update.installment_not_capacity'),
                        'status' => 'error'
                    ];
                    return sendError([], trans('update.installment_not_capacity'));
                }


                $rules = [
                    'item_id' => 'required',
                    'item_type' => 'nullable|in:bundles',
                ];

                validateParam($data, $rules);


                $attachments = (!empty($data['attachments']) and count($data['attachments'])) ? array_map('array_filter', $data['attachments']) : [];

                $attachments = !empty($attachments) ? array_filter($attachments) : [];

                if ($installment->request_uploads) {
                    if (count($attachments) < 1) {
                        return sendError([
                            'attachment' => trans('validation.required', ['attribute' => 'المرفق'])
                        ], trans('validation error'));
                    }
                }

                if (!empty($installment->capacity)) {
                    $openOrdersCount = InstallmentOrder::query()->where('installment_id', $installment->id)
                        ->where('status', 'open')
                        ->count();

                    if ($openOrdersCount >= $installment->capacity) {
                        return sendError([], trans('update.installment_not_capacity'));
                    }
                }

                $item = $this->getItem($itemId, $itemType, $user);

                if (!empty($item)) {
                    if (!$installment->bundles->contains($item) and $request->item_type == "bundles") {

                        return sendError([], trans('update.installment_not_allowed_for_this_item'));
                    }

                    $productOrder = null;

                    $columnName = $this->getColumnByItemType($itemType);

                    $status = 'paying';

                    if (empty($installment->upfront)) {
                        $status = 'open';

                        if ($installment->needToVerify()) {
                            $status = 'pending_verification';
                        }
                    }

                    $itemPrice = $item->getPrice();

                    $order = InstallmentOrder::query()->updateOrCreate([
                        'installment_id' => $installment->id,
                        'user_id' => $user->id,
                        $columnName => $itemId,
                        'product_order_id' => (!empty($productOrder)) ? $productOrder->id : null,
                        'item_price' => $itemPrice,
                        'status' => $status,
                    ], [
                        'created_at' => time(),
                    ]);

                    /* Attachments */
                    $this->handleAttachments($attachments, $order);

                    /* Store Installment Data */
                    $this->handleSelectedInstallment($user, $order, $installment);

                    $notifyOptions = [
                        '[u.name]' => $order->user->full_name,
                        '[installment_title]' => $installment->main_title,
                        '[time.date]' => dateTimeFormat(time(), 'j M Y - H:i'),
                        '[amount]' => handlePrice($itemPrice),
                    ];

                    sendNotification("instalment_request_submitted", $notifyOptions, $order->user_id);
                    sendNotification("instalment_request_submitted_for_admin", $notifyOptions, 1);


                    /* Payment and Cart */
                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment::query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'selected_installment_step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => 'paying',
                        ], [
                            'created_at' => time(),
                        ]);

                        // Cart::updateOrCreate([
                        //     'creator_id' => $user->id,
                        //     'installment_payment_id' => $installmentPayment->id,
                        // ], [
                        //     'created_at' => time()
                        // ]);

                        $installment_payment_id = $installmentPayment->id;

                        // $paymentChannels = PaymentChannel::where('status', 'active')->get();

                        $calculate = $this->calculatePrice($bundle = null, $installment_payment_id, $user, $discountCoupon = null);

                        $order = $this->createOrderAndOrderItems($bundle = $item, $installment_payment_id, $calculate, $user, $discountCoupon = null);

                        if (!empty($order) and $order->total_amount > 0) {
                            $razorpay = false;
                            $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));

                            // foreach ($paymentChannels as $paymentChannel) {
                            //     if ($paymentChannel->class_name == 'Razorpay' and (!$isMultiCurrency or in_array(currency(), $paymentChannel->currencies))) {
                            //         $razorpay = true;
                            //     }
                            // }
                            $data['order'] = [
                                'id' => $order->id,
                                'status' => $order->status,
                                'total_amount' => $order->total_amount,
                            ];

                            return sendResponse($data, "order created successfully, continue to pay");
                        } else {

                            return $this->handlePaymentOrderWithZeroTotalAmount($request, $order);
                        }
                    } else {

                        // if ($installment->needToVerify()) {
                        //     sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                        //     sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1); // Admin

                        //     //TODO: edit response
                        //     return redirect('/installments/request_submitted');
                        // } else {
                        sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                        $data['order'] = [
                            'id' => $order->id,
                            'status' => $order->status,
                            'total_amount' => $order->total_amount,
                        ];
                        return sendResponse($data, trans('update.your_installment_purchase_has_been_successfully_completed'));
                        // }
                    }
                }
            }

            return $this->notFound();
        } else {
            $rules = [
                'item_id' => 'required',
                'item_type' => 'nullable|in:bundles,webinars',
            ];


            validateParam($data, $rules);
            if ($itemType === 'bundles') {
               $item_id = $request->item_id;
               $item = Bundle::find( $item_id);
            } else {
                $item_id = $request->item_id;
                 $item = Webinar::find( $item_id);
            }
            // $bundle_id = $request->item_id;
            // $bundle = Bundle::find($bundle_id);
            // $paymentChannels = PaymentChannel::where('status', 'active')->get();

            $calculate = $this->calculatePrice($item, $installment_payment_id = null, $user, $discountCoupon = null);

            $order = $this->createOrderAndOrderItems($item, $installment_payment_id = null, $calculate, $user, $discountCoupon = null);

            if (!empty($order) and $order->total_amount > 0) {
                // $razorpay = false;
                // $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));

                // foreach ($paymentChannels as $paymentChannel) {
                //     if ($paymentChannel->class_name == 'Razorpay' and (!$isMultiCurrency or in_array(currency(), $paymentChannel->currencies))) {
                //         $razorpay = true;
                //     }
                // }

                $data['order'] = [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                ];

                return sendResponse($data, "order created successfully, continue to pay");
            } else {
                return $this->handlePaymentOrderWithZeroTotalAmount($request, $order);
            }
        }
    }


    public function purchases(Request $request)
    {
        $user = auth('api')->user();

        $giftsIds = Gift::query()->where('email', $user->email)
            ->where('status', 'active')
            ->whereNull('product_id')
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->pluck('id')
            ->toArray();

        $query = Sale::query()
            ->where(function ($query) use ($user, $giftsIds) {
                $query->where('sales.buyer_id', $user->id)
                    ->orWhereIn('sales.gift_id', $giftsIds);
            })
            ->whereNull('sales.refund_at')
            ->where('access_to_purchased_item', true)
            ->where(function ($query) {
                $query->whereNotNull('sales.bundle_id')
                    ->whereIn('sales.type', ['bundle', 'installment_payment'])
                    ->whereHas('bundle');
            })
            ->distinct()
            ->select('sales.bundle_id');



        $sales = deepClone($query)
            ->with([
                'webinar' => function ($query) {
                    $query->with([
                        'files',
                        'reviews' => function ($query) {
                            $query->where('status', 'active');
                        },
                        'category',
                        'teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                    ]);
                    $query->withCount([
                        'sales' => function ($query) {
                            $query->whereNull('refund_at');
                        }
                    ]);
                },
                'bundle' => function ($query) {
                    $query->with([
                        'reviews' => function ($query) {
                            $query->where('status', 'active');
                        },
                        'category',
                        'teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                    ]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(6);


        $time = time();

        $giftDurations = 0;
        $giftUpcoming = 0;
        $giftPurchasedCount = 0;

        foreach ($sales as $sale) {
            if (!empty($sale->gift_id)) {
                $gift = $sale->gift;

                $sale->webinar_id = $gift->webinar_id;
                $sale->bundle_id = $gift->bundle_id;

                $sale->webinar = !empty($gift->webinar_id) ? $gift->webinar : null;
                $sale->bundle = !empty($gift->bundle_id) ? $gift->bundle : null;

                $sale->gift_recipient = !empty($gift->receipt) ? $gift->receipt->full_name : $gift->name;
                $sale->gift_sender = $sale->buyer->full_name;
                $sale->gift_date = $gift->date;;

                $giftPurchasedCount += 1;

                if (!empty($sale->webinar)) {
                    $giftDurations += $sale->webinar->duration;

                    if ($sale->webinar->start_date > $time) {
                        $giftUpcoming += 1;
                    }
                }

                if (!empty($sale->bundle)) {
                    $bundleWebinars = $sale->bundle->bundleWebinars;

                    foreach ($bundleWebinars as $bundleWebinar) {
                        $giftDurations += $bundleWebinar->webinar->duration;
                    }
                }
            }
        }

        $purchasedCount = deepClone($query)
            ->where(function ($query) {
                $query->whereHas('webinar');
                $query->orWhereHas('bundle');
            })
            ->count();

        $webinarsHours = deepClone($query)->join('webinars', 'webinars.id', 'sales.webinar_id')
            ->select(DB::raw('sum(webinars.duration) as duration'))
            ->sum('duration');
        $bundlesHours = deepClone($query)->join('bundle_webinars', 'bundle_webinars.bundle_id', 'sales.bundle_id')
            ->join('webinars', 'webinars.id', 'bundle_webinars.webinar_id')
            ->select(DB::raw('sum(webinars.duration) as duration'))
            ->sum('duration');

        $hours = $webinarsHours + $bundlesHours + $giftDurations;

        $upComing = deepClone($query)->join('webinars', 'webinars.id', 'sales.webinar_id')
            ->where('webinars.start_date', '>', $time)
            ->count();

        $bundles = $sales->map(
            function ($sale) {
                return  BundleResource::make($sale->bundle);
            }
        );
        $paginatedBundles = new LengthAwarePaginator(
            $bundles,
            $sales->total(),
            $sales->perPage(),
            $sales->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $data = [
            'pageTitle' => trans('webinars.webinars_purchases_page_title'),
            // 'sales' => $sales,
            'bundles' => $paginatedBundles,
            'purchasedCount' => $purchasedCount + $giftPurchasedCount,
            'hours' => $hours,
            'upComing' => $upComing + $giftUpcoming

        ];

        return sendResponse($data,  "All purchased bundles are retrieved");
    }

    private function getItem($itemId, $itemType, $user)
    {
        if ($itemType == 'bundles') {
            $bundle = Bundle::where('id', $itemId)
                ->where('status', 'active')
                ->first();

            $hasBought = $bundle->checkUserHasBought($user);
            $canSale = ($bundle->canSale() and !$hasBought);

            if ($canSale and !empty($bundle->price)) {
                return $bundle;
            }
        }

        return null;
    }

    private function getColumnByItemType($itemType)
    {
        if ($itemType == 'bundles') {
            return 'bundle_id';
        }
    }

    private function handleAttachments($attachments, $order)
    {
        InstallmentOrderAttachment::query()->where('installment_order_id', $order->id)->delete();

        if (!empty($attachments)) {
            $attachmentsInsert = [];

            foreach ($attachments as $attachment) {
                if (!empty($attachment['title']) and !empty($attachment['file'])) {
                    $attachmentsInsert[] = [
                        'installment_order_id' => $order->id,
                        'title' => $attachment['title'],
                        'file' => $attachment['file'],
                    ];
                }
            }

            if (!empty($attachmentsInsert)) {
                InstallmentOrderAttachment::query()->insert($attachmentsInsert);
            }
        }
    }


    private function handlePaymentOrderWithZeroTotalAmount(Request $request, $order)
    {
        $order->update([
            'payment_method' => Order::$paymentChannel
        ]);

        $paymentController = new PaymentController();

        $paymentController->setPaymentAccounting($order);


        $order->update([
            'status' => Order::$paid
        ]);

        return (new PaymentController())->payStatusJson($request, $order->id);
    }

private function calculatePrice($item, $installment_payment_id, $user, $discountCoupon = null)
{
    $financialSettings = getFinancialSettings();

    $subTotal         = 0;
    $totalDiscount    = 0;
    $tax              = (!empty($financialSettings['tax']) && $financialSettings['tax'] > 0)
                        ? $financialSettings['tax'] : 0;
    $taxPrice         = 0;
    $commissionPrice  = 0;
    $commission       = 0;
    $taxIsDifferent   = true;        // keep your original flag

    /* delegate real work to handleOrderPrices() */
    $orderPrices      = $this->handleOrderPrices(
        $item,
        $installment_payment_id,
        $user,
        $taxIsDifferent,
        $discountCoupon
    );

    $subTotal       += $orderPrices['sub_total'];
    $totalDiscount  += $orderPrices['total_discount'];
    $tax             = $orderPrices['tax'];
    $taxPrice       += $orderPrices['tax_price'];
    $commission     += $orderPrices['commission'];
    $commissionPrice+= $orderPrices['commission_price'];
    $taxIsDifferent  = $orderPrices['tax_is_different'];

    if ($totalDiscount > $subTotal) {
        $totalDiscount = $subTotal;
    }

    $subTotalWithoutDiscount = $subTotal - $totalDiscount;
    $productDeliveryFee      = 0;

    $total = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;
    if ($total < 0) {
        $total = 0;
    }

    return [
        'sub_total'            => round($subTotal, 2),
        'total_discount'       => round($totalDiscount, 2),
        'tax'                  => $tax,
        'tax_price'            => round($taxPrice, 2),
        'commission'           => $commission,
        'commission_price'     => round($commissionPrice, 2),
        'total'                => round($total, 2),
        'product_delivery_fee' => round($productDeliveryFee, 2),
        'tax_is_different'     => $taxIsDifferent,
    ];
}

    private function handleSelectedInstallment($user, $order, $installment)
    {
        $selected = SelectedInstallment::query()->updateOrCreate([
            'user_id' => $user->id,
            'installment_id' => $installment->id,
            'installment_order_id' => $order->id,
        ], [
            'start_date' => $installment->start_date,
            'end_date' => $installment->end_date,
            'upfront' => $installment->upfront,
            'upfront_type' => $installment->upfront_type,
            'created_at' => time(),
        ]);

        SelectedInstallmentStep::query()->where('selected_installment_id', $selected->id)->delete();

        $insert = [];

        foreach ($installment->steps as $step) {
            $insert[] = [
                'selected_installment_id' => $selected->id,
                'installment_step_id' => $step->id,
                'deadline' => $step->deadline,
                'amount' => $step->amount,
                'amount_type' => $step->amount_type,
            ];
        }

        if (!empty($insert)) {
            SelectedInstallmentStep::query()->insert($insert);
        }
    }


public function handleOrderPrices($item, $installment_payment_id = null, $user,
                                  $taxIsDifferent = false, $discountCoupon = null)
{
    // seller → bundles use creator; webinars use teacher
    $seller = $item instanceof \App\Models\Bundle
                ? $item->creator
                : ($item->teacher ?? null);

    $financialSettings = getFinancialSettings();

    $subTotal        = 0;
    $totalDiscount   = 0;
    $tax             = (!empty($financialSettings['tax']) && $financialSettings['tax'] > 0)
                       ? $financialSettings['tax'] : 0;
    $taxPrice        = 0;
    $commissionPrice = 0;

    /* commission percentage */
    if ($seller) {
        $commission = $seller->getCommission();              // model method
    } else {
        $commission = !empty($financialSettings['commission'])
                      ? (int)$financialSettings['commission'] : 0;
    }

    /* --------- (A) Normal pay‑in‑full ---------- */
    if ($item && empty($installment_payment_id)) {

        $price               = $item->price;
        $discount            = 0;                  // apply your own coupon logic here
        $priceWithoutDiscount= $price - $discount;

        if ($tax > 0 && $priceWithoutDiscount > 0) {
            $taxPrice += $priceWithoutDiscount * $tax / 100;
        }
        if ($commission > 0) {
            $commissionPrice += $priceWithoutDiscount > 0
                              ? $priceWithoutDiscount * $commission / 100 : 0;
        }

        $totalDiscount += $discount;
        $subTotal      += $price;
    }

    /* --------- (B) Up‑front installment payment ---------- */
    elseif (!empty($installment_payment_id)) {

        $installmentOrderPayment = InstallmentOrderPayment::findOrFail($installment_payment_id);
        $price               = $installmentOrderPayment->amount;
        $discount            = 0;
        $priceWithoutDiscount= $price - $discount;

        if ($tax > 0 && $priceWithoutDiscount > 0) {
            $taxPrice += $priceWithoutDiscount * $tax / 100;
        }
        if ($commission > 0) {
            $commissionPrice += $priceWithoutDiscount > 0
                              ? $priceWithoutDiscount * $commission / 100 : 0;
        }

        $totalDiscount += $discount;
        $subTotal      += $price;
    }

    if ($totalDiscount > $subTotal) {
        $totalDiscount = $subTotal;
    }

    return [
        'sub_total'        => round($subTotal, 2),
        'total_discount'   => round($totalDiscount, 2),
        'tax'              => $tax,
        'tax_price'        => round($taxPrice, 2),
        'commission'       => $commission,
        'commission_price' => round($commissionPrice, 2),
        'tax_is_different' => $taxIsDifferent,
    ];
}


 public function createOrderAndOrderItems($item, $installment_payment_id,
                                         array $calculate, $user,
                                         $discountCoupon = null)
{
    $totalCouponDiscount = 0;                      // inject coupon logic if needed
    $totalAmount         = $calculate['total'] - $totalCouponDiscount;

    /* ---------- 3.1 Order shell ---------- */
    $order = Order::create([
        'user_id'            => $user->id,
        'status'             => Order::$pending,
        'amount'             => $calculate['sub_total'],
        'tax'                => $calculate['tax_price'],
        'total_discount'     => $calculate['total_discount'] + $totalCouponDiscount,
        'total_amount'       => max($totalAmount, 0),
        'product_delivery_fee' => $calculate['product_delivery_fee'] ?? null,
        'created_at'         => time(),
    ]);

    /* ---------- 3.2 Per‑item prices ---------- */
    $orderPrices     = $this->handleOrderPrices(
        $item,
        $installment_payment_id,
        $user,
        false,
        $discountCoupon
    );

    $price           = $orderPrices['sub_total'];
    $totalDiscount   = $orderPrices['total_discount'];
    $tax             = $orderPrices['tax'];
    $taxPrice        = $orderPrices['tax_price'];
    $commission      = $orderPrices['commission'];
    $commissionPrice = $orderPrices['commission_price'];

    $productDeliveryFee = 0;
    $allDiscountPrice   = $totalDiscount;

    if ($totalCouponDiscount > 0 && $price > 0) {
        $percent          = ($price / $calculate['sub_total']) * 100;
        $allDiscountPrice += ($totalCouponDiscount * $percent) / 100;
    }

    $subTotalWithoutDiscount = $price - $allDiscountPrice;
    $totalAmount             = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

    /* ---------- 3.3 One OrderItem ---------- */
    OrderItem::create([
        'user_id'             => $user->id,
        'order_id'            => $order->id,
        'webinar_id'          => $item instanceof \App\Models\Webinar ? $item->id : null,
        'bundle_id'           => $item instanceof \App\Models\Bundle  ? $item->id : null,
        'installment_payment_id' => $installment_payment_id,
        'discount_id'         => $discountCoupon?->id,
        'amount'              => $price,
        'total_amount'        => $totalAmount,
        'tax'                 => $tax,
        'tax_price'           => $taxPrice,
        'commission'          => $commission,
        'commission_price'    => $commissionPrice,
        'product_delivery_fee'=> $productDeliveryFee,
        'discount'            => $allDiscountPrice,
        'created_at'          => time(),
    ]);

    return $order;
}
}
