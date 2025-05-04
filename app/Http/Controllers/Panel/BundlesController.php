<?php

namespace App\Http\Controllers\Panel;

use App\Exports\WebinarStudents;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\PaymentController;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\Bundle;
use App\Models\BundleFilterOption;
use App\Models\Category;
use App\Models\Gift;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Tag;
use App\Models\Translation\BundleTranslation;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\Models\WebinarFilterOption;
use App\Models\WebinarPartnerTeacher;
use App\User;
use App\Models\Session;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PaymentChannel;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderAttachment;
use App\Models\InstallmentOrderPayment;
use App\Models\SelectedInstallment;
use App\Models\SelectedInstallmentStep;

class BundlesController extends Controller
{
    private $deviceBrowserInfo;
    private function getDeviceBrowserInfo($userAgent)
    {
        $device = preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent) ? 'Mobile' : (preg_match('/Tablet/i', $userAgent) ? 'Tablet' : 'Desktop');
 
        // Detect browser and version
        if (preg_match('/Firefox\/([\d\.]+)/', $userAgent, $matches)) {
            $browser = 'Firefox';
            $version = $matches[1];
        } elseif (preg_match('/Chrome\/([\d\.]+)/', $userAgent, $matches) && strpos($userAgent, 'Safari') !== false) {
            $browser = 'Chrome';
            $version = $matches[1];
        } elseif (preg_match('/Safari\/([\d\.]+)/', $userAgent, $matches) && strpos($userAgent, 'Chrome') === false) {
            $browser = 'Safari';
            $version = $matches[1];
        } elseif (preg_match('/Opera\/([\d\.]+)/', $userAgent, $matches) || preg_match('/OPR\/([\d\.]+)/', $userAgent, $matches)) {
            $browser = 'Opera';
            $version = $matches[1];
        } elseif (preg_match('/Edge\/([\d\.]+)/', $userAgent, $matches)) {
            $browser = 'Edge';
            $version = $matches[1];
        } elseif (preg_match('/MSIE ([\d\.]+)/', $userAgent, $matches) || preg_match('/rv:([\d\.]+)/', $userAgent, $matches)) {
            $browser = 'Internet Explorer';
            $version = $matches[1];
        } else {
            $browser = 'Unknown Browser';
            $version = 'Unknown Version';
        }
 
        // Detect platform
        if (preg_match('/Windows NT 10.0/i', $userAgent)) {
            $platform = 'Windows 10';
        } elseif (preg_match('/Windows NT 6.3/i', $userAgent)) {
            $platform = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6.2/i', $userAgent)) {
            $platform = 'Windows 8';
        } elseif (preg_match('/Windows NT 6.1/i', $userAgent)) {
            $platform = 'Windows 7';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $platform = 'Mac OS X';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $platform = 'iOS';
        } else {
            $platform = 'Unknown Platform';
        }
 
        $ipAddress = request()->ip();
 
        return "{$device} | {$browser} ({$version}) | {$platform} | {$ipAddress}";
 
    }
 
    public function __construct(Request $request)
    {
        $this->deviceBrowserInfo = $this->getDeviceBrowserInfo($request->header('User-Agent'));
    }
    public function index()
    {

        $user = auth()->user();

        if ($user->isUser()) {
            return view('errors.404');
        }

        $query = Bundle::where(function ($query) use ($user) {
            $query->where('bundles.teacher_id', $user->id)
                ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                    $q->where('teacher_id', $user->id);
                })
                ->orWhere('bundles.creator_id', $user->id);
        });

        $bundlesHours = deepClone($query)->join('bundle_webinars', 'bundle_webinars.bundle_id', 'bundles.id')
            ->join('webinars', 'webinars.id', 'bundle_webinars.webinar_id')
            ->select('bundles.*', DB::raw('sum(webinars.duration) as duration'))
            ->sum('duration');

        $query->with([
            /*'reviews' => function ($query) {
                $query->where('status', 'active');
            },*/
            'bundleWebinars',
            'category',
            'teacher',
            'sales' => function ($query) {
                $query->where('type', 'bundle')
                    ->whereNull('refund_at');
            }
        ])->orderBy('updated_at', 'desc');

        $bundlesCount = $query->count();

        $bundles = $query->paginate(10);

        $bundleSales = Sale::where('seller_id', $user->id)
            ->where('type', 'bundle')
            ->whereNotNull('bundle_id')
            ->whereNull('refund_at')
            ->get();

        $data = [
            'pageTitle' => trans('update.my_bundles'),
            'bundles' => $bundles,
            'bundlesCount' => $bundlesCount,
            'bundleSalesAmount' => $bundleSales->sum('amount'),
            'bundleSalesCount' => $bundleSales->count(),
            'bundlesHours' => $bundlesHours,
        ];

        return view('web.default.panel.bundle.index', $data);
    }
    public function purchases(Request $request)
    {
        $user = auth()->user();

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
            // ->where('access_to_purchased_item', true)
            ->where(function ($query) {
                $query->whereNotNull('sales.bundle_id')
                    ->whereIn('sales.type', ['bundle', 'installment_payment', 'bridging'])
                    ->whereHas('bundle');
            })
            ->distinct()
            ->select(['sales.bundle_id', 'sales.access_to_purchased_item']);



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
            ->paginate(10);


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
                $sale->gift_date = $gift->date;

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

        $data = [
            'pageTitle' => trans('webinars.webinars_purchases_page_title'),
            'sales' => $sales,
            'purchasedCount' => $purchasedCount + $giftPurchasedCount,
            'hours' => $hours,
            'upComing' => $upComing + $giftUpcoming
        ];
     //  dd($sales); 

        return view(getTemplate() . '.panel.bundle.purchases', $data);
    }
    public function getJoinInfo(Request $request)
    {
        //to do

        return response()->json([], 422);
    }
    public function createOrderAndOrderItems($bundle, $installment_payment_id, $calculate, $user, $discountCoupon = null)
    {
        $totalCouponDiscount = 0;

        $totalAmount = $calculate["total"] - $totalCouponDiscount;

        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => $calculate["sub_total"],
            'tax' => $calculate["tax_price"],
            'total_discount' => $calculate["total_discount"] + $totalCouponDiscount,
            'total_amount' => ($totalAmount > 0) ? $totalAmount : 0,
            'product_delivery_fee' => $calculate["product_delivery_fee"] ?? null,
            'device_browser_info' => $this->deviceBrowserInfo,
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
            $percent = (($price / $calculate["sub_total"]) * 100);
            $allDiscountPrice += (($totalCouponDiscount * $percent) / 100);
        }

        $subTotalWithoutDiscount = $price - $allDiscountPrice;
        $totalAmount = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

        OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'webinar_id' => null,
            'bundle_id' => $bundle ? $bundle->id : null,
            'certificate_template_id' =>  null,
            'certificate_bundle_id' =>  null,
            'product_id' => null,
            'product_order_id' =>  null,
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

    public function handleOrderPrices($bundle, $installment_payment_id = null, $user, $taxIsDifferent = false, $discountCoupon = null)
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
                $commission = (int)$financialSettings['commission'];
            }
        }

        if (!empty($bundle) && empty($installment_payment_id)) {
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
            'tax_is_different' => $taxIsDifferent
        ];
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
            'tax_is_different' => $taxIsDifferent
        ];
    }

    public function purchase_bundle(Request $request, $installmentId = null, $carts = null)
    {
        if (!empty($installmentId)) {
            $user = auth()->user();
            $itemId = $request->get('item');
            $itemType = $request->get('item_type');

            if (empty($user) or !$user->enable_installments) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.you_cannot_use_installment_plans'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
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
                    return back()->with(['toast' => $toastData]);
                }


                $this->validate($request, [
                    'item' => 'required',
                    'item_type' => 'required',
                ]);

                $data = $request->all();
                $attachments = (!empty($data['attachments']) and count($data['attachments'])) ? array_map('array_filter', $data['attachments']) : [];
                $attachments = !empty($attachments) ? array_filter($attachments) : [];

                if ($installment->request_uploads) {
                    if (count($attachments) < 1) {
                        return redirect()->back()->withErrors([
                            'attachments' => trans('validation.required', ['attribute' => 'attachments'])
                        ]);
                    }
                }

                if (!empty($installment->capacity)) {
                    $openOrdersCount = InstallmentOrder::query()->where('installment_id', $installment->id)
                        ->where('status', 'open')
                        ->count();

                    if ($openOrdersCount >= $installment->capacity) {
                        $toastData = [
                            'title' => trans('public.request_failed'),
                            'msg' => trans('update.installment_not_capacity'),
                            'status' => 'error'
                        ];

                        return back()->with(['toast' => $toastData]);
                    }
                }

                $item = $this->getItem($itemId, $itemType, $user);

                if (!empty($item)) {

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

                        $paymentChannels = PaymentChannel::where('status', 'active')->get();

                        $calculate = $this->calculatePrice($bundle = null, $installment_payment_id, $user, $discountCoupon = null);

                        $order = $this->createOrderAndOrderItems($bundle = $item, $installment_payment_id, $calculate, $user, $discountCoupon = null);

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
                                'carts' => $carts,
                                'subTotal' => $calculate["sub_total"],
                                'totalDiscount' => $calculate["total_discount"],
                                'tax' => $calculate["tax"],
                                'taxPrice' => $calculate["tax_price"],
                                'total' => $calculate["total"],
                                'userGroup' => $user->userGroup ? $user->userGroup->group : null,
                                'order' => $order,
                                'count' => 0,
                                'userCharge' => $user->getAccountCharge(),
                                'razorpay' => $razorpay,
                                'totalCashbackAmount' => null,
                                'previousUrl' => url()->previous(),
                            ];

                            return redirect('/payment/' . $order->id);
                        } else {

                            return $this->handlePaymentOrderWithZeroTotalAmount($order);
                        }
                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1); // Admin

                            return redirect('/installments/request_submitted');
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }
                }
            }

            return view('errors.404');
        } else {
            $bundle_id = $request->item_id;
            $bundle = Bundle::find($bundle_id);
            $user = auth()->user();

            $paymentChannels = PaymentChannel::where('status', 'active')->get();

            $calculate = $this->calculatePrice($bundle, $installment_payment_id = null, $user, $discountCoupon = null);

            $order = $this->createOrderAndOrderItems($bundle, $installment_payment_id = null, $calculate, $user, $discountCoupon = null);

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
                    'carts' => $carts,
                    'subTotal' => $calculate["sub_total"],
                    'totalDiscount' => $calculate["total_discount"],
                    'tax' => $calculate["tax"],
                    'taxPrice' => $calculate["tax_price"],
                    'total' => $calculate["total"],
                    'userGroup' => $user->userGroup ? $user->userGroup->group : null,
                    'order' => $order,
                    'count' => 0,
                    'userCharge' => $user->getAccountCharge(),
                    'razorpay' => $razorpay,
                    'totalCashbackAmount' => null,
                    'previousUrl' => url()->previous(),
                ];

                return redirect('/payment/' . $order->id);
            } else {

                return $this->handlePaymentOrderWithZeroTotalAmount($order);
            }
        }
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
            'deadline_type'=>$installment->deadline_type,
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
    private function getColumnByItemType($itemType)
    {
        if ($itemType == 'bundles') {
            return 'bundle_id';
        }
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
    private function handlePaymentOrderWithZeroTotalAmount($order)
    {
        $order->update([
            'payment_method' => Order::$paymentChannel
        ]);

        $paymentController = new PaymentController();

        $paymentController->setPaymentAccounting($order);

        $order->update([
            'status' => Order::$paid
        ]);

        return redirect('/payments/status/' . $order->id);
    }

    public function create()
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            return view('errors.404');
        }

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $teachers = null;
        $isOrganization = $user->isOrganization();

        if ($isOrganization) {
            $teachers = User::where('role_name', Role::$teacher)
                ->where('organ_id', $user->id)->get();
        }


        $data = [
            'pageTitle' => trans('update.new_bundle'),
            'teachers' => $teachers,
            'categories' => $categories,
            'isOrganization' => $isOrganization,
            'currentStep' => 1,
            'userLanguages' => getUserLanguagesLists(),
        ];

        return view('web.default.panel.bundle.create', $data);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            return view('errors.404');
        }

        $rules = [
            'title' => 'required|max:255',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        if (empty($data['video_demo'])) {
            $data['video_demo_source'] = null;
        }

        if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
            $data['video_demo_source'] = 'upload';
        }

        $bundle = Bundle::create([
            'teacher_id' => $user->isTeacher() ? $user->id : (!empty($data['teacher_id']) ? $data['teacher_id'] : $user->id),
            'creator_id' => $user->id,
            'slug' => Bundle::makeSlug($data['title']),
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'],
            'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
            'status' => ((!empty($data['draft']) and $data['draft'] == 1) or (!empty($data['get_next']) and $data['get_next'] == 1)) ? Bundle::$isDraft : Bundle::$pending,
            'created_at' => time(),
        ]);

        if ($bundle) {
            BundleTranslation::updateOrCreate([
                'bundle_id' => $bundle->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);
        }

        $notifyOptions = [
            '[u.name]' => $user->full_name,
            '[item_title]' => $bundle->title,
            '[content_type]' => trans('update.bundle'),
        ];
        sendNotification("bundle_submission", $notifyOptions, $user->id);
        sendNotification("bundle_submission_for_admin", $notifyOptions, 1);
        sendNotification("new_item_created", $notifyOptions, 1);

        $url = '/panel/bundles';
        if ($data['get_next'] == 1) {
            $url = '/panel/bundles/' . $bundle->id . '/step/2';
        }

        return redirect($url);
    }

    public function edit(Request $request, $id, $step = 1)
    {
        $user = auth()->user();
        $isOrganization = $user->isOrganization();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            return view('errors.404');
        }
        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('update.new_bundle_page_title_step', ['step' => $step]),
            'currentStep' => $step,
            'isOrganization' => $isOrganization,
            'userLanguages' => getUserLanguagesLists(),
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
        ];

        $query = Bundle::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id)
                    ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                        $q->where('teacher_id', $user->id);
                    });
            });

        if ($step == '1') {
            $data['teachers'] = $user->getOrganizationTeachers()->get();
        } elseif ($step == 2) {
            $query->with([
                'category' => function ($query) {
                    $query->with([
                        'filters' => function ($query) {
                            $query->with('options');
                        }
                    ]);
                },
                'filterOptions',
                'tags',
            ]);

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $data['categories'] = $categories;
        } elseif ($step == 3) {
            $query->with([
                'tickets' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ]);
        } elseif ($step == 4) {
            $query->with([
                'bundleWebinars' => function ($query) {
                    $query->with([
                        'webinar'
                    ]);
                    $query->orderBy('order', 'asc');
                }
            ]);
        } elseif ($step == 5) {
            $query->with([
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                }
            ]);
        }

        $bundle = $query->first();

        if (empty($bundle)) {
            return view('errors.404');
        }

        $data['bundle'] = $bundle;

        $data['pageTitle'] = trans('public.edit') . ' ' . $bundle->title;

        $definedLanguage = [];
        if ($bundle->translations) {
            $definedLanguage = $bundle->translations->pluck('locale')->toArray();
        }

        $data['definedLanguage'] = $definedLanguage;

        if ($step == 2) {
            $data['bundleTags'] = $bundle->tags->pluck('title')->toArray();

            $bundleCategoryFilters = !empty($bundle->category) ? $bundle->category->filters : [];

            if (empty($bundle->category) and !empty($request->old('category_id'))) {
                $category = Category::where('id', $request->old('category_id'))->first();

                if (!empty($category)) {
                    $bundleCategoryFilters = $category->filters;
                }
            }

            $data['bundleCategoryFilters'] = $bundleCategoryFilters;
        } elseif ($step == 4) {
            $data['webinars'] = Webinar::select('id', 'creator_id', 'teacher_id')
                ->where('status', Webinar::$active)
                ->where('private', false)
                ->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id)
                        ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                            $q->where('teacher_id', $user->id);
                        });
                })
                ->get();
        }

        return view('web.default.panel.bundle.create', $data);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            return view('errors.404');
        }

        $rules = [];
        $data = $request->all();
        $currentStep = $data['current_step'];
        $getStep = $data['get_step'];
        $getNextStep = (!empty($data['get_next']) and $data['get_next'] == 1);
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);

        $bundle = Bundle::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id)
                    ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                        $q->where('teacher_id', $user->id);
                    });
            })->first();

        if (empty($bundle)) {
            return view('errors.404');
        }

        if ($currentStep == 1) {
            $rules = [
                'title' => 'required|max:255',
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
            ];
        }

        if ($currentStep == 2) {
            $rules = [
                'category_id' => 'required',
            ];
        }

        $bundleRulesRequired = false;
        if (($currentStep == 6 and !$getNextStep and !$isDraft) or (!$getNextStep and !$isDraft)) {
            $bundleRulesRequired = empty($data['rules']);
        }

        $this->validate($request, $rules);


        $data['status'] = ($isDraft or $bundleRulesRequired) ? Bundle::$isDraft : Bundle::$pending;
        $data['updated_at'] = time();

        if ($currentStep == 1) {
            if (empty($data['video_demo'])) {
                $data['video_demo_source'] = null;
            }

            if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                $data['video_demo_source'] = 'upload';
            }

            BundleTranslation::updateOrCreate([
                'bundle_id' => $bundle->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);
        }

        if ($currentStep == 2) {
            BundleFilterOption::where('bundle_id', $bundle->id)->delete();
            Tag::where('bundle_id', $bundle->id)->delete();

            $filters = $request->get('filters', null);
            if (!empty($filters) and is_array($filters)) {
                foreach ($filters as $filter) {
                    BundleFilterOption::create([
                        'bundle_id' => $bundle->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }

            if (!empty($request->get('tags'))) {
                $tags = explode(',', $request->get('tags'));

                foreach ($tags as $tag) {
                    Tag::create([
                        'bundle_id' => $bundle->id,
                        'title' => $tag,
                    ]);
                }
            }
        }

        if ($currentStep == 3) {
            $data['subscribe'] = !empty($data['subscribe']) ? true : false;
            $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
        }

        unset(
            $data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_next'],
            $data['partners'],
            $data['tags'],
            $data['filters'],
            $data['ajax'],
            $data['title'],
            $data['description'],
            $data['seo_description'],
        );

        if (empty($data['teacher_id']) and $user->isOrganization() and $bundle->creator_id == $user->id) {
            $data['teacher_id'] = $user->id;
        }

        $bundle->update($data);

        $url = '/panel/bundles';
        if ($getNextStep) {
            $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;

            $url = '/panel/bundles/' . $bundle->id . '/step/' . (($nextStep <= 6) ? $nextStep : 6);
        }

        if ($bundleRulesRequired) {
            $url = '/panel/bundles/' . $bundle->id . '/step/6';

            return redirect($url)->withErrors(['rules' => trans('validation.required', ['attribute' => 'rules'])]);
        }

        if (!$getNextStep and !$isDraft and !$bundleRulesRequired) {
            $notifyOptions = [
                '[u.name]' => $user->full_name,
                '[item_title]' => $bundle->title,
                '[content_type]' => trans('update.bundle'),
            ];
            sendNotification("content_review_request", $notifyOptions, 1);
        }

        return redirect($url);
    }

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            return view('errors.404');
        }

        $bundle = Bundle::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (!$bundle) {
            return view('errors.404');
        }

        $bundle->delete();

        return response()->json([
            'code' => 200,
            'redirect_to' => $request->get('redirect_to')
        ], 200);
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        $bundle = Bundle::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id)
                    ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                        $q->where('teacher_id', $user->id);
                    });
            })->first();

        if (!empty($bundle)) {

            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($bundle->$relation)) {
                $item = $bundle->$relation->where('id', $itemId)->first();

                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }

                    return response()->json([
                        'item' => $item
                    ], 200);
                }
            }
        }

        abort(403);
    }

    public function exportStudentsList($id)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            return view('errors.404');
        }

        $bundle = Bundle::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id)
                    ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                        $q->where('teacher_id', $user->id);
                    });
            })->first();

        if (!empty($bundle)) {
            $sales = Sale::where('type', 'bundle')
                ->where('bundle_id', $bundle->id)
                ->whereNull('refund_at')
                ->whereHas('buyer')
                ->with([
                    'buyer' => function ($query) {
                        $query->select('id', 'full_name', 'email', 'mobile');
                    }
                ])->get();

            if (!empty($sales) and !$sales->isEmpty()) {
                $export = new WebinarStudents($sales);
                return Excel::download($export, trans('panel.users') . '.xlsx');
            }

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('webinars.export_list_error_not_student'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }

        return view('errors.404');
    }

    public function courses($id)
    {
        $user = auth()->user();
        $bundle = Bundle::findOrFail($id);

        if (!$user->isTeacher() and !$user->isOrganization() and !$bundle->isPartnerTeacher($user->id)) {
            return view('errors.404');
        }

        $bundle = Bundle::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id)
                    ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                        $q->where('teacher_id', $user->id);
                    });
            })
            ->with([
                'bundleWebinars' => function ($query) use ($user) {
                    $query->whereHas('webinar', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id)
                            ->orWhere('creator_id', $user->id)
                            ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                                $q->Where('teacher_id', $user->id);
                            });
                    })
                        ->with([
                            'webinar'
                        ]);
                    $query->orderBy('order', 'asc');
                }
            ])
            ->first();

        if (!empty($bundle)) {

            $data = [
                'pageTitle' => trans('product.courses'),
                'bundle' => $bundle
            ];

            return view('web.default.panel.bundle.courses', $data);
        }

        return view('errors.404');
    }
}
