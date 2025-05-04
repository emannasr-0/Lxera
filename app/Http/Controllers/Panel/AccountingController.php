<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\PaymentController;
use App\Models\Accounting;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use App\BundleStudent;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotifications;
use App\Models\Notification;


class AccountingController extends Controller
{
    public function index()
    {
        $userAuth = auth()->user();

        $accountings = Accounting::where('user_id', $userAuth->id)
            ->where('system', false)
            ->where('tax', false)
            ->with([
                'webinar',
                'promotion',
                'subscribe',
                'meetingTime' => function ($query) {
                    $query->with(['meeting' => function ($query) {
                        $query->with(['creator' => function ($query) {
                            $query->select('id', 'full_name');
                        }]);
                    }]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);


        $data = [
            'pageTitle' => trans('financial.summary_page_title'),
            'accountings' => $accountings,
            'commission' => getFinancialSettings('commission') ?? 0
        ];

        return view(getTemplate() . '.panel.financial.summary', $data);
    }

    public function account($id = null)
    {
        $userAuth = auth()->user();

        return view('errors.404');

        $editOfflinePayment = null;
        if (!empty($id)) {
            $editOfflinePayment = OfflinePayment::where('id', $id)
                ->where('user_id', $userAuth->id)
                ->first();
        }


        $paymentChannels = PaymentChannel::where('status', 'active')->get();
        $offlinePayments = OfflinePayment::where('user_id', $userAuth->id)->orderBy('created_at', 'desc')->get();

        $offlineBanks = OfflineBank::query()
            ->orderBy('created_at', 'desc')
            ->with([
                'specifications'
            ])
            ->get();

        $razorpay = false;
        foreach ($paymentChannels as $paymentChannel) {
            if ($paymentChannel->class_name == 'Razorpay') {
                $razorpay = true;
            }
        }

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

        $data = [
            'pageTitle' => trans('financial.charge_account_page_title'),
            'offlinePayments' => $offlinePayments,
            'paymentChannels' => $paymentChannels,
            'offlineBanks' => $offlineBanks,
            'accountCharge' => $userAuth->getAccountCharge(),
            'readyPayout' => $userAuth->getPayout(),
            'totalIncome' => $userAuth->getIncome(),
            'editOfflinePayment' => $editOfflinePayment,
            'razorpay' => $razorpay,
            'registrationBonusAmount' => $registrationBonusAmount,
        ];

        return view('web.default.panel.financial.account', $data);
    }

    public function charge(Request $request)
    {
        $rules = [
            'amount' => 'required|numeric|min:0',
            'gateway' => 'required',
            'account' => 'required_if:gateway,offline',
            'referral_code' => 'required_if:gateway,offline',
            'date' => 'required_if:gateway,offline',
        ];

        if (!empty($request->file('attachment'))) {
            $rules['attachment'] = 'image|mimes:jpeg,png,jpg|max:10240';
        }

        $this->validate($request, $rules);

        $gateway = $request->input('gateway');
        $amount = $request->input('amount');
        $account = $request->input('account');
        $referenceNumber = $request->input('referral_code');
        $date = $request->input('date');

        if ($amount <= 0) {
            return back()->withErrors([
                'amount' => trans('update.the_amount_must_be_greater_than_0')
            ]);
        }

        $amount = convertPriceToDefaultCurrency($amount);
        $userAuth = auth()->user();

        if ($gateway === 'offline') {

            $attachment = null;

            if (!empty($request->file('attachment'))) {
                $attachment = $this->handleUploadAttachment($userAuth, $request->file('attachment'));
            }

            $date = convertTimeToUTCzone($date, getTimezone());

            OfflinePayment::create([
                'user_id' => $userAuth->id,
                'amount' => $amount,
                'offline_bank_id' => $account,
                'reference_number' => $referenceNumber,
                'status' => OfflinePayment::$waiting,
                'pay_date' => $date->getTimestamp(),
                'attachment' => $attachment,
                'created_at' => time(),
            ]);

            $notifyOptions = [
                '[amount]' => handlePrice($amount),
                '[u.name]' => $userAuth->full_name
            ];
            sendNotification('offline_payment_request', $notifyOptions, $userAuth->id);
            sendNotification('new_offline_payment_request', $notifyOptions, 1);

            $sweetAlertData = [
                'msg' => trans('financial.offline_payment_request_success_store'),
                'status' => 'success'
            ];
            return back()->with(['sweetalert' => $sweetAlertData]);
        }

        $paymentChannel = PaymentChannel::where('class_name', $gateway)->where('status', 'active')->first();

        if (!$paymentChannel) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('public.payment_dont_access'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }

        $order = Order::create([
            'user_id' => $userAuth->id,
            'status' => Order::$pending,
            'payment_method' => Order::$paymentChannel,
            'is_charge_account' => true,
            'total_amount' => $amount,
            'amount' => $amount,
            'created_at' => time(),
            'type' => Order::$charge,
        ]);

        OrderItem::updateOrCreate([
            'user_id' => $userAuth->id,
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


        if ($paymentChannel->class_name == 'Razorpay') {
            return $this->echoRozerpayForm($order);
        } else {
            $paymentController = new PaymentController();

            $paymentRequest = new Request();
            $paymentRequest->merge([
                'gateway' => $paymentChannel->id,
                'order_id' => $order->id
            ]);

            return $paymentController->paymentRequest($paymentRequest);
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

        $storage->put($path, (string)$img->encode());

        return $name;
    }

    private function echoRozerpayForm($order)
    {
        $generalSettings = getGeneralSettings();

        echo '<form action="/payments/verify/Razorpay" method="get">
            <input type="hidden" name="order_id" value="' . $order->id . '">

            <script src="/assets/default/js/app.js"></script>
            <script src="https://checkout.razorpay.com/v1/checkout.js"
                    data-key="' . env('RAZORPAY_API_KEY') . '"
                    data-amount="' . (int)($order->total_amount * 100) . '"
                    data-buttontext="product_price"
                    data-description="Rozerpay"
                    data-currency="' . currency() . '"
                    data-image="' . $generalSettings['logo'] . '"
                    data-prefill.name="' . $order->user->full_name . '"
                    data-prefill.email="' . $order->user->email . '"
                    data-theme.color="#43d477">
            </script>

            <style>
                .razorpay-payment-button {
                    opacity: 0;
                    visibility: hidden;
                }
            </style>

            <script>
                $(document).ready(function() {
                    $(".razorpay-payment-button").trigger("click");
                })
            </script>
        </form>';
        return '';
    }

    public function updateOfflinePayment(Request $request, $id)
    {
        $user = auth()->user();
        $offline = OfflinePayment::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($offline)) {
            $validator =  Validator::make($request->all(), [
                'account' => 'required|exists:offline_banks,id',
                'IBAN' => 'required|string',
                'attachment' => 'required|file|mimes:jpeg,jpg,png|max:10240'
            ]);

            if ($validator->fails()) {
                $sweetAlertData = [
                    'msg' => implode(', ', $validator->errors()->all()),
                    'status' => 'error'
                ];
                return back()->with(['sweetalert' => $sweetAlertData]);
            }



            $attachment = $offline->attachment;

            if (!empty($request->file('attachment'))) {

                $attachment = $request->file('attachment');
                if (!in_array(strtolower($attachment->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                    $sweetAlertData = [
                        'msg' => "يجب أن يكون المرفق صورة بإمتداد: jpeg, jpg, png الصورة المرفوعة بامتداد " . $attachment->getClientOriginalExtension(),
                        'status' => 'error'
                    ];
                    return back()->with(['sweetalert' => $sweetAlertData]);
                }
                $attachment = $this->handleUploadAttachment($user, $request->file('attachment'));
            }

            $date = convertTimeToUTCzone($request['date'], getTimezone());

            if ($offline->pay_for == 'form_fee') {
                $status = 'pending';
            } else {
                if(!empty($offline->order->orderItems->first()->installmentPayment->step->installmentStep)){
                    $status = 'approved';
                }else{
                    $status = 'paying';
                }
            }

            $offline->update([
                'status' => OfflinePayment::$waiting,
                'attachment' => $attachment,
                'offline_bank_id' => $request->input('account'),
                'iban' =>  $request->input('IBAN'),
            ]);

            BundleStudent::where(['student_id' => $offline->user->student->id, 'bundle_id' => $offline->order->orderItems->first()->bundle_id])->update(['status' => $status]);
            $sweetAlertData = [
                'msg' => 'تم اعادة ارسال طلبك بنجاح',
                'status' => 'success'
            ];

            $notifyOptions = [
                '[amount]' => handlePrice($offline->amount),
                '[u.name]' => $user->full_name,
                '[p.body]' => 'تم اعادة ارسال طلبك بنجاح '
            ];

            sendNotification('offline_payment_request', $notifyOptions, $user->id);
            sendNotification('new_offline_payment_request', collect($notifyOptions)->except(['[p.body]'])->toArray(), 1);
            return back()->with(['sweetalert' => $sweetAlertData]);
        }

        return view('errors.404');
    }

    public function deleteOfflinePayment($id)
    {
        $user = auth()->user();
        $offline = OfflinePayment::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        dd($offline);
        if (!empty($offline)) {
            $offline->delete();

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }
    public function cancelOfflinePayment($id)
    {


        $user = auth()->user();
        $offline = OfflinePayment::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($offline)) {
            $offline->update(['status' => 'canceled']);

            $BundlStudent = BundleStudent::where(['student_id' => $offline->user->student->id, 'bundle_id' => $offline->order->orderItems->first()->bundle_id])->first();
            $bundleTitle = $offline->order->orderItems->first()->bundle->title;
            // if ($offline->pay_for == 'form_fee') {
            //     $BundlStudent->delete();
            // } else {
            //     $BundlStudent->update(['status' => 'approved']);
            // }


            if ($offline->pay_for == 'form_fee') {
                $purpuse = 'لحجز مقعد دراسي ';
                $BundlStudent->delete();
            } elseif ($offline->pay_for == 'bundle') {
                $purpuse = 'للدفع الكامل ل '.  $bundleTitle;
                $BundlStudent->update(['status' => 'approved']);
            } elseif ($offline->pay_for == 'installment') {
                $purpuse = 'لدفع ' .($offline->order->orderItems->first()->installmentPayment->step->installmentStep->title ?? 'القسط الأول').' من ' . $bundleTitle;
                $BundlStudent->update(['status' => 'approved']);

            } else {
                $purpuse = '';
            }

                $data['title'] = "إلغاء طلب دفع من حساب بنكي";

                $data['body'] = "لقد تم إلغاء طلبك  " . $purpuse .' بقيمة '. handlePrice($offline->amount);

            Notification::create([
                'user_id' => $offline->user_id,
                'sender_id' => 1,
                'title' => $data['title'],
                'message' => $data['body'],
                'sender' => Notification::$AdminSender,
                'type' => "single",
                'created_at' => time()
            ]);

            Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['body'], 'name' =>$offline->user->full_name]));

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    public function getOfflinePayment()
    {
        $user = auth()->user();
        $offlinePayments = OfflinePayment::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(5);
        $offlineBanks = OfflineBank::query()
            ->orderBy('created_at', 'desc')
            ->with([
                'specifications'
            ])
            ->get();
        return view('web.default.panel.financial.offline_payments.index', compact('offlinePayments', 'offlineBanks'));
    }
}
