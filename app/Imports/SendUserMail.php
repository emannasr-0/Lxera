<?php

namespace App\Imports;

use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Mail\SendNotifications;
use App\Models\Notification;
use App\User;
use App\Student;
use App\BundleStudent;
use App\Models\Bundle;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sale;
use App\Models\Code;
use App\Models\Accounting;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\StudyClass;
use App\Models\TicketUser;
use App\Models\Webinar;
use Carbon\Carbon;

class SendUserMail implements ToModel
{
    protected $skipFirstRow = true;

    protected $currentRow = 1; // Initialize row counter
    protected $errors = [];
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */


    public function __construct() {}
    public function model(array $row)
    {
        try {
            // Skip processing if the row is empty
            if (empty(array_filter($row))) {
                return null;
            }

            if ($this->skipFirstRow) {
                $this->skipFirstRow = false;
                return null;
            }
            // Increment row counter
            $this->currentRow++;


            $email = $row[0];

            $rules = [
                'email' => 'required|email|max:255|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
            ];

            $fileData = [
                'email' => $email,
            ];
            // validate imported data
            $validator = Validator::make($fileData, $rules);

            if ($validator->fails()) {
                $this->errors[] = "في الصف رقم {$this->currentRow}: "  . implode(', ', $validator->errors()->all());
                return null;
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->errors[] = "في الصف رقم {$this->currentRow}: هذا الإيميل لا يمثل طالب";
                return null;
            }

            $data['title'] = "انشاء حساب جديد";
            $data['body'] = " تهانينا تم انشاء حساب لكم في اكاديمية انس 
                            <br>
                            <br>
                            يمكن تسجيل الدخول من خلال هذا الرابط
                            <a href='https://lms.anasacademy.uk/login' class='btn btn-danger'>اضغط هنا للدخول</a>
                            <br>
                            بإستخدام هذا البريد الإلكتروني وكلمة المرور
                            <br>
                            <span style='font-weight:bold;'>البريد الالكتروني: </span> $user->email
                            <br>
                             <span style='font-weight:bold;'>كلمة المرور: </span> anasAcademy123
                            <br>
                ";



            $this->sendEmail($user, $data);



            return null;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return null; // Skip invalid row
        }
    }

    public function sendEmail($user, $data)
    {
        if (!empty($user) and !empty($user->email)) {
            Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'] ?? '', 'message' => $data['body'] ?? '']));
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
