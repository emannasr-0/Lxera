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

    // PUT/PATCH /api/chapters/{id}
    // public function update(Request $request, $id)
    // {
    //     $user = apiAuth();

    //     $chapter = WebinarChapter::with('webinar')->findOrFail($id);

   

    //     $data = $request->validate([
    //         'title'                   => ['nullable', 'string', 'max:255'],
    //         'status'                  => ['nullable', 'boolean'],
    //         'check_all_contents_pass' => ['nullable', 'boolean'],
    //         'locale'                  => ['nullable', 'string', 'max:10'],
    //     ]);

    //     $locale = mb_strtolower($data['locale'] ?? app()->getLocale());

    //     $chapter = DB::transaction(function () use ($chapter, $data, $locale) {
    //         // Update main chapter fields (only if provided)
    //         if (array_key_exists('status', $data)) {
    //             $chapter->status = $data['status']
    //                 ? WebinarChapter::$chapterActive
    //                 : WebinarChapter::$chapterInactive;
    //         }

    //         if (array_key_exists('check_all_contents_pass', $data)) {
    //             $chapter->check_all_contents_pass = (bool)$data['check_all_contents_pass'];
    //         }

    //         $chapter->save();

    //         // Update translation (only if title provided)
    //         if (!empty($data['title'])) {
    //             WebinarChapterTranslation::updateOrCreate(
    //                 [
    //                     'webinar_chapter_id' => $chapter->id,
    //                     'locale'             => $locale,
    //                 ],
    //                 [
    //                     'title'              => $data['title'],
    //                 ]
    //             );
    //         }

    //         return $chapter->load(['translations']);
    //     });

    //     $title = $chapter->translations
    //         ->firstWhere('pivot.locale', $locale)->title
    //         ?? $chapter->translations->first()->title
    //         ?? null;

    //     return response()->json([
    //         'message' => 'Chapter updated.',
    //         'data'    => [
    //             'id'        => $chapter->id,
    //             'webinar_id'=> $chapter->webinar_id,
    //             'status'    => $chapter->status,
    //             'check_all_contents_pass' => (bool)$chapter->check_all_contents_pass,
    //             'title'     => $title,
    //             'locale'    => $locale,
    //             'updated_at'=> $chapter->updated_at ?? null,
    //         ],
    //     ]);
    // }
 public function update($url_name, Request $request, $id)
{
    try {

        // ✅ Validate JSON body
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:10'],
            'status' => ['nullable', 'boolean'],                 // accepts 1/0 or true/false
            'check_all_contents_pass' => ['nullable', 'boolean'] // accepts 1/0 or true/false
        ]);

        $chapter = WebinarChapter::find($id);

        if (!$chapter) {
            return response()->json(['message' => 'Chapter not found'], 404);
        }

        // ✅ Convert boolean to your enum/status strings
        $status = !empty($data['status']) && $data['status']
            ? WebinarChapter::$chapterActive
            : WebinarChapter::$chapterInactive;

        // ✅ Update chapter fields
        $chapter->update([
            'status' => $status,
            'check_all_contents_pass' => (bool)($data['check_all_contents_pass'] ?? false),
        ]);

        // ✅ Update translation
        WebinarChapterTranslation::updateOrCreate(
            [
                'webinar_chapter_id' => $chapter->id,
                'locale' => mb_strtolower($data['locale']),
            ],
            [
                'title' => $data['title'],
            ]
        );

        removeContentLocale();

        return response()->json([
            'code' => 200,
            'chapter' => $chapter->fresh()->load('translations'),
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {

        $formattedErrors = [];
        $currentLocale = app()->getLocale();

        foreach ($e->errors() as $field => $messages) {
            $payload = $e->validator->getData();
            $rules = [$field => $e->validator->getRules()[$field]];

            app()->setLocale('ar');
            $arValidator = Validator::make($payload, $rules);
            $arMessage = $arValidator->errors()->first($field);

            app()->setLocale('en');
            $enValidator = Validator::make($payload, $rules);
            $enMessage = $enValidator->errors()->first($field);

            $formattedErrors[$field] = [
                'ar' => $arMessage,
                'en' => $enMessage
            ];
        }

        app()->setLocale($currentLocale);

        return response()->json([
            'message' => 'Validation failed',
            'errors' => $formattedErrors
        ], 422);
    }
}

    // DELETE /api/chapters/{id}
   public function destroy( Request $request, $id)
    {
        $chapter = WebinarChapter::where('id', $id)->first();

        if (!empty($chapter)) {
            $chapter->delete();
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Chapter Deleted Successfully'
        ], 200);
    }

    
}
