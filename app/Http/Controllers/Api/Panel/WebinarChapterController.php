<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Panel\WebinarController;
use App\Models\Api\Bundle;
use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarChapterResource;
use App\Models\Api\Webinar;
use App\Models\WebinarChapter;
use Illuminate\Http\Request;


use App\Exports\WebinarStudents;
use App\Models\Api\Certificate;
use App\Models\Category;
use App\Models\CourseNoticeboard;
use App\Models\FAQ;
use App\Models\File;
use App\Models\Prerequisite;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Tag;
use App\Models\TextLesson;
use App\Models\Ticket;
use App\User;
use App\Models\WebinarPartnerTeacher;
use App\Models\WebinarFilterOption;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class WebinarChapterController extends Controller
{
    public function index($webinar_id)
    {
        $chapters = WebinarChapter::where('webinar_id', $webinar_id)
            ->where('status', WebinarChapter::$chapterActive)
            ->orderBy('order', 'asc')
            ->with([
                'chapterItems' => function ($query) {
                    $query->orderBy('order', 'asc');
                }
            ])
            ->get();
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), WebinarChapterResource::collection($chapters));
    }

   
}
