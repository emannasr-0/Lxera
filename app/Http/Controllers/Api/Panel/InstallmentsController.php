<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Api\Web\PaymentController;
use App\Http\Controllers\Api\Controller;
use App\Mixins\Installment\InstallmentAccounting;
use App\Models\Bundle;
use App\Models\Cart;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Product;
use App\Models\RegistrationPackage;
use App\Models\SelectedInstallmentStep;
use App\Models\Subscribe;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;

class InstallmentsController extends Controller
{
    public function index()
    {
        $user = auth("api")->user();

        $query = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'paying');

        $openInstallmentsCount = deepClone($query)->where('status', 'open')->count();
        $pendingVerificationCount = deepClone($query)->where('status', 'pending_verification')->count();
        $finishedInstallmentsCount = $this->getFinishedInstallments($user);

        $orders = $query->with([
            'selectedInstallment' => function ($query) {
                $query->with([
                    'steps' => function ($query) {
                        $query->orderBy('deadline', 'asc');
                    },
                ]);
                $query->withCount([
                    'steps',
                ]);
            },
        ])->orderBy('created_at', 'desc')
            ->get();

        $ordersList = [];
        foreach ($orders as $order) {
            $getRemainedInstallments = $this->getRemainedInstallments($order);
            $order->upcoming_installment = $this->getUpcomingInstallment($order);

            // $order->remained_installments_count = $getRemainedInstallments['total'];
            // $order->remained_installments_amount = $getRemainedInstallments['amount'];
            $upcoming_installment_deadline = null;
            if ($order->upcoming_installment) {
                if ($order->selectedInstallment->deadline_type == 'days')
                    $upcoming_installment_deadline = dateTimeFormat($order->upcoming_installment->deadline * 86400 + $order->bundle->start_date, 'j M Y');
                else
                    $upcoming_installment_deadline = dateTimeFormat($order->upcoming_installment->deadline, 'j M Y');
            }
            $itemPrice = $order->getItemPrice();

            $upfrontPayment = $order->payments
                ->where('type', 'upfront')
                ->first();


            $steps = [];
            foreach ($order->selectedInstallment->steps as $step) {
                $stepPayment = $order->payments
                    ->where('selected_installment_step_id', $step->id)
                    ->where('status', 'paid')
                    ->first();
                if ($order->selectedInstallment->deadline_type == 'days') {
                    $dueAt =
                        $step->deadline * 86400 +
                        $order->bundle->start_date;
                } else {
                    $dueAt = $step->deadline;
                }
                $isOverdue = ($dueAt < time() and empty($stepPayment));

                dateTimeFormat($dueAt, 'j M Y');

                $steps[] = [
                    'id' => $step->id,
                    'title' => $step->installmentStep->title,
                    'amount' => $step->amount,
                    'deadline' =>  dateTimeFormat($dueAt, 'j M Y'),
                    'payment_date' => !empty($stepPayment) ? dateTimeFormat($stepPayment->created_at, 'j M Y H:i') : '--',
                    'payment_status' => !empty($stepPayment) ? trans('public.paid') : trans('update.unpaid'),
                    'isOverdue' => $isOverdue
                ];
            }

            $newOrder = [
                'id' => $order->id,
                'status' => $order->status,
                'registerd_date' => dateTimeFormat($order->created_at, 'j M Y H:i'),
                'total_installments_count' => $order->selectedInstallment->steps_count + 1,
                'total_installments_amount' => ($order->selectedInstallment->totalPayments($itemPrice, false) + (!empty($order->selectedInstallment->upfront) ? $order->selectedInstallment->getUpfront($itemPrice) : 0)),
                'remained_installments_count' => $getRemainedInstallments['total'],
                'remained_installments_amount' => $getRemainedInstallments['amount'],
                // 'upcoming_installment' => $this->getUpcomingInstallment($order),
                'upcoming_installment_deadline' => $upcoming_installment_deadline,
                'upcoming_installment_amount' => $order->upcoming_installment?->getPrice($itemPrice) ?? 0,
                'has_overdue' => $order->checkOrderHasOverdue(),
                'overdue_count' => 0,
                'overdue_amount' => 0,
                'orderItem' => [
                    'id' => $order->getItem()->id,
                    'title' => $order->getItem()->title,
                    'itemType' => $order->getItemType(),
                    'itemPrice' =>  $itemPrice,
                ],

                'upfront' => [
                    'title' => trans('update.upfront'),
                    'amount' => $order->selectedInstallment->getUpfront($itemPrice),
                    'payment_date' => !empty($upfrontPayment) ? dateTimeFormat($upfrontPayment->created_at, 'j M Y H:i') : '--',
                    'payment_status' => !empty($upfrontPayment) ? trans('public.paid') : trans('update.unpaid'),
                ],
                'installments_steps' => $steps,
                'orderIsCompleted' => $order->isCompleted(),

            ];


            // is overdue
            $hasOverdue = $order->checkOrderHasOverdue();
            // $order->has_overdue = $hasOverdue;
            // $order->overdue_count = 0;
            // $order->overdue_amount = 0;

            if ($hasOverdue) {
                $getOrderOverdueCountAndAmount = $order->getOrderOverdueCountAndAmount();
                $order->overdue_count = $getOrderOverdueCountAndAmount['count'];
                $order->overdue_amount = $getOrderOverdueCountAndAmount['amount'];
                $newOrder["overdue_count"] = $getOrderOverdueCountAndAmount['count'];
                $newOrder["overdue_amount"] = $getOrderOverdueCountAndAmount['amount'];
            }

            $ordersList[] = $newOrder;
        }

        $overdueInstallmentsCount = $this->getOverdueInstallments($user);

        $data = [
            'pageTitle' => trans('update.installments'),
            // 'openInstallmentsCount' => $openInstallmentsCount,
            // 'pendingVerificationCount' => $pendingVerificationCount,
            // 'finishedInstallmentsCount' => $finishedInstallmentsCount,
            'overdueInstallmentsCount' => $overdueInstallmentsCount,
            // 'orders' => $orders,
            'ordersList' => $ordersList,
        ];

        return sendResponse($data, "All installments are retrieved");
    }

    private function getRemainedInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $itemPrice = $order->getItemPrice();

        foreach ($order->selectedInstallment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('selected_installment_step_id', $step->id)
                ->where('status', 'paid')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                })
                ->first();

            if (empty($payment)) {
                $total += 1;
                $amount += $step->getPrice($itemPrice);
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getOverdueOrderInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $time = time();
        $itemPrice = $order->getItemPrice();

        foreach ($order->selectedInstallment->steps as $step) {
            if ($order->selectedInstallment->deadline_type == 'days') {
                $dueAt =
                    $step->deadline * 86400 +
                    $order->bundle->start_date;
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

    private function getOverdueInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            if ($order->checkOrderHasOverdue()) {
                $count += 1;
            }
        }

        return $count;
    }

    private function getFinishedInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $steps = $order->selectedInstallment->steps;
            $paidAllSteps = true;

            foreach ($steps as $step) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('selected_installment_step_id', $step->id)
                    ->where('status', 'paid')
                    ->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    })
                    ->first();

                if (empty($payment)) {
                    $paidAllSteps = false;
                }
            }

            if ($paidAllSteps) {
                $count += 1;
            }
        }

        return $count;
    }

    public function show($orderId)
    {
        $user = auth()->user();

        $order = InstallmentOrder::query()
            ->where('id', $orderId)
            ->where('user_id', $user->id)
            ->with([
                'selectedInstallment' => function ($query) {
                    $query->with([
                        'steps' => function ($query) {
                            $query->orderBy('deadline', 'asc');
                        },
                    ]);
                },
            ])
            ->first();

        if (!empty($order) and !in_array($order->status, ['refunded', 'canceled'])) {

            $getRemainedInstallments = $this->getRemainedInstallments($order);
            $getOverdueOrderInstallments = $this->getOverdueOrderInstallments($order);

            $totalParts = $order->selectedInstallment->steps->count();
            $remainedParts = $getRemainedInstallments['total'];
            $remainedAmount = $getRemainedInstallments['amount'];
            $overdueAmount = $getOverdueOrderInstallments['amount'];

            $data = [
                'pageTitle' => trans('update.installments'),
                'totalParts' => $totalParts,
                'remainedParts' => $remainedParts,
                'remainedAmount' => $remainedAmount,
                'overdueAmount' => $overdueAmount,
                'order' => $order,
                'payments' => $order->payments,
                'installment' => $order->selectedInstallment,
                'itemPrice' => $order->getItemPrice(),
            ];

            return view('web.default.panel.financial.installments.details', $data);
        }

        abort(404);
    }

    public function cancelVerification($orderId)
    {
        if (getInstallmentsSettings('allow_cancel_verification')) {
            $user = auth()->user();

            $order = InstallmentOrder::query()
                ->where('id', $orderId)
                ->where('user_id', $user->id)
                ->where('status', 'pending_verification')
                ->first();

            if (!empty($order)) {
                $installmentRefund = new InstallmentAccounting();
                $installmentRefund->refundOrder($order);

                return response()->json([
                    'code' => 200,
                    'title' => trans('public.request_success'),
                    'text' => trans('update.order_status_changes_to_canceled'),
                ]);
            }
        }

        abort(404);
    }

    public function payUpcomingPart(Request $request, $orderId)
    {
        $user = auth()->user();

        $order = InstallmentOrder::query()
            ->where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($order)) {
            $upcomingStep = $this->getUpcomingInstallment($order);

            if (!empty($upcomingStep)) {
                return $this->handlePayStep($request, $order, $upcomingStep);
            }
        }

        abort(404);
    }

    public function payStep(Request $request, $orderId, $stepId)
    {
        $user = auth('api')->user();

        $order = InstallmentOrder::query()
            ->where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($order)) {
            $selectedInstallment = $order->selectedInstallment;

            if (!empty($selectedInstallment)) {
                $step = SelectedInstallmentStep::query()
                    ->where('selected_installment_id', $selectedInstallment->id)
                    ->where('id', $stepId)
                    ->first();

                if (!empty($step)) {
                    return $this->handlePayStep($request, $order, $step);
                }
            }
        }

        return $this->notFound();
    }

    private function handlePayStep(Request $request, $order, $step)
    {
        $installmentPayment = InstallmentOrderPayment::query()->updateOrCreate([
            'installment_order_id' => $order->id,
            'sale_id' => null,
            'type' => 'step',
            'selected_installment_step_id' => $step->id,
            'amount' => $step->getPrice($order->getItemPrice()),
            'status' => 'paying',
        ], [
            'created_at' => time(),
        ]);

        // Cart::updateOrCreate([
        //     'creator_id' => $order->user_id,
        //     'installment_payment_id' => $installmentPayment->id,
        // ], [
        //     'created_at' => time()
        // ]);
        $installment_payment_id = $installmentPayment->id;
        $user = User::findOrFail($order->user_id);
        // $paymentChannels = PaymentChannel::where('status', 'active')->get();

        $calculate = $this->calculatePrice($bundle = null, $installment_payment_id, $user, $discountCoupon = null);

        $order = $this->createOrderAndOrderItems($bundle = $order->bundle ?? null, $installment_payment_id, $calculate, $user, $discountCoupon = null);

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
    }

    private function calculatePrice($bundle, $installment_payment_id, $user, $discountCoupon)
    {
        $financialSettings = getFinancialSettings();

        $subTotal = 0;
        $totalDiscount = 0;
        $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
        $taxPrice = 0;
        $commissionPrice = 0;
        $commission = 0;

        // $cartHasCertificate = array_filter($carts->pluck('certificate_template_id')->toArray());
        // $cartHasInstallmentPayment = array_filter($carts->pluck('installment_payment_id')->toArray());

        $taxIsDifferent = (1
            // or count($cartHasCertificate) or count($cartHasInstallmentPayment)
        );

        $orderPrices = $this->handleOrderPrices($bundle, $installment_payment_id, $user, $taxIsDifferent, $discountCoupon);
        $subTotal += $orderPrices['sub_total'];
        $totalDiscount += $orderPrices['total_discount'];
        $tax = $orderPrices['tax'];
        $taxPrice += $orderPrices['tax_price'];
        $commission += $orderPrices['commission'];
        $commissionPrice += $orderPrices['commission_price'];
        $taxIsDifferent = $orderPrices['tax_is_different'];

        if ($totalDiscount > $subTotal) {
            $totalDiscount = $subTotal;
        }

        $subTotalWithoutDiscount = $subTotal - $totalDiscount;
        $productDeliveryFee = 0;

        $total = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

        if ($total < 0) {
            $total = 0;
        }

        return [
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'tax' => $tax,
            'tax_price' => round($taxPrice, 2),
            'commission' => $commission,
            'commission_price' => round($commissionPrice, 2),
            'total' => round($total, 2),
            'product_delivery_fee' => round($productDeliveryFee, 2),
            'tax_is_different' => $taxIsDifferent,
        ];
    }

    public function handleOrderPrices($bundle, $installment_payment_id, $user, $taxIsDifferent = false, $discountCoupon = null)
    {
        $seller = $bundle->creator ?? null;
        $financialSettings = getFinancialSettings();

        $subTotal = 0;
        $totalDiscount = 0;
        $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
        $taxPrice = 0;
        $commissionPrice = 0;

        if (!empty($seller)) {
            $commission = $seller->getCommission();
        } else {
            $commission = 0;

            if (!empty($financialSettings) and !empty($financialSettings['commission'])) {
                $commission = (int) $financialSettings['commission'];
            }
        }

        if (!empty($bundle)) {
            // $item = !empty($cart->webinar_id) ? $cart->webinar : $cart->bundle;
            $item = $bundle;

            $price = $item->price;
            $discount = null;
            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            if (!empty($commission) and $commission > 0) {
                $commissionPrice += $priceWithoutDiscount > 0 ? $priceWithoutDiscount * $commission / 100 : 0;
            }

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($installment_payment_id)) {
            $installmentOrderPayment = InstallmentOrderPayment::findOrFail($installment_payment_id);
            $price = $installmentOrderPayment->amount;
            // $cart->installmentPayment->amount;
            $discount = 0;

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            if (!empty($commission) and $commission > 0) {
                $commissionPrice += $priceWithoutDiscount > 0 ? $priceWithoutDiscount * $commission / 100 : 0;
            }

            $totalDiscount += $discount;
            $subTotal += $price;
        }

        if ($totalDiscount > $subTotal) {
            $totalDiscount = $subTotal;
        }

        return [
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'tax' => $tax,
            'tax_price' => round($taxPrice, 2),
            'commission' => $commission,
            'commission_price' => round($commissionPrice, 2),
            //'product_delivery_fee' => round($productDeliveryFee, 2),
            'tax_is_different' => $taxIsDifferent,
        ];
    }

    public function createOrderAndOrderItems($bundle, $installment_payment_id, $calculate, $user, $discountCoupon = null)
    {
        $totalCouponDiscount = 0;

        $totalAmount = $calculate['total'] - $totalCouponDiscount;

        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => $calculate['sub_total'],
            'tax' => $calculate['tax_price'],
            'total_discount' => $calculate['total_discount'] + $totalCouponDiscount,
            'total_amount' => ($totalAmount > 0) ? $totalAmount : 0,
            'product_delivery_fee' => $calculate['product_delivery_fee'] ?? null,
            'created_at' => time(),
        ]);

        $orderPrices = $this->handleOrderPrices($bundle, $installment_payment_id, $user, $taxIsDifferent = false, $discountCoupon);
        $price = $orderPrices['sub_total'];
        $totalDiscount = $orderPrices['total_discount'];
        $tax = $orderPrices['tax'];
        $taxPrice = $orderPrices['tax_price'];
        $commission = $orderPrices['commission'];
        $commissionPrice = $orderPrices['commission_price'];

        $productDeliveryFee = 0;

        $allDiscountPrice = $totalDiscount;
        if ($totalCouponDiscount > 0 and $price > 0) {
            $percent = (($price / $calculate['sub_total']) * 100);
            $allDiscountPrice += (($totalCouponDiscount * $percent) / 100);
        }

        $subTotalWithoutDiscount = $price - $allDiscountPrice;
        $totalAmount = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

        OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'webinar_id' => null,
            'bundle_id' => $bundle ? $bundle->id : null,
            'certificate_template_id' => null,
            'certificate_bundle_id' => null,
            'product_id' => null,
            'product_order_id' => null,
            'reserve_meeting_id' => null,
            'subscribe_id' => null,
            'promotion_id' => null,
            'gift_id' => null,
            'installment_payment_id' => $installment_payment_id ?? null,
            'ticket_id' => null,
            'discount_id' => $discountCoupon ? $discountCoupon->id : null,
            'amount' => $price,
            'total_amount' => $totalAmount,
            'tax' => $tax,
            'tax_price' => $taxPrice,
            'commission' => $commission,
            'commission_price' => $commissionPrice,
            'product_delivery_fee' => $productDeliveryFee,
            'discount' => $allDiscountPrice,
            'created_at' => time(),
        ]);

        return $order;
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


    public function makeInstallmentPayment(Request $request, $installmentId)
    {
        $user = auth('api')->user();
        // return $user;
        $itemId = $request->item;
        $itemType = $request->item_type ?? 'bundles';
        $rules = [
            'item' => 'required|integer',
        ];

        validateParam($request->all(), $rules);

        if (empty($user) or !$user->enable_installments) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('update.you_cannot_use_installment_plans'),
                'status' => 'error'
            ];
            return sendError([], trans('update.you_cannot_use_installment_plans'));
        }

        if (!empty($itemId) and !empty($itemType) and getInstallmentsSettings('status')) {

            $item = $this->getItem($itemId, $itemType, $user);

            if (!empty($item)) {
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

                    if (!$installment->bundles->contains($item)) {

                        return sendError([], trans('update.installment_not_allowed_for_this_item'));
                    }
                    $itemPrice = $item->getPrice();

                    $hasPhysicalProduct = false;
                    if ($itemType == 'product') {
                        $quantity = $request->get('quantity', 1);
                        $itemPrice = $itemPrice * $quantity;
                        $hasPhysicalProduct = ($item->type == Product::$physical);
                    }

                    $data = [
                        'pageTitle' => trans('update.verify_your_installments'),
                        'installment' => $installment,
                        'itemPrice' => $itemPrice,
                        'itemType' => $itemType,
                        'item' => $item,
                        'hasPhysicalProduct' => $hasPhysicalProduct,
                    ];



                    return sendResponse($data, 'installment verification is done');
                }
            }
        }

        return $this->notFound();
    }


    private function getItem($itemId, $itemType, $user)
    {
        if ($itemType == 'course') {
            $course = Webinar::where('id', $itemId)
                ->where('status', 'active')
                ->first();

            $hasBought = $course->checkUserHasBought($user);
            $canSale = ($course->canSale() and !$hasBought);

            if ($canSale and !empty($course->price)) {
                return $course;
            }
        } else if ($itemType == 'bundles') {
            $bundle = Bundle::where('id', $itemId)
                ->where('status', 'active')
                ->first();

            $hasBought = $bundle->checkUserHasBought($user);
            $canSale = ($bundle->canSale() and !$hasBought);

            if ($canSale and !empty($bundle->price)) {
                return $bundle;
            }
        } elseif ($itemType == 'product') {
            $product = Product::where('status', Product::$active)
                ->where('id', $itemId)
                ->first();

            $hasBought = $product->checkUserHasBought($user);

            if (!$hasBought and !empty($product->price)) {
                return $product;
            }
        } elseif ($itemType == 'registration_package') {
            $package = RegistrationPackage::where('id', $itemId)
                ->where('status', 'active')
                ->first();

            return $package;
        } elseif ($itemType == 'subscribe') {
            return Subscribe::where('id', $itemId)->first();
        }

        return null;
    }
}
