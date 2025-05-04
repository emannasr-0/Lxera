<?php


namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\BundleResource;
use App\Http\Resources\WebinarResource;
use App\Models\Api\Sale;
use App\Models\Api\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarPartnerTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Gift;

class WebinarsController extends Controller
{
    public function show($id)
    {
        $user = apiAuth();
        $webinar = Webinar::where('id', $id)
            ->with([
                'quizzes' => function ($query) {
                    $query->where('status', 'active')
                        ->with(['quizResults', 'quizQuestions']);
                },
                'tags',
                'prerequisites' => function ($query) {
                    $query->with(['prerequisiteWebinar' => function ($query) {
                        $query->with(['teacher' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        }]);
                    }]);
                    $query->orderBy('order', 'asc');
                },
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'webinarExtraDescription' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'chapters' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive);
                    $query->orderBy('order', 'asc');

                    $query->with([
                        'chapterItems' => function ($query) {
                            $query->orderBy('order', 'asc');
                        }
                    ]);
                },
                'files' => function ($query) use ($user) {
                    $query->join('webinar_chapters', 'webinar_chapters.id', '=', 'files.chapter_id')
                        ->select('files.*', DB::raw('webinar_chapters.order as chapterOrder'))
                        ->where('files.status', WebinarChapter::$chapterActive)
                        ->orderBy('chapterOrder', 'asc')
                        ->orderBy('files.order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'textLessons' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->withCount(['attachments'])
                        ->orderBy('order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'sessions' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'assignments' => function ($query) {
                    $query->where('status', WebinarChapter::$chapterActive);
                },
                'tickets' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'filterOptions',
                'category',
                //    'teacher',
                'reviews' => function ($query) {
                    $query->where('status', 'active');
                    $query->with([
                        'comments' => function ($query) {
                            $query->where('status', 'active');
                        },
                        'creator' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        }
                    ]);
                },
                'comments' => function ($query) {
                    $query->where('status', 'active');
                    $query->whereNull('reply_id');
                    $query->with([
                        'user' => function ($query) {
                            $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                        },
                        'replies' => function ($query) {
                            $query->where('status', 'active');
                            $query->with([
                                'user' => function ($query) {
                                    $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                                }
                            ]);
                        }
                    ]);
                    $query->orderBy('created_at', 'desc');
                },
            ])
            ->withCount([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                },
                'noticeboards'
            ])
            ->where('status', 'active')
            ->first();
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [$webinar]);
    }

    public function list(Request $request, $id = null)
    {
        return [
            'my_classes' => $this->myClasses($request),
            'purchases' => $this->purchases($request),
            'organizations' => $this->organizations($request),
            'invitations' => $this->invitations($request),
        ];
    }

    public function myClasses(Request $request)
    {
        $user = apiAuth();

        $webinars = Webinar::where(function ($query) use ($user) {

            if ($user->isTeacher()) {
                $query->where('teacher_id', $user->id);
            } elseif ($user->isOrganization()) {
                $query->where('creator_id', $user->id);
            }
        })->handleFilters()->orderBy('updated_at', 'desc')->get()->map(function ($webinar) {
            return $webinar->brief;
        });

        return $webinars;
    }

    public function indexPurchases()
    {
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $this->purchases());
    }

    public function free(Request $request, $id)
    {
        $user = apiAuth();

        $course = Webinar::where('id', $id)
            ->where('status', 'active')
            ->first();
        abort_unless($course, 404);


        $checkCourseForSale = $course->checkCourseForSale($user);

        if ($checkCourseForSale != 'ok') {
            return apiResponse2(0, $checkCourseForSale, trans('api.course.purchase.' . $checkCourseForSale));
        }

        if (!empty($course->price) and $course->price > 0) {
            return apiResponse2(0, 'not_free', trans('api.cart.not_free'));


        }

        Sale::create([
            'buyer_id' => $user->id,
            'seller_id' => $course->creator_id,
            'webinar_id' => $course->id,
            'type' => Sale::$webinar,
            'payment_method' => Sale::$credit,
            'amount' => 0,
            'total_amount' => 0,
            'created_at' => time(),
        ]);

        return apiResponse2(1, 'enrolled', trans('api.webinar.enrolled'));

    }

    // public function purchases()
    // {
    //     $user = auth("api")->user();
    //     $webinarIds = $user->getPurchasedCoursesIds();

    //     $webinars = Sale::where('sales.buyer_id', $user->id)
    //         ->whereNull('sales.refund_at')
    //         ->where('access_to_purchased_item', true)
    //         ->where(function ($query) {
    //             $query->where(function ($query) {
    //                 $query->whereNotNull('sales.webinar_id')
    //                     ->where('sales.type', 'webinar')
    //                     ->whereHas('webinar');
    //             });
    //             // $query->orWhere(function ($query) {
    //             //     $query->whereNotNull('sales.bundle_id')
    //             //         ->where('sales.type', 'bundle')
    //             //         ->whereHas('bundle', function ($query) {
    //             //             $query->where('status', 'active');
    //             //         });
    //             // });
    //         })->with([
    //             'webinar' => function ($query) {
    //                 $query->with([
    //                     'files',
    //                     'reviews' => function ($query) {
    //                         $query->where('status', 'active');
    //                     },
    //                     'category',
    //                     'teacher' => function ($query) {
    //                         $query->select('id', 'full_name');
    //                     },
    //                 ]);
    //                 $query->withCount([
    //                     'sales' => function ($query) {
    //                         $query->whereNull('refund_at');
    //                     }
    //                 ]);
    //             },
    //             // 'bundle' => function ($query) {
    //             //     $query->with([
    //             //         'reviews' => function ($query) {
    //             //             $query->where('status', 'active');
    //             //         },
    //             //         'category',
    //             //         'teacher' => function ($query) {
    //             //             $query->select('id', 'full_name');
    //             //         },
    //             //     ]);
    //             // }
    //         ])
    //         //  ->handleFilters()
    //         ->orderBy('created_at', 'desc')
    //         ->get()->map(function ($sale) {
    //             return ($sale->webinar) ? new WebinarResource($sale->webinar) : new BundleResource($sale->bundle);
    //         });

    //     return $webinars;

    // }

    public function invitations(Request $request)
    {
        $user = apiAuth();

        $invitedWebinarIds = WebinarPartnerTeacher::where('teacher_id', $user->id)->pluck('webinar_id')->toArray();
        $webinars = Webinar::where('status', 'active')
            ->whereIn('id', $invitedWebinarIds)
            ->handleFilters()
            ->orderBy('updated_at', 'desc')->get()->map(function ($webinar) {
                return $webinar->brief;
            });

        return $webinars;
    }

    public function organizations()
    {
        $user = apiAuth();
        // $user=User::find(927) ;

        $webinars = Webinar::where('creator_id', $user->organ_id)
            ->where('status', 'active')->handleFilters()
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')->get()->map(function ($webinar) {
                return $webinar->brief;
            });

        return $webinars;
    }

    public function indexOrganizations()
    {

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
            [
                'webinars' => $this->organizations()
            ]);

    }


    public function purchases()
    {
        $user = auth("api")->user();
        $webinarIds = $user->getPurchasedCoursesIds();

        $giftsIds = Gift::query()->where('email', $user->email)
            ->where('status', 'active')
            ->whereNull('product_id')
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->pluck('id')
            ->toArray();

        $query = Sale::query()
            ->where(function ($query) use ($user, $giftsIds) {
                $query->where('sales.buyer_id', $user->id)
                    ->orWhereIn('sales.gift_id', $giftsIds);
            })
            ->whereNull('sales.refund_at')
            ->where('access_to_purchased_item', true)
            ->where(function ($query) {
                $query->whereNotNull('sales.webinar_id')
                ->where('sales.type', 'webinar')
                ->whereHas('webinar');
            })
            ->distinct()
            ->select('sales.webinar_id');

        $sales = deepClone($query)
            ->with([
                'webinar' => function ($query) {
                    $query->with([
                        'files',
                        'reviews' => function ($query) {
                            $query->where('status', 'active');
                        },
                        'category',
                        'teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                    ]);
                    $query->withCount([
                        'sales' => function ($query) {
                            $query->whereNull('refund_at');
                        }
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();


        $time = time();

        $giftDurations = 0;
        $giftUpcoming = 0;
        $giftPurchasedCount = 0;

        foreach ($sales as $sale) {
            if (!empty($sale->gift_id)) {
                $gift = $sale->gift;

                $sale->webinar_id = $gift->webinar_id;
                $sale->webinar = !empty($gift->webinar_id) ? $gift->webinar : null;
                $sale->gift_recipient = !empty($gift->receipt) ? $gift->receipt->full_name : $gift->name;
                $sale->gift_sender = $sale->buyer->full_name;
                $sale->gift_date = $gift->date;;

                $giftPurchasedCount += 1;

                if (!empty($sale->webinar)) {
                    $giftDurations += $sale->webinar->duration;

                    if ($sale->webinar->start_date > $time) {
                        $giftUpcoming += 1;
                    }
                }
            }
        }

        $purchasedCount = deepClone($query)
            ->where(function ($query) {
                $query->whereHas('webinar');
            })
            ->count();

        $webinarsHours = deepClone($query)->join('webinars', 'webinars.id', 'sales.webinar_id')
        ->select(DB::raw('sum(webinars.duration) as duration'))
        ->sum('duration');


        $hours = $webinarsHours + $giftDurations;

        $upComing = deepClone($query)->join('webinars', 'webinars.id', 'sales.webinar_id')
        ->where('webinars.start_date', '>', $time)
            ->count();


        $webinars = $sales->map(
            function ($sale) {
                return ($sale->webinar) ? new WebinarResource($sale->webinar) : new BundleResource($sale->bundle);
            });

        $data = [
            'pageTitle' => trans('webinars.webinars_purchases_page_title'),
            // 'sales' => $sales,
            'webinars' => $webinars,
            'purchasedCount' => $purchasedCount + $giftPurchasedCount,
            'hours' => $hours,
            'upComing' => $upComing + $giftUpcoming
        ];

        return $data;
        // return apiResponse2(1, 200, 'All purchases webinars are retrieved successfully', $data);
    }


}
