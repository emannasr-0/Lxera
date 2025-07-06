<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Bundle;
use App\Models\Certificate;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;

class WebinarCertificateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_course_certificate_list');

        $query = Certificate::where(function ($query) {
            $query->whereNotNull('webinar_id')
                ->orWhereNotNull('bundle_id');
        });

        $query = $this->filters($query, $request);

        $certificates = $query->with([
            'webinar',
            'student',
            'bundle',
            'webinar.teacher',
        ])->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'certificates' => $certificates,
        ];

        $teacher_ids = $request->get('teacher_ids');
        $student_ids = $request->get('student_ids');
        $webinarsIds = $request->get('webinars_ids');
        $bundleIds = $request->get('bundle_ids');

        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')
                ->whereIn('id', $teacher_ids)->get();
        }

        if (!empty($student_ids)) {
            $data['students'] = User::select('id', 'full_name')
                ->whereIn('id', $student_ids)->get();
        }

        if (!empty($webinarsIds)) {
            $data['webinars'] = Webinar::select('id')
                ->whereIn('id', $webinarsIds)->get();
        }

        if (!empty($bundleIds)) {
            $data['bundles'] = Bundle::select('id')
                ->whereIn('id', $bundleIds)->get();
        }

        return response()->json([
            'success' => true,
            'message' => $data
        ]);
    }

    private function filters($query, $request)
    {
        $filters = $request->all();

        // Search by student code
        if (!empty($filters['student_code'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('user_code', 'like', '%' . $filters['student_code'] . '%');
            });
        }

        // Search by student email
        if (!empty($filters['email'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('email', 'like', '%' . $filters['email'] . '%');
            });
        }

        // Existing filters
        if (!empty($filters['student_ids'])) {
            $query->whereIn('student_id', $filters['student_ids']);
        }

        if (!empty($filters['teacher_ids'])) {
            $webinarsIds = Webinar::where(function ($query) use ($filters) {
                $query->whereIn('creator_id', $filters['teacher_ids']);
                $query->orWhereIn('teacher_id', $filters['teacher_ids']);
            })
                ->pluck('id')->toArray();

            if ($webinarsIds && is_array($webinarsIds)) {
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

        abort(404);
    }
}
