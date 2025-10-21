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
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceUser;
use App\Models\StudyClass;
use Illuminate\Support\Facades\Date;
use App\Facades\MicrosoftGraph;
use App\Services\MicrosoftGraphService;


//paymob
class PaymentPaymobController extends Controller
{
    
    
    protected $order_session_key = 'payment.order_id';
    // 1) Webhook: يتحقق HMAC من obj ويرجع JSON (من غير redirect)
    
    
    
  
    public function paymobWebhook(Request $request)
    {
        Log::info('PAYMOB WEBHOOK HIT', $request->all());

        // Paymob بيبعت hmac في الـ query (?hmac=...) والـ payload جوه obj في الـ body
        $hmac = $request->query('hmac');
        $data = $request->all();
        $obj  = $data['obj'] ?? null;

        if (!$hmac || !is_array($obj) || !$this->validatePaymobHmacWebhook($obj, $hmac)) {
            Log::warning('Paymob Webhook: invalid HMAC');
            return response()->json(['message' => 'invalid hmac'], 400);
        }

        $success          = (bool)($obj['success'] ?? false);
        $merchantOrderId  = $obj['order']['merchant_order_id'] ?? null;
        $transactionId    = $obj['id'] ?? null;

        if (!$merchantOrderId) {
            return response()->json(['message' => 'missing merchant_order_id'], 400);
        }

        $order = \App\Models\Order::find($merchantOrderId);
        if (!$order) {
            return response()->json(['message' => 'order not found'], 404);
        }

        if ($success) {
            $order->update([
                'status'            => \App\Models\Order::$paying,
                'payment_reference' => $transactionId,
            ]);
            // سيبّي الـ UI/redirect يتعامل من مسار الـ redirect. هنا نرجّع JSON 200
            return response()->json(['message' => 'ok', 'order_id' => $order->id, 'status' => 'paying'], 200);
        } else {
            $order->update([
                'status'            => \App\Models\Order::$fail,
                'payment_reference' => $transactionId,
            ]);
            return response()->json(['message' => 'failed', 'order_id' => $order->id, 'status' => 'fail'], 200);
        }
    }

    // 2) دالة حساب HMAC للـ Webhook (على obj فقط وبالترتيب الصحيح)
    private function validatePaymobHmacWebhook(array $obj, string $incomingHmac): bool
    {
        $secret = trim((string) env('PAYMOB_HMAC'));

        $fields = [
            "amount_cents",
            "created_at",
            "currency",
            "error_occured",
            "has_parent_transaction",
            "id",
            "integration_id",
            "is_3d_secure",
            "is_auth",
            "is_capture",
            "is_refunded",
            "is_standalone_payment",
            "is_voided",
            "order",
            "owner",
            "pending",
            "source_data.pan",
            "source_data.sub_type",
            "source_data.type",
            "success",
        ];

        $get = function (array $arr, string $path): string {
            if (strpos($path, '.') === false) {
                return array_key_exists($path, $arr) ? (string)$arr[$path] : '';
            }
            $parts = explode('.', $path);
            $val = $arr;
            foreach ($parts as $p) {
                if (!is_array($val) || !array_key_exists($p, $val)) return '';
                $val = $val[$p];
            }
            return (string)$val;
        };

        $concat = '';
        foreach ($fields as $f) {
            $concat .= $get($obj, $f);
        }

        $calc = hash_hmac('sha512', $concat, $secret);
        return hash_equals(strtolower($calc), strtolower($incomingHmac));
    }

    public function paymobRedirect(Request $request)
    {
        // ناخد الـ query الخام (زي ما Paymob بعتها)
        $raw = $request->server('QUERY_STRING') ?? '';
        parse_str($raw, $query); // بيفكّ URL-encoding من غير "تطبيع" للأنواع

        $hmac = $query['hmac'] ?? null;
        if (!$hmac) {
            return response()->json(['message' => 'Missing HMAC'], 400);
        }

        if (!$this->validatePaymobHmacRedirect($query, $hmac)) {
            return response()->json(['message' => 'Invalid HMAC'], 400);
        }

        $merchantOrderId = $query['merchant_order_id'] ?? null;
        if (!$merchantOrderId) {
            return response()->json(['message' => 'Missing merchant_order_id'], 400);
        }

        $order = \App\Models\Order::find($merchantOrderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $success = ($query['success'] ?? '') === 'true';
        $txnId   = $query['id'] ?? null;

        if ($success) {
            $order->update([
                'status'            => \App\Models\Order::$paying,
                'payment_reference' => $txnId,
            ]);
            // نفس المسار القياسي بعد الدفع
            return $this->paymentOrderAfterVerify($order);
        } else {
            $order->update([
                'status'            => \App\Models\Order::$fail,
                'payment_reference' => $txnId,
            ]);
            session()->put($this->order_session_key, $order->id);
            return redirect('/payments/status');
        }
    }

    // 4) دالة حساب HMAC للـ Redirect (بتتعامل مع source_data.* و source_data_*)
    private function validatePaymobHmacRedirect(array $data, string $incomingHmac): bool
    {
        $secret = trim((string) env('PAYMOB_HMAC')); // مهم: من غير مسافات/تعليقات في .env

        $fields = [
            "amount_cents",
            "created_at",
            "currency",
            "error_occured",
            "has_parent_transaction",
            "id",
            "integration_id",
            "is_3d_secure",
            "is_auth",
            "is_capture",
            "is_refunded",
            "is_standalone_payment",
            "is_voided",
            "order",
            "owner",
            "pending",
            "source_data.pan",
            "source_data.sub_type",
            "source_data.type",
            "success",
        ];

        // نطبّع المفاتيح عشان تدعم الشكلين: source_data.type و source_data_type
        $normalized = $data;
        foreach ($data as $k => $v) {
            if (strpos($k, 'source_data.') === 0) {
                [$prefix, $sub] = explode('.', $k, 2);
                $normalized[$prefix][$sub] = $v;
            } elseif (strpos($k, 'source_data_') === 0) {
                $sub = substr($k, strlen('source_data_'));
                $normalized['source_data'][$sub] = $v;
            }
        }

        $get = function (array $arr, string $path): string {
            if (strpos($path, '.') === false) {
                return array_key_exists($path, $arr) ? (string)$arr[$path] : '';
            }
            $parts = explode('.', $path);
            $val = $arr;
            foreach ($parts as $p) {
                if (!is_array($val) || !array_key_exists($p, $val)) return '';
                $val = $val[$p];
            }
            return (string)$val;
        };

        $concat = '';
        foreach ($fields as $f) {
            $concat .= $get($normalized, $f);
        }

        $calc = hash_hmac('sha512', $concat, $secret);
        return hash_equals(strtolower($calc), strtolower($incomingHmac));
    }


    public function paymentRequest(Request $request)
    {
       
        $this->validate($request, [
            'gateway' => 'required'
        ]);

        $user = auth()->user();
        $gateway = $request->input('gateway');
        $orderId = $request->input('order_id');

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if ($order->type === Order::$meeting) {
            $orderItem = OrderItem::where('order_id', $order->id)->first();
            $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
            $reserveMeeting->update(['locked_at' => time()]);
        }
     
      if ($gateway === 'credit') {

            if ($user->getAccountingCharge() < $order->total_amount) {   
               
                $order->update(['status' => Order::$fail]);

                session()->put($this->order_session_key, $order->id);

                return redirect('/payments/status');
            }

            $order->update([
                'payment_method' => Order::$credit
            ]);

            $this->setPaymentAccounting($order, 'credit');

            $order->update([
                'status' => Order::$paid
            ]);

            session()->put($this->order_session_key, $order->id);

            return redirect('/payments/status');
        }

        $paymentChannel = PaymentChannel::where('class_name', $gateway)
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
        $order->save();

        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);
            $redirect_url = $channelManager->paymentRequest($order);

            if (in_array($paymentChannel->class_name, PaymentChannel::$gatewayIgnoreRedirect)) {
                return $redirect_url;
            }

            return Redirect::away($redirect_url);
        } catch (\Exception $exception) {

            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }

    public function paymentVerify(Request $request, $gateway)
    {
        $paymentChannel = PaymentChannel::where('class_name', $gateway)
            ->where('status', 'active')
            ->first();

        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);
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

    private function paymentOrderAfterVerify($order)
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
        $cashbackAccounting = new CashbackAccounting();

        if ($order->is_charge_account) {
            Accounting::charge($order);

            $cashbackAccounting->rechargeWallet($order);
        } else {
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

        Cart::emptyCart($order->user_id);
    }

    public function payStatus(Request $request)
    {
        $orderId = $request->get('order_id', null);

        if (!empty(session()->get($this->order_session_key, null))) {
            $orderId = session()->get($this->order_session_key, null);
            session()->forget($this->order_session_key);
        }

        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!empty($order)) {
            $data = [
                'pageTitle' => trans('public.cart_page_title'),
                'order' => $order,
            ];

            return view('web.default.cart.status_pay', $data);
        }

        return redirect('/panel');
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
}
