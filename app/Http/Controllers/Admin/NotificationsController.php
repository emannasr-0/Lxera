<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SendNotifications;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Notification;
use App\Models\NotificationStatus;
use App\Models\Role;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotificationsController extends Controller
{
    public function index()
    {
        $this->authorize('admin_notifications_list');

        $notifications = Notification::where('user_id', auth()->id())
            ->orWhere(function($query){
            $query->whereNull('user_id')->Where('type', 'all_users');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.notifications'),
            'notifications' => $notifications
        ];

        return view('admin.notifications.lists', $data);
    }

    public function posted(Request $request)
    {
        $this->authorize('admin_notifications_posted_list');
        $query = Notification::where('sender', Notification::$AdminSender);
        $notificationsQuery = $this->getNotificationFilters($query, $request);
        $notifications = $notificationsQuery
            ->orderBy('created_at', 'desc')
            ->with([
                'senderUser' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'notificationStatus'
            ])
            ->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.posted_notifications'),
            'notifications' => $notifications
        ];

        return view('admin.notifications.posted', $data);
    }

    public function create()
    {
        $this->authorize('admin_notifications_send');

        $userGroups = Group::all();

        $data = [
            'pageTitle' => trans('notification.send_notification'),
            'userGroups' => $userGroups
        ];

        return view('admin.notifications.send', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_notifications_send');

        $this->validate($request, [
            'title' => 'required|string',
            'type' => 'required|string',
            'user_id' => 'required_if:type,single',
            'group_id' => 'required_if:type,group',
            'webinar_id' => 'required_if:type,course_students',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        Notification::create([
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            'group_id' => !empty($data['group_id']) ? $data['group_id'] : null,
            'webinar_id' => !empty($data['webinar_id']) ? $data['webinar_id'] : null,
            'sender_id' => auth()->id(),
            'title' => $data['title'],
            'message' => $data['message'],
            'sender' => Notification::$AdminSender,
            'type' => $data['type'],
            'created_at' => time()
        ]);

        if (!empty($data['user_id']) and env('APP_ENV') == 'production') {
            $user = User::where('id', $data['user_id'])->first();

            if (!empty($user) and !empty($user->email)) {
                $name = $user->student ? $user->student->ar_name : $user->fullname;
                Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message'], 'name' => $name]));
            }
        }


        if (!empty($data['type']) and $data['type'] == 'all_users' and env('APP_ENV') == 'production') {
            $users = User::where('status', 'active')->get();

            foreach ($users as $user) {
                if (!empty($user) and !empty($user->email)) {
                    $name = $user->student ? $user->student->ar_name : $user->fullname;
                    Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message'], 'name' => $name]));
                }
            }
        }

        if (!empty($data['type']) and $data['type'] == 'students' and env('APP_ENV') == 'production') {
            $users = User::where('status', 'active')->whereIn('role_id', Role::$students)->get();

            foreach ($users as $user) {
                if (!empty($user) and !empty($user->email)) {
                    $name = $user->student ? $user->student->ar_name : $user->fullname;
                    Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message'], 'name' => $name]));
                }
            }
        }

        if (!empty($data['type']) and $data['type'] == 'instructors' and env('APP_ENV') == 'production') {
            $users = User::where('status', 'active')->where('role_id', Role::$teacher)->get();

            foreach ($users as $user) {
                if (!empty($user) and !empty($user->email)) {
                    $name = $user->student ? $user->student->ar_name : $user->fullname;
                    Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message'], 'name' => $name]));
                }
            }
        }

        if (!empty($data['group_id']) and env('APP_ENV') == 'production') {
            $group = Group::find($data['group_id']);
            if ($group) {
                $users = $group->students;
                foreach ($users as $user) {
                    if (!empty($user) and !empty($user->email)) {
                        $name = $user->student ? $user->student->ar_name : $user->fullname;
                        Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message'], 'name' => $name]));
                    }
                }
            }
        }

        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::find($data['webinar_id']);
            if ($webinar) {
                $users = User::whereHas('webinarSales', function ($query) use ($webinar) {
                    $query->where('webinar_id', $webinar->id);
                })->groupBy('id')->get();
                foreach ($users as $user) {
                    if (!empty($user) and !empty($user->email)) {
                        $name = $user->student ? $user->student->ar_name : $user->fullname;
                        Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message'], 'name' => $name]));
                    }
                }
            }
        }

        return redirect(getAdminPanelUrl() . '/notifications/posted');
    }

    public function edit($id)
    {
        $this->authorize('admin_notifications_edit');

        $notification = Notification::where('id', $id)
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'group'
            ])->first();

        if (!empty($notification)) {
            $userGroups = Group::all();

            $data = [
                'pageTitle' => trans('notification.edit_notification'),
                'userGroups' => $userGroups,
                'notification' => $notification
            ];

            return view('admin.notifications.send', $data);
        }

        return view('errors.404');
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_notifications_edit');

        $this->validate($request, [
            'title' => 'required|string',
            'type' => 'required|string',
            'user_id' => 'required_if:type,single',
            'group_id' => 'required_if:type,group',
            'webinar_id' => 'required_if:type,course_students',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        $notification = Notification::findOrFail($id);

        $notification->update([
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            'group_id' => !empty($data['group_id']) ? $data['group_id'] : null,
            'webinar_id' => !empty($data['webinar_id']) ? $data['webinar_id'] : null,
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'],
            'created_at' => time()
        ]);

        return redirect(getAdminPanelUrl() . '/notifications');
    }

    public function delete($id)
    {
        $this->authorize('admin_notifications_delete');

        $notification = Notification::findOrFail($id);

        $notification->delete();

        return redirect(getAdminPanelUrl() . '/notifications');
    }

    public function markAllRead()
    {
        $this->authorize('admin_notifications_markAllRead');

        $adminUser = User::find(1);

        $unreadNotifications = $adminUser->getUnReadNotifications();

        if (!empty($unreadNotifications) and !$unreadNotifications->isEmpty()) {
            foreach ($unreadNotifications as $unreadNotification) {
                NotificationStatus::updateOrCreate(
                    [
                        'user_id' => $adminUser->id,
                        'notification_id' => $unreadNotification->id,
                    ],
                    [
                        'seen_at' => time()
                    ]
                );
            }
        }

        return back();
    }

    public function markAsRead($id)
    {
        $this->authorize('admin_notifications_edit');

        $adminUser = User::find(1);

        NotificationStatus::updateOrCreate(
            [
                'user_id' => $adminUser->id,
                'notification_id' => $id,
            ],
            [
                'seen_at' => time()
            ]
        );


        return response()->json([], 200);
    }


    private function getNotificationFilters($query, $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $userName = $request->get('user_name');
        $email = $request->get('email');
        $user_code = $request->get('user_code');
        $title = $request->get('title');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($userName)) {
            $query->when($userName, function ($query) use ($userName) {
                $query->whereHas('user', function ($q) use ($userName) {
                    $q->where('full_name', 'like', "%$userName%");
                });
            });
        }

        if (!empty($email)) {
            $query->when($email, function ($query) use ($email) {
                $query->whereHas('user', function ($q) use ($email) {
                    $q->where('email', 'like', "%$email%");
                });
            });
        }
        if (!empty($user_code)) {
            $query->when($user_code, function ($query) use ($user_code) {
                $query->whereHas('user', function ($q) use ($user_code) {
                    $q->where('user_code', 'like', "%$user_code%");
                });
            });
        }


        if (!empty($title)) {
            $query->where('title', 'like', "%$title%");
        }

        return $query;
    }
}
