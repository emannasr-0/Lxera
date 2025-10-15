<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\SaleResource;
use App\Http\Resources\UserResource;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\Api\Bundle;
use App\Models\Api\Certificate;
use App\Models\Api\Organization;
use App\Models\Comment;
use App\Models\Gift;
use App\Models\Meeting;
use App\Models\ReserveMeeting;
use App\Models\Sale;
use App\Models\Support;
use App\Models\Webinar;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{
    public function dashboard(Request $request, $url_name)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $bundlesCount = Bundle::count();
        $activeWebinarsCount = Webinar::where('status', 'active')->count();
        $pendingWebinarsCount = Webinar::where('status', 'pending')->count();
        $inactiveWebinarsCount = Webinar::where('status', 'inactive')->count();
        $activeBundlesCount = Bundle::where('status', 'active')->count();
        $pendingBundlesCount = Bundle::where('status', 'pending')->count();
        $inactiveBundlesCount = Bundle::where('status', 'inactive')->count();
        $webinarsCount = Webinar::count();
        $usersCount = User::count();
        $certificatesCount = Certificate::count();

        $webinarActivePercentage  = $webinarsCount > 0 ? round(($activeWebinarsCount / $webinarsCount) * 100, 2) : 0;
        $webinarPendingPercentage  = $webinarsCount > 0 ? round(($pendingWebinarsCount / $webinarsCount) * 100, 2) : 0;
        $webinarInactivePercentage  = $webinarsCount > 0 ? round(($inactiveWebinarsCount / $webinarsCount) * 100, 2) : 0;

        $bundleActivePercentage   = $bundlesCount > 0 ? round(($activeBundlesCount / $bundlesCount) * 100, 2) : 0;
        $bundlePendingPercentage   = $bundlesCount > 0 ? round(($pendingBundlesCount / $bundlesCount) * 100, 2) : 0;
        $bundleInactivePercentage   = $bundlesCount > 0 ? round(($inactiveBundlesCount / $bundlesCount) * 100, 2) : 0;


        return response()->json([
            'status' => 'success',
            'message' => 'Organization statistics retrieved successfully',
            'data' => [
                'total_users' => $usersCount,
                'total_webinars' => $webinarsCount,
                'total_active_webinars' => $activeWebinarsCount,
                'active_webinars_percentage' => $webinarActivePercentage,
                'pending_webinars_percentage' => $webinarPendingPercentage,
                'inactive_webinars_percentage' => $webinarInactivePercentage,
                'total_bundles' => $bundlesCount,
                'total_active_bundles' => $activeBundlesCount,
                'active_bundles_percentage' => $bundleActivePercentage,
                'pending_bundles_percentage' => $bundlePendingPercentage,
                'inactive_bundles_percentage' => $bundleInactivePercentage,
                'total_certificates' => $certificatesCount,

            ]
        ], 200);

        //     $nextBadge = $user->getBadges(true, true);

        //     $data = [
        //         'pageTitle' => trans('panel.dashboard'),
        //         'nextBadge' => $nextBadge,
        //         'authUser' => UserResource::make($user)
        //     ];

        //     if (!$user->isUser()) {
        //         $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id')->toArray();
        //         $pendingAppointments = ReserveMeeting::whereIn('meeting_id', $meetingIds)
        //             ->whereHas('sale')
        //             ->where('status', ReserveMeeting::$pending)
        //             ->count();

        //         $userWebinarsIds = $user->webinars->pluck('id')->toArray();
        //         $supports = Support::whereIn('webinar_id', $userWebinarsIds)->where('status', 'open')->get();

        //         $comments = Comment::whereIn('webinar_id', $userWebinarsIds)
        //             ->where('status', 'active')
        //             ->whereNull('viewed_at')
        //             ->get();

        //         $time = time();
        //         $firstDayMonth = strtotime(date('Y-m-01', $time)); // First day of the month.
        //         $lastDayMonth = strtotime(date('Y-m-t', $time)); // Last day of the month.

        //         $monthlySales = Sale::where('seller_id', $user->id)
        //             ->whereNull('refund_at')
        //             ->whereBetween('created_at', [$firstDayMonth, $lastDayMonth])
        //             ->get();

        //         $data['pendingAppointments'] = $pendingAppointments;
        //         $data['supportsCount'] = count($supports);
        //         $data['commentsCount'] = count($comments);
        //         $data['monthlySalesCount'] = count($monthlySales) ? $monthlySales->sum('total_amount') : 0;
        //         $data['monthlyChart'] = $this->getMonthlySalesOrPurchase($user);
        //     } else {
        //         $webinarsIds = $user->getPurchasedCoursesIds();

        //         $webinars = Webinar::whereIn('id', $webinarsIds)
        //             ->where('status', 'active')
        //             ->get();

        //         $reserveMeetings = ReserveMeeting::where('user_id', $user->id)
        //             ->whereHas('sale', function ($query) {
        //                 $query->whereNull('refund_at');
        //             })
        //             ->where('status', ReserveMeeting::$open)
        //             ->get();

        //         $supports = Support::where('user_id', $user->id)
        //             ->whereNotNull('webinar_id')
        //             ->where('status', 'open')
        //             ->get();

        //         $comments = Comment::where('user_id', $user->id)
        //             ->whereNotNull('webinar_id')
        //             ->where('status', 'active')
        //             ->get();


        //         $bundleSales = Sale::where('buyer_id', $user->id)
        //             ->where(function ($query) {
        //                 $query->whereIn('type', ['bundle', 'installment_payment'])
        //                     ->whereNotNull(['bundle_id', 'order_id']);
        //             })->get()->unique('bundle_id');

        //         $webinarSales = Sale::where('buyer_id', $user->id)
        //             ->Where(function ($query) use ($user) {
        //                 $query->where('buyer_id', $user->id)
        //                     ->where('type', 'webinar')
        //                     ->whereNull('bundle_id')
        //                     ->whereNotNull('order_id');
        //             })
        //             ->get()
        //             ->unique('webinar_id');

        //         // Merge both collections
        //         // $bundleSales = $bundleSales->merge($webinarSales);

        //         $data['monthlyChart'] = $this->getMonthlySalesOrPurchase($user);
        //         $data['webinars'] = $webinars;
        //         $data['bundleSales'] = SaleResource::collection($bundleSales);
        //         $data['webinarSales'] = SaleResource::collection($webinarSales);
        //     }

        //     $data['giftModal'] = $this->showGiftModal($user);

        //     return apiResponse2(1, 'retrived_all', "dashboard", $data);
    }

    private function showGiftModal($user)
    {
        $gift = Gift::query()->where('email', $user->email)
            ->where('status', 'active')
            ->where('viewed', false)
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->first();

        if (!empty($gift)) {
            $gift->update([
                'viewed' => true
            ]);

            $data = [
                'gift' => $gift
            ];

            $result = (string)view()->make('web.default.panel.dashboard.gift_modal', $data);
            $result = str_replace(array("\r\n", "\n", "  "), '', $result);

            return $result;
        }

        return null;
    }

    private function getMonthlySalesOrPurchase($user)
    {
        $months = [];
        $data = [];

        // all 12 months
        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create(date('Y'), $month);

            $start_date = $date->timestamp;
            $end_date = $date->copy()->endOfMonth()->timestamp;

            $months[] = trans('panel.month_' . $month);

            if (!$user->isUser()) {
                $monthlySales = Sale::where('seller_id', $user->id)
                    ->whereNull('refund_at')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->sum('total_amount');

                $data[] = round($monthlySales, 2);
            } else {
                $monthlyPurchase = Sale::where('buyer_id', $user->id)
                    ->whereNull('refund_at')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->count();

                $data[] = $monthlyPurchase;
            }
        }

        return [
            'months' => $months,
            'data' => $data
        ];
    }

    public function impersonate(Request $request, $url_name, $user_id)
    {
        $this->authorize('admin_users_impersonate');
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $user = User::findOrFail($user_id);

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot impersonate another admin.'], 403);
        }

        session()->put(['impersonated' => $user->id]);

        // Optional: issue a new token for impersonated user (if using token auth like Sanctum or Passport)
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Now impersonating user.',
            'token' => $token,
            'redirect' => $user->isUser() ? url('/panel') : url('/')
        ]);
    }

    public function stopImpersonate()
    {
        session()->forget('impersonated');
        return response()->json(['message' => 'Impersonation ended.']);
    }
}
