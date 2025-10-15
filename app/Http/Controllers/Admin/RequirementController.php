<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RequirementsExport;
use App\Http\Controllers\Controller;
use App\Student;
use App\User;
use App\StudentRequirement;
use App\BundleStudent;
use Illuminate\Http\Request;

use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotifications;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
class RequirementController extends Controller
{
    public function index(Request $request)
    {
        $query=StudentRequirement::orderByDesc('created_at');
        $query = $this->filters($query, $request);
        $requirements = $query->paginate(20);
        // $requirements = StudentRequirement::orderByDesc('created_at')->paginate(20);

        return view('admin.requirements.index', ['requirements' => $requirements]);
    }
    public function filters($query, $request)
    {

        $user_code = $request->get('user_code');
        $ar_name = $request->get('ar_name');
        $email = $request->get('email');
        $mobile = $request->get('mobile');

        if (!empty($ar_name)) {
            $query->whereHas('bundleStudent.student', function ($q) use ($ar_name) {
                $q->where('ar_name', 'like', "%$ar_name%");
                $q->orWhere('en_name', 'like', "%$ar_name%");
            });
        }
        if (!empty($user_code)) {
            $query->whereHas('bundleStudent.student.registeredUser', function ($q) use ($user_code) {
                $q->where('user_code', 'like', "%$user_code%");
            });
        }

        if (!empty($email)) {
            $query->whereHas('bundleStudent.student.registeredUser', function ($q) use ($email) {
                $q->where('email', 'like', "%$email%");
            });
        }
        if (!empty($mobile)) {
            $query->whereHas('bundleStudent.student.registeredUser', function ($q) use ($mobile) {
                $q->where('mobile', 'like', "%$mobile%");
            });
        }
        return $query;
    }

    public function exportExcelRequirements(Request $request)
    {
        $this->authorize('admin_requirements_export_excel');

        $requirements = StudentRequirement::orderByDesc('created_at')->get();

        $requirementsExport = new RequirementsExport($requirements);

        return Excel::download($requirementsExport, 'نموذج المتطلبات.xlsx');
    }
    public function students()
    {

        $requirements = '';

        return view('admin.requirements.students', ['requirements' => $requirements]);
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function approve($id)
    {
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
                return back()->with('success', 'تم الموافقة علي طلب القبول وارسال ايميل للطالب بهذا');
            } else {
                return back()->with('error', 'هذا الطلب لم يعد متوفرا');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ ما يرجي المحاولة مرة أخري');
        }
    }
    public function reject($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
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
                $data['body'] = "لقد تم رفض طلبك بسبب ".$request['reason'];
                $requirements->message =  $request['reason']. "<br>";
                if(isset($request['message'])){
                    $data['body'] =  $data['body'] . "\n" . $request['message'];
                    $requirements->message.= $request['message'];
                }

                $this->sendNotification($data);
                // $this->sendEmail($data);

                $requirements->save();
                return back()->with('success', 'تم رفض الطلب وارسال ايميل للطالب بهذا');
            } else {
                return back()->with('error', 'هذا الطلب لم يعد متوفرا');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ ما يرجي المحاولة مرة أخري');
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
            $user = User::where('id', $data['user_id'])->first();
            if (!empty($user) and !empty($user->email)) {
                Mail::to($user->email)->send(new SendNotifications(['title' => $data['subject'], 'message' => $data['body'],'name' => $data['name']]));
            }
        }

        return true;
    }

    // protected function sendEmail($data)
    // {
    //     try {
    //         Mail::send('web.default.emails.confirm', $data, function ($message) use ($data) {
    //             $message->to($data['receiver'])
    //                 ->from($data['fromEmail'], $data['fromName'])
    //                 ->subject($data['subject']);
    //         });
    //         return true;
    //     } catch (\Exception $e) {
    //         return false;
    //     }
    // }
}
