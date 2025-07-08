<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Api\Controller;
use App\Models\Api\Organization;
use App\Models\Notification;
use App\Models\NotificationStatus;
use Illuminate\Http\Request;

use function App\Http\Controllers\Api\Panel\sendError;

class NotificationsController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->input('status');
        if ($status == 'unread') {
            $notifications = $this->unRead();
        } elseif ($status == 'read') {
            $notifications = $this->read();
        } else {
            $notifications = $this->all();
        }
        $notifications = self::brief($notifications);
        return sendResponse($notifications, trans('public.retrieved'));
    }

    public static function brief($notifications)
    {
        $notifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'status' => ($notification->notificationStatus) ? 'read' : 'unread',
                'created_at' => dateTimeFormat($notification->created_at, 'j M Y - H:i')
            ];
        });
        return [
            'count' => count($notifications),
            'notifications' => $notifications,
        ];
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
