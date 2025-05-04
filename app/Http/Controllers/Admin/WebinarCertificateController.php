<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Bundle;
use App\Models\Certificate;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;

class WebinarCertificateController extends Controller
{
    // public function index(Request $request)
    // {
    //     $this->authorize('admin_course_certificate_list');

    //     $query = Certificate::whereNotNull('webinar_id');

    //     $query = $this->filters($query, $request);

    //     $certificates = $query->with(
    //         [
    //             'webinar',
    //             'student',
    //         ]
    //     )->orderBy('created_at', 'desc')
    //         ->paginate(10);


    //     $data = [
    //         'pageTitle' => trans('update.competition_certificates'),
    //         'certificates' => $certificates,
    //     ];

    //     $teacher_ids = $request->get('teacher_ids');
    //     $student_ids = $request->get('student_ids');
    //     $webinarsIds = $request->get('webinars_ids');

    //     if (!empty($teacher_ids)) {
    //         $data['teachers'] = User::select('id', 'full_name')
    //             ->whereIn('id', $teacher_ids)->get();
    //     }

    //     if (!empty($student_ids)) {
    //         $data['students'] = User::select('id', 'full_name')
    //             ->whereIn('id', $student_ids)->get();
    //     }

    //     if (!empty($webinarsIds)) {
    //         $data['webinars'] = Webinar::select('id')
    //             ->whereIn('id', $webinarsIds)->get();
    //     }

    //     return view('admin.certificates.course_certificates', $data);
    // }


    public function index(Request $request)
    {
        $this->authorize('admin_course_certificate_list');
    
        // Start with querying the certificates that are either part of a webinar or a bundle.
        $query = Certificate::where(function ($query) {
            $query->whereNotNull('webinar_id')
                  ->orWhereNotNull('bundle_id');
        });
    
        // Apply filters (if any)
        $query = $this->filters($query, $request);
    
        // Eager load relationships: 'webinar', 'student', 'bundle', and 'teacher' (for webinars)
        $certificates = $query->with([
            'webinar',
            'student',
            'bundle',  // Eager load the 'bundle' relationship
            'webinar.teacher', // Eager load the teacher for webinars
        ])->orderBy('created_at', 'desc')
          ->paginate(10);
    
        // Prepare the data to pass to the view
        $data = [
            'pageTitle' => trans('update.competition_certificates'),
            'certificates' => $certificates,
        ];
    
        // Get additional filters from the request
        $teacher_ids = $request->get('teacher_ids');
        $student_ids = $request->get('student_ids');
        $webinarsIds = $request->get('webinars_ids');
        $bundleIds = $request->get('bundle_ids'); // New filter for bundles
    
        // If teacher_ids are provided, retrieve teachers
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')
                ->whereIn('id', $teacher_ids)->get();
        }
    
        // If student_ids are provided, retrieve students
        if (!empty($student_ids)) {
            $data['students'] = User::select('id', 'full_name')
                ->whereIn('id', $student_ids)->get();
        }
    
        // If webinars_ids are provided, retrieve webinars
        if (!empty($webinarsIds)) {
            $data['webinars'] = Webinar::select('id')
                ->whereIn('id', $webinarsIds)->get();
        }
    
        // If bundle_ids are provided, retrieve bundles
        if (!empty($bundleIds)) {
            $data['bundles'] = Bundle::select('id')
                ->whereIn('id', $bundleIds)->get();
        }
    
        // Pass the data to the view
        return view('admin.certificates.course_certificates', $data);
    }
    




    private function filters($query, $request)
    {
        $filters = $request->all();

        if (!empty($filters['student_ids'])) {
            $query->whereIn('student_id', $filters['student_ids']);
        }

        if (!empty($filters['teacher_ids'])) {
            $webinarsIds = Webinar::where(function ($query) use ($filters) {
                $query->whereIn('creator_id', $filters['teacher_ids']);
                $query->orWhereIn('teacher_id', $filters['teacher_ids']);
            })
                ->pluck('id')->toArray();

            if ($webinarsIds and is_array($webinarsIds)) {
                $query->whereIn('webinar_id', $webinarsIds);
            }
        }

        if (!empty($filters['webinars_ids'])) {
            $query->whereIn('webinar_id', $filters['webinars_ids']);
        }


        if (!empty($filters['bundle_ids'])) {
            $query->whereIn('bundle_id', $filters['bundle_ids']);
        }

        
       
        return $query;
    }

    public function show($certificateId)
    {
        $this->authorize('admin_course_certificate_list');

        $certificate = Certificate::findOrFail($certificateId);

        if ($certificate->type == 'course') {
            $makeCertificate = new MakeCertificate();

            return $makeCertificate->makeCourseCertificate($certificate);
        }

        return view('errors.404');
    }
}
