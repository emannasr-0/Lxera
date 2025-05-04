<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\ActiveBundleResource;
use App\Http\Resources\ActiveCategoryResource;
use App\Http\Resources\BundleBridgingResource;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Api\Category;
use App\Models\Api\Sale;
use App\Models\Bundle;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Models\BridgingRequest;
use App\Models\BundleBridging;
use App\Models\BundleTransform;
use App\Models\InstallmentOrder;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Cookie;
use App\Models\ServiceUser;
use App\User;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{

    public function index()
    {
        $user = apiAuth();

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
        return sendResponse($services, 'services retrieved');
    }

    public function requests()
    {
        //
        $user = apiAuth();
        $services = $user->services;

        $request = ServiceUser::where('user_id', $user->id)
        ->with(['service', "bundleTransform", "BridgingRequest"])
        ->get();

        return sendResponse([$services,$request], 'Service Requests list');
    }


    public function sendRequest(Request $request, Service $service, $content = null)
    {
        //
        $user = auth('api')->user();
        if (empty($content)) {
            $content = $service->title;
        }

        if($service->status != 'active'){
            return sendError([], "can't apply to this service");
        }
        if ($service->price > 0) {

            Cookie::queue('service_content', json_encode($content));
            $order = $this->createOrder($service);
            return sendResponse(
                [
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                    ]
                ],
                'order is created successfully'
            );
        } else {
            $serviceRequest = ServiceUser::create(['service_id' => $service->id, 'user_id' => $user->id, 'content' => $content]);
            $notifyOptions = [
                '[u.name]' => $user?->full_name,
                '[u.code]' => $user?->user_code,
                '[s.title]' => $service?->title,
            ];
            $adminUsers = User::where(['status' => 'active'])->whereIn('role_id', Role::$admissionRoles)->get();
            foreach ($adminUsers as $adminUser) {
                sendNotification('user_service_request', $notifyOptions, $adminUser->id);
            }

            return sendResponse($serviceRequest, "تم ارسال الطلب بنجاح وهو قيد المراجعة من قبل الإدارة المختصة");
        }
    }

    function bundleBridgingRequest(Service $service)
    {

        $user = apiAuth();
        $purchasedBundles = $user->bundles;
        $bundlesId = $purchasedBundles->pluck('id');

        //bundle[bridging] wherehas birdgingbundle record[from_bundle_id]
        // $bundles = Bundle::where('type', 'bridging')
        //     ->where('status', 'active')
        //     ->whereHas('bridgingBundles', function ($query) use ($bundlesId) {
        //         $query->whereIn('from_bundle_id', $bundlesId);
        //     })
        //     ->with(['bridgings', 'bridgingBundles', 'bridgings.fromBundle', 'bridgings.toBundle'])->get();


        // $bridings = BundleBridging::whereHas('mainBundle', function ($query) {
        //     $query->where('type', 'bridging')->where('status', 'active');
        // })
        //     ->whereHas('fromBundle', function ($query) use ($bundlesId) {
        //         $query->whereIn('id', $bundlesId);
        //     })
        //     // ->groupBy('bridging_id')
        //     ->get();

        $programs = $user->bundles()
            ->whereHas('bridgingFromBundles', function ($query) use ($bundlesId) {
                $query->where('type', 'bridging')
                    ->where('status', 'active');
            })
            ->select(['bundles.id', 'bundles.price'])
            ->with(['bridgingFromBundles' => function ($query) {
                $query->where('status', 'active')->pluck('bundle_bridging.bridging_id');
            }])
            ->get();

        $data = [
            'programs' => BundleBridgingResource::collection($programs),
        ];

        return sendResponse($data, 'bundle briding retrieved');
    }

    public function bundleBridging(Request $request, Service $service)
    {

        $user = apiAuth();

        $rules = [
            'from_bundle_id' => 'required|exists:bundles,id',
            'bridging_id' => "required|exists:bundles,id"
        ];

        $validatedData = validateParam($request->all(), $rules);


        $bridging = Bundle::where('id', $request->bridging_id)->first();
        // $fromBundle = Bundle::where('id', $request->from_bundle_id)->first();

        // if (empty($bridging) || empty($fromBundle) ) return $this->notFound();
        if (empty($bridging)) return $this->notFound();

        if (!($bridging->bridgingBundles->contains($request->from_bundle_id))) {
            return sendError([
                'bridging_id' => "can't bridging to this program"
            ], "can't bridging to this program");
        };

        $content = " طلب تقديم لبرنامج " . trans('update.bridging') . " " . $bridging->title;

        if ($service->price > 0) {
            Cookie::queue('service_content', json_encode($content));
            Cookie::queue('bundle_bridging', json_encode($validatedData));
            $order = $this->createOrder($service);
            return sendResponse(
                [
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                    ]
                ],
                'order is created successfully'
            );
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
        return sendResponse($serviceRequest, 'bridging service request is done successfully');
    }

    function bundleTransformRequest(Service $service)
    {
        $user = apiAuth();

        $categories = Category::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('activeBundles');
            })
            ->select(['id']) // Select id and slug for categories
            ->with([
                'activeBundles' => function ($query) {
                    $query->with('additionBundles', function ($query) {
                        $query->where('status', 'active');
                    });
                },
            ])
            ->get();

        $data = [
            'categories' => ActiveCategoryResource::collection($categories),
            'purchases' => ActiveBundleResource::collection($user->bundles)
        ];
        return sendResponse($data, 'bridging service request is done successfully');
    }

    function bundleTransform(Request $request, Service $service)
    {

        $user = auth('api')->user();

        $to_bundle = Bundle::where('id', $request->to_bundle_id)->first();
        $rules = [
            'from_bundle_id' => 'required|exists:bundles,id',
            'to_bundle_id' => 'required|exists:bundles,id',
            'certificate' => $to_bundle ? ($to_bundle->has_certificate ? 'required|boolean' : "") : '',

        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request, $to_bundle) {
            // Check if user is already registered in the bundle
            $user = auth('api')->user();
            $student = $user->student;

            if ($student && $request->to_bundle_id && ($request->want_addition != 1) && $student->bundles()->where('bundles.id', $request->to_bundle_id)->exists()) {
                $validator->errors()->add('to_bundle_id', 'انت مسجل بالفعل في هذا البرنامج');
            }

            $hasAdditionBundles = ($to_bundle && $to_bundle->additionBundles()->exists());

            if (
                $hasAdditionBundles && ($request->want_addition == 1) && !$request->addition_bundle_id
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
            return sendError($validator->errors(), 'request validation error');
        }

        // If validation passes, proceed with the request
        $validatedData = $validator->validated();

        $toBundleId = $request->to_bundle_id;
        $to_bundle_key = 'to_bundle_id';
        if ($request->addition_bundle_id && $request->want_addition == 1) {
            $toBundleId = $request->addition_bundle_id;
            $to_bundle_key = 'addition_bundle_id';
            $to_bundle = Bundle::find($toBundleId);
        }

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
                return sendError([
                    'from_bundle_id' =>
                    ["لا يمكن التحويل من هذا البرنامج يرجي التواصل مع إدارة التدريب"]
                ], "لا يمكن التحويل من هذا البرنامج يرجي التواصل مع إدارة التدريب");
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
                return sendError([
                    "$to_bundle_key" =>
                    ["البرنامج المختار غير متوفر له بلان تقسيط"]
                ], "البرنامج المختار غير متوفر له بلان تقسيط");
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
                ...$validatedData,
                'type' => $type,
                'transform_Type' => $transformType,
                'amount' => abs($amount)
            ];

            Cookie::queue('service_content', json_encode($content));
            Cookie::queue('bundle_transform', json_encode($bundleRequest));
            $order = $this->createOrder($service);
            return sendResponse(
                [
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                    ]
                ],
                'order is created successfully'
            );
        } else {
            $serviceRequest = ServiceUser::create(['service_id' => $service->id, 'user_id' => $user->id, 'content' => $content]);
            BundleTransform::create([
                ...$validatedData,
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
            return sendResponse($serviceRequest, "تم ارسال الطلب بنجاح وهو قيد المراجعة من قبل الإدارة المختصة");
        }
    }

    function bundleTransformPay(Request $request, bundleTransform $bundleTransform)
    {

        $user = auth('api')->user();

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

        return sendResponse(
            [
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                ]
            ],
            'order is created successfully'
        );
    }

    public function createOrder(Service $service)
    {
        $user = apiAuth();
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
