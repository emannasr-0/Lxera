<?php

namespace App\Http\Controllers\Web;

use App\BundleStudent;
use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Webinar;
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
use App\Http\Controllers\Web\PaymentController;
use App\Models\Accounting;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\Models\Code;
use App\Models\StudyClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;

class ApplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
    public function index(Request $request, Bundle $bundle)
    {

        // return view("web.default.pages.registration_close");
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();
        // $categories = Category::whereNull('parent_id')->whereHas('bundles')->get();

        // $categories = Category::whereNull('parent_id')->where('status', 'active')
        //     ->where(function ($query) {
        //         $query->whereHas('activeBundles')
        //             ->orWhereHas('activeWebinars')
        //             ->orWhereHas('activeSubCategories', function ($query) {
        //                 $query->whereHas('activeBundles')
        //                     ->orWhereHas('activeWebinars');
        //             });
        //     })->select(['id', 'slug'])
        //     ->with([
        //         'activeBundles',
        //         'activeWebinars',
        //         'activeSubCategories' => function ($query) {
        //             $query->whereHas('activeBundles')
        //                 ->orWhereHas('activeWebinars')
        //                 ->with([
        //                     'activeBundles',
        //                     'activeWebinars',
        //                 ]);
        //         },
        //     ])
        //     ->get();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('activeBundles')
                    ->orWhereHas('activeWebinars')
                    ->orWhereHas('activeSubCategories', function ($query) {
                        $query->whereHas('activeBundles')
                            ->orWhereHas('activeWebinars');
                    });
            })
            ->select(['id']) // Select id and slug for categories
            ->with([
                'activeBundles' => function ($query) {
                    $query->with('additionBundles', function ($query) {
                        $query->where('status', 'active');
                    });
                },
                'activeWebinars',
                'activeSubCategories' => function ($query) {
                    $query->whereHas('activeBundles')
                        ->orWhereHas('activeWebinars')

                        ->with([
                            'activeBundles' => function ($query) {
                                $query->with('additionBundles', function ($query) {
                                    $query->where('status', 'active');
                                });
                            },
                            'activeWebinars',
                        ]);
                },
            ])
            ->get();

        if (count($categories) == 0) {
            return view("web.default.pages.registration_close");
        }
        // dd(['categories' => $categories]);

        // $courses = Webinar::where('unattached', 1)->where('status', 'active')->get();
        $data = [
            // 'courses' => $courses,
            'categories' => $categories,
            'bundle' => $bundle,
            'user' => $user,
            'student' => $student,
        ];

        $webinarId = $request->webinar_id;
        $bundleId = $request->bundle_id;
        $addition_bundle_id = $request->addition_bundle_id;

        if (empty($bundleId) and $user->application_type == 'programs') {
            $bundleId = $user->program_id;
        }

        // if(empty($addition_bundle_id) and $user->application_type == 'addition_programs'){
        //     $addition_bundle_id = $user->program_id;
        // }

        if (empty($webinarId) and $user->application_type == 'courses') {
            $webinarId = $user->program_id;
        }

        if ($webinarId) {
            $webinar = Webinar::where(['id' => $webinarId, 'status' => 'active', 'unattached' => 1])->first();
            // $mainCategory = null;
            if (!empty($webinar)) {
                $mainCategory = $webinar->category;
                if (!empty($mainCategory->parent_id)) {
                    $subCategory = $mainCategory;
                    $mainCategory = $subCategory->category;
                    $data['sub_category_id'] = $subCategory->id;
                }

                $data['webinar_id'] = $webinar->id;
                $data['main_category_id'] = $mainCategory->id;
            }
        }

        if ($bundleId) {
            $bundle = Bundle::where(['id' => $bundleId, 'status' => 'active'])->first();

            if (!empty($bundle)) {
                $mainCategory = $bundle->category;
                if (!empty($mainCategory->parent_id)) {
                    $subCategory = $mainCategory;
                    $mainCategory = $subCategory->category;
                    $data['sub_category_id'] = $subCategory->id;
                }

                $data['bundle_id'] = $bundle->id;
                $data['addition_bundle_id'] = $addition_bundle_id;
                $data['want_addition'] = !empty($addition_bundle_id) ? '1' : '0';
                $data['main_category_id'] = $mainCategory->id;
            }
        }

        return view(getTemplate() . '.pages.application_form', $data);
    }

    public function showProfessionalWebinar()
    {
        $user = auth()->user();

        $student = $user->student;
        // Get the IDs of the bundles associated with the student
        $userBundleIds = $user->purchasedBundles()->pluck('bundle_id')->toArray();
        //  dd ($userBundleIds);
        $bundles = Bundle::whereIn('id', $userBundleIds)
            ->with('bundleProfessionalWebinars') // Eager load the webinars
            ->get();
        $professionalWebinars = collect();

        foreach ($bundles as $bundle) {
            $webinars = $bundle->bundleProfessionalWebinars;  // Access webinars directly
            $professionalWebinars = $professionalWebinars->merge($webinars);
        }

        //   $professionalWebinars = $professionalWebinars->unique('id');

        return view(getTemplate() . '.panel.newEnrollment.professionalWebinar',  [
            'professionalWebinars' => $professionalWebinars,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function newEnrollment()
    {
        // return view("web.default.pages.registration_close");
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('activeBundles')
                    ->orWhereHas('activeWebinars')
                    ->orWhereHas('activeSubCategories', function ($query) {
                        $query->whereHas('activeBundles')
                            ->orWhereHas('activeWebinars');
                    });
            })
            ->select(['id']) // Select id and slug for categories
            ->with([
                'activeBundles' => function ($query) {
                    $query->with('additionBundles', function ($query) {
                        $query->where('status', 'active');
                    });
                },
                'activeWebinars',
                'activeSubCategories' => function ($query) {
                    $query->whereHas('activeBundles')
                        ->orWhereHas('activeWebinars')

                        ->with([
                            'activeBundles' => function ($query) {
                                $query->with('additionBundles', function ($query) {
                                    $query->where('status', 'active');
                                });
                            },
                            'activeWebinars',
                        ]);
                },
            ])
            ->get();

        // dd($categories);
        $courses = Webinar::where('unattached', 1)->where('status', 'active')->get();

        $data = [
            'courses' => $courses,
            'categories' => $categories,
            'user' => $user,
            'student' => $student,
        ];
        if (count($categories) == 0) {
            return view("web.default.pages.registration_close");
        }
        return view(getTemplate() . '.panel.newEnrollment.index', $data);
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

        $bundle = Bundle::find($request->bundle_id);
        $webinar = Webinar::find($request->webinar_id);
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        // dd($student->id,$bundle->id);



        try {

            $rules = [
                'user_id' => 'required',
                'main_category_id' => 'exists:categories,id',
                'sub_category_id' => [
                    'nullable',
                    'exists:categories,id',
                ],
                'bundle_id' => [
                    'nullable',
                    'exists:bundles,id',
                ],
                'addition_bundle_id' => [
                    'nullable',
                    'exists:bundles,id',
                ],
                'webinar_id' => [
                    'nullable',
                    'exists:webinars,id',
                ],
                'certificate' => $bundle ? ($bundle->has_certificate ? 'required|boolean' : "") : '',
                //'requirement_endorsement' => $bundle ? 'accepted'  : ''

            ];
            if (!$student) {
                $rules = [
                    ...$rules,
                    // 'ar_name' => 'required|string|regex:/^[\p{Arabic} ]+$/u|max:255|min:5',
                    // 'en_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255|min:5',
                    // 'nationality' => 'required|string|min:3|max:25',
                    // 'country' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                    // 'area' => 'nullable|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                    // 'city' => 'nullable|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                    // 'town' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
                    // 'birthdate' => 'required|date',
                    // 'gender' => 'required|in:male,female',
                    'about_us' => 'required|string|min:3|max:255',
                    'terms' => 'accepted',
                ];
            }

            if ($request->about_us == 'academy_family') {
                $rules['friend_code'] = 'required|string|max:255';
                $rules['friend_email'] = 'required|email|max:255';
            }

            $validator = Validator::make($request->all(), $rules);

            // Add custom validation logic after the standard rules
            $validator->after(function ($validator) use ($request) {
                // dd($request->all());
                $mainCategory = Category::find($request->main_category_id);
                $subCategory = Category::find($request->sub_category_id);

                // Check if subcategory is required
                if ($mainCategory && $mainCategory->activeSubCategories()->exists() && !$request->sub_category_id) {
                    $validator->errors()->add('sub_category_id', 'حقل التخصص مطلوب');
                }

                // Check if bundle is required
                $hasBundles =
                    ($mainCategory && $mainCategory->activeBundles()->exists()) ||
                    ($subCategory && $subCategory->activeBundles()->exists());

                if ($hasBundles && !$request->bundle_id) {
                    $validator->errors()->add('bundle_id', 'حقل البرنامج الدراسي مطلوب');
                }

                // Check if user is already registered in the bundle
                $user = auth()->user();
                $student = Student::where('user_id', $user->id)->first();

                // if ($student && $request->bundle_id && ($request->want_addition != 1) && $student->bundles()->where('bundles.id', $request->bundle_id)->exists()) {
                //     $validator->errors()->add('bundle_id', 'انت مسجل بالفعل في هذا البرنامج');
                // }


                // Check if webinar is required
                $hasWebinars =
                    ($mainCategory && $mainCategory->activeWebinars()->exists()) ||
                    ($subCategory && $subCategory->activeWebinars()->exists());

                if (!$hasBundles && $hasWebinars && !$request->webinar_id) {
                    $validator->errors()->add('webinar_id', 'حقل الدورة مطلوب');
                }

                $user = auth()->user();
                $student = Student::where('user_id', $user->id)->first();

                $user = auth()->user();
                $purchasedWebinarsIds = $user->getAllPurchasedWebinarsIds();

                if ($user && in_array($request->webinar_id, $purchasedWebinarsIds)) {
                    $validator->errors()->add('webinar_id', 'انت مسجل بالفعل في هذة الدورة');
                }

                // Check if addition bundle is required
                $mainBundle = Bundle::find($request->bundle_id);
                $hasAdditionBundles = ($mainBundle && $mainBundle->additionBundles()->exists());

                if ($hasAdditionBundles && ($request->want_addition == 1) && !$request->addition_bundle_id) {
                    $validator->errors()->add('addition_bundle_id', 'حقل التخصص المزدوج مطلوب');
                }

                // if (
                //     $student && $request->addition_bundle_id
                //     && $request->want_addition == 1 &&
                //     $student->bundles()->where('bundles.id', $request->addition_bundle_id)->exists()
                // ) {
                //     $validator->errors()->add('addition_bundle_id', 'انت مسجل بالفعل في هذا البرنامج');
                // }

                $bundle = Bundle::find($request->bundle_id);
                $student = Student::where('user_id', $user->id)->first();
                $check_registered_bundle = BundleStudent::where('student_id', $student->id)
                    ->where('bundle_id', $bundle->id);

                if ($check_registered_bundle->count() > 0) {
                    $check_registered_bundle = 1;
                } else {
                    $check_registered_bundle = 0;
                }

                    if ($check_registered_bundle) {
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

            if (empty($user->student) && $request->direct_register) {
                $studentData = [
                    //'ar_name' => $request->ar_name??$user->en_name,
                    'en_name' => $request->en_name ?? $user->en_name,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone' => $user->mobile,
                    'mobile' => $user->mobile,
                    // 'gender' => $request->gender,
                    // 'birthdate' => $request->birthdate,
                    // 'nationality' => $request->nationality,
                    // 'country' => $request->country,
                    // 'area' => $request->area,
                    // 'city' => $request->city,
                    // 'town' => $request->town,
                    'about_us' => $request->about_us,
                    'friend_code' => $request->friend_code ?? null,  // Store friend_code
                    'friend_email' => $request->friend_email ?? null,

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
        } catch (\Exception $e) {
            // dd($e);
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        if (empty($bundle) and empty($webinar)) {
            $toastData = [
                'title' => 'تقديم طلب',
                'msg' => 'يرجى اختيار برنامج صحيح',
                'status' => 'error'
            ];
            return redirect()->back()
                ->withErrors([
                    'webinar_id' => 'يرجى اختيار دورة صحيحة',
                    'bundle_id' => 'يرجى اختيار برنامج صحيح'
                ])
                ->withInput()->with(['toast' => $toastData]);
        }

        $bundleId = $request->bundle_id;
        if ($request->addition_bundle_id && $request->want_addition == 1) {
            $bundleId = $request->addition_bundle_id;
            $bundle = Bundle::find($bundleId);
        }

        if ($request->direct_register) {

            $class =  StudyClass::get()->last();
            if (!$class) {
                $class = StudyClass::create(['title' => "الدفعة الأولي"]);
            }

            $student->bundles()->attach($bundleId, [
                'certificate' => (!empty($request['certificate'])) ? $request['certificate'] : null,
                'created_at' => Date::now(),  // Set current timestamp for created_at
                'updated_at' => Date::now()
            ]);

            if (count($bundle->category->categoryRequirements) > 0) {
                return redirect("/panel/requirements");
            } else {
                return redirect("/panel/requirements/applied");
            }
        }
        $keysToExclude = [
            'main_category_id',
            'sub_category_id',
            'bundle_id',
            'webinar_id',
            'type',
            'terms',
            'certificate',
            'timezone',
            'password',
            'password_confirmation',
            'email_confirmation',
            //'requirement_endorsement'
        ];
        $studentData = collect($validatedData)->except($keysToExclude)->toArray();
        Cookie::queue('user_data', json_encode($studentData));


        // $paymentChannels = PaymentChannel::where('status', 'active')->get();
        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' =>  !empty($bundleId) ? 59 : $webinar->price ?? 0,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' =>  !empty($bundleId) ? 59 : $webinar->price ?? 0,
            'product_delivery_fee' => null,
            'device_browser_info' => $this->deviceBrowserInfo,

            'created_at' => time(),
        ]);

        OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'webinar_id' => $request->webinar_id ?? null,
            'bundle_id' => $bundleId ?? null,
            'certificate_template_id' => null,
            'certificate_bundle_id' => null,
            'form_fee' => !empty($bundleId) ? 1 : null,
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
            'amount' => !empty($bundleId) ? 230 : $webinar->price ?? 0,
            'total_amount' => !empty($bundleId) ? 230 : $webinar->price ?? 0,
            'tax' => null,
            'tax_price' => 0,
            'commission' => 0,
            'commission_price' => 0,
            'product_delivery_fee' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);


        if (!empty($order) and $order->total_amount > 0) {

            return redirect('/payment/' . $order->id);
        } else {
            return $this->handlePaymentOrderWithZeroTotalAmount($request, $order);
        }


        return redirect('/panel');
    }


    function bookSeat(Request $request, Bundle $bundle)
    {
        $user = auth()->user();
        $student = $user->student;
        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' =>  59,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' => 59,
            'product_delivery_fee' => null,
            'device_browser_info' => $this->deviceBrowserInfo,
            'created_at' => time(),
        ]);
        OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'webinar_id' => null,
            'bundle_id' => $bundle->id,
            'certificate_template_id' => null,
            'certificate_bundle_id' => null,
            'form_fee' => 1,
            'product_id' => null,
            'product_order_id' => null,
            'reserve_meeting_id' => null,
            'subscribe_id' => null,
            'promotion_id' => null,
            'gift_id' => null,
            'installment_payment_id' => null,
            'ticket_id' => null,
            'discount_id' => null,
            'amount' =>  59,
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

            return redirect('/payment/' . $order->id);
        }

        return back();
    }


    // function newApplyToBundle(Request $request, Bundle $bundle)
    // {
    //     $user = auth()->user();
    //     $student = $user->student;
    //     $order = Order::create([
    //         'user_id' => $user->id,
    //         'status' => Order::$pending,
    //         'amount' =>  230,
    //         'tax' => 0,
    //         'total_discount' => 0,
    //         'total_amount' => 230,
    //         'product_delivery_fee' => null,
    //         'created_at' => time(),
    //     ]);
    //     OrderItem::create([
    //         'user_id' => $user->id,
    //         'order_id' => $order->id,
    //         'webinar_id' => null,
    //         'bundle_id' => $bundle->id,
    //         'certificate_template_id' => null,
    //         'certificate_bundle_id' => null,
    //         'form_fee' => 1,
    //         'product_id' => null,
    //         'product_order_id' => null,
    //         'reserve_meeting_id' => null,
    //         'subscribe_id' => null,
    //         'promotion_id' => null,
    //         'gift_id' => null,
    //         'installment_payment_id' => null,
    //         'ticket_id' => null,
    //         'discount_id' => null,
    //         'amount' =>  230,
    //         'total_amount' => 230,
    //         'tax' => null,
    //         'tax_price' => 0,
    //         'commission' => 0,
    //         'commission_price' => 0,
    //         'product_delivery_fee' => 0,
    //         'discount' => 0,
    //         'created_at' => time(),
    //     ]);


    //     if (!empty($order) and $order->total_amount > 0) {

    //         return redirect('/payment/' . $order->id);
    //     }

    //     return back();
    // }

    function newApplyToWebinar(Request $request, Webinar $webinar)
    {

        if (!Auth::check()) {
            return redirect('/login?type=courses&webinar_id=' . $webinar->id);
        }
        $user = auth()->user();

        $purchasedWebinarsIds = $user->getAllPurchasedWebinarsIds();

        if ($user && in_array($webinar->id, $purchasedWebinarsIds)) {
            return redirect('/panel')->with('toast', [
                'status' => 'success',
                'title' => 'تسجيل في دورة',
                'msg' => 'انت مسجل بالفعل في هذه الدورة',
            ]);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' =>  $webinar->price ?? 0,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' => $webinar->price ?? 0,
            'product_delivery_fee' => null,
            'device_browser_info' => $this->deviceBrowserInfo,
            'created_at' => time(),
        ]);

        OrderItem::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'webinar_id' =>  $webinar->id,
            'bundle_id' => null,
            'certificate_template_id' => null,
            'certificate_bundle_id' => null,
            'product_id' => null,
            'product_order_id' => null,
            'reserve_meeting_id' => null,
            'subscribe_id' => null,
            'promotion_id' => null,
            'gift_id' => null,
            'installment_payment_id' => null,
            'ticket_id' => null,
            'discount_id' => null,
            'amount' =>  $webinar->price ?? 0,
            'total_amount' => $webinar->price ?? 0,
            'tax' => null,
            'tax_price' => 0,
            'commission' => 0,
            'commission_price' => 0,
            'product_delivery_fee' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        $data = [
            'user_id' => $user->id,
            'ar_name' => $user->full_name,
            'en_name' => $user?->en_name,
            'email' => $user->email,
            'phone' => $user->mobile,
            'mobile' => $user->mobile,
        ];

        Cookie::queue('user_data', json_encode($data));
        if (!empty($order) and $order->total_amount > 0) {
            // dd(78);
            return redirect("/panel/requirements/applied");
            return redirect('/payment/' . $order->id);
        } else {
            return $this->handlePaymentOrderWithZeroTotalAmount($request, $order);
        }
    }
    public function handlePaymentOrderWithZeroTotalAmount(Request $request, $order)
    {
        $order->update([
            'payment_method' => Order::$paymentChannel
        ]);

        $paymentController = new PaymentController();

        $paymentController->setPaymentAccounting($order);

        $order->update([
            'status' => Order::$paid
        ]);

        session()->put('payment.order_id', $order->id);
        // return (new PaymentController())->payStatus($request, $order->id);
        return redirect('/payments/status/' . $order->id);
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
