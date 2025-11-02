<?php

namespace App\Http\Controllers\Api\Instructor;


use App\Models\Translation\WebinarChapterTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Http\Controllers\Api\Controller;


class WebinarChapterController extends Controller
{
    // POST /api/chapters
    public function store(Request $request)
    {
        //dd($request->all());
        $user = apiAuth(); // works with Sanctum: auth:sanctum

        // Expect a flat JSON body:
        // { "webinar_id": 1, "title": "Intro", "status": true, "check_all_contents_pass": false, "locale": "en" }
        $data = $request->validate([
            'webinar_id'             => ['required', 'integer', 'exists:webinars,id'],
            'title'                  => ['required', 'string', 'max:255'],
            // 'type'                 => ['required', Rule::in(WebinarChapter::$chapterTypes)],
            'status'                 => ['nullable', 'boolean'], // true/false instead of 'on'
            'check_all_contents_pass'=> ['nullable', 'boolean'],
            'locale'                 => ['nullable', 'string', 'max:10'],
        ]);

        $webinar = Webinar::findOrFail($data['webinar_id']);

        // Authorization (use your policy/gate). If you must keep canAccess:
        if (method_exists($webinar, 'canAccess') && !$webinar->canAccess($user)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        // or: $this->authorize('update', $webinar);

        $status = !empty($data['status']) && $data['status']
            ? WebinarChapter::$chapterActive
            : WebinarChapter::$chapterInactive;

        $locale = mb_strtolower($data['locale'] ?? app()->getLocale());

        $chapter = DB::transaction(function () use ($user, $webinar, $data, $status, $locale) {
            $chapter = WebinarChapter::create([
                'user_id'                => $user->id,
                'webinar_id'             => $webinar->id,
                // 'type'                 => $data['type'],
                'status'                 => $status,
                'check_all_contents_pass'=> (bool)($data['check_all_contents_pass'] ?? false),
        'created_at'             => time(),

                // Don’t set created_at manually; Eloquent timestamps will handle it
            ]);

            WebinarChapterTranslation::updateOrCreate(
                [
                    'webinar_chapter_id' => $chapter->id,
                    'locale'             => $locale,
                ],
                [
                    'title'              => $data['title'],
                ]
            );

            return $chapter->load(['translations']); // adjust relation name if different
        });

        return response()->json([
            'message' => 'Chapter created.',
            'data'    => [
                'id'        => $chapter->id,
                'webinar_id'=> $chapter->webinar_id,
                'status'    => $chapter->status,
                'check_all_contents_pass' => (bool)$chapter->check_all_contents_pass,
                'title'     => $chapter->translations->firstWhere('pivot.locale', $locale)->title
                                ?? $data['title'],
                'locale'    => $locale,
                'created_at'=> $chapter->created_at,
            ],
        ], 201); // 201 Created
    }


    
}
