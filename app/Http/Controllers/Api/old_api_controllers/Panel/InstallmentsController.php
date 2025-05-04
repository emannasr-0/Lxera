<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Mixins\Installment\InstallmentAccounting;
use App\Models\Cart;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\SelectedInstallmentStep;
use App\User;

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

        foreach ($orders as $order) {
            $getRemainedInstallments = $this->getRemainedInstallments($order);

            $order->remained_installments_count = $getRemainedInstallments['total'];
            $order->remained_installments_amount = $getRemainedInstallments['amount'];

            $order->upcoming_installment = $this->getUpcomingInstallment($order);

            // is overdue
            $hasOverdue = $order->checkOrderHasOverdue();
            $order->has_overdue = $hasOverdue;
            $order->overdue_count = 0;
            $order->overdue_amount = 0;

            if ($hasOverdue) {
                $getOrderOverdueCountAndAmount = $order->getOrderOverdueCountAndAmount();
                $order->overdue_count = $getOrderOverdueCountAndAmount['count'];
                $order->overdue_amount = $getOrderOverdueCountAndAmount['amount'];
            }
        }

        $overdueInstallmentsCount = $this->getOverdueInstallments($user);

        $data = [
            'pageTitle' => trans('update.installments'),
            'openInstallmentsCount' => $openInstallmentsCount,
            'pendingVerificationCount' => $pendingVerificationCount,
            'finishedInstallmentsCount' => $finishedInstallmentsCount,
            'overdueInstallmentsCount' => $overdueInstallmentsCount,
            'orders' => $orders,
        ];

        return apiResponse2(1, "all_retreived", "All installments are retrieved", $data);
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

        return view('errors.404');
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

        return view('errors.404');
    }

    public function payUpcomingPart($orderId)
    {
        $user = auth()->user();

        $order = InstallmentOrder::query()
            ->where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($order)) {
            $upcomingStep = $this->getUpcomingInstallment($order);

            if (!empty($upcomingStep)) {
                return $this->handlePayStep($order, $upcomingStep);
            }
        }

        return view('errors.404');
    }

    public function payStep($orderId, $stepId)
    {
        $user = auth()->user();

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
                    return $this->handlePayStep($order, $step);
                }
            }
        }

        return view('errors.404');
    }

    private function handlePayStep($order, $step)
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
        $paymentChannels = PaymentChannel::where('status', 'active')->get();

        $calculate = $this->calculatePrice($bundle = null, $installment_payment_id, $user, $discountCoupon = null);

        $order = $this->createOrderAndOrderItems($bundle = $order->bundle ?? null, $installment_payment_id, $calculate, $user, $discountCoupon = null);

        if (!empty($order) and $order->total_amount > 0) {
            $razorpay = false;
            $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));

            foreach ($paymentChannels as $paymentChannel) {
                if ($paymentChannel->class_name == 'Razorpay' and (!$isMultiCurrency or in_array(currency(), $paymentChannel->currencies))) {
                    $razorpay = true;
                }
            }

            $data = [
                'pageTitle' => trans('public.checkout_page_title'),
                'paymentChannels' => $paymentChannels,
                'carts' => null,
                'subTotal' => $calculate['sub_total'],
                'totalDiscount' => $calculate['total_discount'],
                'tax' => $calculate['tax'],
                'taxPrice' => $calculate['tax_price'],
                'total' => $calculate['total'],
                'userGroup' => $user->userGroup ? $user->userGroup->group : null,
                'order' => $order,
                'count' => 0,
                'userCharge' => $user->getAccountCharge(),
                'razorpay' => $razorpay,
                'totalCashbackAmount' => null,
                'previousUrl' => url()->previous(),
            ];

            return view(getTemplate() . '.cart.payment', $data);
        } else {

            return $this->handlePaymentOrderWithZeroTotalAmount($order);
        }

        return redirect('/cart');
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

    private function handlePaymentOrderWithZeroTotalAmount($order)
    {
        $order->update([
            'payment_method' => Order::$paymentChannel,
        ]);

        $paymentController = new PaymentController();

        $paymentController->setPaymentAccounting($order);

        $order->update([
            'status' => Order::$paid,
        ]);

        return redirect('/payments/status/' . $order->id);
    }
}
