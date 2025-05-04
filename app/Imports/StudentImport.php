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

class StudentImport implements ToModel
{
    protected $skipFirstRow = true;

    protected $currentRow = 1; // Initialize row counter
    protected $scholarship;
    protected $enrollCourse;
    protected $errors = [];
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */


     public function __construct($scholarship=false, $enrollCourse= false){
        $this->scholarship = $scholarship;
        $this->enrollCourse = $enrollCourse;
     }
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


            $programCode = $row[6];


            $program = $this->enrollCourse ? Webinar::find($programCode) : bundle::find($programCode);

            if (!$program) {
                $this->errors[] = "في الصف رقم {$this->currentRow}: كود البرنامج غير صحيح";
                return null;
            }
            $rules = [
                'ar_name' => 'required|string|regex:/^[\p{Arabic} ]+$/u|max:255|min:5',
                'en_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255|min:5',
                'email' => 'required|email|max:255|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
                'mobile' => 'required',
                'gender' => 'required|in:male,female',
                'identifier_num' => 'required|regex:/^[A-Za-z0-9]{6,10}$/',
                // 'deaf' => 'required|in:نعم,لا',
                // 'birthdate' => 'required|date_format:Y-m-d'
            ];

            $fileData = [
                'ar_name' => $row[0],
                'en_name' => $row[1],
                'email' => $row[2],
                'mobile' => $row[3],
                'gender' => $row[4],
                'identifier_num' => $row[5],
                // 'deaf' => $row[22],
                // 'birthdate' => $row[5],
            ];
            // validate imported data
            $validator = Validator::make($fileData, $rules);

            if ($validator->fails()) {
                $this->errors[] = "في الصف رقم {$this->currentRow}: "  . implode(', ', $validator->errors()->all());
                return null;
            }

            // find or create user if doesn't exist
            $user = User::firstOrCreate(['email' => $row[2]], [
                'role_name' => 'registered_user',
                'role_id' => 13,
                'full_name' => $row[0],
                'status' => User::$active,
                'verified' => 1,
                'access_content' => 1,
                'password' => Hash::make('anasAcademy123'),
                'affiliate' => 0,
                'timezone' => getGeneralSettings('default_time_zone'),
                'created_at'=>time()
            ]);


            // if the user was created newly send an email to him with email and password
            if ($user->wasRecentlyCreated) {
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
            }

            // update user code
            if (empty($user->user_code)) {
                $code = $this->generateStudentCode();
                $user->update(['user_code' => $code]);

                // update code
                Code::latest()->first()->update(['lst_sd_code' => $code]);
            }

            // create student if doesn't exist
            $student = $user->student ?? Student::create([
                'user_id' => $user->id,
                'ar_name' => $row[0],
                'en_name' => $row[1],
                'email' => $row[2],
                'phone' => $row[3] ,
                'mobile' => $row[3],
                // 'birthdate' => $row[5] ?? '1999-01-01',
                'gender' => $row[4],
                'identifier_num' => $row[5],
                // 'nationality' => $row[9] ?? 'سعودي/ة',
                // 'country' => $row[10] ?? 'السعودية',
                // 'town' => $row[11] ?? 'الرياض',
                // 'educational_qualification_country' => $row[12],
                // 'educational_area' => $row[13] ?? 'الرياض',
                // 'university' => $row[14],
                // 'faculty' => $row[15],
                // 'education_specialization' => $row[16],
                // 'graduation_year' => $row[17],
                // 'gpa' => $row[18],
                // 'school' => $row[19],
                // 'secondary_school_gpa' => $row[20],
                // 'secondary_graduation_year' => $row[21],
                // 'deaf' => ($row[22] == 'نعم') ? 1 : 0,
                // 'disabled_type' => $row[23] ?? null,
                // 'healthy_problem' => $row[24],
                // 'job' => $row[25] ?? null,
                // 'job_type' => $row[26] ?? null,
                // 'referral_person' => $row[27] ?? 'صديق',
                // 'relation' => $row[28] ?? 'صديق',
                // 'referral_email' => $row[29] ?? 'email@example.com',
                // 'referral_phone' => $row[30] ?? '0000000',
                'about_us' => $row[7] ?? 'facebook',
                'created_at' => date('Y-m-d H:i:s')


            ]);

            if(!$this->enrollCourse){
                // check the user apply to this bundle before or not
                $bundleStudent = BundleStudent::where(['student_id' => $student->id, 'bundle_id' => $program->id])->first();
                if ($bundleStudent) {
                    return null;
                }

                $class =  StudyClass::get()->last();
                if (!$class) {
                    $class = StudyClass::create(['title' => "الدفعة الأولي"]);
                }
                // apply bundle for student
                $bundleStudent = BundleStudent::create([
                    'student_id' => $student->id,
                    'bundle_id' => $program->id,
                    'class_id' => $class->id,
                ]);

            }


            // create order
            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::$paid,
                'amount' =>  ($this->scholarship ||  $this->enrollCourse) ? $program->price ?? 0 : 59,
                'payment_method' =>  $this->scholarship? 'scholarship': 'payment_channel',
                'tax' => 0,
                'total_discount' => 0,
                'total_amount' =>  $this->scholarship ? 0 : ($this->enrollCourse ? $program->price ?? 0 : 59),
                'product_delivery_fee' => null,
                'created_at' => time(),
            ]);

            // create order item
            $orderItem = OrderItem::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'bundle_id' =>  !$this->enrollCourse ? $program->id : null,
                'webinar_id' =>  $this->enrollCourse ? $program->id : null,
                'certificate_template_id' =>  null,
                'certificate_bundle_id' => null,
                'form_fee' => ($this->scholarship || $this->enrollCourse) ? null: 1,
                'product_id' =>  null,
                'product_order_id' => null,
                'reserve_meeting_id' => null,
                'subscribe_id' => null,
                'promotion_id' => null,
                'gift_id' => null,
                'installment_payment_id' => null,
                'ticket_id' => null,
                'discount_id' => null,
                'amount' =>  ($this->scholarship ||  $this->enrollCourse) ? $program->price ?? 0 : 59,
                'total_amount' => $this->scholarship ? 0 : ($this->enrollCourse ? $program->price ?? 0 : 59),
                'tax' => null,
                'tax_price' => 0,
                'commission' => 0,
                'commission_price' => 0,
                'product_delivery_fee' => 0,
                'discount' => 0,
                'created_at' => time(),
            ]);




            if ($this->enrollCourse && !empty($program->hasGroup)) {
                $webinar = $program;
                $lastGroup = Group::where('webinar_id', $webinar->id)->latest()->first();
                $startDate = now()->addMonth()->startOfMonth();
                $endDate = now()->addMonth(2)->startOfMonth();
                if (!$lastGroup) {
                    $lastGroup = Group::create(['name' => 'A', 'creator_id' => 1, 'webinar_id' => $webinar->id, 'capacity' => 20, 'start_date' => $startDate, 'end_date' => $endDate]);
                }
                $enrollments = $lastGroup->enrollments->count();
                $enrolled = Enrollment::where(['user_id' => $user->id, 'group_id' => $lastGroup->id,])->first();
                if(!empty($enrolled)){
                    return null;
                }
                if ($enrollments >= $lastGroup->capacity || $lastGroup->start_date < now() ) {
                    $lastGroup = Group::create(['name' => chr(ord($lastGroup->name) + 1), 'creator_id' => 1, 'webinar_id' => $webinar->id, 'capacity' => 20,'start_date' => $startDate, 'end_date' => $endDate]);
                }

                Enrollment::create([
                    'user_id' => $user->id,
                    'group_id' => $lastGroup->id,
                ]);
            }


            if ($this->scholarship || $this->enrollCourse) {
                $user->update([
                    'role_id' => 1,
                    'role_name' => 'user',
                ]);
            }

            // create sale
            $sale = Sale::createSales($orderItem, $order->payment_method);
            Accounting::createAccounting($orderItem);
            TicketUser::useTicket($orderItem);


            return null;

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return null; // Skip invalid row
        }
    }

    public function sendEmail($user, $data)
    {
        if (!empty($user) and !empty($user->email) and env('APP_ENV') == 'production') {
            Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'] ?? '', 'message' => $data['body'] ?? '']));
        }
    }

    public function sendNotification($user, $data)
    {
        Notification::create([
            'user_id' => $user->id ?? 0,
            'sender_id' => auth()->id(),
            'title' => $data['title'] ?? '',
            'message' => $data['body'] ?? '',
            'sender' => Notification::$AdminSender,
            'type' => "single",
            'created_at' => time()
        ]);
    }

    public function generateStudentCode()
    {
        // USER CODE
        $lastCode = Code::latest()->first();
        if (!empty($lastCode)) {
            if (empty($lastCode->lst_sd_code)) {
                $lastCode->lst_sd_code = $lastCode->student_code;
            }
            $lastCodeAsInt = intval(substr($lastCode->lst_sd_code, 2));
            do {
                $nextCodeAsInt = $lastCodeAsInt + 1;
                $nextCode = 'SD' . str_pad($nextCodeAsInt, 5, '0', STR_PAD_LEFT);

                $codeExists = User::where('user_code', $nextCode)->exists();

                if ($codeExists) {
                    $lastCodeAsInt = $nextCodeAsInt;
                } else {
                    break;
                }
            } while (true);

            return $nextCode;
        }
        return 'SD00001';
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
