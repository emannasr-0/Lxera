<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Api\Controller;
use App\Models\Api\Organization;
use App\Models\Notification;
use App\Models\NotificationStatus;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use function App\Http\Controllers\Api\Panel\sendError;

class NotificationsController extends Controller
{
    public function list(Request $request)
{
    $user   = apiAuth(); // or auth()->user()
    $status = $request->input('status');          // unread | read | null
    $search = $request->input('search');          // search in title
    $limit  = (int) $request->input('limit', 15); // default 15 per page

    // ----- Base query for this user -----
    $query = Notification::where('user_id', $user->id);

    // Filter by status
    if ($status === 'unread') {
        // adjust to your logic for unread
        $query->whereDoesntHave('notificationStatus');
    } elseif ($status === 'read') {
        // adjust to your logic for read
        $query->whereHas('notificationStatus');
    }

    // Search by title (optional)
    if (!empty($search)) {
        $query->where('title', 'like', '%' . $search . '%');
    }

    // Order newest first
    $query->orderBy('created_at', 'desc');

    // ----- Unread count (independent from pagination & search) -----
    $unreadCount = Notification::where('user_id', $user->id)
        ->whereDoesntHave('notificationStatus')   // same unread logic
        ->count();

    // ----- Paginate -----
    $paginator = $query->paginate($limit);

    // Use your brief() to format notifications (it now accepts paginator)
    $data = self::brief($paginator);

    // Add unread_count to response
    $data['unread_count'] = $unreadCount;

    return sendResponse($data, trans('public.retrieved'));
} 

   public static function brief($notifications)
{
    // If it's paginator, extract collection + build meta
    if ($notifications instanceof LengthAwarePaginator || $notifications instanceof Paginator) {
        $collection = $notifications->getCollection();

        $pagination = [
            'current_page' => $notifications->currentPage(),
            'per_page'     => $notifications->perPage(),
            'total'        => $notifications->total(),
            'last_page'    => $notifications->lastPage(),
        ];
    } else {
        // Normal collection/array
        $collection = $notifications instanceof Collection
            ? $notifications
            : collect($notifications);

        $pagination = null;
    }

    $mapped = $collection->map(function ($notification) {
        return [
            'id'         => $notification->id,
            'title'      => $notification->title,
            'message'    => $notification->message,
            'type'       => $notification->type,
            'status'     => ($notification->notificationStatus) ? 'read' : 'unread',
            'created_at' => dateTimeFormat($notification->created_at, 'j M Y - H:i'),
        ];
    });

    $result = [
        // count of items in this page
        'count'         => $mapped->count(),
        'notifications' => $mapped->values(),
    ];

    if ($pagination) {
        $result['pagination'] = $pagination;
    }

    return $result;
}

    public function seen($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $user = apiAuth();

        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $alreadySeen = NotificationStatus::where('user_id', $user->id)
            ->where('notification_id', $notification->id)
            ->exists();

        if ($alreadySeen) {
            return response()->json(['message' => 'Notification already seen']);
        }

        NotificationStatus::create([
            'user_id' => $user->id,
            'notification_id' => $notification->id,
            'seen_at' => time(),
        ]);

        return response()->json(['message' => 'Notification marked as seen successfully']);
    }

    public function unRead()
    {
        $user = apiAuth();
        $unReadNotifications = $user->getUnReadNotifications();
        return $unReadNotifications;
    }

    public function read()
    {
        return $this->all()->diff($this->unRead());
    }

    public function all()
    {
        $user = apiAuth();
        $notifications = Notification::where(function ($query) use ($user) {
            $query->where('notifications.user_id', $user->id)
                ->where('notifications.type', 'single');
        })->orWhere(function ($query) use ($user) {
            if (!$user->isAdmin()) {
                $query->whereNull('notifications.user_id')
                    ->whereNull('notifications.group_id')
                    ->where('notifications.type', 'all_users');
            }
        });

        $userGroup = $user->userGroup()->first();
        if (!empty($userGroup)) {
            $notifications->orWhere(function ($query) use ($userGroup) {
                $query->where('notifications.group_id', $userGroup->group_id)
                    ->where('notifications.type', 'group');
            });
        }

        $notifications->orWhere(function ($query) use ($user) {
            $query->whereNull('notifications.user_id')
                ->whereNull('notifications.group_id')
                ->where(function ($query) use ($user) {
                    if ($user->isUser()) {
                        $query->where('notifications.type', 'students');
                    } elseif ($user->isTeacher()) {
                        $query->where('notifications.type', 'instructors');
                    } elseif ($user->isOrganization()) {
                        $query->where('notifications.type', 'organizations');
                    }
                });
        });

        $notifications = $notifications->orderBy('notifications.created_at', 'DESC')->get();
        return $notifications;
        /*$notifications = $notifications->leftJoin('notifications_status', 'notifications.id', '=', 'notifications_status.notification_id')
            ->selectRaw('notifications.*, count(notifications_status.notification_id) AS `count`')
            ->groupBy('notifications.id')
            ->orderBy('count', 'asc')
            ->orderBy('notifications.created_at', 'DESC')
            ->get();*/
    }
}
