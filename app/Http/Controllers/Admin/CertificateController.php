<?php

namespace App\Http\Controllers\Admin;

use App\BundleStudent;
use App\Exports\CertificatesExport;
use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Certificate;
use App\Models\QuizzesResult;
use App\Models\Translation\CertificateTemplateTranslation;
use App\User;
use App\Models\Bundle;
use App\Models\Webinar;
use App\Models\Sale;
use App\Models\Quiz;
use App\Models\CertificateTemplate;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Panel\CertificateController AS PanelCertificateController;


class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_certificate_list');

        $query = Certificate::whereNull('webinar_id');

        $query = $this->filters($query, $request);

        $certificates = $query->with(
            [
                'quiz' => function ($query) {
                    $query->with('webinar');
                },
                'student',
                'quizzesResult'
            ]
        )->orderBy('created_at', 'desc')
            ->paginate(10);


        $data = [
            'pageTitle' => trans('admin/main.certificate_list_page_title'),
            'certificates' => $certificates,
            'student' => $filters['student'] ?? null,
            'instructor' => $filters['instructor'] ?? null,
            'quiz_title' => $filters['quiz_title'] ?? null,
        ];

        $teacher_ids = $request->get('teacher_ids');
        $student_ids = $request->get('student_ids');

        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')
                ->whereIn('id', $teacher_ids)->get();
        }

        if (!empty($student_ids)) {
            $data['students'] = User::select('id', 'full_name')
                ->whereIn('id', $student_ids)->get();
        }

        return view('admin.certificates.lists', $data);
    }

    public function purchase()
    {
        $purchased_certificates = Sale::whereNotNull('certificate_template_id')
            ->get();

        return view('admin.certificates.purchased_certificates', compact('purchased_certificates'));
    }

    private function filters($query, $request)
    {
        $filters = $request->all();

        if (!empty($filters['student_ids'])) {
            $query->whereIn('student_id', $filters['student_ids']);
        }

        if (!empty($filters['teacher_ids'])) {
            $quizzes = Quiz::whereIn('creator_id', $filters['teacher_ids'])->pluck('id')->toArray();

            if ($quizzes and is_array($quizzes)) {
                $query->whereIn('quiz_id', $quizzes);
            }
        }

        if (!empty($filters['quiz_title'])) {
            $quizzes = Quiz::whereTranslationLike('title', '%' . $filters['quiz_title'] . '%')->pluck('id')->toArray();
            $query->whereIn('quiz_id', $quizzes);
        }

        return $query;
    }

    public function CertificatesTemplatesList(Request $request)
    {
        $this->authorize('admin_certificate_template_list');

        removeContentLocale();

        $templates = CertificateTemplate::orderBy('created_at', 'desc')
            ->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.certificate_templates_list_page_title'),
            'templates' => $templates,
        ];

        return view('admin.certificates.templates', $data);
    }

    public function CertificatesNewTemplate()
    {

        $this->authorize('admin_certificate_template_create');

        removeContentLocale();
        $courses = Webinar::get();
        $bundles = Bundle::get();
        $data = [
            'pageTitle' => trans('admin/main.certificate_new_template_page_title'),
            'bundles' => $bundles,
            'courses' => $courses,
        ];

        //  dd($bundles); // Debug the data array
        return view('admin.certificates.new_templates', $data);
    }

    public function CertificatesTemplateStore(Request $request, $template_id = null)
    {

        $rules = [
            'title' => 'required',
            'image' => 'required',
            'type' => 'required|in:quiz,course,bundle,attendance',
            // 'bundles'=>'required',
            //    'student_name'=>'required',
            'position_x_student' => 'required',
            'position_y_student' => 'required',
            'font_size_student' => 'required',
            // 'text'=>'required',
            // 'position_x_text' => 'required',
            // 'position_y_text' => 'required',
            // 'font_size_text' => 'required',
            // 'course_name'=>'required',
            'position_x_course' => 'required',
            'position_y_course' => 'required',
            'font_size_course' => 'required',
            // 'graduation_date' => 'required|date',
            'position_x_date' => 'required',
            'position_y_date' => 'required',
            'font_size_date' => 'required',
            //'gpa' => 'required',
            'position_x_gpa' =>'required',
            'position_y_gpa' => 'required',
            'font_size_gpa' => 'required',

            'position_x_certificate_code' => 'required',
            'position_y_certificate_code' => 'required',
            'font_size_certificate_code' => 'required',
            'text_color' => 'required',
        ];

        $this->validate($request, $rules);

        try {
            $this->authorize('admin_certificate_template_create');
            $data = $request->all();


            // if ($data['status'] and $data['status'] == 'publish') { // set draft for other templates
            //     CertificateTemplate::where('status', 'publish')
            //         ->where('type', $data['type'])
            //         ->update([
            //             'status' => 'draft'
            //         ]);
            // }


            if (!empty($template_id)) {

                $template = CertificateTemplate::findOrFail($template_id);

                $template->update([
                    'image' => $data['image'],
                    'status' => $data['status'],
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'student_name' => $data['student_name'],
                    'position_x_student' => $data['position_x_student'],
                    'position_y_student' => $data['position_y_student'],
                    'font_size_student' => $data['font_size_student'],
                    'text' => $data['text'],
                    'position_x_text' => $data['position_x_text'],
                    'position_y_text' => $data['position_y_text'],
                    'font_size_text' => $data['font_size_text'],
                    'course_name' => $data['course_name'],
                    'position_x_course' => $data['position_x_course'],
                    'position_y_course' => $data['position_y_course'],
                    'font_size_course' => $data['font_size_course'],
                    'graduation_date' => $data['graduation_date'],
                    'position_x_date' => $data['position_x_date'],
                    'position_y_date' => $data['position_y_date'],
                    'font_size_date' => $data['font_size_date'],

                    'gpa' => $data['gpa'],
                    'position_x_gpa' => $data['position_x_gpa'],
                    'position_y_gpa' => $data['position_y_gpa'],
                    'font_size_gpa' => $data['font_size_gpa'],

                    'position_x_certificate_code' => $data['position_x_certificate_code'],
                    'position_y_certificate_code' => $data['position_y_certificate_code'],
                    'font_size_certificate_code' => $data['font_size_certificate_code'],
                    'text_color' => $data['text_color'],

                    'updated_at' => time(), // Use Carbon's now() instead of time()
                ]);
                // if (!empty($request->input('bundles'))) {
                //     $template->bundle()->sync($request->input('bundles', []));
                // }

                $template->bundle()->sync($request->input('bundles', []));
                $template->webinar()->sync($request->input('webinars', []));
                //if(!empty($request->input('webinars'))){
                //     $template->webinar()->sync($request->input('webinars', []));
                // }

            } else {
                $template = CertificateTemplate::create([
                    'image' => $data['image'],
                    'status' => $data['status'],
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'student_name' => $data['student_name'],
                    'position_x_student' => $data['position_x_student'],
                    'position_y_student' => $data['position_y_student'],
                    'font_size_student' => $data['font_size_student'],
                    'text' => $data['text'],
                    'position_x_text' => $data['position_x_text'],
                    'position_y_text' => $data['position_y_text'],
                    'font_size_text' => $data['font_size_text'],
                    'course_name' => $data['course_name'],
                    'position_x_course' => $data['position_x_course'],
                    'position_y_course' => $data['position_y_course'],
                    'font_size_course' => $data['font_size_course'],
                    'graduation_date' => $data['graduation_date'],
                    'position_x_date' => $data['position_x_date'],
                    'position_y_date' => $data['position_y_date'],
                    'font_size_date' => $data['font_size_date'],
                    'gpa' => $data['gpa'],
                    'position_x_gpa' => $data['position_x_gpa'],
                    'position_y_gpa' => $data['position_y_gpa'],
                    'font_size_gpa' => $data['font_size_gpa'],

                    'text_color' => $data['text_color'],
                    'position_x_certificate_code' => $data['position_x_certificate_code'],
                    'position_y_certificate_code' => $data['position_y_certificate_code'],
                    'font_size_certificate_code' => $data['font_size_certificate_code'],
                    'created_at' => time(), // Use Carbon's now() instead of time()
                ]);

                if (!empty($request->input('bundles'))) {
                    $template->bundle()->sync($request->input('bundles', []));
                }

               if(!empty($request->input('webinars'))){
                $template->webinar()->sync($request->input('webinars', []));

            }
            }


            CertificateTemplateTranslation::updateOrCreate([
                'certificate_template_id' => $template->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                //  'body' => $data['body'],
                'rtl' => $data['rtl'],
            ]);

            removeContentLocale();

            return redirect(getAdminPanelUrl() . '/certificates/templates');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    // public function CertificatesTemplateStore(Request $request, $template_id = null, $certificates_id = null)
    // {
    //     try {
    //         $this->authorize('admin_certificate_template_create');

    //         $rules = [
    //             'title' => 'required',
    //             'image' => 'required',
    //             'type' => 'required|in:quiz,course,bundle',
    //             'student_name' => 'required',
    //             'position_x_student' => 'required',
    //             'position_y_student' => 'required',
    //             'font_size_student' => 'required',
    //             'text' => 'required',
    //             'position_x_text' => 'required',
    //             'position_y_text' => 'required',
    //             'font_size_text' => 'required',
    //             'course_name' => 'required',
    //             'position_x_course' => 'required',
    //             'position_y_course' => 'required',
    //             'font_size_course' => 'required',
    //             'graduation_date' => 'required|date',
    //             'position_x_date' => 'required',
    //             'position_y_date' => 'required',
    //             'font_size_date' => 'required',
    //             'text_color' => 'required',
    //         ];

    //         $this->validate($request, $rules);

    //         $data = $request->all();

    //         // Set draft for other templates if publishing
    //         if (isset($data['status']) && $data['status'] == 'publish') {
    //             CertificateTemplate::where('status', 'publish')
    //                 ->where('type', $data['type'])
    //                 ->update(['status' => 'draft']);
    //         }

    //         if (!empty($template_id)) {
    //             $template = CertificateTemplate::findOrFail($template_id);
    //             $template->update([
    //                 'image' => $data['image'],
    //                 'status' => $data['status'],
    //                 'type' => $data['type'],
    //                 'price' => $data['price'],
    //                 'student_name' => $data['student_name'],
    //                 'position_x_student' => $data['position_x_student'],
    //                 'position_y_student' => $data['position_y_student'],
    //                 'font_size_student' => $data['font_size_student'],
    //                 'text' => $data['text'],
    //                 'position_x_text' => $data['position_x_text'],
    //                 'position_y_text' => $data['position_y_text'],
    //                 'font_size_text' => $data['font_size_text'],
    //                 'course_name' => $data['course_name'],
    //                 'position_x_course' => $data['position_x_course'],
    //                 'position_y_course' => $data['position_y_course'],
    //                 'font_size_course' => $data['font_size_course'],
    //                 'graduation_date' => $data['graduation_date'],
    //                 'position_x_date' => $data['position_x_date'],
    //                 'position_y_date' => $data['position_y_date'],
    //                 'font_size_date' => $data['font_size_date'],
    //                 'text_color' => $data['text_color'],
    //                 'updated_at' => time(),
    //             ]);

    //             if ($data['type'] === 'course') {
    //                 if ($template_id) {
    //                     try {
    //                         $certificates = Certificate::findOrFail($template_id);
    //                         $certificates->update([
    //                             'webinar_id' => $request->input('courses', []), // Assuming this is how you're associating courses
    //                             'type' => 'course',
    //                         ]);
    //                     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //                         return redirect()->back()->with('error', 'Certificate not found.');
    //                     }
    //                 }
    //             }

    //             $template->bundle()->sync($request->input('bundles', []));
    //         } else {
    //             $template = CertificateTemplate::create([
    //                 'image' => $data['image'],
    //                 'status' => $data['status'],
    //                 'type' => $data['type'],
    //                 'price' => $data['price'],
    //                 'student_name' => $data['student_name'],
    //                 'position_x_student' => $data['position_x_student'],
    //                 'position_y_student' => $data['position_y_student'],
    //                 'font_size_student' => $data['font_size_student'],
    //                 'text' => $data['text'],
    //                 'position_x_text' => $data['position_x_text'],
    //                 'position_y_text' => $data['position_y_text'],
    //                 'font_size_text' => $data['font_size_text'],
    //                 'course_name' => $data['course_name'],
    //                 'position_x_course' => $data['position_x_course'],
    //                 'position_y_course' => $data['position_y_course'],
    //                 'font_size_course' => $data['font_size_course'],
    //                 'graduation_date' => $data['graduation_date'],
    //                 'position_x_date' => $data['position_x_date'],
    //                 'position_y_date' => $data['position_y_date'],
    //                 'font_size_date' => $data['font_size_date'],
    //                 'text_color' => $data['text_color'],
    //                 'created_at' => time(),
    //             ]);

    //             if ($data['type'] === 'course') {
    //                 $certificates = Certificate::create([
    //                     'webinar_id' => $request->input('courses', []), // Assuming this is how you're associating courses
    //                     'type' => 'course',
    //                 ]);
    //             }

    //             $template->bundle()->sync($request->input('bundles', []));
    //         }

    //         CertificateTemplateTranslation::updateOrCreate([
    //             'certificate_template_id' => $template->id,
    //             'locale' => mb_strtolower($data['locale']),
    //         ], [
    //             'title' => $data['title'],
    //             'rtl' => $data['rtl'],
    //         ]);

    //         removeContentLocale();

    //         return redirect(getAdminPanelUrl() . '/certificates/templates');
    //     } catch (\Exception $e) {
    //         dd($e);
    //     }
    // }





    public function CertificatesTemplatePreview(Request $request)
    {
        $rules = [
            'image' => 'required',
        ];
        $this->validate($request, $rules);
        // Load the background image
        $imgPath = public_path($request->get('image'));
        $img = Image::make($imgPath);
        $textColor = $request->get('text_color') ?? '#000000';

        $student_name = $request->get('student_name') ?? 'student name text';
        $position_x_student = (int)($request->get('position_x_student') ?? 835); // Default to 800 if not provided
        $position_y_student = (int)($request->get('position_y_student') ?? 1250);
        $font_size_student = (int)($request->get('font_size_student') ?? 40);

        $course_name = $request->get('course_name') ?? 'course title text';
        $position_x_course = (int)($request->get('position_x_course') ?? 835); // Default to 800 if not provided
        $position_y_course = (int)($request->get('position_y_course') ?? 1450);
        $font_size_course = (int)($request->get('font_size_course') ?? 40);

        $text = $request->get('text') ?? 'description text';
        $position_x_text = (int)($request->get('position_x_text') ?? 835); // Default to 800 if not provided
        $position_y_text = (int)($request->get('position_y_text') ?? 1400);
        $font_size_text = (int)($request->get('font_size_text') ?? 40);

        $graduation_date = $request->get('graduation_date');
        $position_x_date = (int)($request->get('position_x_date') ?? 835); // Default to 800 if not provided
        $position_y_date = (int)($request->get('position_y_date') ?? 1510);
        $font_size_date = (int)($request->get('font_size_date') ?? 40);

        $gpa = $request->get('gpa');
        $position_x_gpa = (int)($request->get('position_x_gpa') ?? 835); // Default to 800 if not provided
        $position_y_gpa = (int)($request->get('position_y_gpa') ?? 1510);
        $font_size_gpa = (int)($request->get('font_size_gpa') ?? 40);

        $position_x_certificate_code = (int)($request->get('position_x_certificate_code') ?? 560); // Default to 800 if not provided
        $position_y_certificate_code = (int)($request->get('position_y_certificate_code') ?? 2236);
        $font_size_certificate_code = (int)($request->get('font_size_certificate_code') ?? 20);

        // Define font path
        $fontPath2 = public_path('assets/default/fonts/Trajan-Bold.otf'); // Bold font path
        $fontPath = public_path('assets/default/fonts/Trajan-Regular.ttf'); // Make sure this font file exists

        // Helper function to get ordinal suffix
        function getOrdinal($number)
        {
            $suffix = [' th', ' st', ' nd', ' rd'];
            $lastDigit = $number % 10;
            $lastTwoDigits = $number % 100;

            if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
                return $number . $suffix[0];
            }

            return $number . ($suffix[$lastDigit] ?? $suffix[0]);
        }

        // Format the issue date
        $graduation_date = new \DateTime($graduation_date);
        $day = $graduation_date->format('j');
        $month = $graduation_date->format('F');
        $year = $graduation_date->format('Y');
        $formattedDate = "on the " . getOrdinal($day) . " of " . $month . " " . $year;

        // Add Student Name

        // ADD certificate code to image
        $id = "AC000001";
        // dd($id, $position_y_certificate_code, $position_x_certificate_code);
        $img->text($id, $position_x_certificate_code, $position_y_certificate_code, function ($font) use ($fontPath, $textColor, $font_size_certificate_code) {
            $font->file($fontPath);
            $font->size($font_size_certificate_code); // Adjust as needed
            $font->color($textColor);
            $font->align('center');
            $font->valign('top');
        });

        // Add Student Name to image
        $img->text($student_name, $position_x_student, $position_y_student, function ($font) use ($fontPath2, $textColor, $font_size_student) {
            $font->file($fontPath2);
            $font->size($font_size_student); // Adjust as needed
            $font->color($textColor);
            $font->align('center');
            $font->valign('top');
        });

        // Add Text to image
        $img->text($text, $position_x_text, $position_y_text, function ($font) use ($fontPath, $textColor, $font_size_text) {
            $font->file($fontPath);
            $font->size($font_size_text); // Adjust as needed
            $font->color($textColor);
            $font->align('center');
            $font->valign('top');
        });

        // Add Course/Diploma Name to image
        $img->text($course_name, $position_x_course, $position_y_course, function ($font) use ($fontPath2, $textColor, $font_size_course) {
            $font->file($fontPath2);
            $font->size($font_size_course); // Adjust as needed
            $font->color($textColor);
            $font->align('center');
            $font->valign('top');
        });

        $img->text($gpa, $position_x_gpa, $position_y_gpa, function ($font) use ($fontPath, $textColor, $font_size_gpa) {
            $font->file($fontPath);
            $font->size($font_size_gpa); // Adjust as needed
            $font->color($textColor);
            $font->align('center');
            $font->valign('top');
        });


        $graduation_date2 = "with a total of 6 hours training " . $formattedDate;
        // Add Date of Issue
        $img->text($graduation_date2, $position_x_date, $position_y_date, function ($font) use ($fontPath, $textColor, $font_size_date) {
            $font->file($fontPath);
            $font->size($font_size_date); // Adjust as needed
            $font->color($textColor);
            $font->align('center');
            $font->valign('top');
        });

        // Save the modified image
        //$img->save(public_path('path_to_save_the_certificate.jpg'));

        // Optionally, return the image directly
        return $img->response('jpg');
    }









    public function CertificatesTemplatesEdit(Request $request, $template_id)
    {
        $this->authorize('admin_certificate_template_edit');

        $template = CertificateTemplate::findOrFail($template_id);

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $template->getTable(), $template->id);
        $bundles = Bundle::get();
        $courses = Webinar::get();
        $data = [
            'pageTitle' => trans('admin/main.certificate_template_edit_page_title'),
            'template' => $template,
            'bundles' => $bundles,
            'courses' => $courses,
        ];
        return view('admin.certificates.new_templates', $data);
    }

    public function CertificatesTemplatesDelete($template_id)
    {
        $this->authorize('admin_certificate_template_delete');

        $template = CertificateTemplate::findOrFail($template_id);

        $template->delete();

        return redirect(getAdminPanelUrl() . '/certificates/templates');
    }

    public function CertificatesDownload($id)
    {
        $certificate = Certificate::findOrFail($id);

        $makeCertificate = new MakeCertificate();

        if ($certificate->type == 'quiz') {
            $quizResult = QuizzesResult::where('id', $certificate->quiz_result_id)
                ->where('status', QuizzesResult::$passed)
                ->with([
                    'quiz' => function ($query) {
                        $query->with(['webinar']);
                    },
                    'user'
                ])
                ->first();

            return $makeCertificate->makeQuizCertificate($quizResult);
        } else if ($certificate->type == 'course') {

            return $this->makeCourseCertificate($certificate);
        } else if ($certificate->type == 'bundle') {

            return $this->makeBundleCertificate($certificate);
        }

        return view('errors.404');
    }


    public function makeCourseCertificate(Certificate $certificate, $format = 'png')
    {
        $user = $certificate->student;
        $course = $certificate->webinar;
        $template = $course->certificate_template()->where('status', 'publish')
        ->where('type', 'course')->latest()->first();

        $makeCertificate = new MakeCertificate();

        if (!empty($template)) {


            $userCertificate = $makeCertificate->saveCourseCertificate($user, $course, $template);

            $group = $course->groups()->whereHas('enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();


            $body = $makeCertificate->makeBody($template);
            $body['certificate_code'] = $userCertificate->certificate_code;
            $body['graduation_date'] = $group->end_date;
            $body['student_name'] = $user->student->en_name ?? '';
            $body['course_name'] = $course->course_name_certificate;
            $body['course_hours'] = $course->duration;

            // Generate the image
            $img = $makeCertificate->makeImage($template, $body);
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

    public function makeBundleCertificate(Certificate $certificate, $format = 'png')
    {
        $user = $certificate->student;
        $bundle = $certificate->bundle;
        $template = $bundle->certificate_template()->where('status', 'publish')
        ->where('type', 'bundle')->latest()->first();

        $templateattendance = $bundle->certificate_template()->where('status', 'publish')
            ->where('type', 'attendance')
            ->latest()
            ->first();

        $makeCertificate = new MakeCertificate();

        if (!empty($template)) {


            $userCertificate = $makeCertificate->savebundleCertificate($user, $bundle, $template);

            $bundleStudent = BundleStudent::where(['student_id'=> $user->student->id, 'bundle_id' => $bundle->id])->first();

            $gpa = $bundleStudent->gpa;
            $allAssignmentsPassed = (new PanelCertificateController())->checkAssignmentsStatus($bundle, $bundleStudent);
            // Set the image based on whether all assignments were passed

            if ($allAssignmentsPassed && $gpa !== null) {
                $data = $template;
                $body = $makeCertificate->makeBody($data);
                $body['certificate_code'] = $userCertificate->certificate_code;
                $body['student_name'] = $user->student->en_name ?? '';
                $body['course_name'] = $bundle->bundle_name_certificate;
                $body['graduation_date'] = $bundle->end_date; // Include the end date
                $body['gpa'] = $gpa;
                $img = $makeCertificate->makeImage($template, $body);
            } else {
                if(!$templateattendance){
                   return view('errors.404');
                }
                $data = $templateattendance;
                $body = $makeCertificate->makeBody($data);
                $body['certificate_code'] = $userCertificate->certificate_code;
                $body['student_name'] = $user->student->en_name ?? '';
                $body['course_name'] = $bundle->bundle_name_certificate;
                $body['graduation_date'] = $bundle->end_date; // Include the end date
                $body['gpa'] = $gpa;
                $img = $makeCertificate->makeImage($templateattendance, $body);
            }
            //


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


    public function exportExcel(Request $request)
    {
        $this->authorize('admin_certificate_export_excel');

        $query = Certificate::query();

        $query = $this->filters($query, $request);

        $certificates = $query
            ->whereHas('quiz')
            ->with(
                [
                    'quiz' => function ($query) {
                        $query->with('webinar');
                    },
                    'student',
                    'quizzesResult'
                ]
            )->orderBy('created_at', 'desc')
            ->get();

        $export = new CertificatesExport($certificates);

        return Excel::download($export, 'certificates.xlsx');
    }
}
