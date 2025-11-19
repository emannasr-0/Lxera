<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\File;
use App\Models\Quiz;
use App\Models\Session;
use App\Models\TextLesson;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChaptersController extends Controller
{
    public function getChapter($url_name, Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $chapter = WebinarChapter::where('id', $id)
            ->first();

        $locale = $request->get('locale', app()->getLocale());

        if (!empty($chapter)) {
            foreach ($chapter->translatedAttributes as $attribute) {
                try {
                    $chapter->$attribute = $chapter->translate(mb_strtolower($locale))->$attribute;
                } catch (\Exception $e) {
                    $chapter->$attribute = null;
                }
            }

            $data = [
                'chapter' => $chapter
            ];

            return response()->json($data, 200);
        }

        abort(403);
    }

    public function listChaptersByWebinarId($url_name, $webinarId)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $webinar = Webinar::with([
            'chapters.chapterItems.quiz',
            'chapters.chapterItems.assignment',
            'chapters.chapterItems.file',
            'chapters.chapterItems.session',
            'chapters.chapterItems.textLesson',
        ])->findOrFail($webinarId);

        $data = [
            'Webinar' => $webinar->id,
            'chapters' => $webinar->chapters
                ->sortBy('order') // Sort chapters by 'order'
                ->values()
                ->map(function ($chapter) {
                    return [
                        'chapter' => $chapter->only(['id', 'title', 'status', 'created_at', 'order', 'translations']),
                        'items' => $chapter->chapterItems
                            ->sortBy(function ($item) {
                                return is_null($item->order) ? PHP_INT_MAX : $item->order;
                            })
                            ->values()
                            ->map(function ($item) {
                                $details = null;

                                switch ($item->type) {
                                    case WebinarChapterItem::$chapterQuiz:
                                        $details = $item->quiz;
                                        break;
                                    case WebinarChapterItem::$chapterAssignment:
                                        $details = $item->assignment;
                                        break;
                                    case WebinarChapterItem::$chapterFile:
                                        $details = $item->file;
                                        break;
                                    case WebinarChapterItem::$chapterSession:
                                        $details = $item->session;
                                        break;
                                    case WebinarChapterItem::$chapterTextLesson:
                                        $details = $item->textLesson;
                                        break;
                                }

                                return [
                                    'type' => $item->type,
                                    'order' => $item->order,
                                    'created_at' => $item->created_at,
                                    'details' => $details,
                                ];
                            }),

                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            $validator = Validator::make($data, [
                'webinar_id' => 'required|exists:webinars,id',
                //'type' => 'required|' . Rule::in(WebinarChapter::$chapterTypes),
                'title' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (!empty($data['webinar_id'])) {
                $webinar = Webinar::where('id', $data['webinar_id'])->first();

                if (!empty($webinar)) {
                    $teacher = $webinar->creator;
                    $status = (!empty($data['status']) and $data['status'] == 'on') ? WebinarChapter::$chapterActive : WebinarChapter::$chapterInactive;


                    $lastOrder = WebinarChapter::where('webinar_id', $webinar->id)->max('order') ?? 0;
                    $newOrder = $lastOrder + 1;

                    $chapter = WebinarChapter::create([
                        'user_id' => $teacher->id,
                        'webinar_id' => $webinar->id,
                        //'type' => $data['type'],
                        'title' => $data['title'],
                        'status' => $status,
                        'check_all_contents_pass' => (!empty($data['check_all_contents_pass']) and $data['check_all_contents_pass'] == 'on'),
                        'order' => $newOrder, // ✅ حطينا الترتيب هنا
                        'created_at' => time(),
                    ]);

                    if (!empty($chapter)) {
                        WebinarChapterTranslation::updateOrCreate([
                            'webinar_chapter_id' => $chapter->id,
                            'locale' => mb_strtolower($data['locale']),
                        ], [
                            'title' => $data['title'],
                        ]);
                    }

                    return response()->json([
                        'code' => 200,
                        'chapter' =>  $chapter,
                    ], 200);
                }
            }

            return response()->json([], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
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

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $chapter = WebinarChapter::where('id', $id)->first();

        if (!empty($chapter)) {
            $locale = $request->get('locale', app()->getLocale());
            if (empty($locale)) {
                $locale = app()->getLocale();
            }
            storeContentLocale($locale, $chapter->getTable(), $chapter->id);

            $chapter->title = $chapter->getTitleAttribute();
            $chapter->locale = mb_strtoupper($locale);

            return response()->json([
                'chapter' => $chapter
            ], 200);
        }

        return response()->json([], 422);
    }

    public function update($url_name, Request $request, $id)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $data = $request->all();

            $this->validate($request, [
                'title' => 'required|string|max:255',
                'locale' => 'required|string',
            ]);

            $data = $request->all();

            $chapter = WebinarChapter::find($id);

            if (!$chapter) {
                return response()->json(['message' => 'Chapter not found'], 404);
            }

            $status = (!empty($data['status']) && $data['status'] == 'on')
                ? WebinarChapter::$chapterActive
                : WebinarChapter::$chapterInactive;

            $chapter->update([
                'check_all_contents_pass' => (!empty($data['check_all_contents_pass']) && $data['check_all_contents_pass'] == 'on'),
                'status' => $status,
            ]);

            WebinarChapterTranslation::updateOrCreate([
                'webinar_chapter_id' => $chapter->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
            ]);

            removeContentLocale();

            return response()->json([
                'code' => 200,
                'chapter' => $chapter,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
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

    public function destroy($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $chapter = WebinarChapter::where('id', $id)->first();

        if (!empty($chapter)) {
            $chapter->delete();
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Chapter Deleted Successfully'
        ], 200);
    }

    public function change(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id'    => 'required',
            'item_type'  => 'required',
            'chapter_id' => 'required',
            'webinar_id' => 'required',
            'order'      => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response([
                'code'   => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = null;

        $webinar = Webinar::find($data['webinar_id']);

        if (!empty($webinar)) {
            switch ($data['item_type']) {
                case WebinarChapterItem::$chapterSession:
                    $item = Session::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;

                case WebinarChapterItem::$chapterFile:
                    $item = File::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;

                case WebinarChapterItem::$chapterTextLesson:
                    $item = TextLesson::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;

                case WebinarChapterItem::$chapterQuiz:
                    $item = Quiz::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;

                case WebinarChapterItem::$chapterAssignment:
                    $item = WebinarAssignment::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;
            }
        }

        if (!empty($item)) {

            // Update item chapter
            $item->update([
                'chapter_id' => !empty($data['chapter_id']) ? $data['chapter_id'] : null
            ]);

            // Remove existing link
            WebinarChapterItem::where('item_id', $item->id)
                ->where('type', $data['item_type'])
                ->delete();

            // Add new link
            if (!empty($data['chapter_id'])) {
                $chapterItem = WebinarChapterItem::makeItem(
                    $item->creator_id,
                    $data['chapter_id'],
                    $item->id,
                    $data['item_type']
                );

                // Save initial order if provided
                if (!empty($data['order'])) {
                    $chapterItem->order = $data['order'];
                    $chapterItem->save();
                }
            }

            /*
        |--------------------------------------------------------------------------
        | إعادة ترتيب العناصر داخل الـ chapter (نفس منطق orderChapters)
        |--------------------------------------------------------------------------
        */
            if (!empty($data['chapter_id'])) {

                // Get all items in this chapter
                $items = WebinarChapterItem::where('chapter_id', $data['chapter_id'])
                    ->orderBy('order')
                    ->get();

                $orderedItems = [];
                $newOrder = $data['order'] ?? 1;

                // Remove current item to re-insert it later
                foreach ($items as $i) {
                    if ($i->id != $chapterItem->id) {
                        $orderedItems[] = $i;
                    }
                }

                // Insert item into the new index
                array_splice($orderedItems, $newOrder - 1, 0, [$chapterItem]);

                // Re-assign ordering sequentially
                foreach ($orderedItems as $index => $itm) {
                    $itm->order = $index + 1;
                    $itm->save();
                }
            }
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Item moved successfully',
            'data'    => [
                'item_id'    => $item->id,
                'item_type'  => $data['item_type'],
                'webinar_id' => $data['webinar_id'],
                'chapter_id' => $item->chapter_id,
                'order'      => $data['order'] ?? null
            ]
        ], 200);
    }

    public function orderChapters($url_name, Request $request, $webinarId)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'chapters' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $webinar = Webinar::find($webinarId);
        if (!$webinar) {
            return response()->json(['message' => 'Webinar not found'], 404);
        }

        $chapterIds = array_filter(array_unique($data['chapters']));

        if (!empty($chapterIds)) {
            foreach ($chapterIds as $order => $id) {
                WebinarChapter::where('id', $id)
                    ->where('webinar_id', $webinar->id)
                    ->update(['order' => ($order + 1)]);
            }
        }

        return response()->json([
            'msg' => "success",
            'title' => trans('public.request_success')
        ]);
    }
}
