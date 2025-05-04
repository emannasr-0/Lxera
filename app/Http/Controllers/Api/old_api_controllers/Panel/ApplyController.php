<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Product;
use App\Models\Sale;
use App\User;
use App\Student;
use App\Models\Category;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\web\PaymentController;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CourseResource;
use App\Models\Bundle;
use App\Models\Code;
use App\Models\Webinar;
use Illuminate\Support\Facades\Date;

class ApplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth("api")->user();
        $student = $user->student;

        $categories = Category::whereNull('parent_id')->where('status', 'active')
        ->where(function ($query) {
            $query->whereHas('activeBundles')
            ->orWhereHas('activeSubCategories', function ($query) {
                $query->whereHas('activeBundles')->with('activeBundles');
            });
        })->with(['activeBundles', 'activeSubCategories'])
        ->get();

        $courses = Webinar::where('unattached', 1)->where('status', 'active')->get();
        $data = [
            'categories' => CategoryResource::collection($categories),
            'courses'  => CourseResource::collection($courses),
            'types' => ['programs', 'courses'],

        ];

        return apiResponse2(1, 'apply_page', "successfully retrive data needed to apply to diploma", $data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function checkout(Request $request, $carts = null)
    {
        app()->setLocale('ar');

        $user = auth("api")->user();
        $student = $user->student;

        $bundle = Bundle::where('id', $request->bundle_id)->first();
        $webinar = Webinar::where('id', $request->webinar_id)->first();
        $type = $request->input('type');

        // return [$type, $bundle, $webinar];
        try {
            if ($student) {
                $rules = [
                    'type' => 'required|in:courses,programs',

                    'bundle_id' => array_merge(
                        $type && $type == 'programs' ? ['required', 'exists:bundles,id'] : [''],
                        [function ($attribute, $value, $fail) use ($request, $student, $type) {

                            if ($student && $student->bundles()->where('bundles.id', $value)->exists()) {
                                $fail('User has already applied for this bundle.');
                            }
                        },
                    ]),

                    'webinar_id' => array_merge(
                        $type && $type == 'courses' ? ['required', 'exists:webinars,id'] : [],
                        [
                        function ($attribute, $value, $fail)  use ($request, $user, $type) {

                            $purchasedWebinarsIds = $user->getAllPurchasedWebinarsIds();

                            if ($user && in_array($value, $purchasedWebinarsIds)) {
                                $fail('User has already applied for this webinar.');
                            }
                        },
                    ]),
                    'certificate' => $bundle ? ($bundle->has_certificate ? 'required|boolean' : "") : '',
                    'direct_register' => $bundle ? "required|boolean"  : '',
                ];
            } else {
                $rules = [
                    'bundle_id' => array_merge(
                        $type && $type == 'programs' ? ['required', 'exists:bundles,id'] : [''],
                        [
                            function ($attribute, $value, $fail) use ($request, $student, $type) {

                                if ($student && $student->bundles()->where('bundles.id', $value)->exists()) {
                                    $fail('User has already applied for this bundle.');
                                }
                            },
                        ]
                    ),

                    'webinar_id' => array_merge(
                        $type && $type == 'courses' ? ['required', 'exists:webinars,id'] : [],
                        [
                            function ($attribute, $value, $fail)  use ($request, $user, $type) {

                                $purchasedWebinarsIds = $user->getAllPurchasedWebinarsIds();

                                if ($user && in_array($value, $purchasedWebinarsIds)) {
                                    $fail('User has already applied for this webinar.');
                                }
                            },
                        ]
                    ),
                    'certificate' => $bundle ? ($bundle->has_certificate ? 'required|boolean' : "") : '',
                    'direct_register' => $bundle ? "required|boolean"  : ''
                    ,
                    'ar_name' => 'required|string|regex:/^[\p{Arabic} ]+$/u|max:255|min:5',
                    'en_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255|min:5',
                    'identifier_num' => 'required|numeric|regex:/^\d{6,10}$/',
                    'country' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                    'area' => 'nullable|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                    'city' => 'nullable|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                    'town' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                    'birthdate' => 'required|date',
                    'gender' => 'required|in:male,female',
                    'about_us' => 'required|string|min:3|max:255'
                ];

                if (!empty($request->direct_register) and !empty($request->bundle_id)) {

                    $studentData = [
                        'ar_name' => $request->ar_name,
                        'en_name' => $request->en_name,
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'phone' => $user->mobile,
                        'mobile' => $user->mobile,
                        'gender' => $request->gender,
                        'birthdate' => $request->birthdate,
                        'identifier_num' => $request->identifier_num,
                        'country' => $request->country,
                        'area' => $request->area,
                        'city' => $request->city,
                        'town' => $request->town,
                        'about_us' => $request->about_us,

                    ];

                    $student = Student::create($studentData);
                    $code = generateStudentCode();
                    $user->update([
                        'user_code' => $code,
                        'access_content' => 1
                    ]);

                    // update code
                    Code::latest()->first()->update(['lst_sd_code' => $code]);
                }
            }

        } catch (\Exception $e) {
            return apiResponse2(0, '500', 'internal server error', null, $e->getMessage());
        }

        validateParam($request->all(), $rules);

        $data = [
            "type" => $type,
            "bundle" => $bundle->title ?? null,
            "course" => $webinar->title ?? null
        ];
        if($type=="programs"){
            $data["apply_type"] = $request->direct_register ? "direct_register" : "book_seat";

        }

        if (!empty($request->direct_register) and !empty($request->bundle_id)) {
            $student->bundles()->attach($request->bundle_id, [
                'certificate' => (!empty($request['certificate'])) ? $request['certificate'] : null,
                'created_at' => Date::now(),  // Set current timestamp for created_at
                'updated_at' => Date::now()
            ]);


            if (count($bundle->category->categoryRequirements) > 0) {
                $data["has_requirements"] = true;
                $data['redirect_route'] = "/panel/requirements";

            }
             else {
                $data["has_requirements"] = false;
                $data['redirect_route'] = "/panel/requirements/applied";
            }

            return apiResponse2('1', 'applied', "تم التسجيل بنجاح", $data);
        }

        // $paymentChannels = PaymentChannel::where('status', 'active')->get();
        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' =>  $request->type == 'programs' ? 59 : $webinar->price ?? 0,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' =>  $request->type == 'programs' ? 59 : $webinar->price ?? 0,
            'product_delivery_fee' => null,
            'created_at' => time(),
        ]);
        $orderItem = OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'webinar_id' => $request->webinar_id ?? null,
            'bundle_id' => $request->bundle_id ?? null,
            'certificate_template_id' => null,
            'certificate_bundle_id' => null,
            'form_fee' => $request->type == 'programs' ? 1 : null,
            'product_id' => null,
            'product_order_id' => null,
            'reserve_meeting_id' => null,
            'subscribe_id' => null,
            'promotion_id' => null,
            'gift_id' => null,
            'installment_payment_id' => null,
            'ticket_id' => null,
            'discount_id' => null,
            // 'amount' =>  230,
            // 'total_amount' => 230,
            'amount' => $request->type == 'programs' ? 59 : $webinar->price ?? 0,
            'total_amount' => $request->type == 'programs' ? 59 : $webinar->price ?? 0,
            'tax' => null,
            'tax_price' => 0,
            'commission' => 0,
            'commission_price' => 0,
            'product_delivery_fee' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        $paymentChannels = PaymentChannel::where('status', 'active')->get();

        if (!empty($order) and $order->total_amount > 0) {
            Cookie::queue('user_data', json_encode($request->all()));
             $data['redirect_route'] = "/payment/$order->id";
             $order->order_item = $order->orderItems()->with('bundle', 'webinar')->first();
            $data['order'] = $order;
            $data['paymentChannels'] = $paymentChannels;


            return apiResponse2(1, 'applied', "application for programs is successfully, pay to continue", $data);
            // return apiResponse2('1', 'stored', "تم التسجيل بنجاح", $data);
        }
        else{
            return $this->handlePaymentOrderWithZeroTotalAmount($order);
        }

        // return apiResponse2(1, 'applied', "application for diploma is successfully");

    }

    public function checkout2(Request $request, $carts = null)
    {
        // dd($request->all());
        $user = auth("api")->user();
        $student = $user->student;

        // $categoryTitle = $category->title;

        if ($student) {
            $rules = [
                'category_id' => 'required|exists:id,categories',
                'bundle_id' =>  [
                    'required',
                    function ($attribute, $value, $fail) {
                        $user = auth("api")->user();
                        $student = Student::where('user_id', $user->id)->first();

                        if ($student && $student->bundles()->where('bundles.id', $value)->exists()) {
                            $fail('User has already applied for this bundle.');
                        }
                    },
                ],
                'terms' => 'accepted'
            ];
        } else {

            $rules = [
                'user_id' => 'required',
                'category_id' => 'required|exists:categories,id',
                'bundle_id' =>  [
                    'required',
                    function ($attribute, $value, $fail) {
                        $user = auth("api")->user();
                        $student = Student::where('user_id', $user->id)->first();

                        if ($student && $student->bundles()->where('bundles.id', $value)->exists()) {
                            $fail('User has already applied for this bundle.');
                        }
                    },
                ],
                'ar_name' => 'required|string|regex:/^[\p{Arabic} ]+$/u|max:255|min:5',
                'en_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255|min:5',
                'identifier_num' => 'required|regex:/^[A-Za-z0-9]{6,10}$/',
                'country' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                'area' => 'nullable|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                'city' => 'nullable|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                'town' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                'email' => 'required|email|max:255|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
                'birthdate' => 'required|date',
                'phone' => 'required|min:5|max:20',
                'mobile' => 'required|min:5|max:20',
                'deaf' => 'required|in:0,1',
                'disabled_type' => $request->disabled == 1 ? 'required|string|max:255|min:3' : 'nullable',
                'gender' => 'required|in:male,female',
                'healthy_problem' => $request->healthy == 1 ? 'required|string|max:255|min:3' : 'nullable',
                'nationality' => 'required|string|min:3|max:25',
                'job' => 'nullable',
                'job_type' => 'nullable',
                'referral_person' => 'required|string|min:3|max:255',
                'relation' => 'required|string|min:3|max:255',
                'referral_email' => 'required|email|max:255|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
                'referral_phone' => 'required|min:3|max:20',
                'about_us' => 'required|string|min:3|max:255',
                'terms' => 'accepted',
            ];
        }

        validateParam($request->all(), $rules);

        $category = Category::where('id', $request->category_id)->first();
        $rules2 = [
            'educational_qualification_country' => $category->title != "دبلوم متوسط" ? 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u' : '',
            'secondary_school_gpa' => $category->title == "دبلوم متوسط" ? 'required|string|max:255|min:1' : '',
            'educational_area' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
            'secondary_graduation_year' => $category->title == "دبلوم متوسط" ? 'required|numeric|regex:/^\d{3,10}$/' : '',
            'school' => $category->title == "دبلوم متوسط" ? 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u' : '',
            'university' => $category->title != "دبلوم متوسط" ? 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u' : '',
            'faculty' => $category->title != "دبلوم متوسط" ? 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u' : '',
            'education_specialization' => $category->title != "دبلوم متوسط" ? 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u' : '',
            'graduation_year' => $category->title != "دبلوم متوسط" ? 'required|numeric|regex:/^\d{3,10}$/' : '',
            'gpa' => $category->title != "دبلوم متوسط" ? 'required|string|max:255|min:1' : '',
        ];
        validateParam($request->all(), $rules2);

        Cookie::queue('user_data', json_encode($request->all()));
        $user_data = $request->all();
        $user = auth("api")->user();

        $paymentChannels = PaymentChannel::where('status', 'active')->get();
        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => 59,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' => 59,
            'product_delivery_fee' => null,
            'created_at' => time(),
        ]);
        OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'webinar_id' => null,
            'bundle_id' => $request->bundle_id ?? null,
            'certificate_template_id' =>  null,
            'certificate_bundle_id' => null,
            'form_fee' => 1,
            'product_id' =>  null,
            'product_order_id' => null,
            'reserve_meeting_id' => null,
            'subscribe_id' => null,
            'promotion_id' => null,
            'gift_id' => null,
            'installment_payment_id' => null,
            'ticket_id' => null,
            'discount_id' => null,
            'amount' => 59,
            'total_amount' => 59,
            'tax' => null,
            'tax_price' => 0,
            'commission' => 0,
            'commission_price' => 0,
            'product_delivery_fee' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);


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
                'subTotal' => null,
                'totalDiscount' => null,
                'tax' => null,
                'taxPrice' => null,
                'total' => 59,
                'userGroup' => $user->userGroup ? $user->userGroup->group : null,
                "user_data" =>$user_data,
                'order' => $order,
                'type' => $order->orderItems[0]->form_fee,
                'count' => 0,
                'userCharge' => $user->getAccountCharge(),
                'razorpay' => $razorpay,
                'totalCashbackAmount' => null,
                'previousUrl' => url()->previous(),
            ];

            return apiResponse2(1, 'applied', "application for diploma is successfully, pay to continue", $data);
        } else {

            return $this->handlePaymentOrderWithZeroTotalAmount($order);
        }

        return apiResponse2(1, 'applied', "application for diploma is successfully");
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
        return apiResponse2(1, 'paid', "program is bought successfully");
        // return redirect('/payments/status?order_id=' . $order->id);
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
}
