<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\StudentRequirement;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotifications;
use App\Models\Notification;
use App\Exports\RequirementsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RequirementsController extends Controller
{
    public function index(Request $request)
    {
        $requirements = StudentRequirement::with([
            'bundleStudent.bundle',
            'bundleStudent.student'
        ])->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data' => $requirements
        ]);
    }

    public function approve(Request $request, $url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        try {
            $admin = auth()->user();
            $requirements = StudentRequirement::find($id);

            if ($requirements) {
                $requirements->status = StudentRequirement::approved;
                $requirements->approved_by = $admin->id;

                $data['user_id'] = $requirements->bundleStudent->student->registeredUser->id;
                $data['name'] = $requirements->bundleStudent->student->registeredUser->ar_name;
                $data['receiver'] = $requirements->bundleStudent->student->email;
                $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
                $data['fromName'] = env('MAIL_FROM_NAME');
                $data['subject'] = 'الرد علي متطلبات القبول المرسلة';
                $data['body'] = 'نود اعلامك علي انه تم الموافقة علي مرفقات متطلبات القبول التي قمت بارسالها، يرجي الذهاب للموقع الخاص بنا للمتابعه في باقي الخطوات';

                $this->sendNotification($data);
                // $this->sendEmail($data);

                $requirements->save();

                return response()->json([
                    'success' => true,
                    'message' => 'تم الموافقة علي طلب القبول وارسال ايميل للطالب بهذا'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الطلب لم يعد متوفرا'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ ما يرجي المحاولة مرة أخري',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string',
                'message' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $admin = auth()->user();
            $requirements = StudentRequirement::find($id);

            if ($requirements) {

                $requirements->status = StudentRequirement::rejected;
                $requirements->approved_by = $admin->id;

                $data['user_id'] = $requirements->bundleStudent->student->registeredUser->id;
                $data['name'] = $requirements->bundleStudent->student->ar_name;
                $data['receiver'] = $requirements->bundleStudent->student->email;
                $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
                $data['fromName'] = env('MAIL_FROM_NAME');
                $data['subject'] = 'الرد علي متطلبات القبول المرسلة';
                // $data['body'] = 'نود اعلامك علي انه يوجد مشكلة في الملفات التي قمت برفعها يرجي الذهاب للموقع لرفعها مرة اخري بشكل صحيح ';
                $data['body'] = "لقد تم رفض طلبك بسبب " . $request['reason'];
                $requirements->message =  $request['reason'] . "<br>";
                if (isset($request['message'])) {
                    $data['body'] =  $data['body'] . "\n" . $request['message'];
                    $requirements->message .= $request['message'];
                }

                $this->sendNotification($data);
                // $this->sendEmail($data);
                $requirements->save();

                return response()->json([
                    'success' => true,
                    'message' => 'تم رفض الطلب وارسال ايميل للطالب بهذا'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الطلب لم يعد متوفرا'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ ما يرجي المحاولة مرة أخري',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function sendNotification($data)
    {
        // $this->authorize('admin_notifications_send');
        Notification::create([
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            'sender_id' => auth()->id(),
            'title' => "متطلبات القبول",
            'message' => $data['body'],
            'sender' => Notification::$AdminSender,
            'type' => "single",
            'created_at' => time()
        ]);

        if (!empty($data['user_id']) and env('APP_ENV') == 'production') {
            $user = User::where('id', $data['user_id']);
            if (!empty($user) and !empty($user->email)) {
                Mail::to($user->email)->send(new SendNotifications([
                    'title' => $data['subject'],
                    'message' => $data['body'],
                    'name' => $data['name']
                ]));
            }
        }
        return true;
    }

    public function exportExcelRequirements(Request $request)
    {
        $this->authorize('admin_requirements_export_excel');

        $requirements = StudentRequirement::orderByDesc('created_at')->get();

        $requirementsExport = new RequirementsExport($requirements);

        return Excel::download($requirementsExport, 'نموذج المتطلبات.xlsx');
    }
}
