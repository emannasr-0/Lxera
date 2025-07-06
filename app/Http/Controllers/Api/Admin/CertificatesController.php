<?php

namespace App\Http\Controllers\Api\Admin;

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
use Illuminate\Support\Facades\Validator;
use App\Models\Quiz;
use App\Models\CertificateTemplate;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Panel\CertificateController as PanelCertificateController;
use App\Models\Api\Organization;
use Illuminate\Support\Carbon;

class CertificatesController extends Controller
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
            ->get();


        $data = [
            'certificates' => $certificates,
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

        return response()->json([
            'success' => true,
            'message' => $data
        ]);
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
            ->get();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
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

    public function CertificatesTemplateStore($url_name, Request $request, $template_id = null)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This organization was not found.'], 404);
        }

        $rules = [
            'title' => 'required|string',
            'image' => 'required|string',
            'type' => 'required|in:quiz,course,bundle,attendance,new_verssion_bundle,new_verssion_course,new_verssion_attendance',

            'position_x_student' => 'required|numeric',
            'position_y_student' => 'required|numeric',
            'font_size_student' => 'required|numeric',

            'position_x_course' => 'required|numeric',
            'position_y_course' => 'required|numeric',
            'font_size_course' => 'required|numeric',

            'position_x_date' => 'required|numeric',
            'position_y_date' => 'required|numeric',
            'font_size_date' => 'required|numeric',

            'position_x_gpa' => 'required|numeric',
            'position_y_gpa' => 'required|numeric',
            'font_size_gpa' => 'required|numeric',

            'position_x_certificate_code' => 'required|numeric',
            'position_y_certificate_code' => 'required|numeric',
            'font_size_certificate_code' => 'required|numeric',

            'text_color' => 'required|string',
            'locale' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->authorize('admin_certificate_template_create');

            $data = $request->all();

            $templateData = [
                'image' => $data['image'],
                'status' => $data['status'] ?? 'draft',
                'type' => $data['type'],
                'price' => $data['price'] ?? null,
                'student_name' => $data['student_name'] ?? null,
                'position_x_student' => $data['position_x_student'],
                'position_y_student' => $data['position_y_student'],
                'font_size_student' => $data['font_size_student'],

                'text' => $data['text'] ?? null,
                'position_x_text' => $data['position_x_text'] ?? null,
                'position_y_text' => $data['position_y_text'] ?? null,
                'font_size_text' => $data['font_size_text'] ?? null,

                'course_name' => $data['course_name'] ?? null,
                'position_x_course' => $data['position_x_course'],
                'position_y_course' => $data['position_y_course'],
                'font_size_course' => $data['font_size_course'],

                'graduation_date' => $data['graduation_date'] ?? null,
                'position_x_date' => $data['position_x_date'],
                'position_y_date' => $data['position_y_date'],
                'font_size_date' => $data['font_size_date'],

                'gpa' => $data['gpa'] ?? null,
                'position_x_gpa' => $data['position_x_gpa'],
                'position_y_gpa' => $data['position_y_gpa'],
                'font_size_gpa' => $data['font_size_gpa'],

                'text_color' => $data['text_color'],
                'position_x_certificate_code' => $data['position_x_certificate_code'],
                'position_y_certificate_code' => $data['position_y_certificate_code'],
                'font_size_certificate_code' => $data['font_size_certificate_code'],
            ];

            if (!empty($template_id)) {
                $template = CertificateTemplate::findOrFail($template_id);
                $template->update($templateData);
            } else {
                $templateData['created_at'] = time();
                $templateData['updated_at'] = time();
                $template = CertificateTemplate::create($templateData);
            }

            $template->bundle()->sync($request->input('bundles', []));
            $template->webinar()->sync($request->input('webinars', []));

            CertificateTemplateTranslation::updateOrCreate(
                ['certificate_template_id' => $template->id, 'locale' => strtolower($data['locale'])],
                ['title' => $data['title'], 'rtl' => $data['rtl'] ?? 0]
            );

            removeContentLocale();

            return response()->json([
                'success' => true,
                'message' => 'Certificate template saved successfully',
                'data' => $template
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }



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
            //  $suffix = [' th', ' st', ' nd', ' rd'];
            $lastDigit = $number % 10;
            $lastTwoDigits = $number % 100;

            if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
                return $number;
            }

            return $number;
        }

        // Format the issue date
        $graduation_date = new \DateTime($graduation_date);
        $day = $graduation_date->format('j');
        $month = $graduation_date->format('F');
        $year = $graduation_date->format('Y');
        $formattedDate =  getOrdinal($day) . " "  . $month . " " . $year;

        // Add Student Name

        // ADD certificate code to image
        $id = "AC0000012345678";
        // dd($id, $position_y_certificate_code, $position_x_certificate_code);
        $img->text($id, $position_x_certificate_code, $position_y_certificate_code, function ($font) use ($fontPath, $textColor, $font_size_certificate_code) {
            $font->file($fontPath);
            $font->size($font_size_certificate_code); // Adjust as needed
            $font->color($textColor);
            $font->align('center');
            $font->valign('top');
        });

        // Add Student Name to image
        $img->text($student_name, $position_x_student, $position_y_student, function ($font) use ($fontPath, $textColor, $font_size_student) {
            $font->file($fontPath);
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
        $img->text($course_name, $position_x_course, $position_y_course, function ($font) use ($fontPath, $textColor, $font_size_course) {
            $font->file($fontPath);
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


        $graduation_date2 =  $formattedDate;
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

    public function CertificatesTemplatesDelete($url_name, $template_id)
    {
        $this->authorize('admin_certificate_template_delete');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This organization was not found.'], 404);
        }

        $template = CertificateTemplate::findOrFail($template_id);

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certificate template Deleted Successfully'
        ]);
    }

    public function CertificatesDownload($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This organization was not found.'], 404);
        }

        $certificate = Certificate::findOrFail($id);
        if (!$certificate) {
            return response()->json(['message' => 'Certificate not found.'], 404);
        }

        $makeCertificate = new MakeCertificate();

        switch ($certificate->type) {
            case 'quiz':
                $quizResult = QuizzesResult::where('id', $certificate->quiz_result_id)
                    ->where('status', QuizzesResult::$passed)
                    ->with([
                        'quiz' => function ($query) {
                            $query->with('webinar');
                        },
                        'user'
                    ])->first();

                if (!$quizResult) {
                    return response()->json(['message' => 'Quiz result not found or not passed.'], 404);
                }

                $certificateData = $makeCertificate->makeQuizCertificate($quizResult);
                break;

            case 'course':
                $certificateData = $this->makeCourseCertificate($certificate);
                break;

            case 'bundle':
                $certificateData = $this->makeBundleCertificate($certificate);
                break;

            default:
                return response()->json(['message' => 'Invalid certificate type.'], 400);
        }

        $filePath = 'http://127.0.0.1:8000/admin/certificates/' . $id . '/download';

        return response()->json([
            'message' => 'Certificate generated successfully.',
            'data' => $filePath
        ], 200,  [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function deleteSelected(Request $request)
    {
        // Validate the request
        $request->validate([
            'certificate_ids' => 'required|array', // Ensure that at least one certificate is selected
            'certificate_ids.*' => 'exists:certificates,id', // Validate each certificate ID
        ]);

        $certificateIds = $request->input('certificate_ids');

        // Loop through each selected certificate ID
        foreach ($certificateIds as $certificateId) {
            // Find the certificate
            $certificate = Certificate::findOrFail($certificateId);

            // Determine the file path based on whether it's a course or bundle certificate
            if ($certificate->webinar_id) { // Course certificate
                $certificateNamePng = 'certificate_' . $certificate->student->id . '_' . $certificate->webinar_id . '.png';
                $certificateNamePdf = 'certificate_' . $certificate->student->id . '_' . $certificate->webinar_id . '.pdf';
            } elseif ($certificate->bundle_id) { // Bundle certificate
                $certificateNamePng = 'certificate_bundle_' . $certificate->student->id . '_' . $certificate->bundle_id . '.png';
                $certificateNamePdf = 'certificate_bundle_' . $certificate->student->id . '_' . $certificate->bundle_id . '.pdf';
            } else {
                continue; // If the certificate has neither a course nor bundle, skip it
            }

            // Define the file paths
            $filePathPng = public_path('certificates/' . $certificateNamePng);
            $filePathPdf = public_path('certificates/' . $certificateNamePdf);

            // Delete the PNG file if it exists
            if (file_exists($filePathPng)) {
                unlink($filePathPng); // Delete the PNG file
            }

            // Delete the PDF file if it exists
            if (file_exists($filePathPdf)) {
                unlink($filePathPdf); // Delete the PDF file
            }

            // Optionally, delete the certificate record from the database if needed
            // $certificate->delete();
        }

        $toastData = [
            'title' => 'Request Successful', // Title for the toast
            'msg' => 'تم حذف الشهاده من المجلد بنجاح', // Message for the toast
            'status' => 'success' // Status (success, error, warning, etc.)
        ];

        // Redirect back with the toast message
        return back()->with(['toast' => $toastData]);
    }


    public function makeCourseCertificate(Certificate $certificate, $format = 'png')
    {
        $user = $certificate->student;
        $course = $certificate->webinar;
        $template = $course->certificate_template()
            ->where(function ($query) {
                $query->where('type', 'course')
                    ->orWhere('type', 'new_verssion_course');
            })
            ->where('status', 'publish')
            ->latest()
            ->first();

        $makeCertificate = new MakeCertificate();

        if (!empty($template)) {


            $userCertificate = $makeCertificate->saveCourseCertificate($user, $course, $template);

            $group = $course->groups()->whereHas('enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();


            $body = $makeCertificate->makeBody($template);
            $body['certificate_code'] = $userCertificate->certificate_code;
            $body['graduation_date'] = $group->end_date ?? $userCertificate->created_at;
            $body['student_name'] = $user->student->en_name ?? '';
            $body['course_name'] = $course->course_name_certificate;
            $body['course_hours'] = $course->duration;

            $img = $makeCertificate->makeImage($template, $body);
            $certificateFolder = public_path('certificates'); // Path to public/certificates
            if (!is_dir($certificateFolder)) {
                mkdir($certificateFolder, 0777, true); // Create the folder if it doesn't exist
            }

            // Define the certificate filename
            $certificateName = 'certificate_' . $user->id . '_' . $course->id . '.' . $format;
            $path = $certificateFolder . '/' . $certificateName; // Full path to store the certificate file
            // if (file_exists($path)) {
            //     return response()->file($path);
            // }
            if ($format === 'pdf') {
                // Convert the image to a base64 string
                $imageData = (string) $img->encode('data-url'); // Assuming $img is an instance of Intervention Image

                // Generate PDF with embedded image
                $pdf = PDF::loadView('web.default.certificate_template.index', [
                    'pageTitle' => trans('public.certificate'),
                    'body' => $body,
                    'dynamicImage' => $imageData, // Pass the base64 image string to the view
                ]);
                $pdf->save($path);
                return $pdf->setPaper('a4')
                    ->setWarnings(false)
                    ->stream('course_certificate.pdf');
            } else {
                // Handle image download logic as before
                $img->save($path);
                return $img->response('png');
            }
        }

        abort(404);
    }

    public function makeBundleCertificate(Certificate $certificate, $format = 'png')
    {
        $user = $certificate->student;
        $bundle = $certificate->bundle;

        $template = $bundle->certificate_template()
            ->where(function ($query) {
                $query->where('type', 'bundle')
                    ->orWhere('type', 'new_verssion_bundle');
            })
            ->where('status', 'publish')
            ->latest()
            ->first();

        $templateattendance = $bundle->certificate_template()
            ->where('status', 'publish')
            ->where(function ($query) {
                $query->where('type', 'attendance')
                    ->orWhere('type', 'new_verssion_attendance');
            })
            ->latest()
            ->first();

        $makeCertificate = new MakeCertificate();

        if (!empty($template)) {


            $userCertificate = $makeCertificate->savebundleCertificate($user, $bundle, $template);

            $bundleStudent = BundleStudent::where(['student_id' => $user->student->id, 'bundle_id' => $bundle->id])->first();

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
                if (!$templateattendance) {
                    abort(404);
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
            $certificateFolder = public_path('certificates'); // Path to public/certificates
            if (!is_dir($certificateFolder)) {
                mkdir($certificateFolder, 0777, true); // Create the folder if it doesn't exist
            }

            // Define the certificate filename
            $certificateName = 'certificate_bundle_' . $user->id . '_' . $bundle->id . '.' . $format;
            $path = $certificateFolder . '/' . $certificateName;
            // if (file_exists($path)) {
            //     return response()->file($path);
            // }
            if ($format === 'pdf') {

                // Convert the image to a base64 string
                $imageData = (string) $img->encode('data-url'); // Assuming $img is an instance of Intervention Image

                // Generate PDF with embedded image
                $pdf = PDF::loadView('web.default.certificate_template.index', [
                    'pageTitle' => trans('public.certificate'),
                    'body' => $body,
                    'dynamicImage' => $imageData, // Pass the base64 image string to the view
                ]);
                $pdf->save($path);
                return $pdf->setPaper('a4')
                    ->setWarnings(false)
                    ->stream('course_certificate.pdf');
            } else {
                // Handle image download logic as before
                $img->save($path);
                return $img->response('png');
            }
        }

        abort(404);
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

    public function updateGraduationDate(Certificate $certificate, Request $request)
    {
        $request->validate([
            'graduation_date' => 'required|date',
        ]);

        $certificate->update([
            'graduation_date' => $request->input('graduation_date'),
        ]);

        return back()->with('success', 'Graduation date updated.');
    }
}
