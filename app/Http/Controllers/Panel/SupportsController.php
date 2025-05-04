<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Mail\SendNotifications;
use App\Models\Bundle;
use App\Models\Sale;
use App\Models\Support;
use App\Models\SupportConversation;
use App\Models\SupportDepartment;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportsController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $user = auth()->user();

        $userWebinarsIds = $user->webinars->pluck('id')->toArray();
        $purchasedWebinarsIds = $user->getPurchasedCoursesIds();
        $webinarIds = array_merge($purchasedWebinarsIds, $userWebinarsIds);

         $bundlesIds = $user->purchasedBundles->pluck('bundle_id')->toArray();


        $query = Support::whereNull('department_id')
            ->where(function ($query) use ($user, $userWebinarsIds) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('webinar_id', $userWebinarsIds);
            });

        $supportsCount = deepClone($query)->count();
        $openSupportsCount = deepClone($query)->where('status', '!=', 'close')->count();
        $closeSupportsCount = deepClone($query)->where('status', 'close')->count();

        $query = $this->filters($query, $request, $userWebinarsIds);

        $supports = $query->orderBy('created_at', 'desc')
            ->orderBy('status', 'asc')
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'role_name');
                },
                'webinar' => function ($query) {
                    $query->with(['teacher' => function ($query) {
                        $query->select('id', 'full_name', 'avatar');
                    }]);
                },
                'conversations' => function ($query) {
                    $query->orderBy('created_at', 'desc')
                        ->first();
                }
            ])->get();

        $webinars = Webinar::select('id')
            ->whereIn('id', array_unique($webinarIds))
            ->where('status', 'active')
            ->get();


            $bundles = Bundle::select('id')
            ->whereIn('id', $bundlesIds)
           // ->where('status', 'active')
            ->get();

        $teacherIds = $webinars->pluck('teacher_id')->toArray();

        $teachers = User::select('id', 'full_name')
            ->where('id', '!=', $user->id)
            ->whereIn('id', array_unique($teacherIds))
            ->where('status', 'active')
            ->get();

        $studentsIds = Sale::whereIn('webinar_id', $userWebinarsIds)
            ->whereNull('refund_at')
            ->pluck('buyer_id')
            ->toArray();

        $students = [];
        if (!$user->isUser()) {
            $students = User::select('id', 'full_name')
                ->whereIn('id', array_unique($studentsIds))
                ->where('status', 'active')
                ->get();
        }

        $data = [
            'pageTitle' => trans('panel.send_new_support'),
            'supports' => $supports,
            'supportsCount' => $supportsCount,
            'openSupportsCount' => $openSupportsCount,
            'closeSupportsCount' => $closeSupportsCount,
            'purchasedWebinarsIds' => $purchasedWebinarsIds,
            'students' => $students,
            'teachers' => $teachers,
            'webinars' => $webinars,
            'bundles' => $bundles, 
        ];

        if (!empty($id) and is_numeric($id)) {
            $selectSupport = Support::where('id', $id)
                ->where(function ($query) use ($user, $userWebinarsIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $userWebinarsIds);
                })
                ->with([
                    'department',
                    'conversations' => function ($query) {
                        $query->with([
                            'sender' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar', 'role_name');
                            },
                            'supporter' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar', 'role_name');
                            }
                        ]);
                        $query->orderBy('created_at', 'asc');
                    },
                    'webinar' => function ($query) {
                        $query->with(['teacher' => function ($query) {
                            $query->select('id', 'full_name', 'avatar', 'role_name');
                        },
                        
                        
                        ]);
                    }])->first();

            if (empty($selectSupport)) {
                return back();
            }

            $data['selectSupport'] = $selectSupport;
        }

        return view(getTemplate() . '.panel.support.conversations', $data);
    }


   


    public function tickets(Request $request, $id = null)
    {
        //dd($id);
        $user = auth()->user();
    
        // Define the base query for supports (for both students and teachers)
        $query = Support::whereNotNull('department_id');
    
        // For students: restrict the query to their own tickets
        if ($user->role_name == 'student') {
            $query->where('user_id', $user->id);
        }
      
    
        // Get counts for all, open, and closed supports
        $supportsCount = deepClone($query)->count();
        $openSupportsCount = deepClone($query)->where('status', 'open')->count();
        $closeSupportsCount = deepClone($query)->where('status', 'close')->count();
    
        // Apply any additional filters if provided in the request
        $query = $this->filters($query, $request);
    
        // Fetch the supports with relationships (user, department, and conversations)
        $supports = $query->orderBy('created_at', 'desc')
            ->orderBy('status', 'asc')
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'role_name');
                },
                'department',
                'conversations' => function ($query) {
                    $query->orderBy('created_at', 'desc')
                        ->first();
                }
            ])->get();
    
        // Fetch all departments (to be used in the view for filtering or displaying)
        $departments = SupportDepartment::all();
    
        // Prepare data to pass to the view
        $data = [
            'pageTitle' => trans('panel.send_new_support'),
            'departments' => $departments,
            'supports' => $supports,
            'supportsCount' => $supportsCount,
            'openSupportsCount' => $openSupportsCount,
            'closeSupportsCount' => $closeSupportsCount,
        ];
    
        // If a specific ticket ID is provided, fetch that ticket
        if (!empty($id) && is_numeric($id)) {
            $selectSupport = Support::where('id', $id)
                ->whereNotNull('department_id')
                ->where(function ($query) use ($user) {
                    // If the user is a student, they can only see their own ticket
                    if ($user->role_name == 'student') {
                        $query->where('user_id', $user->id);
                    }
                    // If the user is a teacher, they can see tickets in their department
                    elseif ($user->role_name == 'teacher') {
                        $query->whereHas('department', function ($query) use ($user) {
                            // $query->where('teacher_id', $user->id);
                        });
                    }
                })
                ->with([
                    'department',
                    'conversations' => function ($query) {
                        $query->with([
                            'sender' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar', 'role_name');
                            },
                            'supporter' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar', 'role_name');
                            }
                        ]);
                        $query->orderBy('created_at', 'asc');
                    }
                ])->first();
    
            if (empty($selectSupport)) {
                return back();
            }
    
            // Pass the selected ticket to the view
            $data['selectSupport'] = $selectSupport;
        }
    
        // Return the view with the ticket data
        return view(getTemplate() . '.panel.support.ticket_conversations', $data);
    }
    

    private function filters($query, $request, $userWebinarsIds = [])
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $role = $request->get('role');
        $student_id = $request->get('student');
        $teacher_id = $request->get('teacher');
        $webinar_id = $request->get('webinar');
        $department = $request->get('department');
        $status = $request->get('status');
        $serial_number = $request->get('serial_number');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($serial_number)) {
            $query->where('serial_number', 'like', '%' . $serial_number . '%');  // Use LIKE to search for partial matches
        }

        if (!empty($role) and $role == 'student' and (empty($student_id) or $student_id == 'all')) {
            $studentsIds = Sale::whereIn('webinar_id', $userWebinarsIds)
                ->whereNull('refund_at')
                ->pluck('buyer_id')
                ->toArray();

            $query->whereIn('user_id', $studentsIds);
        }

        if (!empty($student_id) and $student_id != 'all') {
            $query->where('user_id', $student_id);
        }

        if (!empty($teacher_id) and $teacher_id != 'all') {
            $teacher = User::where('id', $teacher_id)
                ->where('status', 'active')
                ->first();

            $teacherWebinarIds = $teacher->webinars->pluck('id')->toArray();

            $query->whereIn('webinar_id', $teacherWebinarIds);
        }

        if (!empty($webinar_id) and $webinar_id != 'all') {
            $query->where('webinar_id', $webinar_id);
        }

        if (!empty($status) and $status != 'all') {
            $query->where('status', $status);
        }


        if (!empty($department) and $department != 'all') {
            $query->where('department_id', $department);
        }

        return $query;
    }

    public function create()
    {
        $departments = SupportDepartment::all();
        $user = auth()->user();

        $webinarIds = $user->getPurchasedCoursesIds();
       // $bundleIds = $user->getPurchasedCoursesIds();
        $bundlesIds = $user->purchasedBundles->pluck('bundle_id');

        $webinars = Webinar::select('id', 'creator_id')
            ->whereIn('id', $webinarIds)
            // ->where('support', true)
            ->with(['creator' => function ($query) {
                $query->select('id', 'full_name');
            }])->get();

            $bundles = Bundle::select('id', 'creator_id')
            ->whereIn('id', $bundlesIds)
            // ->where('support', true)
            ->with(['creator' => function ($query) {
                $query->select('id', 'full_name');
            }])->get();   


        $data = [
            'pageTitle' => trans('panel.send_new_support'),
            'departments' => $departments,
            'webinars' => $webinars,
            'bundles'=>$bundles,
        ];
      //dd($webinars);
        return view(getTemplate() . '.panel.support.new', $data);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $this->validate($request, [
            'title' => 'required|min:2',
            'type' => 'required',
            'department_id' => 'required',
            'webinar_id' => 'required_if:type,course_support|exists:webinars,id',
            'bundle_id' => 'required_if:type,bundle_support|exists:bundles,id',
            'message' => 'required|min:2',
            'attach' => 'nullable|string',
        ]);

        $data = $request->all();
        unset($data['type']);

        $now = now(); 
        $year = $now->format('Y');  
        $month = $now->format('m'); 
        $day = $now->format('d');

        $support = Support::create([
            'user_id' => $user->id,
            'department_id' => !empty($data['department_id']) ? $data['department_id'] : null,
            'webinar_id' => !empty($data['webinar_id']) ? $data['webinar_id'] : null,
            'bundle_id' => !empty($data['bundle_id']) ? $data['bundle_id'] : null,
            'title' => $data['title'],
            'status' => 'open',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $serialNumber = "SUP".$support->id . $year . $month . $day;
        $support->serial_number = $serialNumber;
        $support->save();

        SupportConversation::create([
            'support_id' => $support->id,
            'sender_id' => $user->id,
            'message' => $data['message'],
            'attach' => $data['attach'],
            'created_at' => time(),
        ]);

        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::findOrFail($data['webinar_id']);

            $notifyOptions = [
                '[c.title]' => $webinar->title,
                '[u.name]' => $user->full_name
            ];
            sendNotification('support_message', $notifyOptions, $webinar->teacher_id);
          //  dd($webinar->teacher_id);
        }

        if (!empty($data['bundle_id'])) {
            $bundle = Bundle::findOrFail($data['bundle_id']);
             
            $notifyOptions = [
                '[c.title]' => $bundle->title,
                '[u.name]' => $user->full_name,
               
            ];
            sendNotification('support_message', $notifyOptions, $bundle->teacher_id);
        }

        if (!empty($data['department_id'])) {
            $notifyOptions = [
                '[s.t.title]' => $support->title,
            ];
            sendNotification('support_message_admin', $notifyOptions, 1); // for admin
        }

        $url = '/panel/support';

        // if (!empty($data['department_id'])) {
        //     $url = '/panel/support/tickets';
        // }

        return redirect($url);
    }

    public function storeConversations(Request $request, $id)
    {
       // dd($id);
        $this->validate($request, [
            'message' => 'required|string|min:2',
        ]);

        $data = $request->all();
        $user = auth()->user();

        $userWebinarsIds = $user->webinars->pluck('id')->toArray();
     
        $bundlesIds = Bundle::where('teacher_id', $user->id)->pluck('id')->toArray();
     
          $support = Support::where('id', $id)
              ->where(function ($query) use ($user, $userWebinarsIds, $bundlesIds) {
                  $query->where('user_id', $user->id) // Check if the user is the owner
                      ->orWhereIn('webinar_id', $userWebinarsIds) // Check if the user is associated with the webinar
                      ->orWhereIn('bundle_id', $bundlesIds); // Check if the user is associated with the bundle (if the user is a teacher)
              })->first();
      
      
       
       // dd( $bundlesIds);
        if (empty($support)) {
            return view('errors.404');
        }

        $support->update([
            'status' => ($support->user_id == $user->id) ? 'open' : 'replied',
            'updated_at' => time()
        ]);

     $conversation=   SupportConversation::create([
            'support_id' => $support->id,
            'sender_id' => $user->id,
            'message' => $data['message'],
            'attach' => $data['attach'],
            'created_at' => time(),
        ]);

        if (!empty($support->webinar_id)) {
            $webinar = Webinar::findOrFail($support->webinar_id);
            $ticketurl = config('app.url') . "/panel/support/tickets/{$conversation->support_id}/conversations";
            $conversationMessage = $conversation->message;
            $notifyOptions = [
                '[c.title]' => $webinar->title,
                 '[r.btn]'=>"<a href = '$ticketurl' class='btn btn-primary'>اضغط هنا للرد علي الرسالة</a>",
                 '[c.message]' => nl2br(e($conversationMessage)), 
            ];
            sendNotification('support_message_replied', $notifyOptions, ($support->user_id == $user->id) ? $webinar->teacher_id : $user->id);
          // Mail::to($user->email)->send(new SendNotifications(['title' =>'test title' , 'message' => 'mail  body']));
 
        }


        if (!empty($support->bundle_id)) {
            $bundle = Bundle::findOrFail($support->bundle_id);
            $ticketurl = config('app.url') . "/panel/support/tickets/{$conversation->support_id}/conversations";
            $conversationMessage = $conversation->message;
            $notifyOptions = [
                '[c.title]' => $bundle->title,
                '[r.btn]'=>"<a href = '$ticketurl' class='btn btn-primary'>اضغط هنا للرد علي الرسالة</a>",
                '[c.message]' => nl2br(e($conversationMessage)), 
            ];
            sendNotification('support_message_replied', $notifyOptions, ($support->user_id == $user->id) ? $bundle->teacher_id : $user->id);
        }

        if (!empty($support->department_id)) {
            $ticketurl="config('app_url')/panel/support/tickets/$conversation->id/conversations";
            $notifyOptions = [
                '[s.t.title]' => $support->title,
                '[r.btn]'=>"<a href = '$ticketurl' class='btn btn-primary'>اضغط هنا للرد علي الرسالة</a>"
            ];
            sendNotification('support_message_replied_admin', $notifyOptions, 1); // for admin
        }

        return back();
    }

    public function close($id)
    {
        $user = auth()->user();
        $userWebinarsIds = $user->webinars->pluck('id')->toArray();

        $support = Support::where('id', $id)
            ->where(function ($query) use ($user, $userWebinarsIds) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('webinar_id', $userWebinarsIds);
            })->first();

        if (empty($support)) {
            return view('errors.404');
        }

        $support->update([
            'status' => 'close',
            'updated_at' => time()
        ]);

        return back();
    }
}
