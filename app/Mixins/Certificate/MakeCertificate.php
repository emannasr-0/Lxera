<?php

namespace App\Mixins\Certificate;

use App\Models\Bundle;
use App\Models\BundleWebinar;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Webinar;
use \Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\Facades\Image;
use PhpOffice\PhpSpreadsheet\Writer\Pdf as WriterPdf;
use Carbon\Carbon;

class MakeCertificate
{
    public function makeQuizCertificate($quizResult)
    {
        $template = CertificateTemplate::where('status', 'publish')
            ->where('type', 'quiz')
            ->first();



        if (!empty($template)) {
            $quiz = $quizResult->quiz;
            $user = $quizResult->user;

            $userCertificate = $this->saveQuizCertificate($user, $quiz, $quizResult);
            //$certificateId = $userCertificate->id;
            $data = $template;


            $body = $this->makeBody($data, $userCertificate);
            $body['certificat_code'] = "AC" . str_pad($userCertificate->id, 4, "0", STR_PAD_LEFT);

            /*$data = [
                'pageTitle' => trans('public.certificate'),
                'image' => public_path($template->image),
                'body' => $body,
            ];*/

            $img = $this->makeImage($template, $body);
            // dd( $userCertificate,$body);
            return $img->response('png');
        }

        return view('errors.404');
    }

    private function saveQuizCertificate($user, $quiz, $quizResult)
    {
        $certificate = Certificate::where('quiz_id', $quiz->id)
            ->where('student_id', $user->id)
            ->where('quiz_result_id', $quizResult->id)
            ->first();

        $data = [
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'quiz_result_id' => $quizResult->id,
            'user_grade' => $quizResult->user_grade,
            'type' => 'quiz',
            'created_at' => time()
        ];

        if (!empty($certificate)) {
            $certificate->update($data);
        } else {
            $certificate = Certificate::create($data);

            $notifyOptions = [
                '[c.title]' => $quiz->webinar ? $quiz->webinar->title : '-',
            ];
            sendNotification('new_certificate', $notifyOptions, $user->id);
        }

        return $certificate;
    }

    // private function makeBody($userCertificate, $user, $body, $courseTitle = null, $userGrade = null, $teacherFullName = null, $duration = null)
    // {
    //     $body = str_replace('[student]', $user->full_name, $body);
    //     $body = str_replace('[course]', $courseTitle, $body);
    //     $body = str_replace('[grade]', $userGrade, $body);
    //     $body = str_replace('[certificate_id]', $userCertificate->id, $body);
    //     $body = str_replace('[date]', dateTimeFormat($userCertificate->created_at, 'j M Y | H:i'), $body);
    //     $body = str_replace('[instructor_name]', $teacherFullName, $body);
    //     $body = str_replace('[duration]', $duration, $body);

    //     $userCertificateAdditional = $user->userMetas->where('name', 'certificate_additional')->first();
    //     $userCertificateAdditionalValue = !empty($userCertificateAdditional) ? $userCertificateAdditional->value : null;
    //     $body = str_replace('[user_certificate_additional]', $userCertificateAdditionalValue, $body);

    //     return $body;
    // }



    public function makeBody($data)
    {
        $bodyData = [
            'student_name' => $data['student_name'] ?? '', // Default to empty string if not provided
            'position_x_student' => (int)($data['position_x_student'] ?? 835), // Default to 835 if not provided
            'position_y_student' => (int)($data['position_y_student'] ?? 1250),
            'font_size_student' => (int)($data['font_size_student'] ?? 40),

            'course_name' => $data['course_name'] ?? '',
            'position_x_course' => (int)($data['position_x_course'] ?? 835), // Default to 835 if not provided
            'position_y_course' => (int)($data['position_y_course'] ?? 1450),
            'font_size_course' => (int)($data['font_size_course'] ?? 40),

            'text' => $data['text'] ?? '',
            'position_x_text' => (int)($data['position_x_text'] ?? 835), // Default to 835 if not provided
            'position_y_text' => (int)($data['position_y_text'] ?? 1400),
            'font_size_text' => (int)($data['font_size_text'] ?? 40),

            'graduation_date' => $data['graduation_date'] ?? '',
            'position_x_date' => (int)($data['position_x_date'] ?? 835), // Default to 835 if not provided
            'position_y_date' => (int)($data['position_y_date'] ?? 1510),
            'font_size_date' => (int)($data['font_size_date'] ?? 40),

            'certificate_code' => $data['certificate_code'] ?? '',
            'position_x_certificate_code' => (int)($data['position_x_certificate_code'] ?? 560), // Fixed key name
            'position_y_certificate_code' => (int)($data['position_y_certificate_code'] ?? 2236),
            'font_size_certificate_code' => (int)($data['font_size_certificate_code'] ?? 20),

            //'course_hours' => $data['course_hours'] ?? '',
           'gpa_text' => $data['gpa'] ?? '',
           'position_x_gpa' => (int)($data['position_x_gpa'] ?? 835), // Default to 835 if not provided
            'position_y_gpa' => (int)($data['position_y_gpa'] ?? 1510),
            'font_size_gpa' => (int)($data['font_size_gpa'] ?? 40),

        ];

        return $bodyData;
    }




    // private function makeImage($certificateTemplate, $body)
    // {
    //     $img = Image::make(public_path($certificateTemplate->image));


    //     if ($certificateTemplate->rtl) {
    //         $Arabic = new \I18N_Arabic('Glyphs');
    //         $body = $Arabic->utf8Glyphs($body);
    //     }

    //     $img->text($body, $certificateTemplate->position_x, $certificateTemplate->position_y, function ($font) use ($certificateTemplate) {
    //         $font->file($certificateTemplate->rtl ? public_path('assets/default/fonts/vazir/Vazir-Medium.ttf') : public_path('assets/default/fonts/Montserrat-Medium.ttf'));
    //         $font->size($certificateTemplate->font_size);
    //         $font->color($certificateTemplate->text_color);
    //         $font->align($certificateTemplate->rtl ? 'right' : 'left');
    //     });

    //     return $img;
    // }
    function getOrdinal($number)
    {
        $suffix = ['th', 'st', 'nd', 'rd'];
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return $number . $suffix[0];
        }

        return $number . ($suffix[$lastDigit] ?? $suffix[0]);
    }

    public function makeImage($certificateTemplate, $body)
    {
        // Load the background image
        $img = Image::make(public_path($certificateTemplate->image));

        $fontPath2 = public_path('assets/default/fonts/Trajan-Bold.otf'); // Bold font path
        $fontPath = public_path('assets/default/fonts/Trajan-Regular.ttf'); // Regular font path

        // GPA to Honor Level mapping
        $honorLevels = [
            ['from' => 5.0, 'to' => 5.0, 'level' => "above excellent first class honors"],
            ['from' => 4.85, 'to' => 4.99, 'level' => "above excellent second class honors"],
            ['from' => 4.75, 'to' => 4.84, 'level' => "excellent"],
            ['from' => 4.5, 'to' => 4.74, 'level' => "above very good"],
            ['from' => 4.0, 'to' => 4.49, 'level' => "very good"],
            ['from' => 3.5, 'to' => 3.99, 'level' => "above good"],
            ['from' => 3.0, 'to' => 3.49, 'level' => "good"],
            ['from' => 2.5, 'to' => 2.99, 'level' => "above pass"],
            ['from' => 2.0, 'to' => 2.49, 'level' => "pass"],
            ['from' => 0.0, 'to' => 1.99, 'level' => "fail"],
        ];

        // Format the graduation date
        $formattedDate = '';
        if (isset($body['graduation_date'])) {
            if (is_numeric($body['graduation_date'])) {
                $timestamp = (int)$body['graduation_date'];
                $graduation_date = new \DateTime();
                $graduation_date->setTimestamp($timestamp);
            } else {
                $graduation_date = new \DateTime($body['graduation_date']);
            }

            $day = $graduation_date->format('j');
            $month = $graduation_date->format('F');
            $year = $graduation_date->format('Y');
            $formattedDate = "on the " . $this->getOrdinal($day) . " of " . $month . " " . $year;
        }

        // Add Graduation Date
        if (isset($body['course_hours']) && $formattedDate) {

            $body['graduation_date'] = "with a total of " . $body['course_hours'] . " training hours " . $formattedDate;
        }
        else{
            $body['graduation_date'] =   $formattedDate;
        }
       
        // Add Graduation Date Text
        if (isset($body['graduation_date'])) {
            $img->text($body['graduation_date'], $body['position_x_date'], $body['position_y_date'], function ($font) use ($fontPath, $certificateTemplate, $body) {
                $font->file($fontPath);
                $font->size($body['font_size_date']);
                $font->color($certificateTemplate->text_color);
                $font->align('center');
                $font->valign('top');
            });
        }

        // Add Student Name
        if (isset($body['student_name'])) {
            $img->text($body['student_name'], $body['position_x_student'], $body['position_y_student'], function ($font) use ($fontPath2, $certificateTemplate, $body) {
                $font->file($fontPath2);
                $font->size($body['font_size_student']);
                $font->color($certificateTemplate->text_color);
                $font->align('center');
                $font->valign('top');
            });
        }

        // Add Course Name
        if (isset($body['course_name'])) {
            $img->text($body['course_name'], $body['position_x_course'], $body['position_y_course'], function ($font) use ($fontPath2, $certificateTemplate, $body) {
                $font->file($fontPath2);
                $font->size($body['font_size_course']);
                $font->color($certificateTemplate->text_color);
                $font->align('center');
                $font->valign('top');
            });
        }

       

       
           // GPA Check and Honor Level
           $gpaMessage = ''; // Initialize the message

           // Check if 'gpa' is set and not null
 
           if (array_key_exists('gpa', $body)) {
               if ($body['gpa'] === null || $body['gpa'] == 0) {

                if (isset($body['gpa_text'])) {
                    $img->text($body['gpa_text'], $body['position_x_gpa'], $body['position_y_gpa'], function ($font) use ($fontPath, $certificateTemplate, $body) {
                        $font->file($fontPath);
                        $font->size($body['font_size_gpa']);
                        $font->color($certificateTemplate->text_color);
                        $font->align('center');
                        $font->valign('top');
                    });
                }
                
                   // Case when GPA is null
                   //$gpaMessage = "without graduation project requirements have not been fulfilled";
               } elseif ($body['gpa'] != 0.0 && !empty($body['gpa']) ) {
                   // Determine honor level based on GPA
                   $honorLevel = "Fail"; // Default value
                   foreach ($honorLevels as $range) {
                       if ($body['gpa'] >= $range['from'] && $body['gpa'] <= $range['to']) {
                           $honorLevel = $range['level'];
                           break; // Stop once we find the applicable level
                       }
                   }
           
                   $gpaMessage = "with a " . $honorLevel . " and a GPA of (" . $body['gpa'] . "/5)";
               }   
           }
    
        // Add GPA Text
        if (!empty($gpaMessage)) {
            $img->text($gpaMessage, $body['position_x_gpa'], $body['position_y_gpa'], function ($font) use ($fontPath, $certificateTemplate, $body) {
                $font->file($fontPath);
                $font->size($body['font_size_gpa']);
                $font->color($certificateTemplate->text_color);
                $font->align('center');
                $font->valign('top');
            });
        }

        // Add Additional Text
        if (isset($body['text'])) {
            $img->text($body['text'], $body['position_x_text'], $body['position_y_text'], function ($font) use ($fontPath, $certificateTemplate, $body) {
                $font->file($fontPath);
                $font->size($body['font_size_text']);
                $font->color($certificateTemplate->text_color);
                $font->align('center');
                $font->valign('top');
            });
        }

        // Add Certificate Code
        if (isset($body['certificate_code'])) {
            $img->text($body['certificate_code'], $body['position_x_certificate_code'], $body['position_y_certificate_code'], function ($font) use ($fontPath, $certificateTemplate, $body) {
                $font->file($fontPath);
                $font->size($body['font_size_certificate_code']);
                $font->color($certificateTemplate->text_color);
                $font->align('center');
                $font->valign('top');
            });
        }

        return $img;
    }







    public function makeCourseCertificate(Webinar $course, $format = 'png')
    {
 
        $template = $course->certificate_template()->where('status', 'publish')
            ->where('type', 'course')->latest()->first();
        // $course = $certificate->webinar;

        if (!empty($template) and !empty($course)) {
            $user = auth()->user();

            $userCertificate = $this->saveCourseCertificate($user, $course, $template);

            $group = $course->groups()->whereHas('enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();


            $body = $this->makeBody($template);
            $body['certificate_code'] = $userCertificate->certificate_code;
            $body['graduation_date'] = $group->end_date;
            $body['student_name'] = $user->student->en_name ?? '';
            $body['course_name'] = $course->course_name_certificate;
            $body['course_hours'] = $course->duration;
           // dd($body['gpa']);
           //unset($body['gpa']);
            // Generate the image
            $img = $this->makeImage($template, $body);
            if ($format === 'pdf') {

                // Convert the image to a base64 string
                $imageData = (string) $img->encode('data-url'); // Assuming $img is an instance of Intervention Image

                // Generate PDF with embedded image
                $pdf = PDF::loadView('web.default.certificate_template.index', [
                    'pageTitle' => trans('public.certificate'),

                    'body' => $body,
                    'dynamicImage' => $imageData, // Pass the base64 image string to the view
                ]);

                return $pdf->setPaper('a4')
                    ->setWarnings(false)
                    ->stream('course_certificate.pdf');
            } else {
                // Handle image download logic as before
                return $img->response('png');
            }

        }

        return view('errors.404');
    }




    public function saveCourseCertificate($user, $course, $template)
    {
        $certificate = Certificate::where('webinar_id', $course->id)
            ->where('student_id', $user->id)
            ->first();

        $data = [
            'webinar_id' => $course->id,

            'student_id' => $user->id,
            'template_id' => $template->id,
            'type' => 'course',
            'created_at' => time()
        ];
        $group = $course->groups()->whereHas('enrollments', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        if (empty($certificate)) {
            $date = Carbon::parse($group->end_date);
            $year = $date->format('Y');
            $month = $date->format('m');
            $day = $date->format('d');
            $certificate = Certificate::create($data);
            $certificateCode = "AC" . $certificate->id . $year . $month . $day;
            $data['certificate_code'] = $certificateCode;
            $certificate->update($data);

            $notifyOptions = [
                '[c.title]' => $course->title,
            ];
            sendNotification('new_certificate', $notifyOptions, $user->id);
        }




        return $certificate;
    }


    public function makebundleCertificate(Bundle $bundle, $format = 'png', $gpa, $allAssignmentsPassed)
    {
        // Determine the appropriate template/image based on the assignment status
        $template = $bundle->certificate_template()->where('status', 'publish')
            ->where('type', 'bundle')
            ->latest()
            ->first();
       $templateattendance = $bundle->certificate_template()->where('status', 'publish')
            ->where('type', 'attendance')
            ->latest()
            ->first();
        if (!empty($template) && !empty($bundle)) {
            $user = auth()->user();
            $userCertificate = $this->saveBundleCertificate($user, $bundle, $template);
         
           // dd($data);
            
            
    
    
            // Set the image based on whether all assignments were passed
         
            if ($allAssignmentsPassed && $gpa !== null) {
                   $data = $template;
            $body = $this->makeBody($data);
            $body['certificate_code'] = $userCertificate->certificate_code;
            $body['student_name'] = $user->student->en_name ?? '';
            $body['course_name'] = $bundle->bundle_name_certificate;
            $body['graduation_date'] = $bundle->end_date; // Include the end date
            $body['gpa'] = $gpa;
                $img = $this->makeImage($template, $body);
            } else { 
                   $data = $templateattendance;
                $body = $this->makeBody($data);
            $body['certificate_code'] = $userCertificate->certificate_code;
            $body['student_name'] = $user->student->en_name ?? '';
            $body['course_name'] = $bundle->bundle_name_certificate;
            $body['graduation_date'] = $bundle->end_date; // Include the end date
            $body['gpa'] = $gpa;
                $img = $this->makeImage($templateattendance , $body);
            }
    
            if ($format === 'pdf') {
                // Generate PDF with embedded image
                $imageData = (string) $img->encode('data-url');
                $pdf = PDF::loadView('web.default.certificate_template.index', [
                    'pageTitle' => trans('public.certificate'),
                    'body' => $body,
                    'dynamicImage' => $imageData,
                ]);
    
                return $pdf->setPaper('a4')
                    ->setWarnings(false)
                    ->stream('course_certificate.pdf');
            } else {
                return $img->response('png');
            }
        }
    
        return view('errors.404');
    }
    






    public function savebundleCertificate($user, $bundle, $template)
    {
        $certificate = Certificate::where('bundle_id', $bundle->id)
            ->where('student_id', $user->id)
            ->first();

        $data = [
            'bundle_id' => $bundle->id,
            'student_id' => $user->id,
            'template_id' => $template->id,
            'type' => 'bundle',
            'created_at' => time()
        ];

        if (empty($certificate)) {
            $date = Carbon::createFromTimestamp((int)$bundle->end_date);
            $year = $date->format('Y');
            $month = $date->format('m');
            $day = $date->format('d');
            $certificate = Certificate::create($data);
            $certificateCode = "AC" . $certificate->id . $year . $month . $day;
            $data['certificate_code'] = $certificateCode;
            $certificate->update($data);

            $notifyOptions = [
                '[c.title]' => $bundle->title,
            ];
            sendNotification('new_certificate', $notifyOptions, $user->id);

        }




        return $certificate;
    }
}
