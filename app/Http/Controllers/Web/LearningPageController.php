<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\LearningPageAssignmentTrait;
use App\Http\Controllers\Web\traits\LearningPageForumTrait;
use App\Http\Controllers\Web\traits\LearningPageItemInfoTrait;
use App\Http\Controllers\Web\traits\LearningPageMixinsTrait;
use App\Http\Controllers\Web\traits\LearningPageNoticeboardsTrait;
use App\Models\Bundle;
use App\Models\Certificate;
use App\Models\CourseNoticeboard;
use App\Models\Webinar;
use Illuminate\Http\Request;

class LearningPageController extends Controller
{
    use LearningPageMixinsTrait, LearningPageAssignmentTrait, LearningPageItemInfoTrait,
        LearningPageNoticeboardsTrait, LearningPageForumTrait;

    public function index(Request $request,Bundle $bundle=null, $id)
    {
        $requestData = $request->all();

        $webinarController = new WebinarController();

        $webinar = Webinar::findOrFail($id);
        $data = $webinarController->course($id, true);

        $course = $data['course'];
        $user = $data['user'];
        $itemId= $course->id;
        $itemName= 'webinar_id';

        if(empty($course->unattached) && !empty($bundle)){
            $itemId = $bundle->id;
            $itemName = "bundle_id";
        }

        $installmentLimitation = $webinarController->installmentContentLimitation($user, $itemId, $itemName);
        if ($installmentLimitation != "ok") {
            return $installmentLimitation;
        }


        if (!$data or (!$data['hasBought'] and empty($course->getInstallmentOrder()))) {
            abort(403);
        }

        if (!empty($requestData['type']) and $requestData['type'] == 'assignment' and !empty($requestData['item'])) {

            $assignmentData = $this->getAssignmentData($course, $requestData);

            $data = array_merge($data, $assignmentData);
        }

        if ($course->creator_id != $user->id and $course->teacher_id != $user->id and !$user->isAdmin() and !$course->isPartnerTeacher($user->id)) {
            $unReadCourseNoticeboards = CourseNoticeboard::where('webinar_id', $course->id)
                ->whereDoesntHave('noticeboardStatus', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->count();

            if ($unReadCourseNoticeboards) {
                $url = $course->getNoticeboardsPageUrl();

                return redirect($url);
            }
        }

        if ($course->certificate) {
            $data["courseCertificate"] = Certificate::where('type', 'course')
                ->where('student_id', $user->id)
                ->where('webinar_id', $course->id)
                ->first();
        }

        return view('web.default.course.learningPage.index', $data);
    }
}
