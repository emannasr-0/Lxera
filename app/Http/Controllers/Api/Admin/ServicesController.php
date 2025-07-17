<?php

namespace App\Http\Controllers\Api\Admin;

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
use App\Models\CertificateService;
use App\Models\Notification;
use App\Models\ServiceItem;
use App\Models\Role;
use App\Models\StudyClass;
use App\User;
use App\Exports\RequestsExport;
use App\Http\Controllers\Admin\BundleDelayController;
use App\Http\Controllers\Admin\BundleTransformController;
use App\Models\Api\Organization;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $services = Service::paginate(10);
        return response()->json([
            'success' => true,
            'data' => $services
        ], 200);
    }

    public function requests($url_name, Service $service)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $serviceUsers = ServiceUser::where('service_id', $service->id)
            ->with('user')
            ->get();

        return response()->json([
            'service_users' => $serviceUsers
        ]);
    }


    public function exportRequests($url_name, Service $service)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $users = $service->users()->get();
        $export = new RequestsExport($users);

        return Excel::download($export, "service_requests_{$service->id}.xlsx");
    }
    public function approveRequest(Request $request, ServiceUser $serviceUser)
    {
        try {
            $admin = auth()->user();

            $serviceUser->status = 'approved';
            $serviceUser->approved_by = $admin->id;

            $certificateService = CertificateService::where('service_request_id', $serviceUser->id)->first();
            if ($certificateService) {
                $certificateService->status = 'approved';
                $certificateService->approved_by = $admin->id;
                $certificateService->save();
            }

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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($url_name, Request $request)
    {
                $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }
        $authUser = Auth::user();

        $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'nullable|string|min:10',
            'price' => 'required|regex:/^\d{1,4}(\.\d{1,6})?$/',
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

        $lastId = Service::max('id') + 1;
        $data['apply_link'] = env('APP_URL') . "panel/services/{$lastId}/apply";
        $data['review_link'] = env('APP_URL') . "panel/services/{$lastId}/review";

        $service = Service::create($data);

        foreach ($request->bundles ?? [] as $bundleId) {
            ServiceItem::create([
                'bundle_id' => $bundleId,
                'service_id' => $service->id,
            ]);
        }

        foreach ($request->courses ?? [] as $webinarId) {
            ServiceItem::create([
                'webinar_id' => $webinarId,
                'service_id' => $service->id,
            ]);
        }

        return response()->json([
            'message' => 'Service created successfully.',
            'service' => $service
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($url_name, Service $service)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }
        // $service->load(['bundles', 'webinars']);

        return response()->json([
            'service' => $service,
            // 'courses' => Webinar::all(),
            // 'bundles' => Bundle::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update($url_name, Request $request, Service $service)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }
        $authUser = Auth::user();

        $request->validate([
            'title' => 'sometimes|string|min:3',
            'description' => 'nullable|string|min:10',
            'price' => 'sometimes|regex:/^\d{1,4}(\.\d{1,6})?$/',
            'apply_link' => 'sometimes|url',
            'review_link' => 'sometimes|url',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'status' => ['sometimes', Rule::in(['pending', 'active', 'inactive'])],
            'target' => 'sometimes|string|in:all,bundles,specific_bundles,webinars,specific_webinars',
            'bundles' => 'nullable|array|required_if:target,specific_bundles',
            'courses' => 'nullable|array|required_if:target,specific_webinars',
        ]);

        $request['created_by'] = $authUser->id;
        $service->update($request->all());

        // Sync relationships
        $service->bundles()->sync($request->input('bundles', []));
        $service->webinars()->sync($request->input('courses', []));

        return response()->json([
            'message' => 'Service updated successfully.',
            'service' => $service
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($url_name, Service $service)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully.'
        ]);
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
