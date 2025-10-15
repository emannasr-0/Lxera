<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\Cashback\CashbackAccounting;
use App\Models\AccountCharge;
use App\Models\Accounting;
use App\Models\BecomeInstructor;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ReserveMeeting;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\TicketUser;
use App\PaymentChannels\ChannelManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Code;
use App\User;
use App\Student;
use Illuminate\Support\Facades\Validator;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\BundleStudent;
use App\Models\BridgingRequest;
use App\Models\BundleDelay;
use App\Models\BundleTransform;
use App\Models\Discount;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Role;
use Illuminate\Support\Facades\Cookie;

use App\Models\ServiceUser;
use App\Models\StudyClass;
use Illuminate\Support\Facades\Date;
use App\Facades\MicrosoftGraph;

class PaymentController extends Controller
{
    protected $order_session_key = 'payment.order_id';


    public function index(Request $request, Order $order)
    {
        $userAuth = auth()->user();
        if ($order->user_id != auth()->user()->id && !$userAuth->isAdmin()) {
            abort(403);
        }
        $paymentChannels = PaymentChannel::where('status', 'active')->get();
        $razorpay = false;
        $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));

        foreach ($paymentChannels as $paymentChannel) {
            if ($paymentChannel->class_name == 'Razorpay' and (!$isMultiCurrency or in_array(currency(), $paymentChannel->currencies))) {
                $razorpay = true;
            }
        }

        $offlinePayments = OfflinePayment::where('user_id', $userAuth->id)->orderBy('created_at', 'desc')->get();

        $offlineBanks = OfflineBank::query()
            ->orderBy('created_at', 'desc')
            ->with([
                'specifications'
            ])
            ->get();


        $registrationBonusAmount = null;

        if ($userAuth->enable_registration_bonus) {
            $registrationBonusSettings = getRegistrationBonusSettings();

            $registrationBonusAccounting = Accounting::query()
                ->where('user_id', $userAuth->id)
                ->where('is_registration_bonus', true)
                ->where('system', false)
                ->first();

            $registrationBonusAmount = (empty($registrationBonusAccounting) and !empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) ? $registrationBonusSettings['registration_bonus_amount'] : null;
        }

        if ($order->status != 'paid') {
            $order->total_amount = $order->amount;
            $order->charge_amount = null;
            $order->save();
        }

        if ($order->total_discount > 0) {
            $order->total_discount = 0;
            $order->total_amount =  $order->amount;
            $order->orderItems[0]->update(['discount_id' => null, 'discount' => 0, 'total_amount' => $order->orderItems[0]->amount]);
            $order->save();
        }

        $enableCoupon = true;
        if ($order->orderItems[0]->form_fee || $order->orderItems[0]->transform_bundle_id || $order->orderItems[0]->service_id) {
            $enableCoupon = false;
        }
        $data = [
            'pageTitle' => trans('public.checkout_page_title'),
            'paymentChannels' => $paymentChannels,
            'carts' => null,
            'subTotal' => null,
            'totalDiscount' => null,
            'tax' => null,
            'taxPrice' => null,
            'total' => $order->total_amount,
            'userGroup' => $userAuth->userGroup ? $userAuth->userGroup->group : null,
            'order' => $order,
            'type' => $order->orderItems[0]->form_fee,
            'enableCoupon' => $enableCoupon,
            'count' => 0,
            'userCharge' => $userAuth->getAccountCharge(),
            'razorpay' => $razorpay,
            'totalCashbackAmount' => null,
            'previousUrl' => url()->previous(),
            'offlinePayments' => $offlinePayments,
            'offlineBanks' => $offlineBanks,
            'accountCharge' => $userAuth->getAccountCharge(),
            'readyPayout' => $userAuth->getPayout(),
            'totalIncome' => $userAuth->getIncome(),
            'registrationBonusAmount' => $registrationBonusAmount,
        ];

        return view(getTemplate() . '.cart.payment', $data);
    }

    public function paymentRequest(Request $request)
    {
        $request->validate([
            'gateway' => 'required',
        ]);



        $user = auth()->user();
        $gateway = $request->input('gateway');
        $orderId = $request->input('order_id');
        $coupon = $request->input('coupon');

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if ($order->type === Order::$meeting) {
            $orderItem = OrderItem::where('order_id', $order->id)->first();
            $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
            $reserveMeeting->update(['locked_at' => time()]);
        }


        if ($gateway == 'offline') {
            return $this->payOffline($request, $order);
        }

        // if ($gateway === 'credit') {

        //     if ($user->getAccountCharge() < $order->total_amount) {
        //         $order->update(['status' => Order::$fail]);

        //         session()->put($this->order_session_key, $order->id);

        //         return redirect('/payments/status');
        //     }

        //     $order->update([
        //         'payment_method' => Order::$credit
        //     ]);

        //     $this->setPaymentAccounting($order, 'credit');

        //     $order->update([
        //         'status' => Order::$paid
        //     ]);



        //     session()->put($this->order_session_key, $order->id);

        //     return redirect('/payments/status');
        // }


        $paymentChannel = PaymentChannel::where('id', $gateway)
            ->where('status', 'active')
            ->first();

        if (!$paymentChannel) {
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('public.channel_payment_disabled'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }

        $order->payment_method = Order::$paymentChannel;

        if (!empty($coupon)) {

            $response = (new CartController())->couponValidate($request);
            $response = json_decode($response->getContent());
            if ($response->status != 200) {
                return back()->with(['toast' => ['status' => 'error', 'msg' => $response->msg]]);
            } else {
                $order->total_discount = (float)$response->total_discount;
                $order->total_amount =  (float)$response->total_amount;
                $order->orderItems[0]->update(['discount_id' => $response->discount_id, 'discount' => (float)$response->total_discount, 'total_amount' => (float)$response->total_amount]);

                if ($response->discount_percent == 100) {
                    $order->payment_method = 'scholarship';
                }

                $order->save();
            }
        }

        $usercharge = $user->getAccountCharge();
        $sub_from_amount = $request->sub_from_amount;
        if (!empty($sub_from_amount)) {

            if ($usercharge > $order->total_amount) {

                $new_amount =  $order->total_amount;
                $order->total_amount = 0;
                $order->orderItems->first->update(['total_amount' => 0]);
            } else {
                $new_amount = $usercharge;
                $orderAmount = $order->total_amount - $usercharge;
                $order->total_amount = $orderAmount;
                $order->orderItems->first->update(['total_amount' => $orderAmount]);
            }
            $order->charge_amount = $new_amount;
        }


        $order->save();

        if ($order->total_amount <= 0) {
            $this->setPaymentAccounting($order);

            $order->update([
                'status' => Order::$paid
            ]);

            return $this->payStatus($request, $order->id);
        }
        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);
            // dd($channelManager );
            $redirect_url = $channelManager->paymentRequest($order);

            if ($paymentChannel->class_name == 'Mada') {
                return $redirect_url;
            }

            if (in_array($paymentChannel->class_name, PaymentChannel::$gatewayIgnoreRedirect)) {

                return $redirect_url;
            }

            return Redirect::away($redirect_url);
        } catch (\Exception $exception) {
            dd($exception);
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
        return redirect('/panel');
    }

    public function paymentVerify(Request $request, $gateway)
    {


        $paymentChannel = PaymentChannel::where('class_name', $gateway)
            ->where('status', 'active')
            ->first();

        try {
            $order_id = $request->query('order_id');
            $check_order_status = Order::find($order_id);
            // dd($check_order_status->status);
            if ($check_order_status->status != 'success') {
                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verify($request);

                return $this->paymentOrderAfterVerify($order);
            }
        } catch (\Exception $exception) {
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return redirect('cart')->with(['toast' => $toastData]);
        }
    }

    /*
     * | this methode only run for payku.result
     * */
    public function paykuPaymentVerify(Request $request, $id)
    {
        $paymentChannel = PaymentChannel::where('class_name', PaymentChannel::$payku)
            ->where('status', 'active')
            ->first();

        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);

            $request->request->add(['transaction_id' => $id]);

            $order = $channelManager->verify($request);

            return $this->paymentOrderAfterVerify($order);
        } catch (\Exception $exception) {
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return redirect('cart')->with(['toast' => $toastData]);
        }
    }

    public function paymentOrderAfterVerify($order)
    {
        if (!empty($order)) {

            if ($order->status == Order::$paying) {

                $this->setPaymentAccounting($order);

                $order->update(['status' => Order::$paid]);
            } else {
                if ($order->type === Order::$meeting) {
                    $orderItem = OrderItem::where('order_id', $order->id)->first();

                    if ($orderItem && $orderItem->reserve_meeting_id) {
                        $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();

                        if ($reserveMeeting) {
                            $reserveMeeting->update(['locked_at' => null]);
                        }
                    }
                }
            }

            session()->put($this->order_session_key, $order->id);

            return redirect('/payments/status');
        } else {
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];

            return redirect('cart')->with($toastData);
        }
    }

    public function setPaymentAccounting($order, $type = null)
    {
        try {
            //code...

            $cashbackAccounting = new CashbackAccounting();

            if ($order->is_charge_account) {
                AccountCharge::charge($order);

                if (!auth()->user()->isAdmin()) {
                    foreach ($order->orderItems as $orderItem) {
                        $sale = Sale::createSales($orderItem, $order->payment_method);
                    }
                }

                $cashbackAccounting->rechargeWallet($order);
            } else {
                if ($order->charge_amount > 0) {
                    AccountCharge::create([
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'amount' => $order->charge_amount,
                        'type' => AccountCharge::$deduction,
                        'description' => 'الدفع الالكتروني',
                    ]);
                }
                foreach ($order->orderItems as $orderItem) {

                    $sale = Sale::createSales($orderItem, $order->payment_method);

                    if (!empty($orderItem->reserve_meeting_id)) {
                        $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                        $reserveMeeting->update([
                            'sale_id' => $sale->id,
                            'reserved_at' => time()
                        ]);

                        $reserver = $reserveMeeting->user;

                        if ($reserver) {
                            $this->handleMeetingReserveReward($reserver);
                        }
                    }

                    if (!empty($orderItem->gift_id)) {
                        $gift = $orderItem->gift;

                        $gift->update([
                            'status' => 'active'
                        ]);

                        $gift->sendNotificationsWhenActivated($orderItem->total_amount);
                    }

                    if (!empty($orderItem->subscribe_id)) {
                        Accounting::createAccountingForSubscribe($orderItem, $type);
                    } elseif (!empty($orderItem->promotion_id)) {
                        Accounting::createAccountingForPromotion($orderItem, $type);
                    } elseif (!empty($orderItem->registration_package_id)) {
                        Accounting::createAccountingForRegistrationPackage($orderItem, $type);

                        if (!empty($orderItem->become_instructor_id)) {
                            BecomeInstructor::where('id', $orderItem->become_instructor_id)
                                ->update([
                                    'package_id' => $orderItem->registration_package_id
                                ]);
                        }
                    } elseif (!empty($orderItem->installment_payment_id)) {
                        Accounting::createAccountingForInstallmentPayment($orderItem, $type);

                        $this->updateInstallmentOrder($orderItem, $sale);
                    } else {
                        // webinar and meeting and product and bundle

                        Accounting::createAccounting($orderItem, $type);
                        TicketUser::useTicket($orderItem);

                        if (!empty($orderItem->product_id)) {
                            $this->updateProductOrder($sale, $orderItem);
                        }
                    }
                }

                // Set Cashback Accounting For All Order Items
                $cashbackAccounting->setAccountingForOrderItems($order->orderItems);
            }
        } catch (\Exception $exception) {
            dd($exception);
        }
        // Cart::emptyCart($order->user_id);
    }

    public function payStatus(Request $request, $order_id = null)
    {

        // $orderId = $request->get('order_id', null);

        if (!empty(session()->get($this->order_session_key, null))) {
            $orderId = session()->get($this->order_session_key, null);
            // session()->forget($this->order_session_key);
        }

        if (!empty($order_id)) {
            $orderId = $order_id;
        }


        $authUser = auth()->user();

        if (in_array(auth()->user()->role_id, Role::$financialRoles)) {

            $order = Order::find($orderId);
            $user = $order?->user;
        } else {
            $user = $authUser;

            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();
            if ($order?->user_id != $user->id) {
                abort(403);
            }
        }

        if (!empty($order)) {
            $data = [
                'pageTitle' => trans('public.cart_page_title'),
                'order' => $order,
            ];

            $userFirstSale = Sale::where('buyer_id', $user->id)
                ->whereIn('type', ['bundle', 'installment_payment', 'webinar', 'bridging'])
                ->first();

            $sale = Sale::where('order_id', $order->id)
                ->where('type', 'form_fee')
                ->where('buyer_id', $user->id)
                ->first();

            $bundle_sale = Sale::where('order_id', $order->id)
                ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
                ->where('buyer_id', $user->id)
                ->first();

            $webinar_sale = Sale::where('order_id', $order->id)
                ->where('type', 'webinar')
                ->where('buyer_id', $user->id)
                ->first();

            $service_sale = Sale::where('order_id', $order->id)
                ->where('type', 'service')
                ->where('buyer_id', $user->id)
                ->first();

            $pivot = null;

            $class =  StudyClass::get()->last();

            if (!$class) {
                $class = StudyClass::create(['title' => "الدفعة الأولي"]);
            }

            if (($sale && $sale->order->user_id == $user->id && $sale->order->status == 'paid') || ($webinar_sale && $webinar_sale->order->user_id == $user->id && $webinar_sale->order->status == 'paid')) {
                //add as student
                try {
                    $userData = $request->cookie('user_data');
                    if (!$userData) {
                        $userData = Cookie::get('user_data');
                    }
                    $userData = json_decode($userData, true);
                    $keysToExclude = [
                        'category_id',
                        'bundle_id',
                        'webinar_id',
                        'type',
                        'terms',
                        'certificate',
                        'timezone',
                        'password',
                        'password_confirmation',
                        'email_confirmation',
                        'requirement_endorsement'
                    ];
                    $studentData = collect($userData)->except($keysToExclude)->toArray();
                    $studentData['email'] = $user->email;
                    $student = $user->student;


                    if (!$student) {
                        $student = Student::create($studentData);
                    }
                    if (!$user->user_code) {
                        $code = generateStudentCode();
                        $user->update([
                            'user_code' => $code,
                            'access_content' => 1
                        ]);

                        // update code
                        Code::latest()->first()->update(['lst_sd_code' => $code]);
                    }

                    if (!empty($order->orderItems->first()->webinar_id)) {
                        $user->update([
                            'role_id' => 1,
                            'role_name' => 'user',
                        ]);

                        if (!empty(env('MICROSOFT_APPLY'))) {

                            if ($userFirstSale && $userFirstSale->id == $webinar_sale->id) {
                                $userEmailInTeams =  $user->user_code . '@anasacademy.uk'; // Replace with the user's email

                                if (!MicrosoftGraph::userExists($userEmailInTeams)) {
                                    $userName = $user?->student?->en_name ?? $user->full_name;
                                    $microsoftUser = MicrosoftGraph::createUser([
                                        'name' => $userName,
                                        'usercode' => $user->user_code,
                                        'password' => 'SD$$2025',
                                    ]);
                                }
                            }
                        }
                    }
                    $bundleId = $order->orderItems->first()->bundle_id;

                    if (!empty($bundleId)) {

                        // Check if the student already has the bundle ID attached
                        if ($student->bundles->contains($bundleId)) {
                            BundleStudent::where(['student_id' => $student->id, 'bundle_id' => $sale->bundle_id])->update(['status' => 'approved', 'class_id' => $sale->class_id]);
                        } else {
                            $student->bundles()->attach($bundleId, [
                                'certificate' => (!empty($userData['certificate'])) ? $userData['certificate'] : null,
                                'class_id' => $sale->class_id,
                                'created_at' => Date::now(),  // Set current timestamp for created_at
                                'updated_at' => Date::now()
                            ]);

                            $pivot = \DB::table('bundle_student')
                                ->where('student_id', $student->id)
                                ->where('bundle_id', $bundleId)->first();
                        }
                    }


                    if (!empty($webinar_sale->webinar->hasGroup)) {
                        $webinar = $webinar_sale->webinar;
                        $lastGroup = Group::where('webinar_id', $webinar->id)->latest()->first();

                        $startDate = now()->addMonth()->startOfMonth();
                        $endDate = now()->addMonth(2)->startOfMonth();
                        if (!$lastGroup) {
                            $lastGroup = Group::create(['name' => 'A', 'creator_id' => 1, 'webinar_id' => $webinar->id, 'capacity' => 20, 'start_date' => $startDate, 'end_date' => $endDate]);
                        }
                        $enrollments = $lastGroup->enrollments->count();
                        if ($enrollments >= $lastGroup->capacity || $lastGroup->start_date < now()) {
                            $lastGroup = Group::create([
                                'name' => chr(ord($lastGroup->name) + 1),
                                'creator_id' => 1,
                                'webinar_id' => $webinar->id,
                                'capacity' => 20,
                                'start_date' => $startDate,
                                'end_date' => $endDate
                            ]);
                        }

                        Enrollment::firstOrCreate([
                            'user_id' => $user->id,
                            'group_id' => $lastGroup->id,
                        ]);
                    }
                } catch (\Exception $exception) {
                    dd(['cookie' => $userData, 'error' => $exception->getMessage()]);
                }
            } elseif ($bundle_sale && $bundle_sale->order->user_id == $user->id && $bundle_sale->order->status == 'paid') {
                $user = User::where('id', $user->id)->first();

                $user->update([
                    'role_id' => 1,
                    'role_name' => 'user',
                ]);
                $userData = $request->cookie('user_data');
                if (!$userData) {
                    $userData = Cookie::get('user_data');
                }

                $userData = json_decode($userData, true);
                $keysToExclude = [
                    'category_id',
                    'bundle_id',
                    'webinar_id',
                    'type',
                    'terms',
                    'certificate',
                    'timezone',
                    'password',
                    'password_confirmation',
                    'email_confirmation',
                    'requirement_endorsement'
                ];
                $student = $user->student;

                if (!$student) {
                    $student = Student::create($studentData);
                }
                if (!$user->user_code) {
                    $code = generateStudentCode();
                    //   dd($code);
                    $user->update([
                        'user_code' => $code,
                        'access_content' => 1
                    ]);

                    // update code
                    Code::latest()->first()->update(['lst_sd_code' => $code]);
                }

                if (!empty(env('MICROSOFT_APPLY'))) {
                    if ($userFirstSale && $userFirstSale->id == $bundle_sale->id) {
                        $userEmailInTeams =  $user->user_code . '@anasacademy.uk'; // Replace with the user's email

                        if (!MicrosoftGraph::userExists($userEmailInTeams)) {
                            $userName = $user?->student?->en_name ?? $user->full_name;
                            $microsoftUser = MicrosoftGraph::createUser([
                                'name' => $userName,
                                'usercode' => $user->user_code,
                                'password' => 'SD$$2025',
                            ]);
                        }
                    }
                }

                BundleStudent::updateOrCreate(
                    ['student_id' => $user->student->id, 'bundle_id' => $bundle_sale->bundle_id],
                    ['status' => 'approved', 'class_id' => $bundle_sale->class_id]
                );

                if (!empty($bundle_sale->bundle->hasGroup) && empty($bundle_sale->order->orderItem[0]->installmentPayment->step->installmentStep)) {
                    $bundle = $bundle_sale->bundle;
                    $lastGroup = Group::where('bundle_id', $bundle->id)->latest()->first();
                    $startDate = now()->addMonth()->startOfMonth();
                    $endDate = now()->addMonth(2)->startOfMonth();
                    if (!$lastGroup) {
                        $lastGroup = Group::create([
                            'name' => 'A',
                            'creator_id' => 1,
                            'bundle_id' => $bundle->id,
                            'capacity' => 20,
                            'start_date' => $startDate,
                            'end_date' => $endDate
                        ]);
                    }

                    $enrollments = $lastGroup->enrollments->count();

                    if ($enrollments >= $lastGroup->capacity || $lastGroup->start_date < now()) {
                        $lastGroup = Group::create([
                            'name' => chr(ord($lastGroup->name) + 1),
                            'creator_id' => 1,
                            'bundle_id' => $bundle->id,
                            'capacity' => 20,
                            'start_date' => $startDate,
                            'end_date' => $endDate
                        ]);
                    }

                    Enrollment::create([
                        'user_id' => $user->id,
                        'group_id' => $lastGroup->id,
                    ]);
                }
            } elseif ($service_sale && $service_sale->order->user_id == $user->id && $service_sale->order->status == 'paid') {
                $serviceRequestContent = $request->cookie('service_content');
                $bundle_transform = $request->cookie('bundle_transform');
                $bundle_bridging = $request->cookie('bundle_bridging');
                $bundle_delay = $request->cookie('bundle_delay');

                $service = $service_sale->order->orderItems->first()->service;
                if ($serviceRequestContent) {
                    $serviceRequestContent = json_decode($serviceRequestContent, true);

                    // $service->users()->attach($user, ['content' => $serviceRequestContent]);
                    $serviceRequest = ServiceUser::create([
                        'service_id' => $service->id,
                        'user_id' => $service_sale->buyer_id,
                        'content' => $serviceRequestContent
                    ]);
                } else {
                    $serviceRequest =  ServiceUser::where([
                        'user_id' => $service_sale->buyer_id,
                        'service_id' => $service->id
                    ])->update(['status' => 'pending']);
                }
                if ($bundle_transform) {
                    $bundle_transform = json_decode($bundle_transform, true);
                    BundleTransform::create([
                        ...$bundle_transform,
                        'user_id' => $service_sale->buyer_id,
                        'service_request_id' => $serviceRequest->id
                    ]);
                }

                if ($bundle_bridging) {
                    $bundle_bridging = json_decode($bundle_bridging, true);
                    BridgingRequest::create([
                        ...$bundle_bridging,
                        'user_id' => $service_sale->buyer_id,
                        'service_request_id' => $serviceRequest->id
                    ]);
                }

                if ($bundle_delay) {
                    $bundle_delay = json_decode($bundle_delay, true);
                    BundleDelay::create([
                        ...$bundle_delay,
                        'user_id' => $service_sale->buyer_id,
                        'service_request_id' => $serviceRequest->id
                    ]);
                }

                $notifyOptions = [
                    '[u.name]' => $service_sale?->buyer?->full_name,
                    '[u.code]' => $service_sale?->buyer?->user_code,
                    '[s.title]' => $service_sale?->service?->title,
                ];

                $adminUsers = User::where(['status' => 'active'])->whereIn('role_id', Role::$admissionRoles)->get();

                foreach ($adminUsers as $adminUser) {
                    sendNotification('user_service_request', $notifyOptions, $adminUser->id);
                }
            }

            if (!empty($data['order']) && $data['order']->status === Order::$paid) {
                $toastData = [
                    'title' => trans('cart.success_pay_title'),
                    'msg' => trans('cart.success_pay_msg'),
                    'status' => 'success'
                ];

                session()->forget($this->order_session_key);

                if (!empty($service_sale)) {
                    return redirect('/panel/services/requests')->with(['toast' => $toastData, 'success' => trans('panel.request_sent_successfully')])
                        ->withCookie(cookie('service_content', null, -1))
                        ->withCookie(cookie('bundle_transform', null, -1))
                        ->withCookie(cookie('bundle_bridging', null, -1))
                        ->withCookie(cookie('bundle_delay', null, -1));
                }

                if (empty($sale)) {
                    return redirect('/panel')->with(['toast' => $toastData]);
                }
                if (!empty($sale) && isset($pivot->id)) {
                    if (count($sale->bundle->category->categoryRequirements) > 0) {
                        return redirect("/panel/requirements")->with(['toast' => $toastData]);
                    } else {
                        return redirect("/panel/requirements/applied")->with(['toast' => $toastData]);
                    }
                }

                // if (!empty($sale) && isset($pivot->id) && ($sale->bundle->early_enroll == 0)) {
                //     return redirect('/panel/bundles/' . $pivot->id . '/requirements')->with(['toast' => $toastData]);
                // }
                return redirect('/')->with(['toast' => $toastData]);
            } else if (!empty($data['order']) && $data['order']->status === Order::$fail) {
                $toastData = [
                    'title' => trans('cart.failed_pay_title'),
                    'msg' => trans('cart.failed_pay_msg'),
                    'status' => 'error'
                ];
                session()->forget($this->order_session_key);
                return redirect('/')->with(['toast' => $toastData]);
            }

            // return view('web.default.cart.status_pay', $data);
        }
        $toastData = [
            'title' => trans('cart.failed_pay_title'),
            'msg' => trans('cart.failed_pay_msg'),
            'status' => 'error'
        ];
        session()->forget($this->order_session_key);
        return redirect('/')->with(['toast' => $toastData]);
    }

    private function handleMeetingReserveReward($user)
    {
        if ($user->isUser()) {
            $type = Reward::STUDENT_MEETING_RESERVE;
        } else {
            $type = Reward::INSTRUCTOR_MEETING_RESERVE;
        }

        $meetingReserveReward = RewardAccounting::calculateScore($type);

        RewardAccounting::makeRewardAccounting($user->id, $meetingReserveReward, $type);
    }

    private function updateProductOrder($sale, $orderItem)
    {
        $product = $orderItem->product;

        $status = ProductOrder::$waitingDelivery;

        if ($product and $product->isVirtual()) {
            $status = ProductOrder::$success;
        }

        ProductOrder::where('product_id', $orderItem->product_id)
            ->where(function ($query) use ($orderItem) {
                $query->where(function ($query) use ($orderItem) {
                    $query->whereNotNull('buyer_id');
                    $query->where('buyer_id', $orderItem->user_id);
                });

                $query->orWhere(function ($query) use ($orderItem) {
                    $query->whereNotNull('gift_id');
                    $query->where('gift_id', $orderItem->gift_id);
                });
            })
            ->update([
                'sale_id' => $sale->id,
                'status' => $status,
            ]);

        if ($product and $product->getAvailability() < 1) {
            $notifyOptions = [
                '[p.title]' => $product->title,
            ];
            sendNotification('product_out_of_stock', $notifyOptions, $product->creator_id);
        }
    }

    private function updateInstallmentOrder($orderItem, $sale)
    {
        $installmentPayment = $orderItem->installmentPayment;

        if (!empty($installmentPayment)) {
            $installmentOrder = $installmentPayment->installmentOrder;

            $installmentPayment->update([
                'sale_id' => $sale->id,
                'status' => 'paid',
            ]);

            /* Notification Options */
            $notifyOptions = [
                '[u.name]' => $installmentOrder->user->full_name,
                '[installment_title]' => $installmentOrder->installment->main_title,
                '[time.date]' => dateTimeFormat(time(), 'j M Y - H:i'),
                '[amount]' => handlePrice($installmentPayment->amount),
            ];

            if ($installmentOrder and $installmentOrder->status == 'paying' and $installmentPayment->type == 'upfront') {
                $installment = $installmentOrder->installment;

                if ($installment) {
                    if ($installment->needToVerify()) {
                        $status = 'pending_verification';

                        sendNotification("installment_verification_request_sent", $notifyOptions, $installmentOrder->user_id);
                        sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1); // Admin
                    } else {
                        $status = 'open';

                        sendNotification("paid_installment_upfront", $notifyOptions, $installmentOrder->user_id);
                    }

                    $installmentOrder->update([
                        'status' => $status
                    ]);

                    if ($status == 'open' and !empty($installmentOrder->product_id) and !empty($installmentOrder->product_order_id)) {
                        $productOrder = ProductOrder::query()->where('installment_order_id', $installmentOrder->id)
                            ->where('id', $installmentOrder->product_order_id)
                            ->first();

                        $product = Product::query()->where('id', $installmentOrder->product_id)->first();

                        if (!empty($product) and !empty($productOrder)) {
                            $productOrderStatus = ProductOrder::$waitingDelivery;

                            if ($product->isVirtual()) {
                                $productOrderStatus = ProductOrder::$success;
                            }

                            $productOrder->update([
                                'status' => $productOrderStatus
                            ]);
                        }
                    }
                }
            }


            if ($installmentPayment->type == 'step') {
                sendNotification("paid_installment_step", $notifyOptions, $installmentOrder->user_id);
                sendNotification("paid_installment_step_for_admin", $notifyOptions, 1); // For Admin
            }
        }
    }


    private function handleUploadAttachment($user, $file)
    {
        $storage = Storage::disk('public');

        $path = '/' . $user->id . '/offlinePayments';

        if (!$storage->exists($path)) {
            $storage->makeDirectory($path);
        }

        $img = Image::make($file);
        $name = time() . '.' . $file->getClientOriginalExtension();

        $path = $path . '/' . $name;

        $storage->put($path, (string) $img->encode());

        return $name;
    }

    public function payOffline(Request $request, Order $order)
    {

        $user = auth()->user();
        $request->validate([
            'account' => 'required|exists:offline_banks,id',
            'IBAN' => 'required|string',
            'attachment' => 'required|file|mimes:jpeg,jpg,png'
        ]);

        $account = $request->input('account');


        $attachment = $request->file('attachment');
        if (!in_array(strtolower($attachment->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
            return back()->withInput($request->all())->withErrors(['attachment' => "يجب أن يكون المرفق صورة بإمتداد: jpeg, jpg, png الصورة المرفوعة بامتداد " . $attachment->getClientOriginalExtension()]);
        }
        $attachment = $this->handleUploadAttachment($user, $request->file('attachment'));


        $item = $order->orderItems->first();
        $bundleId = $order->orderItems->first()->bundle_id;

        if ($item->form_fee || $item->webinar_id) {
            $orderType = $item->form_fee ? 'form_fee' : 'webinar';

            $userData = $request->cookie('user_data');
            $userData = json_decode($userData, true);

            $student = Student::where('user_id', auth()->user()->id)->first();

            if (!$student) {
                if ($userData) {
                    $studentData =
                        collect($userData)->except(['category_id', 'bundle_id', 'webinar_id', 'type', 'terms', 'certificate', 'timezone', 'password', 'password_confirmation', 'email_confirmation', 'requirement_endorsement'])->toArray();

                    $studentData['email'] = $user->email;
                    $studentData['mobile'] = $user->mobile;
                    $studentData['phone'] = $user->mobile;
                    $student = Student::create($studentData);
                } else {
                    return redirect('/apply');
                }
            }
        }

        if ($item->form_fee) {

            $class =  StudyClass::get()->last();
            if (!$class) {
                $class = StudyClass::create(['title' => "الدفعة الأولي"]);
            }
            // Check if the student already has the bundle ID attached
            if (!$student->bundles->contains($bundleId)) {
                $student->bundles()->attach($bundleId, [
                    'certificate' => (!empty($userData['certificate'])) ? $userData['certificate'] : null,
                    'status' => 'pending',
                    'class_id' => $item?->bundle?->batch_id ?? $class?->id,
                    'created_at' => Date::now(),  // Set current timestamp for created_at
                    'updated_at' => Date::now()
                ]);

                $pivot = \DB::table('bundle_student')
                    ->where('student_id', $student->id)
                    ->where('bundle_id', $bundleId)
                    ->value('id');
            }
        } else if ($item->webinar_id) {
            $orderType = 'webinar';
        } else if ($item->service_id) {
            $orderType = 'service';
            $serviceRequestContent = $request->cookie('service_content');
            $service = $item->service;
            if ($serviceRequestContent) {
                $serviceRequestContent = json_decode($serviceRequestContent, true);
                $service->users()->attach($user, ['content' => $serviceRequestContent, 'status' => 'paying']);
            }
        } else {
            $orderType = $item->installment_payment_id ? 'installment' : 'bundle';
            $student = Student::where('user_id', auth()->user()->id)->first();

            if (empty($item->installmentPayment->step->installmentStep)) {
                BundleStudent::where(['student_id' => $student->id, 'bundle_id' => $bundleId])->update(['status' => 'paying']);
            }
        }


        OfflinePayment::create([
            'user_id' => $user->id,
            'amount' => $order->total_amount,
            'offline_bank_id' => $account,
            'iban' => $request->input('IBAN'),
            'order_id' => $order->id,
            'pay_for' => $orderType,
            'status' => OfflinePayment::$waiting,
            'attachment' => $attachment,
            'created_at' => time(),

        ]);

        $notifyOptions = [
            '[amount]' => handlePrice($order->total_amount),
            '[u.name]' => $user->full_name
        ];



        $order->update(['payment_method' => 'offline_payment']);


        sendNotification('offline_payment_request', $notifyOptions, $user->id);
        sendNotification('new_offline_payment_request', $notifyOptions, 1);


        $sweetAlertData = [
            'msg' => trans('financial.offline_payment_request_success_store'),
            'status' => 'success'
        ];
        return redirect('/panel/financial/offline-payments')->with(['sweetalert' => $sweetAlertData]);
    }
}
