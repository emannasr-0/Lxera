<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Models\Api\User;
use App\Models\Bundle;
use App\Models\Countries;
use App\Models\PortalMeetingsSlots;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;
use App\Models\Portal;
use App\Models\ConsultationBundleWebinar;
use App\Models\ZoomConsultants;
use Illuminate\Support\Facades\Validator;

class ConsultationController extends Controller
{

    public function webinars()
    {
        $webinars = Webinar::where('type', 'webinar')->where('status', 'active')->select('id')->get()
            ->map(function ($webinar) {
                $webinar->webinar_id = 'webinar_' . $webinar->id;
                return $webinar;
            });


        return response()->json([
            'webinars' => $webinars
        ]);
    }
    public function bundles()
    {
        $bundles = Bundle::where('type', 'program')->where('status', 'active')->select('id')->get()
            ->map(function ($bundle) {
                $bundle->bundle_id = 'bundle_' . $bundle->id;
                return $bundle;
            });;
        return response()->json([
            'bundles' => $bundles
        ]);
    }

    public function meeting_times()
    {
        $meeting_times = PortalMeetingsSlots::where('active', 1)->get();
        return response()->json([
            'times' => $meeting_times
        ]);
    }


    public function timezones()
    {

        $countries = Countries::select('timezone')->get();

        return response()->json([
            'timezones' => $countries,
        ]);
    }


    public function consultation_post(Request $request)
    {
        $meeting_time = PortalMeetingsSlots::findorFail($request->meeting_time);

        // $validated = $request->validate(
        //     [
        //         'name' => 'required|min:3',
        //         'email' => 'required|min:5|email|unique:portals,email',
        //         // 'bussiness_name'=>'required',
        //         'meeting_time' => 'required',
        //         'timezone' => 'required',
        //         'phone' => 'required|unique:portals,phone',
        //         'items' => 'required|array',
        //         'items.*' => 'string',
        //         // 'address'=>'required',

        //     ]
        // );
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|min:5|email',
            // 'bussiness_name'=>'required',
            'meeting_time' => 'required',
            'timezone' => 'required',
            'phone' => 'required',
            'items' => 'required|array',
            'items.*' => 'string',
            // 'address'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Extract the validated data
        $validated = $validator->validated();

        $msg = 'تم تسجيل طلب الانضمام , سيتم التواصل معك لتحديد البرامج الدراسية و بيانات الدخول الخاصة بك';

        Session::put('zoom_meeting_name', '[ Anas Academy - ' . $request->name . ' ]');

        $meeting_time = PortalMeetingsSlots::find($request->meeting_time);
        $day = $meeting_time['day']; // 'Tuesday'
        $time = $meeting_time['start_time']; // '10:05 AM'

        $time24HourWithSeconds = Carbon::createFromFormat('h:i A', $time)->format('H:i:00');
        $currentDate = Carbon::now();
        $nextDay = $currentDate->isToday() && $currentDate->format('l') === $day
            ? $currentDate
            : $currentDate->next($day);

        $localDateTime = $nextDay->format('Y-m-d') . ' ' . $time24HourWithSeconds;

        // Convert the local datetime to UTC
        $utcDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $localDateTime, $request->timezone)
            ->setTimezone('UTC')
            ->format('Y-m-d\TH:i:s\Z');

        Session::put('zoom_meeting_time', $utcDateTime);


        $client = new Client();

        $client_id = env('ZOOM_CLIENT_KEY');
        $client_secret = env('ZOOM_CLIENT_SECRET');
        $account_id = env('ZOOM_ACCOUNT_ID');

        $response = $client->post('https://zoom.us/oauth/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("$client_id:$client_secret"),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'account_credentials',
                'account_id' => $account_id,
            ],
        ]);

        $tokenData = json_decode($response->getBody(), true);

        $response = $client->post('https://api.zoom.us/v2/users/me/meetings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenData['access_token'],
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'topic'      => Session::get('zoom_meeting_name'),        // Meeting topic
                'type'       => 2,                    // Scheduled meeting
                'start_time' => Session::get('zoom_meeting_time'), // ISO 8601 format
                'duration'   => 60,                   // Meeting duration in minutes
                'timezone'   => $request->timezone,         // Timezone
                // 'password'   => '12345678',           // Optional: Meeting password
                'settings'   => [
                    'join_before_host'  => false,     // Join before host
                    'host_video'        => true,      // Enable host video
                    'participant_video' => true,      // Enable participant video
                    'mute_upon_entry'   => false,     // Mute participants on entry
                    'waiting_room'      => false,     // Enable waiting room
                    'approval_type'     => 0,         // Automatically approve participants
                ],
            ],
        ]);

        $meeting = json_decode($response->getBody(), true);
        // dd($meeting);
        // echo 'Meeting Created! Meeting ID: ' . $meeting['id'];
        // echo 'Join URL: ' . $meeting['join_url'];
        $msg = 'تم تسجيل طلب الاستشارة الخاص بك , سيصلك بريد الكترونى بالتفاصيل';

        $zoom_meeting_url = $meeting['start_url'];
        $zoom_meeting_id = $meeting['id'];
        //send email with zoom details 
        $generalSettings = getGeneralSettings();
        $emailData = [
            'zoom_meeting_url' => $zoom_meeting_url,
            'zoom_meeting_password' => $meeting['password'],
            'zoom_meeting_start_time' => $localDateTime,
            'zoom_meeting_time_zone' => $meeting['timezone'],
            'generalSettings' => $generalSettings,
            'email' => $request->input('email')
        ];
        Mail::send('web.default.auth.zoom_meeting', $emailData, function ($message) use ($request) {
            $message->from(!empty($generalSettings['site_email']) ? $generalSettings['site_email'] : env('MAIL_FROM_ADDRESS'));
            $message->to($request->input('email'));
            $message->subject('Anas Academy Consultation Meeting');
        });

        //
        $portal = Portal::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'bussiness_name' => $request->bussiness_name,
            'meeting_time' => $request->meeting_time,
            'zoom_meeting_id' => $meeting['id']

        ]);

        foreach ($validated['items'] as $item) {

            if (str_starts_with($item, 'webinar_')) {
                $type = 'webinar';
                $id = (int) str_replace('webinar_', '', $item);
            } elseif (str_starts_with($item, 'bundle_')) {
                $type = 'bundle';
                $id = (int) str_replace('bundle_', '', $item);
            } else {
                continue;
            }

            ConsultationBundleWebinar::create([
                'type' => $type,
                'program_id' => $id,
                'portal_id' => $portal->id
            ]);
        }

        // Initialize an array to store user IDs
        $userIds = [];

        // Loop through all selected items
        foreach ($validated['items'] as $item) {
            if (str_starts_with($item, 'webinar_')) {
                $type = 'webinar';
                $id = (int) str_replace('webinar_', '', $item);
            } elseif (str_starts_with($item, 'bundle_')) {
                $type = 'bundle';
                $id = (int) str_replace('bundle_', '', $item);
            } else {
                continue;
            }

            // Fetch unique user_ids for the current program type and ID
            $programUserIds = ZoomConsultants::where('type', $type)
                ->where('program_id', $id)
                ->distinct('user_id')
                ->pluck('user_id')
                ->toArray();

            // Merge the fetched user IDs into the main array
            $userIds = array_merge($userIds, $programUserIds);
        }

        // Remove duplicate user IDs
        $userIds = array_unique($userIds);

        // Fetch user details for all unique user IDs
        $users = User::whereIn('id', $userIds)->get();

        if ($users->isNotEmpty()) {
            foreach ($users as $user) {
                Mail::send('web.default.auth.zoom_meeting', $emailData, function ($message) use ($user, $generalSettings) {
                    $message->from(!empty($generalSettings['site_email']) ? $generalSettings['site_email'] : env('MAIL_FROM_ADDRESS'));
                    $message->to($user->email);
                    $message->subject('Academa Consultation Meeting');
                });
            }
        }

        return response()->json([
            'data' => "تم تسجيل الاستشارة بنجاح , سيصلك بريد الكترونى بتفاصيل ميعاد الاستشارة"
        ]);
    }
}
