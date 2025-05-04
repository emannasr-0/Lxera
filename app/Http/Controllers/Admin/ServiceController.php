<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Service;
use App\Models\ServiceUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotifications;
use App\Models\Api\Bundle;
use App\Models\Api\Webinar;
use App\Models\Category;
use App\Models\Notification;
use App\Models\ServiceItem;
use App\Models\Role;
use App\Models\StudyClass;
use App\User;
use Illuminate\Support\Facades\Validator;

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

        $services = Service::paginate(10);

        return view('admin.services.index', compact('services'));
    }

    public function requests(Service $service)
    {
        //

        // $services = Service::paginate(1);
        $lastBatch = StudyClass::latest()->first();
        $categories = Category::whereNull('parent_id')
            ->whereHas('bundles', function ($query) use ($lastBatch) {
                $query->where('batch_id', $lastBatch->id);
            })
            ->orWhereHas('subCategories', function ($query) use ($lastBatch) {
                $query->whereHas('bundles', function ($query) use ($lastBatch) {
                    $query->where('batch_id', $lastBatch->id);
                });
            })
            ->with(
                [
                    'bundles' => function ($query) use ($lastBatch) {
                        $query->where('batch_id', $lastBatch->id);
                    },

                    'subCategories' => function ($query) use ($lastBatch) {
                        $query->whereHas('bundles', function ($query) use ($lastBatch) {
                            $query->where('batch_id', $lastBatch->id);
                        })->with([
                            'bundles' => function ($query) use ($lastBatch) {
                                $query->where('batch_id', $lastBatch->id);
                            },
                        ]);
                    },
                ]
            )->get();

        return view('admin.services.requests', compact('service', 'categories'));
    }
    public function approveRequest(Request $request, ServiceUser $serviceUser)
    {
        try {
            $admin = auth()->user();

            $serviceUser->status = 'approved';
            $serviceUser->approved_by = $admin->id;

            if ($serviceUser->bundleTransform) {
                if ($serviceUser->bundleTransform->amount == 0) {
                    $response =  (new BundleTransformController())->finishTransform($request, $serviceUser->bundleTransform);
                    $serviceUser->save();

                    return $response;
                }

                $financialUsers = User::where(['status' => 'active'])
                    ->whereIn('role_id', Role::$financialRoles)->get();
                foreach ($financialUsers as $financialUser) {
                    $data['user_id'] = $financialUser->id;
                    $data['name'] = $financialUser->full_name;
                    $data['receiver'] = $financialUser->email;
                    $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
                    $data['fromName'] = env('MAIL_FROM_NAME');
                    $data['subject'] = ' طلب خدمة ' . $serviceUser->bundleTransform->serviceRequest->title;
                    $data['body'] = 'نود اعلامك علي انه تم الموافقة علي طلب التحويل من برنامج  ' . $serviceUser->bundleTransform->fromBundle->title .  ' إلي برنامج ' . $serviceUser->bundleTransform->toBundle->title . " المقدم من الطالب " . $serviceUser->bundleTransform->user->full_name . " من قبل "  . auth()->user()->full_name . "  من ادارة الأدميشن ومنتظر منكم إتمام التحويل ";
                    $this->sendNotification($data);
                }

                $serviceUser->bundleTransform->update(['approved_by' => auth()->id()]);
                $serviceUser->save();
                return back()->with('success', 'تم الموافقة علي طلب الخدمة وارسال الطلب لإدارة المالبة');
            }

            if ($serviceUser->bundleDelay) {
                (new BundleDelayController)->approve($request, $serviceUser->bundleDelay);
            }

            $data['user_id'] = $serviceUser->user_id;
            $data['name'] = $serviceUser->user->full_name;
            $data['receiver'] = $serviceUser->user->email;
            $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
            $data['fromName'] = env('MAIL_FROM_NAME');
            $data['subject'] = 'الرد علي طلب خدمة ' . $serviceUser->service->title;
            $data['body'] = 'نود اعلامك علي انه تم الموافقة علي طلبك لخدمة ' . $serviceUser->service->title . ' التي قمت بارساله ';
            $this->sendNotification($data);
            $toastData = [
                'title' => 'قبول خدمة',
                'msg' => 'تم الموافقة علي طلب الخدمة وارسال ايميل للطالب بهذا',
                'status' => 'success'
            ];
            $serviceUser->save();
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => 'قبول خدمة',
                'msg' => 'حدث خطأ ما في ارسال ايميل للطالب',
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }
    public function rejectRequest(Request $request, ServiceUser $serviceUser)
    {
        try {
            $validator = Validator::make($request->all(), [
                // 'reason' => 'required',
                'message' => 'required|string|min:2'
            ]);

            if ($validator->fails()) {
                return back()->with('error', implode(', ', $validator->errors()->all()));
            }
            $admin = auth()->user();

            $serviceUser->status = 'rejected';
            $serviceUser->approved_by = $admin->id;

            $data['user_id'] = $serviceUser->user_id;
            $data['name'] = $serviceUser->user->full_name;
            $data['receiver'] = $serviceUser->user->email;
            $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
            $data['fromName'] = env('MAIL_FROM_NAME');
            $data['subject'] = 'الرد علي طلب خدمة ' . $serviceUser->service->title;

            $data['body'] = "لقد تم رفض طلبك لخدمة " . $serviceUser->service->title . " بسبب " . $request['message'];
            $serviceUser->message =  $request['message'] . "<br>";
            $this->sendNotification($data);
            $serviceUser->save();
            if ($serviceUser->bundleTransform) {
                $serviceUser->bundleTransform->update(['status' => 'rejected', 'approved_by' => auth()->id()]);

                if (in_array(auth()->user()->role_id, Role::$financialRoles)) {
                    $financialUsers = User::where(['status' => 'active'])
                        ->whereIn('role_id', Role::$financialRoles)->get();
                    foreach ($financialUsers as $financialUser) {
                        $data['user_id'] = $financialUser->id;
                        $data['name'] = $financialUser->full_name;
                        $data['receiver'] = $financialUser->email;
                        $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
                        $data['fromName'] = env('MAIL_FROM_NAME');
                        $data['subject'] = '  طلب خدمة ' . $serviceUser->service->title;
                        $data['body'] = 'نود اعلامك علي انه تم رفض التحويل من برنامج  ' .
                            $serviceUser->bundleTransform->fromBundle->title .  ' إلي برنامج ' .
                            $serviceUser->bundleTransform->toBundle->title . " المقدم من الطالب " .
                            $serviceUser->bundleTransform->user->full_name . " من قبل "  . auth()->user()->full_name;
                        $this->sendNotification($data);
                    }
                }
            }

            return back()->with('success', 'تم رفض طلب الخدمة وارسال ايميل للطالب بهذا');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return back()->with('error', 'حدث خطأ ما يرجي المحاولة مرة أخري');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

        $courses = Webinar::get();
        $bundles = Bundle::get();
        $data = [

            'bundles' => $bundles,
            'courses' => $courses,
        ];

        return view('admin.services.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $authUser = Auth::user();

        $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'nullable|string|min:10',
            'price' => 'required|regex:/^\d{1,4}(\.\d{1,6})?$/',
            // 'apply_link' => 'required|url',
            // 'review_link' => 'required|url',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
            'target' => 'required|string|in:all,bundles,specific_bundles,webinars,specific_webinars',
            'bundles' => 'nullable|array|required_if:target,specific_bundles',
            'courses' => 'nullable|array|required_if:target,specific_webinars',
        ]);

        $request['created_by'] = $authUser->id;
        $data = $request->all();
        $data['status'] = 'inactive';
        $data['target'] = $request->target;
        $lastService = (Service::get()->last()?Service::get()->last()->id:0) + 1;
        $data['apply_link'] = env('APP_URL') . 'panel/services/' . $lastService . '/apply';
        $data['review_link'] = env('APP_URL') . 'panel/services/' . $lastService . '/review';
        $service = Service::create($data);


        if ($request->has('bundles')) {
            foreach ($request->bundles as $bundleId) {
                ServiceItem::create([
                    'bundle_id' => $bundleId,
                    'service_id' => $service->id,
                ]);
            }
        }

        if ($request->has('courses')) {
            foreach ($request->courses as $webinarId) {
                ServiceItem::create([
                    'webinar_id' => $webinarId,
                    'service_id' => $service->id,
                ]);
            }
        }
        return redirect("/admin/services/$service->id/edit")->with('success', 'تم إنشاء الخدمة بنجاح');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Service $service)
    {
        //

        $courses = Webinar::get();
        $bundles = Bundle::get();

        return view('admin.services.show',  compact('service', 'courses', 'bundles'));
    }

    /**
     * Show the form for editing the specified resource.
     *

     * @return \Illuminate\Http\Response
     */
    public function edit(Service $service)
    {
        //
        $courses = Webinar::get();
        $bundles = Bundle::get();


        return view('admin.services.create', compact('service', 'courses', 'bundles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service)
    {
        //
        $authUser = Auth::user();
        $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'nullable|string|min:10',
            'price' => 'required|regex:/^\d{1,4}(\.\d{1,6})?$/',
            'apply_link' => 'required|url',
            'review_link' => 'required|url',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
            'target' => 'required|string|in:all,bundles,specific_bundles,webinars,specific_webinars',
            'bundles' => 'nullable|array|required_if:target,specific_bundles',
            'courses' => 'nullable|array|required_if:target,specific_webinars',

        ]);

        //dd($request);

        $request['created_by'] = $authUser->id;

        $data = $request->all();
        $service->update($data);

        $service->bundles()->sync($request->input('bundles', []));
        $service->webinars()->sync($request->input('courses', []));

        return back()->with('success', 'تم تعديل الخدمة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service)
    {
        //
        $service->delete();
        return redirect('admin/services')->with('success', 'تم حذف الخدمة بنجاح');
    }


    protected function sendNotification($data)
    {
        // $this->authorize('admin_notifications_send');

        Notification::create([
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            'sender_id' => auth()->id(),
            'title' => $data['subject'],
            'message' => $data['body'],
            'sender' => Notification::$AdminSender,
            'type' => "single",
            'created_at' => time()
        ]);

        if (!empty($data['user_id']) and env('APP_ENV') == 'production') {
            $user = User::where('id', $data['user_id'])->first();
            if (!empty($user) and !empty($user->email)) {
                Mail::to($user->email)->send(new SendNotifications(['title' => $data['subject'], 'message' => $data['body'], 'name' => $data['name']]));
            }
        }

        return true;
    }
}
