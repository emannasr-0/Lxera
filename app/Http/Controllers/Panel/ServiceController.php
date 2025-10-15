<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\BridgingRequest;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

use App\Models\Service;
use App\Models\Bundle;
use App\Models\BundleBridging;
use App\Models\BundleDelay;
use App\Models\BundleTransform;
use App\Models\Category;
use App\Models\InstallmentOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\Sale;
use App\Models\ServiceUser;
use App\User;
use Illuminate\Support\Facades\Validator;
use stdClass;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user = auth()->user();

        $student = $user->student;
        $userBundleIds = $student->bundles()->pluck('bundles.id', 'bundles.slug')->toArray();

        $webinarsIds = $user->getAllPurchasedWebinarsIds();

        $services = Service::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where(function ($query) use ($userBundleIds, $webinarsIds) {
                $query
                    ->whereHas('bundles', function ($subQuery) use ($userBundleIds) {
                        $subQuery->whereIn('bundles.id', $userBundleIds);
                    })
                    ->orWhereHas('webinars', function ($subQuery) use ($webinarsIds) {
                        $subQuery->whereIn('webinars.id', $webinarsIds);
                    })
                    ->orWhere('target', 'all');

                if (!empty($userBundleIds)) {
                    $query->orWhere('target', 'bundles');
                }
                if (!empty($webinarsIds)) {
                    $query->orWhere('target', 'webinars');
                }
            })
            ->get();


        return view(getTemplate() . '.panel.services.index', compact('services'));
    }



    public function requests()
    {
        //
        $services = auth()->user()->services()->paginate(10);

        // dd($services);
        return view(getTemplate() . '.panel.services.requests', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Service $service, $content = null)
    {
        //
        $user = auth()->user();
        if (empty($content)) {
            $content = $service->title;
        }

        if ($service->price > 0) {

            Cookie::queue('service_content', json_encode($content));
            $order = $this->createOrder($service);
            return redirect('/payment/' . $order->id);
        } else {
            $service->users()->attach($user, ['content' => $content]);
            $notifyOptions = [
                '[u.name]' => $user?->full_name,
                '[u.code]' => $user?->user_code,
                '[s.title]' => $service?->title,
            ];
            $adminUsers = User::where(['status' => 'active'])->whereIn('role_id', Role::$admissionRoles)->get();
            foreach ($adminUsers as $adminUser) {
                sendNotification('user_service_request', $notifyOptions, $adminUser->id);
            }

            return redirect('/panel/services/requests')->with("success", trans('panel.request_sent_successfully'));
        }
    }


    function bundleTransformRequest(Service $service)
    {
        // $categories = Category::whereNull('parent_id')->where('status', 'active')
        // ->where(function ($query) {
        //     $query->whereHas('activeBundles')
        //         ->orWhereHas('activeSubCategories', function ($query) {
        //             $query->whereHas('activeBundles');
        //         });
        // })->get();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('activeBundles')
                    ->orWhereHas('activeSubCategories', function ($query) {
                        $query->whereHas('activeBundles');
                    });
            })
            ->select(['id']) // Select id and slug for categories
            ->with([
                'activeBundles' => function ($query) {
                    $query->with('additionBundles', function ($query) {
                        $query->where('status', 'active');
                    });
                },
                'activeSubCategories' => function ($query) {
                    $query->whereHas('activeBundles')
                        ->orWhereHas('activeWebinars')

                        ->with([
                            'activeBundles' => function ($query) {
                                $query->with('additionBundles', function ($query) {
                                    $query->where('status', 'active');
                                });
                            },
                        ]);
                },
            ])
            ->get();

        return view('web.default.panel.services.includes.bundleTransform', compact('categories', 'service'));
    }

    function bundleTransform(Request $request, Service $service)
    {

        $user = auth()->user();

        $to_bundle = Bundle::where('id', $request->to_bundle_id)->first();
        // $validatedData = $request->validate([
        //     'from_bundle_id' => 'required|exists:bundles,id',
        //     'to_bundle_id' => [
        //         'required',
        //         'exists:bundles,id',
        //         function ($attribute, $value, $fail) {
        //             $student = auth()->user()->student;
        //             if ($student && $student->bundles()->where('bundles.id', $value)->whereNotNull('class_id')->exists()) {
        //                 $fail('انك مسجل بالفعل في هذا البرنامج');
        //             }
        //         }
        //     ],
        //     'certificate' => $to_bundle ? ($to_bundle->has_certificate ? 'required|boolean' : "") : '',
        //     'addition_bundle_id' => [
        //         'nullable',
        //         'exists:bundles,id',
        //         function ($attribute, $value, $fail) {
        //             $student = auth()->user()->student;
        //             if ($student && $student->bundles()->where('bundles.id', $value)->whereNotNull('class_id')->exists()) {
        //                 $fail('انك مسجل بالفعل في هذا البرنامج');
        //             }
        //         }
        //     ]
        // ]);


        $rules = [
            'from_bundle_id' => 'required|exists:bundles,id',
            'to_bundle_id' => 'required|exists:bundles,id',
            'certificate' => $to_bundle ? ($to_bundle->has_certificate ? 'required|boolean' : "") : '',

        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request, $to_bundle) {
            // Check if user is already registered in the bundle
            $user = auth()->user();
            $student = $user->student;

            if ($student && $request->to_bundle_id && ($request->want_addition != 1) && $student->bundles()->where('bundles.id', $request->to_bundle_id)->exists()) {
                $validator->errors()->add('to_bundle_id', 'انت مسجل بالفعل في هذا البرنامج');
            }

            $hasAdditionBundles = ($to_bundle && $to_bundle->additionBundles()->exists());

            if ($hasAdditionBundles && ($request->want_addition == 1) && !$request->addition_bundle_id
            ) {
                $validator->errors()->add('addition_bundle_id', 'حقل التخصص المزدوج مطلوب');
            }

            if (
                $student && $request->addition_bundle_id
                && $request->want_addition == 1 &&
                $student->bundles()->where('bundles.id', $request->addition_bundle_id)->exists()
            ) {
                $validator->errors()->add('addition_bundle_id', 'انت مسجل بالفعل في هذا البرنامج');
            }

            });

        // Validate the data
        if ($validator->fails()) {
            // dd($validator->errors());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // If validation passes, proceed with the request
        $validatedData = $validator->validated();

        $toBundleId = $request->to_bundle_id;
        $to_bundle_key = 'to_bundle_id';
        if ($request->addition_bundle_id && $request->want_addition == 1) {
            $toBundleId = $request->addition_bundle_id;
            $to_bundle_key = 'addition_bundle_id';
            
        }
        $to_bundle = Bundle::find($toBundleId);

        $from_bundle = Bundle::where('id', $request->from_bundle_id)->first();

        $content = " طلب تحويل من " . $from_bundle->title . " الي " . $to_bundle->title;
        if ($request->certificate) {
            $content .= " والرغبة في حجز الشهادة المهنيه الاحترافية ACP ";
        }

        $amount = $to_bundle->price - $from_bundle->price;
        $transformType = 'bundle';
        $contentExtention = '(تحويل دفع كامل الرسوم)';



        $sale = Sale::where(['bundle_id' => $from_bundle->id, 'buyer_id' => $user->id])->whereIn('type', ['bundle', 'installment_payment'])->first();

        $formFeeSale = Sale::where(['bundle_id' => $from_bundle->id, 'buyer_id' => $user->id, 'type' => 'form_fee'])
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('sales as s2')
                    ->whereRaw('s2.bundle_id = sales.bundle_id')
                    ->where(function ($query) {
                        $query->where('s2.type', 'bundle')
                            ->orWhere('s2.type', 'installment_payment');
                    })
                    ->whereRaw('s2.buyer_id = sales.buyer_id');
            })->first();

        if ($formFeeSale) {
            $transformType = 'form_fee';
            $contentExtention = '(تحويل حجز مقعد)';
            $amount = 0;
        }

        if ($sale && $sale->type == 'installment_payment') {
            $installmentOrder = InstallmentOrder::query()
                ->where(['user_id' => $user->id, 'bundle_id' => $from_bundle->id, 'status' => 'open'])
                ->with(['selectedInstallment', 'selectedInstallment.steps'])->latest()->first();


            if ($installmentOrder->payments->count() > 1) {
                $toastData = [
                    'title' => "خطأ التحويل",
                    'msg' => "لا يمكن التحويل من هذا البرنامج يرجي التواصل مع إدارة التدريب",
                    'status' => 'error'
                ];
                return back()->withInput($request->all())->withErrors(['from_bundle_id' => "لا يمكن التحويل من هذا البرنامج يرجي التواصل مع إدارة التدريب"])->with(['toast' => $toastData]);
            }


            $installmentPlans = new InstallmentPlans($user);

            $newInstallment = $installmentPlans->getPlans(
                'bundles',
                $to_bundle->id,
                $to_bundle->type,
                $to_bundle->category_id,
                $to_bundle->teacher_id
            )->last();

            if (!$newInstallment) {
                $toastData = [
                    'title' => "خطأ التحويل",
                    'msg' => "البرنامج المختار غير متوفر له بلان تقسيط",
                    'status' => 'error'
                ];
                return back()->withInput($request->all())->withErrors(["$to_bundle_key" => "البرنامج المختار غير متوفر له بلان تقسيط"])->with(['toast' => $toastData]);
            }
            $oldInstallment = $installmentOrder->selectedInstallment;
            // $oldInstallment1 = $installmentPlans->getPlans('bundles', $from_bundle->id, $from_bundle->type, $from_bundle->category_id, $from_bundle->teacher_id);

            $amount = $newInstallment->upfront - $oldInstallment->upfront;
            $transformType = 'installment';
            $contentExtention = '(تحويل قسط التسجيل)';


            //ToDo:
            /**
             * want to change installment order bundle id, installment_id, item_price
             * change selectedInstallment installment_id
             * change order payment change installment_order_id
             */
        }

        if ($amount == 0) {
            $type = 'none';
        } else if ($amount > 0) {
            $type = "pay";
        } else {
            $type = "refund";
        }

        $content .= ' ' . $contentExtention;

        if ($service->price > 0) {

            $bundleRequest = [
              
                'from_bundle_id'=>$request->from_bundle_id,
                'to_bundle_id'=>$to_bundle->id,
                'type' => $type,
                'transform_Type' => $transformType,
                'amount' => abs($amount)
            ];

            Cookie::queue('service_content', json_encode($content));
            Cookie::queue('bundle_transform', json_encode($bundleRequest));
            $order = $this->createOrder($service);
            return redirect('/payment/' . $order->id);
        } else {
            $serviceRequest = ServiceUser::create(['service_id' => $service->id, 'user_id' => $user->id, 'content' => $content]);
            BundleTransform::create([
                'from_bundle_id'=>$request->from_bundle_id,
                'to_bundle_id'=>$to_bundle->id,
                'user_id' => $user->id,
                'service_request_id' => $serviceRequest->id,
                'type' => $type,
                'transform_Type' => $transformType,
                'amount' => abs($amount)
            ]);

            $notifyOptions = [
                '[u.name]' => $user?->full_name,
                '[u.code]' => $user?->user_code,
                '[s.title]' => $service?->title,
            ];
            $adminUsers = User::where(['status' => 'active'])->whereIn('role_id', Role::$admissionRoles)->get();
            foreach ($adminUsers as $adminUser) {
                sendNotification('user_service_request', $notifyOptions, $adminUser->id);
            }
            return redirect('/panel/services/requests')->with("success", trans('panel.request_sent_successfully'));
        }
    }

    function bundleTransformPay(Request $request, bundleTransform $bundleTransform)
    {

        $user = auth()->user();
        // Cookie::queue('bundleTransformId', json_encode($bundleTransform->id));
        // $order = $this->createOrder($bundleTransform);
        $price = abs($bundleTransform->amount);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => $price,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' =>  $price,
            'product_delivery_fee' => null,
            'created_at' => time(),
        ]);

        OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'bundle_id' => $bundleTransform->to_bundle_id,
            'transform_bundle_id' => $bundleTransform->from_bundle_id,
            'amount' => $price,
            'total_amount' => $price,
            'tax_price' => 0,
            'commission' => 0,
            'commission_price' => 0,
            'product_delivery_fee' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        return redirect('/payment/' . $order->id);
    }

    function bundleTransformRefund(Request $request, bundleTransform $bundleTransform)
    {

        $user = auth()->user();
        Cookie::queue('bundleTransformId', json_encode($bundleTransform->id));
        // $order = $this->createOrder($bundleTransform);
        $price = abs($bundleTransform->toBundle->price - $bundleTransform->fromBundle->price);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => $price,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' =>  $price,
            'product_delivery_fee' => null,
            'created_at' => time(),
        ]);

        $orderItem = OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'bundle_id' => $bundleTransform->to_bundle_id,
            'transform_bundle_id' => $bundleTransform->from_bundle_id,
            'amount' => $price,
            'total_amount' => $price,
            'tax_price' => 0,
            'commission' => 0,
            'commission_price' => 0,
            'product_delivery_fee' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        Sale::createSales($orderItem, $order->payment_method);

        $toastData = [
            'title' => "اتمام التحويل",
            'msg' => "تم اتمام التحويل واستيرداد المبلغ بنجاح",
            'status' => 'success'
        ];
        return back()->with(['toast' => $toastData]);
    }


    function bundleBridgingRequest(Service $service)
    {

        $bundles = Bundle::where('type', 'bridging')
            ->whereHas('bridgingBundles')
            ->where('status', 'active')
            ->with(['bridgings', 'bridgingBundles', 'bridgings.fromBundle', 'bridgings.toBundle'])->get();
        return view('web.default.panel.services.includes.BundleBridging', compact('bundles', 'service'));
    }
    function bundleBridging(Request $request, Service $service)
    {

        $user = auth()->user();
        $validatedData = $request->validate([
            'from_bundle_id' => 'required|exists:bundles,id',
            'bridging_id' => "required|exists:bundles,id"
        ]);


        $bridging = Bundle::where('id', $request->bridging_id)->first();

        if (empty($bridging)) return view('errors.404');

        $content = " طلب تقديم لبرنامج " . trans('update.bridging') . " " . $bridging->title;

        if ($service->price > 0) {
            Cookie::queue('service_content', json_encode($content));
            Cookie::queue('bundle_bridging', json_encode($validatedData));
            $order = $this->createOrder($service);
            return redirect('/payment/' . $order->id);
        }

        $serviceRequest = ServiceUser::create(['service_id' => $service->id, 'user_id' => $user->id, 'content' => $content]);
        BridgingRequest::create([...$validatedData, 'user_id' => $user->id, 'service_request_id' => $serviceRequest->id]);

        $notifyOptions = [
            '[u.name]' => $user?->full_name,
            '[u.code]' => $user?->user_code,
            '[s.title]' => $service?->title,
        ];
        $adminUsers = User::where(['status' => 'active'])->whereIn('role_id', Role::$admissionRoles)->get();
        foreach ($adminUsers as $adminUser) {
            sendNotification('user_service_request', $notifyOptions, $adminUser->id);
        }
        return redirect('/panel/services/requests')->with("success", trans('panel.request_sent_successfully'));
    }

    function bundleBridgingPay(Request $request, Bundle $bundleBridging)
    {

        $user = auth()->user();
        /* Installments */
        $bundleInstallments = [];


        $hasBought = $bundleBridging->checkUserHasBought($user);
        $canSale = $bundleBridging->canSale() && !$hasBought;
        $bundleObject = new stdClass();
        $bundleObject->bundle = $bundleBridging;
        $bundleObject->status = 'approved';
        $bundleObject->bridging = true;

        // Check if the bundle meets the conditions
        if ($canSale && !empty($bundleBridging->price) && $bundleBridging->price > 0 && getInstallmentsSettings('status') && (empty($user) || $user->enable_installments)) {
            $installmentPlans = new InstallmentPlans($user);
            $installments = $installmentPlans->getPlans('bundles', $bundleBridging->id, $bundleBridging->type, $bundleBridging->category_id, $bundleBridging->teacher_id);

            $bundleInstallments[$bundleBridging->id] = [
                'bundle' => $bundleObject,
                'installments' => $installments,
            ];
        } else {

            $bundleInstallments[$bundleBridging->id] = [
                'bundle' => $bundleObject,
                'installments' => null,
            ];
        }

        return view(getTemplate() . '.panel.requirements.payment_step', ['bundleInstallments' => $bundleInstallments ?? null]);
        // $order = Order::create([
        //     'user_id' => $user->id,
        //     'status' => Order::$pending,
        //     'amount' => $bundleBridging->price,
        //     'tax' => 0,
        //     'total_discount' => 0,
        //     'total_amount' =>  $bundleBridging->price,
        //     'product_delivery_fee' => null,
        //     'created_at' => time(),
        // ]);

        // OrderItem::create([
        //     'user_id' => $user->id,
        //     'order_id' => $order->id,
        //     'bundle_id' => $bundleBridging->id,
        //     'amount' => $bundleBridging->price,
        //     'total_amount' => $bundleBridging->price,
        //     'type' => 'bridging',
        //     'tax_price' => 0,
        //     'commission' => 0,
        //     'commission_price' => 0,
        //     'product_delivery_fee' => 0,
        //     'discount' => 0,
        //     'created_at' => time(),
        // ]);

        // return redirect('/payment/' . $order->id);
    }


    function bundleDelayRequest(Service $service)
    {


        return view('web.default.panel.services.includes.bundleDelay', compact('service'));
    }
    function bundleDelay(Request $request, Service $service)
    {

        $user = auth()->user();
        $validatedData = $request->validate([
            'from_bundle_id' => 'required|exists:bundles,id',
            'reason' => "required|string|min:10|max:1000"
        ]);

        $fromBundle = Bundle::findOrFail($validatedData['from_bundle_id']);

        $content = " طلب تأجيل البرنامج " . $fromBundle->title . " من  " . $fromBundle->batch->title . " للدفعة اللاحقة " .
            " وسبب التأجيل هو : " . $request->reason;

        if ($service->price > 0) {
            Cookie::queue('service_content', json_encode($content));
            Cookie::queue('bundle_delay', json_encode($validatedData));
            $order = $this->createOrder($service);
            return redirect('/payment/' . $order->id);
        }

        $serviceRequest = ServiceUser::create(['service_id' => $service->id, 'user_id' => $user->id, 'content' => $content]);
        BundleDelay::create([...$validatedData, 'user_id' => $user->id, 'service_request_id' => $serviceRequest->id]);

        $notifyOptions = [
            '[u.name]' => $user?->full_name,
            '[u.code]' => $user?->user_code,
            '[s.title]' => $service?->title,
        ];
        $adminUsers = User::where(['status' => 'active'])->whereIn('role_id', Role::$admissionRoles)->get();
        foreach ($adminUsers as $adminUser) {
            sendNotification('user_service_request', $notifyOptions, $adminUser->id);
        }

        return redirect('/panel/services/requests')->with("success", trans('panel.request_sent_successfully'));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function createOrder(Service $service)
    {
        $user = auth()->user();
        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => $service->price,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' =>  $service->price,
            'product_delivery_fee' => null,
            'created_at' => time(),
        ]);

        OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'service_id' => $service->id,
            'amount' => $service->price,
            'total_amount' => $service->price,
            'tax_price' => 0,
            'commission' => 0,
            'commission_price' => 0,
            'product_delivery_fee' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        return $order;
    }
}
