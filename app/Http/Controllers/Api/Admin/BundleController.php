<?php

namespace App\Http\Controllers\Api\Admin;

use App\BundleStudent;
use App\Exports\BundleStatisticsExport;
use App\Exports\SingleBundleUserCodesExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\WebinarStatisticController;
use App\Mail\SendNotifications;
use App\Models\Api\Organization;
use App\Models\Api\Plan;
use App\Models\CertificateTemplate;
use App\Models\StudyClass;
use App\Models\Bundle;
use App\Models\BundleFilterOption;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Gift;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SpecialOffer;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\Translation\BundleTranslation;
use App\Models\Webinar;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BundleController extends Controller
{
    public function index($url_name, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_bundles_list');

        removeContentLocale();

        $teacherNameFilter = $request->input('teacher_name');
        $type = $request->get('type', 'program');
        $query = Bundle::where('bundles.type', $type);

        $totalBundles = $query->count();
        $totalPendingBundles = deepClone($query)->where('bundles.status', Bundle::$pending)->count();
        $totalSales = deepClone($query)->join('sales', 'bundles.id', '=', 'sales.bundle_id')
            ->select(DB::raw('count(sales.bundle_id) as sales_count, sum(total_amount) as total_amount'))
            ->whereNotNull('sales.bundle_id')
            ->whereNull('sales.refund_at')
            ->first();

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $query = $this->handleFilters($query, $request)
            ->with([
                'category',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                // 'sales' => function ($query) {
                //     $query->whereNull('refund_at');
                // }
            ])
            ->withCount([
                'bundleWebinars'
            ]);

        if (!empty($teacherNameFilter)) {
            $query->whereHas('teacher', function ($query) use ($teacherNameFilter) {
                $query->filterBySearch(['full_name' => $teacherNameFilter]);
            });
        }
        
        
        $bundles = $query->with('batch')->paginate(10);

        foreach ($bundles as $bundle) {
            $giftsIds = Gift::query()->where('bundle_id', $bundle->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                // ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            // $sales = Sale::query()
            //     ->where(function ($query) use ($bundle, $giftsIds) {
            //         $query->where('bundle_id', $bundle->id);
            //         $query->orWhereIn('gift_id', $giftsIds);
            //     })
            //     ->whereNull('refund_at')
            //     ->get();

            // $bundle->sales = $sales;
            
              foreach ($bundle->translations as $translation) {
            $translation->description = strip_tags($translation->description);
        }
        }

        $batches = StudyClass::get();

        $data = [
            'bundles' => $bundles,
            'batches' => $batches,
            'totalBundles' => $totalBundles,
            'totalPendingBundles' => $totalPendingBundles,
            'totalSales' => $totalSales,
            'categories' => $categories,
        ];

        $teacher_ids = $request->get('teacher_ids', null);
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }

        return response()->json($data);
    }

    private function handleFilters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $title = $request->get('title', null);
        $teacher_ids = $request->get('teacher_ids', null);
        $category_id = $request->get('category_id', null);
        $status = $request->get('status', null);
        $sort = $request->get('sort', null);
        $batch = $request->get('batch', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($title)) {
            $query->whereTranslationLike('title', '%' . $title . '%');
        }

        if (!empty($teacher_ids) and count($teacher_ids)) {
            $query->whereIn('teacher_id', $teacher_ids);
        }

        if (!empty($category_id)) {
            $query->where('category_id', $category_id);
        }

        if (!empty($status)) {
            $query->where('bundles.status', $status);
        }
        if (!empty($batch)) {
            $query->where('batch_id', $batch);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'has_discount':
                    $now = time();
                    $bundleIdsHasDiscount = [];

                    $tickets = Ticket::where('start_date', '<', $now)
                        ->where('end_date', '>', $now)
                        ->get();

                    foreach ($tickets as $ticket) {
                        if ($ticket->isValid()) {
                            $bundleIdsHasDiscount[] = $ticket->bundle_id;
                        }
                    }

                    $specialOffersBundleIds = SpecialOffer::where('status', 'active')
                        ->where('from_date', '<', $now)
                        ->where('to_date', '>', $now)
                        ->pluck('bundle_id')
                        ->toArray();

                    $bundleIdsHasDiscount = array_merge($specialOffersBundleIds, $bundleIdsHasDiscount);

                    $query->whereIn('id', $bundleIdsHasDiscount)
                        ->orderBy('created_at', 'desc');
                    break;
                case 'sales_asc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.refund_at', DB::raw('count(sales.bundle_id) as sales_count'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_desc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.refund_at', DB::raw('count(sales.bundle_id) as sales_count'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('sales_count', 'desc');
                    break;

                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'income_asc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('amounts', 'asc');
                    break;

                case 'income_desc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('amounts', 'desc');
                    break;

                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'updated_at_asc':
                    $query->orderBy('updated_at', 'asc');
                    break;

                case 'updated_at_desc':
                    $query->orderBy('updated_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }


        return $query;
    }

    public function store(Request $request)
    {
        try {
            // $bundlesCount = Bundle::count();

            // $plan = Plan::where('is_active', 1)->get();

            // if ($bundlesCount >= $plan->max_bundles) {
            //     return response()->json([
            //         'msg' => 'Sorry, you have reached the maximum number of bundles allowed for your subscription plan.'
            //     ], 403);
            // }

            $this->authorize('admin_bundles_create');
            $type = $request->get('type', 'program');
            $rules = [
                'title' => 'required|string|max:255',
                'bundle_name_certificate' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                // 'slug' => 'max:255|unique:bundles,slug',
                'thumbnail' => 'nullable',
                'image_cover' => 'nullable',
                'description' => 'required',
                'teacher_id' => 'required|exists:users,id',
                'category_id' => 'required',
                'batch_id' => 'required|exists:study_classes,id',
                'price' => 'required',
            ];
            $this->validate($request, $rules);

            if ($type == 'bridging') {
                $rules['from_bundle_id'] = "required|array";
                $rules['from_bundle_id.*'] = "required|exists:bundles,id";
                // $rules['to_bundle_id'] ="required|exists:bundles,id";
            }
            $this->validate($request, $rules);

            if (!in_array($type, ['program', 'bridging'])) {
                abort(404);
            }

            $data = $request->all();

            if (empty($data['slug'])) {
                $data['slug'] = Bundle::makeSlug($data['title']) . '_' . Str::random(5);
            }

            if (empty($data['video_demo'])) {
                $data['video_demo_source'] = null;
            }

            if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                $data['video_demo_source'] = 'upload';
            }
            if (!empty($data['start_date'])) {
                if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                    $data['timezone'] = getTimezone();
                }

                $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

                $data['start_date'] = $startDate->getTimestamp();
            }

            if (!empty($data['end_date'])) {
                if (empty($data['timezone']) || !getFeaturesSettings('timezone_in_create_webinar')) {
                    $data['timezone'] = getTimezone();
                }

                $endDate = convertTimeToUTCzone($data['end_date'], $data['timezone']);
                $data['end_date'] = $endDate->getTimestamp();
            }


            if ($request->hasFile('content_table')) {
                $file = $request->file('content_table');
                $originalName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads');
                $file->move($destinationPath, $originalName);
                $data['content_table'] = config('app.url') . 'uploads/' . $originalName; // Adjust the URL as needed

            } else {

                $data['content_table'] = $request->input('content_table');
            }

            if ($request->hasFile('academic_guide')) {
                $file = $request->file('academic_guide');
                $originalName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads');
                $file->move($destinationPath, $originalName);
                $data['academic_guide'] = config('app.url') . 'uploads/' . $originalName; // Adjust the URL as needed

            }

            // dd($request->input('academic_guide'));

            $bundle = Bundle::create([
                'slug' => $data['slug'],
                'bundle_name_certificate' => $data['bundle_name_certificate'],
                'teacher_id' => $data['teacher_id'],
                'creator_id' => $data['teacher_id'],
                'thumbnail' => $data['thumbnail'] ?? "bridging",
                'image_cover' => $data['image_cover'] ?? "bridging",
                'video_demo' => $data['video_demo'] ?? null,
                'video_demo_source' => !empty($data['video_demo']) ? ($data['video_demo_source'] ?? 'upload') : null,
                'subscribe' => !empty($data['subscribe']) ? true : false,
                'points' => $data['points'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'price' => $data['price'],
                'access_days' => $data['access_days'] ?? null,
                'category_id' => $data['category_id'],
                'certificate_template_id' => $data['certificate_template_id'] ?? null,
                'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
                'status' => $data['status'] ?? Bundle::$pending,
                'created_at' => time(),
                'updated_at' => time(),
                'has_certificate' => $data['has_certificate'] ?? 0,
                'hasGroup' => $data['hasGroup'] ?? 0,
                'content_table' => $data['content_table'] ?? null,
                'academic_guide' => $data['academic_guide'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
                'type' => $data['type'] ?? 'program',
                'partner_instructor' => !empty($data['partner_instructor']) ? true : false,
            ]);


            if ($bundle) {
                $studentIds = $request->input('student_id', []); // Get selected student IDs
                if (!empty($studentIds)) {
                    $bundle->studentsExcluded()->attach($studentIds);
                }

                $courseIds = $request->input('course_id', []);
                if (!empty($courseIds)) {
                    $bundle->bundleProfessionalWebinars()->attach($courseIds); // Or use pivot data if needed
                }

                BundleTranslation::updateOrCreate([
                    'bundle_id' => $bundle->id,
                    'locale' => mb_strtolower($data['locale'] ?? app()->getLocale()),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'seo_description' => $data['seo_description'] ?? null,
                ]);

                if (!empty($request['from_bundle_id'])) {
                    $bundle->bridgingBundles()->sync($request->input('from_bundle_id', []));
                }
                if (!empty($request['additions_bundle_id'])) {
                    $bundle->additionBundles()->sync($request->input('additions_bundle_id', []));
                }

                if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {

                    $bundle->PartnerTeachers()->sync($request->get('partners', []));
                }
            }

            $filters = $request->get('filters', null);
            if (!empty($filters) and is_array($filters)) {
                BundleFilterOption::where('bundle_id', $bundle->id)->delete();

                foreach ($filters as $filter) {
                    BundleFilterOption::create([
                        'bundle_id' => $bundle->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }

            if (!empty($request->get('tags'))) {
                $tags = explode(',', $request->get('tags'));
                Tag::where('bundle_id', $bundle->id)->delete();

                foreach ($tags as $tag) {
                    Tag::create([
                        'bundle_id' => $bundle->id,
                        'title' => $tag,
                    ]);
                }
            }
            // removeContentLocale();
            return response()->json([
                'status' => 'success',
                'msg' => 'Bundle Created Successfully',
                'data' => $bundle
            ], 201);
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
        $this->authorize('admin_bundles_edit');

        $bundle = Bundle::where('id', $id)
            ->with([
                'tickets',
                'faqs',
                'category' => function ($query) {
                    $query->with(['filters' => function ($query) {
                        $query->with('options');
                    }]);
                },
                'certificate_template',
                'tags',
                'bundleWebinars'
            ])
            ->first();

        if (empty($bundle)) {
            abort(404);
        }

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $bundle->getTable(), $bundle->id);

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();
        $study_classes = StudyClass::get();

        $certificates = CertificateTemplate::where('type', 'bundle')->get();

        $tags = $bundle->tags->pluck('title')->toArray();

        $userIds = [$bundle->creator_id, $bundle->teacher_id];
        $userWebinars = Webinar::select('id', 'creator_id', 'teacher_id')
            // ->where('status', Webinar::$active)
            // ->where('private', false)
            // ->where('category_id',$bundle->category_id)
            // ->where(function ($query) use ($userIds) {
            //     $query->whereIn('creator_id', $userIds)
            //         ->orWhereIn('teacher_id', $userIds);
            // })
            ->get();
        $students = Student::get();
        $webinars = Webinar::get();

        $studentsForBundle = $bundle->students;


        $data = [
            'pageTitle' => trans('admin/main.edit') . ' | ' . $bundle->title,
            'userWebinars' => $userWebinars,
            'categories' => $categories,
            'certificates' => $certificates,
            'bundle' => $bundle,
            'bundleCategoryFilters' => !empty($bundle->category) ? $bundle->category->filters : null,
            'bundleFilterOptions' => $bundle->filterOptions->pluck('filter_option_id')->toArray(),
            'tickets' => $bundle->tickets,
            'faqs' => $bundle->faqs,
            'bundleTags' => $tags,
            'bundleWebinars' => $bundle->bundleWebinars,
            'study_classes' => $study_classes,
            'students' => $students,
            'studentsForBundles' => $studentsForBundle,
            'bundlePartnerTeacher' => $bundle->bundlePartnerTeacher,
            'webinars' => $webinars,
        ];

        return view('admin.bundles.create', $data);
    }

    public function update($url_name, Request $request, $id)
    {
        try {
            $organization = Organization::where('url_name', $url_name);
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $this->authorize('admin_bundles_edit');

            $data = $request->all();

            $bundle = Bundle::find($id);
            $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
            $reject = (!empty($data['draft']) and $data['draft'] == 'reject');
            $publish = (!empty($data['draft']) and $data['draft'] == 'publish');

            $rules = [
                'title' => 'sometimes|max:255',
                'bundle_name_certificate' => 'sometimes',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                // 'slug' => 'max:255|unique:bundles,slug,' . $bundle->id,
                'thumbnail' => 'sometimes',
                'image_cover' => 'sometimes',
                'description' => 'sometimes',
                'teacher_id' => 'sometimes|exists:users,id',
                'category_id' => 'sometimes',
                'batch_id' => 'sometimes',
                'type' => 'sometimes|in:program,bridging',
                'price' => 'sometimes',
                'content_table' => 'sometimes|string|nullable',
                'academic_guide' => 'sometimes|string|nullable',

            ];

            $this->validate($request, $rules);

            if (!empty($data['teacher_id'])) {
                $teacher = User::findOrFail($data['teacher_id']);
                $creator = $bundle->creator;

                if (empty($teacher) or ($creator->isOrganization() and ($teacher->organ_id != $creator->id and $teacher->id != $creator->id))) {
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => trans('admin/main.is_not_the_teacher_of_this_organization'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }
            }

            if (empty($data['slug'])) {
                $data['slug'] = !empty($data['title'])
                    ? Bundle::makeSlug($data['title']) . '_' . Str::random(5)
                    : $bundle->slug;
            }

            if ($request->hasFile('content_table')) {
                $file = $request->file('content_table');
                $originalName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads');
                $file->move($destinationPath, $originalName);
                $data['content_table'] = config('app.url') . 'uploads/' . $originalName;
            } else {
                $data['content_table'] = $bundle->content_table;
            }

            if ($request->hasFile('academic_guide')) {
                $file = $request->file('academic_guide');
                $originalName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads');
                $file->move($destinationPath, $originalName);
                $data['academic_guide'] = config('app.url') . 'uploads/' . $originalName;
            }
            // if ($request->hasFile('academic_guide')) {
            //     $file = $request->file('academic_guide');
            //     // Proceed with file handling here
            // } else {
            //     // File wasn't uploaded
            //     dd('No file uploaded.');
            // }
            // dd($request->input('academic_guide'));
            // $data['status'] = $publish ? Bundle::$active : ($reject ? Bundle::$inactive : ($isDraft ? Bundle::$isDraft : Bundle::$pending));
            $data['updated_at'] = time();
            $data['subscribe'] = !empty($data['subscribe']) ? true : false;

            if (array_key_exists('category_id', $data) && $data['category_id'] !== $bundle->category_id) {
                BundleFilterOption::where('bundle_id', $bundle->id)->delete();
            }

            $filters = $request->get('filters', null);
            if (!empty($filters) and is_array($filters)) {
                BundleFilterOption::where('bundle_id', $bundle->id)->delete();

                foreach ($filters as $filter) {
                    BundleFilterOption::create([
                        'bundle_id' => $bundle->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }

            if (!empty($request->get('tags'))) {
                $tags = explode(',', $request->get('tags'));
                Tag::where('bundle_id', $bundle->id)->delete();

                foreach ($tags as $tag) {
                    Tag::create([
                        'bundle_id' => $bundle->id,
                        'title' => $tag,
                    ]);
                }
            }

            unset(
                $data['_token'],
                $data['current_step'],
                $data['draft'],
                $data['get_next'],
                $data['partners'],
                $data['tags'],
                $data['filters'],
                $data['ajax']
            );

            if (empty($data['video_demo'])) {
                $data['video_demo_source'] = null;
            }

            if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                $data['video_demo_source'] = 'upload';
            }
            if (!empty($data['start_date'])) {
                if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                    $data['timezone'] = getTimezone();
                }

                $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

                $data['start_date'] = $startDate->getTimestamp();
            } else {
                $data['start_date'] = null;
            }

            if (!empty($data['end_date'])) {
                if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                    $data['timezone'] = getTimezone();
                }

                $startDate = convertTimeToUTCzone($data['end_date'], $data['timezone']);

                $data['end_date'] = $startDate->getTimestamp();
            } else {
                $data['end_date'] = null;
            }


            $bundle->update([
                'slug' => $data['slug'],
                'bundle_name_certificate' => $data['bundle_name_certificate'] ?? $bundle->bundle_name_certificate,
                'teacher_id' => $data['teacher_id'] ?? $bundle->teacher_id,
                'thumbnail' => $data['thumbnail'] ?? $bundle->thumbnail,
                'image_cover' => $data['image_cover'] ?? $bundle->image_cover,
                'video_demo' => array_key_exists('video_demo', $data) ? $data['video_demo'] : $bundle->video_demo,
                'video_demo_source' => (!empty($data['video_demo'])) ? $data['video_demo_source'] ?? null : null,
                'subscribe' => $data['subscribe'],
                'points' => $data['points'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'price' => $data['price'] ?? $bundle->price,
                'access_days' => $data['access_days'] ?? null,
                'category_id' => $data['category_id'] ?? $bundle->category_id,
                'certificate_template_id' => $data['certificate_template_id'] ?? null,
                'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
                'status' => $data['status'] ?? $bundle->status,
                'updated_at' => time(),
                'has_certificate' => $data['has_certificate'] ?? $bundle->has_certificate,
                'hasGroup' => $data['hasGroup'] ?? 0,
                'content_table' => $data['content_table'] ?? null,
                'academic_guide' => $data['academic_guide'] ?? null,
                'type' => $data['type'] ??  $bundle->type,
                'partner_instructor' => !empty($data['partner_instructor']) ? true : false,
            ]);

            if ($bundle) {
                $studentIds = $request->input('student_id', []); // Get selected student IDs


                $bundle->studentsExcluded()->sync($studentIds);

                $courseIds = $request->input('course_id', []);

                $bundle->bundleProfessionalWebinars()->sync($courseIds); // Or use pivot data if needed

                Certificate::where('bundle_id', $bundle->id)->whereHas('student', function ($query) use ($studentIds) {
                    $query->whereHas('student', function ($q) use ($studentIds) {
                        $q->whereIn('id', $studentIds);
                    });
                })->delete();

                BundleTranslation::updateOrCreate([
                    'bundle_id' => $bundle->id,
                    'locale' => mb_strtolower($data['locale'] ?? app()->getLocale()),
                ], [
                    'title' => $request->input('title', $bundle->title),
                    'description' => $request->input('description', $bundle->description),
                    'seo_description' => $request->input('seo_description', $bundle->seo_description),
                ]);


                if ($bundle->type == 'bridging') {
                    $bundle->bridgingBundles()->sync($request->input('from_bundle_id', []));
                }

                if (empty($data['partner_instructor'])) {
                    unset($data['partners']);
                    unset($request['partners']);
                }
                $bundle->PartnerTeachers()->sync($request->get('partners', []));

                $bundle->additionBundles()->sync($request->input('additions_bundle_id', []));
            }

            removeContentLocale();

            $toastData = [
                'status' => 'success',
                'msg' => 'تم التعديل بنجاح',
                'data' => $bundle
            ];
            return response()->json($toastData);
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

    public function destroy($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name);
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_bundles_delete');

        $bundle = Bundle::find($id);

        if (!empty($bundle)) {
            $bundle->delete();
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Bundle deleted successfully'
        ]);
    }

    public function studentsLists($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_webinar_students_lists');

        $filters = $request->only(['email', 'user_code', 'status']);

        $bundle = Bundle::where('id', $id)
            ->with([
                'teacher:id,full_name',
                'bundleWebinars.webinar'
            ])
            ->first();

        if (empty($bundle)) {
            abort(404);
        }

        $giftsIds = Gift::query()->where('bundle_id', $bundle->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('date')
                    ->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->pluck('id')
            ->toArray();

        $bundleUsersIds = $bundle->bundleSalesBridging->pluck('buyer_id');

        $query = User::whereIn('id', $bundleUsersIds)
            ->filterBySearch($filters);

        $query = $this->studentsListsFilters($bundle, $query, $request);

        $totalStudents = $query->count();

        $totalExpireStudents = 0;
        if (!empty($bundle->access_days)) {
            $accessTimestamp = $bundle->access_days * 24 * 60 * 60;

            $totalExpireStudents = User::join('sales', 'sales.buyer_id', 'users.id')
                ->where(function ($q) use ($bundle, $giftsIds) {
                    $q->where('sales.bundle_id', $bundle->id)
                        ->orWhereIn('sales.gift_id', $giftsIds);
                })
                ->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()])
                ->whereNull('sales.refund_at')
                ->count();
        }

        $students = $query
            ->with([
                'student.bundleStudent' => function ($q) use ($bundle) {
                    $q->where('bundle_id', $bundle->id);
                }
            ])
            ->orderBy('users.created_at', 'desc')
            ->paginate(10);

        $webinarStatisticController = new WebinarStatisticController();

        $students->getCollection()->transform(function ($student) use ($bundle, $webinarStatisticController) {
            // Calculate learning
            $learnings = 0;
            $webinarCount = 0;
            foreach ($bundle->bundleWebinars as $bundleWebinar) {
                if (!empty($bundleWebinar->webinar)) {
                    $webinarCount++;
                    $learnings += $webinarStatisticController
                        ->getCourseProgressForStudent($bundleWebinar->webinar, $student->id);
                }
            }
            $learning = ($webinarCount > 0) ? round($learnings / $webinarCount, 2) : 0;

            $status = '';
            if (empty($student->id)) {
                // Gift recipient who has not registered yet
                $status = 'unregistered';
            } elseif (!empty($bundle->access_days) && !$bundle->checkHasExpiredAccessDays($student->purchase_date, $student->gift_id)) {
                $status = 'expired';
            } else {
                $status = 'active';
            }

            return [
                'id'          => $student->id,
                'full_name'   => $student->full_name,
                'created_at'  => \Carbon\Carbon::parse($student->created_at)->toDateString(),
                'role_name'   => $student->role_name,
                'mobile'      => $student->mobile,
                'email'       => $student->email,
                'user_code'   => $student->user_code,
                'learning'    => $learning,
                'gpa'         => optional($student->student->bundleStudent->first())->gpa ?? "--",
                'rate'        => optional($student->webinarReviews->first())->rates ?? 0,
                'ar_name'     => $student->student->ar_name ?? null,
                'en_name'     => $student->student->en_name ?? null,
                'bundle_join_date' => optional($student->student->bundleStudent->first())->created_at
                    ? \Carbon\Carbon::parse(optional($student->student->bundleStudent->first())->created_at)->toDateString()
                    : null,
                'status'      => $status,
            ];
        });

        $statusFilter = $request->get('status');

        $students->setCollection(
            $students->getCollection()->filter(function ($student) use ($statusFilter) {
                if (!$statusFilter) return true;
                return $student['status'] === $statusFilter;
            })->values()
        );

        $data = [
            'totalStudents' => $totalStudents,
            'totalActiveStudents' => $totalStudents - $totalExpireStudents,
            'totalExpireStudents' => $totalExpireStudents,
            'students' => $students,
            'bundle' => $bundle,
            'userGroups' => Group::where('status', 'active')->orderBy('created_at', 'desc')->get(),
        ];

        return response()->json($data);
    }

    private function studentsListsFilters($bundle, $query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $full_name = $request->get('full_name');
        $sort = $request->get('sort');
        $group_id = $request->get('group_id');
        $role_id = $request->get('role_id');
        $status = $request->get('status');

        $query = fromAndToDateFilter($from, $to, $query, 'sales.created_at');

        if (!empty($full_name)) {
            $query->where('users.full_name', 'like', "%$full_name%");
        }

        if (!empty($sort)) {
            if ($sort == 'rate_asc') {
                $query->orderBy('webinar_reviews.rates', 'asc');
            }

            if ($sort == 'rate_desc') {
                $query->orderBy('webinar_reviews.rates', 'desc');
            }
        }

        if (!empty($group_id)) {
            $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();

            $query->whereIn('users.id', $userIds);
        }

        if (!empty($role_id)) {
            $query->where('users.role_id', $role_id);
        }

        if (!empty($status)) {
            if ($status == 'expire' and !empty($bundle->access_days)) {
                $accessTimestamp = $bundle->access_days * 24 * 60 * 60;

                $query->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()]);
            }
        }

        return $query;
    }

    public function notificationToStudents($id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $bundle = Bundle::findOrFail($id);

        $data = [
            'pageTitle' => trans('notification.send_notification'),
            'bundle' => $bundle
        ];

        return view('admin.bundles.send-notification-to-course-students', $data);
    }


    public function sendNotificationToStudents($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_webinar_notification_to_students');

        $this->validate($request, [
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        $bundle = Bundle::where('id', $id)
            ->with([
                'sales' => function ($query) {
                    $query->with(['buyer']);
                }
            ])->first();

        if ($bundle->sales->isEmpty()) {
            return response()->json([
                'status' => 'warning',
                'msg' => 'No students found to notify for this bundle'
            ]);
        }

        if (!empty($bundle)) {
            foreach ($bundle->sales as $sale) {
                if (!empty($sale->buyer)) {
                    $user = $sale->buyer;

                    Notification::create([
                        'user_id' => $user->id,
                        'group_id' => null,
                        'sender_id' => auth()->id(),
                        'title' => $data['title'],
                        'message' => $data['message'],
                        'sender' => Notification::$AdminSender,
                        'type' => 'single',
                        'created_at' => time()
                    ]);
                    if (!empty($user->email) and env('APP_ENV') == 'production') {
                        $name = $user->student ? $user->student->ar_name : $user->fullname;
                        Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message'], 'name' => $name]));
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'msg' => 'Notification sent successfuly'
            ]);
        }

        abort(404);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = Bundle::select('id')
            ->whereTranslationLike('title', "%$term%")->orWhere('slug', 'like', "%$term%");

        $bundles = $query->get();
        return response()->json($bundles, 200);
    }

    public function statistics($url_name, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        removeContentLocale();

        $query = Bundle::query();

        $totalBundles = $query->count();
        $totalPendingBundles = deepClone($query)->where('bundles.status', Bundle::$pending)->count();
        $totalSales = deepClone($query)->join('sales', 'bundles.id', '=', 'sales.bundle_id')
            ->select(DB::raw('count(sales.bundle_id) as sales_count, sum(total_amount) as total_amount'))
            ->whereNotNull('sales.bundle_id')
            ->whereNull('sales.refund_at')
            ->first();

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $batches = StudyClass::get();

        $query = $this->handleFilters($query, $request)
            ->with([
                'category',
                'batch',
                'bundleSales',
                'directRegister',
                'scholarshipSales',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->withCount(['bundleWebinars']);

        $perPage = $request->get('per_page', 10);
        $bundles = $query->paginate($perPage);

        $bundlesData = [];
        foreach ($bundles as $bundle) {
            $giftsIds = Gift::query()->where('bundle_id', $bundle->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $sales = Sale::query()
                ->where(function ($query) use ($bundle, $giftsIds) {
                    $query->where('bundle_id', $bundle->id);
                    $query->orWhereIn('gift_id', $giftsIds);
                })
                ->whereNull('refund_at')
                ->get();

            $bundlesData[] = [
                'id' => $bundle->id,
                'title' => $bundle->title,
                'category_title' => $bundle->category->title,
                'batch_title' => $bundle->batch->title ?? '--',
                'form_fee_sales_count' => $bundle->formFeeSales()->count(),
                'bundle_sales_count' => $bundle->bundleSales->count(),
                'direct_register_count' => $bundle->directRegister->count(),
                'scholarship_sales_count' => $bundle->scholarshipSales->count(),
            ];
        }

        $data = [
            'pagination' => [
                'current_page' => $bundles->currentPage(),
                'last_page' => $bundles->lastPage(),
                'per_page' => $bundles->perPage(),
                'total' => $bundles->total(),
            ],
            'bundles' => $bundlesData,
            'totalBundles' => $totalBundles,
            'totalPendingBundles' => $totalPendingBundles,
            'totalSales' => $totalSales,
            'categories' => $categories,
            'batches' => $batches,
        ];

        $teacher_ids = $request->get('teacher_ids', null);
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function BundleStatisticsExportExcel(Request $request)
    {
        $query = Bundle::query();

        $query = $this->handleFilters($query, $request)
            ->with([
                'category',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->withCount(['bundleWebinars']);

        $bundles = $query->get();

        foreach ($bundles as $bundle) {
            $giftsIds = \App\Models\Gift::query()->where('bundle_id', $bundle->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date')->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $sales = \App\Models\Sale::query()
                ->where(function ($query) use ($bundle, $giftsIds) {
                    $query->where('bundle_id', $bundle->id)
                        ->orWhereIn('gift_id', $giftsIds);
                })
                ->whereNull('refund_at')
                ->get();

            $bundle->sales = $sales;
        }

        return Excel::download(new BundleStatisticsExport($bundles), 'bundle_statistics.xlsx');
    }

    public function exportBundleUserCodes($url_name, $bundleId)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $bundle = Bundle::with([
            'formFeeSales2.buyer',
            'bundleSales.buyer',
            'directRegister.student.registeredUser',
            'scholarshipSales.buyer',
        ])->findOrFail($bundleId);

        return Excel::download(new SingleBundleUserCodesExport($bundle), 'bundle_user_codes_' . $bundle->id . '.xlsx');
    }

    public function groups(Request $request, Bundle $bundle, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = $bundle->groups->unique();
        $totalGroups = deepClone($query)->count();


        $query = (new UserController())->filters($query, $request);

        if ($is_export_excel) {
            $groups = $query->orderBy('created_at', 'desc')->get();
        } else {
            $groups = $query;
        }

        if ($is_export_excel) {
            return $groups;
        }


        $category = Category::where('parent_id', '!=', null)->get();

        $data = [
            'pageTitle' => trans('public.students'),
            'groups' => $groups,
            'item' => $bundle,
            'category' => $category,
            'totalGroups' => $totalGroups,

        ];

        return view('admin.students.courses', $data);
    }
}
